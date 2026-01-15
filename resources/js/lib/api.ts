import axios, {
    AxiosError,
    AxiosInstance,
    AxiosRequestConfig,
    AxiosResponse,
} from 'axios';
import Controllers from '../actions/App/Http/Controllers';

// Create axios instance
const api: AxiosInstance = axios.create({
    baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000',
    headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    },
    withCredentials: true, // Important for Sanctum cookie-based auth
});

// Request interceptor for adding CSRF token and auth token
api.interceptors.request.use(
    async (config: any) => {
        // For Sanctum stateful authentication, ensure CSRF cookie is set
        // This is only needed for state-changing requests (POST, PUT, DELETE, PATCH)
        const isStateChanging = ['post', 'put', 'delete', 'patch'].includes(
            config.method?.toLowerCase() || '',
        );

        if (isStateChanging) {
            // Get CSRF token from meta tag or cookie
            const csrfToken =
                document
                    .querySelector('meta[name="csrf-token"]')
                    ?.getAttribute('content') || getCookie('XSRF-TOKEN');

            if (csrfToken) {
                config.headers = config.headers || {};
                config.headers['X-XSRF-TOKEN'] = decodeURIComponent(csrfToken);
            }
        }

        // Add authorization token if available (for token-based auth)
        const token = localStorage.getItem('token');
        if (token) {
            config.headers = config.headers || {};
            config.headers.Authorization = `Bearer ${token}`;
            console.log('Token attached to request:', token);
        } else {
            console.log('No token found in localStorage');
        }

        return config;
    },
    (error: AxiosError) => {
        return Promise.reject(error);
    },
);

// Helper function to get cookie value
function getCookie(name: string): string | null {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) {
        return parts.pop()?.split(';').shift() || null;
    }
    return null;
}

// Response interceptor for handling errors
api.interceptors.response.use(
    (response: AxiosResponse) => {
        return response;
    },
    (error: AxiosError) => {
        if (error.response) {
            // Handle specific error status codes
            switch (error.response.status) {
                case 401:
                    // Unauthorized - redirect to login
                    window.location.href = '/login';
                    break;
                case 403:
                    // Forbidden - show error message
                    console.error(
                        'Forbidden: You do not have permission to access this resource',
                    );
                    break;
                case 404:
                    // Not found
                    console.error('Resource not found');
                    break;
                case 422:
                    // Validation error
                    console.error('Validation error:', error.response.data);
                    break;
                case 500:
                    // Server error
                    console.error('Server error');
                    break;
            }
        } else if (error.request) {
            // Request was made but no response received
            console.error('No response received:', error.request);
        } else {
            // Something happened in setting up the request
            console.error('Request setup error:', error.message);
        }
        return Promise.reject(error);
    },
);

// API Service class
class ApiService {
    private static instance: ApiService;
    private csrfInitialized = false;

    private constructor() { }

    public static getInstance(): ApiService {
        if (!ApiService.instance) {
            ApiService.instance = new ApiService();
        }
        return ApiService.instance;
    }

    // Initialize CSRF token for Sanctum stateful authentication
    private async initializeCsrf(): Promise<void> {
        if (this.csrfInitialized) {
            return;
        }

        try {
            // Get the base URL without /api
            const baseUrl =
                import.meta.env.VITE_API_URL?.replace('/api', '') ||
                window.location.origin;

            // Call sanctum/csrf-cookie endpoint to set CSRF cookie
            await axios.get(`${baseUrl}/sanctum/csrf-cookie`, {
                withCredentials: true,
            });
            this.csrfInitialized = true;
        } catch (error) {
            console.error('Failed to initialize CSRF token:', error);
            // Don't throw - allow the request to proceed
            // Mark as initialized anyway to avoid repeated failures
            this.csrfInitialized = true;
        }
    }

    // Generic request method
    public async request<T>(
        method: 'get' | 'post' | 'put' | 'patch' | 'delete',
        url: string,
        data?: any,
        config?: AxiosRequestConfig,
    ): Promise<T> {
        // Initialize CSRF token before making state-changing requests
        if (['post', 'put', 'patch', 'delete'].includes(method)) {
            await this.initializeCsrf();
        }

        try {
            console.log(`Making ${method.toUpperCase()} request to:`, url);
            console.log('Base URL:', api.defaults.baseURL);
            console.log('Full URL:', `${api.defaults.baseURL}${url}`);
            console.log(
                'Token in localStorage:',
                localStorage.getItem('token'),
            );

            const response = await api.request<T>({
                method,
                url,
                data,
                ...config,
            });
            console.log('Request successful, response:', response.data);
            return response.data;
        } catch (error) {
            console.error('Request failed:', error);
            throw this.handleError(error);
        }
    }

