import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

interface SystemAdminFeaturesProps {
    systemStatus: {
        version: string;
        environment: string;
        uptime: string;
        database_status: string;
        cache_status: string;
        queue_status: string;
    };
    users: Array<{
        id: number;
        name: string;
        email: string;
        role: string;
        last_login: string;
        status: string;
    }>;
    systemLogs: Array<{
        id: number;
        level: string;
        message: string;
        timestamp: string;
        source: string;
    }>;
    onCreateUser: (data: any) => void;
    onUpdateSystem: (data: any) => void;
}

export default function SystemAdminFeatures({
    systemStatus,
    users,
    systemLogs,
    onCreateUser,
    onUpdateSystem,
}: SystemAdminFeaturesProps) {
    const { data, setData, post, put, errors, processing } = useForm({
        userName: '',
        userEmail: '',
        userRole: 'user',
        userPassword: '',
    });

    const systemForm = useForm({
        maintenanceMode: false,
        backupFrequency: 'daily',
        notificationSettings: 'email',
    });

    const handleUserSubmit = (e: FormEvent) => {
        e.preventDefault();
        onCreateUser(data);
    };

    const handleSystemSubmit = (e: FormEvent) => {
        e.preventDefault();
        onUpdateSystem(systemForm.data);
    };

    const getLogLevelColor = (level: string) => {
        switch (level.toLowerCase()) {
            case 'error':
                return 'bg-red-500';
            case 'warning':
                return 'bg-orange-500';
            case 'info':
                return 'bg-blue-500';
            case 'debug':
                return 'bg-gray-500';
            default:
                return 'bg-green-500';
        }
    };

    return (
        <div className="space-y-6">
            <div className="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">
                            System Version
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold">
                            {systemStatus.version}
                        </div>
                        <p className="text-xs text-muted-foreground">
                            {systemStatus.environment}
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">
                            Uptime
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold">
                            {systemStatus.uptime}
                        </div>
                        <p className="text-xs text-muted-foreground">
                            System uptime
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">
                            Database
                        </CardTitle>
                        <Badge
                            variant={
                                systemStatus.database_status === 'connected'
                                    ? 'default'
                                    : 'destructive'
                            }
                        >
                            {systemStatus.database_status}
                        </Badge>
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold">
                            {systemStatus.database_status === 'connected'
                                ? '✓'
                                : '✗'}
                        </div>
                        <p className="text-xs text-muted-foreground">
                            Database connection
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">
                            Queue
                        </CardTitle>
                        <Badge
                            variant={
                                systemStatus.queue_status === 'active'
                                    ? 'default'
                                    : 'destructive'
                            }
                        >
                            {systemStatus.queue_status}
                        </Badge>
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold">
                            {systemStatus.queue_status === 'active' ? '✓' : '✗'}
                        </div>
                        <p className="text-xs text-muted-foreground">
                            Queue status
                        </p>
                    </CardContent>
                </Card>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Create New User</CardTitle>
                </CardHeader>
                <CardContent>
                    <form onSubmit={handleUserSubmit} className="space-y-6">
                        <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <Label htmlFor="userName">Name</Label>
                                <Input
                                    id="userName"
                                    value={data.userName}
                                    onChange={(e) =>
                                        setData('userName', e.target.value)
                                    }
                                    required
                                />
                                {errors.userName && (
                                    <p className="text-sm text-red-500">
                                        {errors.userName}
                                    </p>
                                )}
                            </div>
                            <div>
                                <Label htmlFor="userEmail">Email</Label>
                                <Input
                                    id="userEmail"
                                    type="email"
                                    value={data.userEmail}
                                    onChange={(e) =>
                                        setData('userEmail', e.target.value)
                                    }
                                    required
                                />
                                {errors.userEmail && (
                                    <p className="text-sm text-red-500">
                                        {errors.userEmail}
                                    </p>
                                )}
                            </div>
                        </div>

                        <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <Label htmlFor="userRole">Role</Label>
                                <Select
                                    value={data.userRole}
                                    onValueChange={(value) =>
                                        setData('userRole', value)
                                    }
                                    required
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select role" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="admin">
                                            Administrator
                                        </SelectItem>
                                        <SelectItem value="doctor">
                                            Doctor
                                        </SelectItem>
                                        <SelectItem value="nurse">
                                            Nurse
                                        </SelectItem>
                                        <SelectItem value="staff">
                                            Staff
                                        </SelectItem>
                                        <SelectItem value="user">
                                            User
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                {errors.userRole && (
                                    <p className="text-sm text-red-500">
                                        {errors.userRole}
                                    </p>
                                )}
                            </div>
                            <div>
                                <Label htmlFor="userPassword">Password</Label>
                                <Input
                                    id="userPassword"
                                    type="password"
                                    value={data.userPassword}
                                    onChange={(e) =>
                                        setData('userPassword', e.target.value)
                                    }
                                    required
                                />
                                {errors.userPassword && (
                                    <p className="text-sm text-red-500">
                                        {errors.userPassword}
                                    </p>
                                )}
                            </div>
                        </div>

                        <Button type="submit" disabled={processing}>
                            {processing ? 'Creating...' : 'Create User'}
                        </Button>
                    </form>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>User Management</CardTitle>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Name</TableHead>
                                <TableHead>Email</TableHead>
                                <TableHead>Role</TableHead>
                                <TableHead>Last Login</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead className="text-right">
                                    Actions
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {users.map((user) => (
                                <TableRow key={user.id}>
                                    <TableCell className="font-medium">
                                        {user.name}
                                    </TableCell>
                                    <TableCell>{user.email}</TableCell>
                                    <TableCell>{user.role}</TableCell>
                                    <TableCell>{user.last_login}</TableCell>
                                    <TableCell>
                                        <Badge
                                            variant={
                                                user.status === 'active'
                                                    ? 'default'
                                                    : 'secondary'
                                            }
                                        >
                                            {user.status}
                                        </Badge>
                                    </TableCell>
                                    <TableCell className="text-right">
                                        <div className="flex justify-end space-x-2">
                                            <Button variant="outline" size="sm">
                                                Edit
                                            </Button>
                                            <Button
                                                variant="destructive"
                                                size="sm"
                                            >
                                                Delete
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>System Configuration</CardTitle>
                </CardHeader>
                <CardContent>
                    <form onSubmit={handleSystemSubmit} className="space-y-6">
                        <div className="flex items-center justify-between">
                            <div>
                                <h3 className="font-medium">
                                    Maintenance Mode
                                </h3>
                                <p className="text-sm text-muted-foreground">
                                    Enable/disable maintenance mode
                                </p>
                            </div>
                            <Button variant="outline" size="sm">
                                {systemForm.data.maintenanceMode
                                    ? 'Disable'
                                    : 'Enable'}
                            </Button>
                        </div>

                        <div>
                            <Label htmlFor="backupFrequency">
                                Backup Frequency
                            </Label>
                            <Select
                                value={systemForm.data.backupFrequency}
                                onValueChange={(value) =>
                                    systemForm.setData('backupFrequency', value)
                                }
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="Select backup frequency" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="hourly">
                                        Hourly
                                    </SelectItem>
                                    <SelectItem value="daily">Daily</SelectItem>
                                    <SelectItem value="weekly">
                                        Weekly
                                    </SelectItem>
                                    <SelectItem value="monthly">
                                        Monthly
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div>
                            <Label htmlFor="notificationSettings">
                                Notification Settings
                            </Label>
                            <Select
                                value={systemForm.data.notificationSettings}
                                onValueChange={(value) =>
                                    systemForm.setData(
                                        'notificationSettings',
                                        value,
                                    )
                                }
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="Select notification settings" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="email">
                                        Email Only
                                    </SelectItem>
                                    <SelectItem value="sms">
                                        SMS Only
                                    </SelectItem>
                                    <SelectItem value="both">
                                        Email + SMS
                                    </SelectItem>
                                    <SelectItem value="none">
                                        Disabled
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <Button type="submit" disabled={systemForm.processing}>
                            {systemForm.processing
                                ? 'Saving...'
                                : 'Save Configuration'}
                        </Button>
                    </form>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>System Logs</CardTitle>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Level</TableHead>
                                <TableHead>Message</TableHead>
                                <TableHead>Source</TableHead>
                                <TableHead>Timestamp</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {systemLogs.map((log) => (
                                <TableRow key={log.id}>
                                    <TableCell>
                                        <div className="flex items-center gap-2">
                                            <div
                                                className={`h-3 w-3 rounded-full ${getLogLevelColor(log.level)}`}
                                            ></div>
                                            <span className="capitalize">
                                                {log.level}
                                            </span>
                                        </div>
                                    </TableCell>
                                    <TableCell>{log.message}</TableCell>
                                    <TableCell>{log.source}</TableCell>
                                    <TableCell>{log.timestamp}</TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>System Actions</CardTitle>
                </CardHeader>
                <CardContent>
                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                        <Button
                            variant="outline"
                            className="flex h-auto flex-col items-center justify-center py-4"
                        >
                            <span className="text-sm font-medium">
                                Database Backup
                            </span>
                        </Button>
                        <Button
                            variant="outline"
                            className="flex h-auto flex-col items-center justify-center py-4"
                        >
                            <span className="text-sm font-medium">
                                Cache Clear
                            </span>
                        </Button>
                        <Button
                            variant="outline"
                            className="flex h-auto flex-col items-center justify-center py-4"
                        >
                            <span className="text-sm font-medium">
                                Generate Reports
                            </span>
                        </Button>
                        <Button
                            variant="outline"
                            className="flex h-auto flex-col items-center justify-center py-4"
                        >
                            <span className="text-sm font-medium">
                                System Restart
                            </span>
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>
    );
}
