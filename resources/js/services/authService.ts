import axios from 'axios';
import { apiService } from '../lib/api';

// Direct API URLs since the controller imports are complex
export interface User {
    id: number;
    name: string;
    email: string;
    role: string;
    clinic_id?: number;
    phone?: string;
    address?: string;
    specialization?: string;
    license_number?: string;
    date_of_birth?: string;
    gender?: string;
    profile_photo_path?: string;
    created_at?: string;
    updated_at?: string;
}

export interface AuthState {
    user: User | null;
    token: string | null;
    isAuthenticated: boolean;
    loading: boolean;
    error: string | null;
}

export interface LoginCredentials {
    email: string;
    password: string;
    device_name: string;
}

export interface RegisterData extends LoginCredentials {
    name: string;
    password_confirmation: string;
    role: 'admin' | 'doctor' | 'staff' | 'patient';
}

class AuthService {
    private static instance: AuthService;
    private state: AuthState;
    private tokenRefreshTimeout: NodeJS.Timeout | null = null;

    private constructor() {
        this.state = {
            user: null,
            token: null,
            isAuthenticated: false,
            loading: false,
            error: null,
        };
        this.initialize();
    }

    public static getInstance(): AuthService {
        if (!AuthService.instance) {
            AuthService.instance = new AuthService();
        }
        return AuthService.instance;
    }

    private initialize(): void {
        // Load token and user from localStorage
        const token = localStorage.getItem('token');
        const user = localStorage.getItem('user');

        if (token && user) {
            try {
                this.state = {
                    user: JSON.parse(user),
                    token,
                    isAuthenticated: true,
                    loading: false,
                    error: null,
                };

                // Set up axios interceptor
                this.setupAxiosInterceptor();

                // Start token refresh timer
                this.startTokenRefreshTimer();
            } catch (error) {
                console.error('Failed to initialize auth:', error);
                this.clearAuthState();
            }
        }
    }

    private setupAxiosInterceptor(): void {
        // Request interceptor
        axios.interceptors.request.use(
            (config) => {
                if (this.state.token) {
                    config.headers = config.headers || {};
                    config.headers.Authorization = `Bearer ${this.state.token}`;
                }
                return config;
            },
            (error) => {
                return Promise.reject(error);
            },
        );

        // Response interceptor
        axios.interceptors.response.use(
            (response) => response,
            (error) => {
                if (error.response && error.response.status === 401) {
                    // Token expired or invalid
                    this.clearAuthState();
                    window.location.href = '/login';
                }
                return Promise.reject(error);
            },
        );
    }

    private startTokenRefreshTimer(): void {
        // Refresh token every 50 minutes (before the typical 60-minute expiration)
        const refreshInterval = 50 * 60 * 1000; // 50 minutes in milliseconds

        this.tokenRefreshTimeout = setTimeout(async () => {
            if (this.state.isAuthenticated) {
                try {
                    await this.refreshToken();
                    // Restart the timer after successful refresh
                    this.startTokenRefreshTimer();
                } catch (error) {
                    console.error('Token refresh failed:', error);
                    this.clearAuthState();
                }
            }
        }, refreshInterval);
    }

    private clearTokenRefreshTimer(): void {
        if (this.tokenRefreshTimeout) {
            clearTimeout(this.tokenRefreshTimeout);
            this.tokenRefreshTimeout = null;
        }
    }

    public getState(): AuthState {
        return this.state;
    }

    public async login(
        credentials: LoginCredentials,
    ): Promise<{
        success: boolean;
        user?: User;
        token?: string;
        error?: string;
    }> {
        try {
            this.setLoading(true);

            const response = await apiService.post<{
                token: string;
                user: User;
            }>('/api/login', credentials);

            this.setAuthState(response.token, response.user);

            return {
                success: true,
                user: response.user,
                token: response.token,
            };
        } catch (error) {
            const errorMessage =
                error instanceof Error ? error.message : 'Login failed';
            this.setError(errorMessage);
            return {
                success: false,
                error: errorMessage,
            };
        } finally {
            this.setLoading(false);
        }
    }