    // GET request
    public async get<T>(url: string, config?: AxiosRequestConfig): Promise<T> {
        return this.request<T>('get', url, undefined, config);
    }

    // POST request
    public async post<T>(
        url: string,
        data?: any,
        config?: AxiosRequestConfig,
    ): Promise<T> {
        return this.request<T>('post', url, data, config);
    }

    // PUT request
    public async put<T>(
        url: string,
        data?: any,
        config?: AxiosRequestConfig,
    ): Promise<T> {
        return this.request<T>('put', url, data, config);
    }

    // PATCH request
    public async patch<T>(
        url: string,
        data?: any,
        config?: AxiosRequestConfig,
    ): Promise<T> {
        return this.request<T>('patch', url, data, config);
    }

    // DELETE request
    public async delete<T>(
        url: string,
        config?: AxiosRequestConfig,
    ): Promise<T> {
        return this.request<T>('delete', url, undefined, config);
    }

    // Handle API errors
    private handleError(error: any): never {
        if (axios.isAxiosError(error)) {
            const axiosError = error as AxiosError;
            if (axiosError.response) {
                // Server responded with a status code outside 2xx
                const responseData = axiosError.response.data as {
                    message?: string;
                    errors?: any;
                };
                throw new Error(
                    responseData.message ||
                    JSON.stringify(responseData.errors) ||
                    `API Error: ${axiosError.response.status}`,
                );
            } else if (axiosError.request) {
                // Request was made but no response received
                throw new Error('No response received from server');
            } else {
                // Something happened in setting up the request
                throw new Error(`Request error: ${axiosError.message}`);
            }
        } else {
            // Non-Axios error
            throw new Error(`Unexpected error: ${error.message}`);
        }
    }

    // Helper method to get route URL from controllers
    public getRouteUrl(route: any, ...args: any[]): string {
        if (typeof route.url === 'function') {
            return route.url(...args);
        }
        return route.url;
    }

    // EMR API Methods

    // Encounter API methods
    public async getEncounters(params?: any): Promise<any> {
        return this.get(
            this.getRouteUrl(Controllers.EncounterController.index, params),
        );
    }

    public async getEncounter(id: number): Promise<any> {
        return this.get(
            this.getRouteUrl(Controllers.EncounterController.show, { id }),
        );
    }

    public async createEncounter(data: any): Promise<any> {
        return this.post(
            this.getRouteUrl(Controllers.EncounterController.store),
            data,
        );
    }

    public async updateEncounter(id: number, data: any): Promise<any> {
        return this.put(
            this.getRouteUrl(Controllers.EncounterController.update, { id }),
            data,
        );
    }

    public async completeEncounter(id: number): Promise<any> {
        return this.post(
            this.getRouteUrl(Controllers.EncounterController.complete, { id }),
        );
    }

    // Vital Signs API methods
    public async getVitalSigns(params?: any): Promise<any> {
        return this.get(
            this.getRouteUrl(Controllers.VitalSignsController.index, params),
        );
    }

    public async getVitalSign(id: number): Promise<any> {
        return this.get(
            this.getRouteUrl(Controllers.VitalSignsController.show, { id }),
        );
    }

    public async createVitalSign(data: any): Promise<any> {
        return this.post(
            this.getRouteUrl(Controllers.VitalSignsController.store),
            data,
        );
    }

    public async updateVitalSign(id: number, data: any): Promise<any> {
        return this.put(
            this.getRouteUrl(Controllers.VitalSignsController.update, { id }),
            data,
        );
    }

    public async getEncounterVitalSigns(encounterId: number): Promise<any> {
        return this.get(
            this.getRouteUrl(
                Controllers.VitalSignsController.getEncounterVitalSigns,
                { encounterId },
            ),
        );
    }

    // Clinical Notes API methods
    public async getClinicalNotes(params?: any): Promise<any> {
        return this.get(
            this.getRouteUrl(Controllers.ClinicalNotesController.index, params),
        );
    }

