import { useEffect, useState } from 'react';
import { authService, type AuthState } from '../services/authService';

export function useAuth() {
    const [state, setState] = useState<AuthState>(authService.getState());

    useEffect(() => {
        const unsubscribe = authService.subscribe((newState) => {
            setState(newState);
        });

        return () => {
            unsubscribe();
        };
    }, []);

    const login = async (credentials: {
        email: string;
        password: string;
        device_name: string;
    }) => {
        return authService.login(credentials);
    };

    const register = async (data: {
        name: string;
        email: string;
        password: string;
        password_confirmation: string;
        role: 'admin' | 'doctor' | 'staff' | 'patient';
        device_name: string;
    }) => {
        return authService.register(data);
    };

    const logout = async () => {
        return authService.logout();
    };

    const refreshToken = async () => {
        return authService.refreshToken();
    };

    const getUser = async () => {
        return authService.getUser();
    };

    const checkAuth = async () => {
        return authService.checkAuth();
    };

    const hasRole = (role: string | string[]) => {
        return authService.hasRole(role);
    };

    const isAdmin = () => {
        return authService.isAdmin();
    };

    const isDoctor = () => {
        return authService.isDoctor();
    };

    const isStaff = () => {
        return authService.isStaff();
    };

    const isPatient = () => {
        return authService.isPatient();
    };

    return {
        ...state,
        login,
        register,
        logout,
        refreshToken,
        getUser,
        checkAuth,
        hasRole,
        isAdmin,
        isDoctor,
        isStaff,
        isPatient,
    };
}

export default useAuth;