    public async register(
        data: RegisterData,
    ): Promise<{
        success: boolean;
        user?: User;
        token?: string;
        error?: string;
    }> {
        try {
            this.setLoading(true);

            const response = await apiService.post<{
                token: string;
                user: User;
            }>('/api/register', data);

            this.setAuthState(response.token, response.user);

            return {
                success: true,
                user: response.user,
                token: response.token,
            };
        } catch (error) {
            const errorMessage =
                error instanceof Error ? error.message : 'Registration failed';
            this.setError(errorMessage);
            return {
                success: false,
                error: errorMessage,
            };
        } finally {
            this.setLoading(false);
        }
    }

    public async logout(): Promise<{ success: boolean; error?: string }> {
        try {
            this.setLoading(true);

            await apiService.post('/api/logout');

            this.clearAuthState();

            return { success: true };
        } catch (error) {
            const errorMessage =
                error instanceof Error ? error.message : 'Logout failed';
            this.setError(errorMessage);
            // Clear auth state even if logout request fails
            this.clearAuthState();
            return {
                success: false,
                error: errorMessage,
            };
        } finally {
            this.setLoading(false);
        }
    }

    public async refreshToken(): Promise<{
        success: boolean;
        token?: string;
        error?: string;
    }> {
        try {
            this.setLoading(true);

            const response = await apiService.post<{ token: string }>(
                '/api/refresh-token',
                { device_name: 'web_browser' },
            );

            if (!this.state.user) {
                throw new Error('No user available for token refresh');
            }

            this.setAuthState(response.token, this.state.user);

            return {
                success: true,
                token: response.token,
            };
        } catch (error) {
            const errorMessage =
                error instanceof Error ? error.message : 'Token refresh failed';
            this.setError(errorMessage);
            this.clearAuthState();
            return {
                success: false,
                error: errorMessage,
            };
        } finally {
            this.setLoading(false);
        }
    }

    public async getUser(): Promise<{
        success: boolean;
        user?: User;
        error?: string;
    }> {
        try {
            this.setLoading(true);

            const response = await apiService.get<{ user: User }>('/api/user');

            if (!this.state.token) {
                throw new Error('No token available');
            }

            this.setAuthState(this.state.token, response.user);

            return {
                success: true,
                user: response.user,
            };
        } catch (error) {
            const errorMessage =
                error instanceof Error ? error.message : 'Failed to fetch user';
            this.setError(errorMessage);
            return {
                success: false,
                error: errorMessage,
            };
        } finally {
            this.setLoading(false);
        }
    }

    public async checkAuth(): Promise<boolean> {
        if (!this.state.isAuthenticated) {
            return false;
        }

        try {
            await this.getUser();
            return true;
        } catch (error) {
            this.clearAuthState();
            return false;
        }
    }

    public hasRole(role: string | string[]): boolean {
        if (!this.state.user) return false;

        const roles = Array.isArray(role) ? role : [role];
        return roles.includes(this.state.user.role);
    }

    public isAdmin(): boolean {
        return this.hasRole('admin');
    }

    public isDoctor(): boolean {
        return this.hasRole('doctor');
    }

    public isStaff(): boolean {
        return this.hasRole('staff');
    }

    public isPatient(): boolean {
        return this.hasRole('patient');
    }

    private setAuthState(token: string, user: User): void {
        this.state = {
            user,
            token,
            isAuthenticated: true,
            loading: false,
            error: null,
        };

        // Store in localStorage
        localStorage.setItem('token', token);
        localStorage.setItem('user', JSON.stringify(user));

        // Set up axios interceptor
        this.setupAxiosInterceptor();

        // Start token refresh timer
        this.startTokenRefreshTimer();
    }

    private clearAuthState(): void {
        this.state = {
            user: null,
            token: null,
            isAuthenticated: false,
            loading: false,
            error: null,
        };

        // Clear from localStorage
        localStorage.removeItem('token');
        localStorage.removeItem('user');

        // Clear token refresh timer
        this.clearTokenRefreshTimer();
    }

    private setLoading(loading: boolean): void {
        this.state = {
            ...this.state,
            loading,
        };
    }

    private setError(error: string | null): void {
        this.state = {
            ...this.state,
            error,
        };
    }

    // Subscribe to state changes
    public subscribe(callback: (state: AuthState) => void): () => void {
        // Simple implementation - in a real app, you might use a more sophisticated approach
        const interval = setInterval(() => {
            callback(this.state);
        }, 100);

        return () => clearInterval(interval);
    }
}

// Export singleton instance
export const authService = AuthService.getInstance();
export default authService;
