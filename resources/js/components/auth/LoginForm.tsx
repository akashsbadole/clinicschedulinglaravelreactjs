import React, { useState } from 'react';
import { useAuth } from '../../hooks/use-auth';
import { Button } from '../ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '../ui/card';
import { Input } from '../ui/input';
import { Label } from '../ui/label';

export const LoginForm: React.FC = () => {
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [deviceName, setDeviceName] = useState('web_browser');
    const [error, setError] = useState<string | null>(null);
    const [loading, setLoading] = useState(false);

    const { login, isAuthenticated } = useAuth();

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setError(null);
        setLoading(true);

        try {
            const result = await login({
                email,
                password,
                device_name: deviceName,
            });

            if (!result.success) {
                setError(result.error || 'Login failed');
            }
        } catch (err) {
            setError('An unexpected error occurred during login');
            console.error('Login error:', err);
        } finally {
            setLoading(false);
        }
    };

    if (isAuthenticated) {
        window.location.href = '/dashboard';
        return null;
    }

    return (
        <Card className="mx-auto w-full max-w-md">
            <CardHeader>
                <CardTitle className="text-center text-2xl font-bold">
                    Login
                </CardTitle>
                <CardDescription className="text-center">
                    Enter your credentials to access your account
                </CardDescription>
            </CardHeader>
            <CardContent>
                <form onSubmit={handleSubmit} className="space-y-4">
                    {error && (
                        <div className="rounded bg-red-100 p-3 text-red-700">
                            {error}
                        </div>
                    )}

                    <div className="space-y-2">
                        <Label htmlFor="email">Email</Label>
                        <Input
                            id="email"
                            type="email"
                            placeholder="example@clinic.com"
                            value={email}
                            onChange={(e) => setEmail(e.target.value)}
                            required
                            disabled={loading}
                        />
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="password">Password</Label>
                        <Input
                            id="password"
                            type="password"
                            placeholder="••••••••"
                            value={password}
                            onChange={(e) => setPassword(e.target.value)}
                            required
                            disabled={loading}
                        />
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="deviceName">Device Name</Label>
                        <Input
                            id="deviceName"
                            type="text"
                            placeholder="My Device"
                            value={deviceName}
                            onChange={(e) => setDeviceName(e.target.value)}
                            disabled={loading}
                        />
                    </div>

                    <Button type="submit" className="w-full" disabled={loading}>
                        {loading ? 'Logging in...' : 'Login'}
                    </Button>
                </form>
            </CardContent>
            <CardFooter className="flex justify-center">
                <p className="text-sm text-gray-500">
                    Don't have an account?{' '}
                    <a
                        href="/register"
                        className="text-blue-600 hover:underline"
                    >
                        Register
                    </a>
                </p>
            </CardFooter>
        </Card>
    );
};

export default LoginForm;
