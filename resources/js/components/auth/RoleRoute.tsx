import React from 'react';
import { useAuth } from '../../hooks/use-auth';

export interface RoleRouteProps {
    children: React.ReactNode;
    roles: string | string[];
    fallback?: React.ReactNode;
}

export const RoleRoute: React.FC<RoleRouteProps> = ({
    children,
    roles,
    fallback = <div>You do not have permission to access this resource</div>,
}) => {
    const { isAuthenticated, loading, hasRole } = useAuth();

    if (loading) {
        return <div>Loading...</div>;
    }

    if (!isAuthenticated) {
        window.location.href = '/login';
        return null;
    }

    if (!hasRole(roles)) {
        return <>{fallback}</>;
    }

    return <>{children}</>;
};

export default RoleRoute;
