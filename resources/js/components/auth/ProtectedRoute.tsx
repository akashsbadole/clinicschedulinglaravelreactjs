import React from 'react';
import { useAuth } from '../../hooks/use-auth';

export interface ProtectedRouteProps {
    children: React.ReactNode;
    roles?: string | string[];
    fallback?: React.ReactNode;
}

export const ProtectedRoute: React.FC<ProtectedRouteProps> = ({
    children,
    roles,
    fallback = <div>Unauthorized access</div>,
}) => {
    const { isAuthenticated, loading, hasRole } = useAuth();

    if (loading) {
        return <div>Loading...</div>;
    }

    if (!isAuthenticated) {
        // Redirect to login or show unauthorized message
        window.location.href = '/login';
        return null;
    }

    if (roles && !hasRole(roles)) {
        // User doesn't have required role
        return <>{fallback}</>;
    }

    return <>{children}</>;
};

export default ProtectedRoute;