    public async getClinicalNote(id: number): Promise<any> {
        return this.get(
            this.getRouteUrl(Controllers.ClinicalNotesController.show, { id }),
        );
    }

    public async createClinicalNote(data: any): Promise<any> {
        return this.post(
            this.getRouteUrl(Controllers.ClinicalNotesController.store),
            data,
        );
    }

    public async updateClinicalNote(id: number, data: any): Promise<any> {
        return this.put(
            this.getRouteUrl(Controllers.ClinicalNotesController.update, {
                id,
            }),
            data,
        );
    }

    public async getEncounterClinicalNotes(encounterId: number): Promise<any> {
        return this.get(
            this.getRouteUrl(
                Controllers.ClinicalNotesController.getEncounterNotes,
                { encounterId },
            ),
        );
    }

    // Diagnosis API methods
    public async getDiagnoses(params?: any): Promise<any> {
        return this.get(
            this.getRouteUrl(Controllers.DiagnosisController.index, params),
        );
    }

    public async getDiagnosis(id: number): Promise<any> {
        return this.get(
            this.getRouteUrl(Controllers.DiagnosisController.show, { id }),
        );
    }

    public async createDiagnosis(data: any): Promise<any> {
        return this.post(
            this.getRouteUrl(Controllers.DiagnosisController.store),
            data,
        );
    }

    public async updateDiagnosis(id: number, data: any): Promise<any> {
        return this.put(
            this.getRouteUrl(Controllers.DiagnosisController.update, { id }),
            data,
        );
    }

    public async getEncounterDiagnoses(encounterId: number): Promise<any> {
        return this.get(
            this.getRouteUrl(
                Controllers.DiagnosisController.getEncounterDiagnoses,
                { encounterId },
            ),
        );
    }

    // ICD-10 Codes API methods
    public async getIcd10Codes(params?: any): Promise<any> {
        return this.get(
            this.getRouteUrl(Controllers.Icd10CodeController.index, params),
        );
    }

    public async getIcd10Code(id: number): Promise<any> {
        return this.get(
            this.getRouteUrl(Controllers.Icd10CodeController.show, { id }),
        );
    }

    public async searchIcd10Codes(query: string): Promise<any> {
        return this.get(
            this.getRouteUrl(Controllers.Icd10CodeController.search, { query }),
        );
    }

    // Lab Orders API methods
    public async getLabOrders(params?: any): Promise<any> {
        return this.get(
            this.getRouteUrl(Controllers.LabOrderController.index, params),
        );
    }

    public async getLabOrder(id: number): Promise<any> {
        return this.get(
            this.getRouteUrl(Controllers.LabOrderController.show, { id }),
        );
    }

    public async createLabOrder(data: any): Promise<any> {
        return this.post(
            this.getRouteUrl(Controllers.LabOrderController.store),
            data,
        );
    }

    public async updateLabOrder(id: number, data: any): Promise<any> {
        return this.put(
            this.getRouteUrl(Controllers.LabOrderController.update, { id }),
            data,
        );
    }

    public async getEncounterLabOrders(encounterId: number): Promise<any> {
        return this.get(
            this.getRouteUrl(
                Controllers.LabOrderController.getEncounterLabOrders,
                { encounterId },
            ),
        );
    }

    // Treatment Plans API methods
    public async getTreatmentPlans(params?: any): Promise<any> {
        return this.get(
            this.getRouteUrl(Controllers.TreatmentPlanController.index, params),
        );
    }

    public async getTreatmentPlan(id: number): Promise<any> {
        return this.get(
            this.getRouteUrl(Controllers.TreatmentPlanController.show, { id }),
        );
    }

    public async createTreatmentPlan(data: any): Promise<any> {
        return this.post(
            this.getRouteUrl(Controllers.TreatmentPlanController.store),
            data,
        );
    }

    public async updateTreatmentPlan(id: number, data: any): Promise<any> {
        return this.put(
            this.getRouteUrl(Controllers.TreatmentPlanController.update, {
                id,
            }),
            data,
        );
    }

    public async getEncounterTreatmentPlans(encounterId: number): Promise<any> {
        return this.get(
            this.getRouteUrl(
                Controllers.TreatmentPlanController.getEncounterTreatmentPlans,
                { encounterId },
            ),
        );
    }
}

// Export singleton instance
export const apiService = ApiService.getInstance();
export default apiService;
