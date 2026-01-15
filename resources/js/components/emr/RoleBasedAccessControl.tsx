import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
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

interface RoleBasedAccessControlProps {
    roles: Array<{
        id: number;
        name: string;
        description: string;
        permissions: Array<{
            module: string;
            read: boolean;
            write: boolean;
            delete: boolean;
        }>;
    }>;
    users: Array<{
        id: number;
        name: string;
        email: string;
        role: string;
    }>;
    onSaveRole: (role: any) => void;
    onAssignRole: (userId: number, roleId: number) => void;
}

export default function RoleBasedAccessControl({
    roles,
    users,
    onSaveRole,
    onAssignRole,
}: RoleBasedAccessControlProps) {
    const { data, setData, post, put, errors, processing } = useForm({
        roleName: '',
        roleDescription: '',
        permissions: {
            patient: { read: false, write: false, delete: false },
            encounter: { read: false, write: false, delete: false },
            clinical_notes: { read: false, write: false, delete: false },
            diagnosis: { read: false, write: false, delete: false },
            lab_orders: { read: false, write: false, delete: false },
            treatment_plans: { read: false, write: false, delete: false },
            procedures: { read: false, write: false, delete: false },
            documents: { read: false, write: false, delete: false },
            reports: { read: false, write: false, delete: false },
            admin: { read: false, write: false, delete: false },
        },
    });

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();
        onSaveRole(data);
    };

    const handlePermissionChange = (
        module: string,
        permission: string,
        checked: boolean,
    ) => {
        setData('permissions', {
            ...data.permissions,
            [module]: {
                ...(data.permissions as any)[module],
                [permission]: checked, // Cast to any to allow string index
            },
        });
    };

    const handleUserRoleChange = (userId: number, roleId: number) => {
        onAssignRole(userId, roleId);
    };

    return (
        <div className="space-y-6">
            <Card>
                <CardHeader>
                    <CardTitle>Create/Edit Role</CardTitle>
                </CardHeader>
                <CardContent>
                    <form onSubmit={handleSubmit} className="space-y-6">
                        <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <Label htmlFor="roleName">Role Name</Label>
                                <Input
                                    id="roleName"
                                    value={data.roleName}
                                    onChange={(e) =>
                                        setData('roleName', e.target.value)
                                    }
                                    required
                                />
                                {errors.roleName && (
                                    <p className="text-sm text-red-500">
                                        {errors.roleName}
                                    </p>
                                )}
                            </div>
                            <div>
                                <Label htmlFor="roleDescription">
                                    Description
                                </Label>
                                <Input
                                    id="roleDescription"
                                    value={data.roleDescription}
                                    onChange={(e) =>
                                        setData(
                                            'roleDescription',
                                            e.target.value,
                                        )
                                    }
                                />
                                {errors.roleDescription && (
                                    <p className="text-sm text-red-500">
                                        {errors.roleDescription}
                                    </p>
                                )}
                            </div>
                        </div>

                        <div className="border-t pt-6">
                            <h3 className="mb-4 text-lg font-semibold">
                                Permissions
                            </h3>
                            <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                                {Object.entries(data.permissions).map(
                                    ([module, perms]) => (
                                        <div
                                            key={module}
                                            className="rounded-lg border p-4"
                                        >
                                            <h4 className="mb-3 font-medium capitalize">
                                                {module.replace('_', ' ')}
                                            </h4>
                                            <div className="space-y-2">
                                                <div className="flex items-center space-x-2">
                                                    <Checkbox
                                                        id={`${module}_read`}
                                                        checked={perms.read}
                                                        onCheckedChange={(
                                                            checked,
                                                        ) =>
                                                            handlePermissionChange(
                                                                module,
                                                                'read',
                                                                checked ===
                                                                    true,
                                                            )
                                                        }
                                                    />
                                                    <Label
                                                        htmlFor={`${module}_read`}
                                                    >
                                                        Read
                                                    </Label>
                                                </div>
                                                <div className="flex items-center space-x-2">
                                                    <Checkbox
                                                        id={`${module}_write`}
                                                        checked={perms.write}
                                                        onCheckedChange={(
                                                            checked,
                                                        ) =>
                                                            handlePermissionChange(
                                                                module,
                                                                'write',
                                                                checked ===
                                                                    true,
                                                            )
                                                        }
                                                    />
                                                    <Label
                                                        htmlFor={`${module}_write`}
                                                    >
                                                        Write
                                                    </Label>
                                                </div>
                                                <div className="flex items-center space-x-2">
                                                    <Checkbox
                                                        id={`${module}_delete`}
                                                        checked={perms.delete}
                                                        onCheckedChange={(
                                                            checked,
                                                        ) =>
                                                            handlePermissionChange(
                                                                module,
                                                                'delete',
                                                                checked ===
                                                                    true,
                                                            )
                                                        }
                                                    />
                                                    <Label
                                                        htmlFor={`${module}_delete`}
                                                    >
                                                        Delete
                                                    </Label>
                                                </div>
                                            </div>
                                        </div>
                                    ),
                                )}
                            </div>
                        </div>

                        <Button type="submit" disabled={processing}>
                            {processing ? 'Saving...' : 'Save Role'}
                        </Button>
                    </form>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Existing Roles</CardTitle>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Role Name</TableHead>
                                <TableHead>Description</TableHead>
                                <TableHead className="text-right">
                                    Actions
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {roles.map((role) => (
                                <TableRow key={role.id}>
                                    <TableCell className="font-medium">
                                        {role.name}
                                    </TableCell>
                                    <TableCell>{role.description}</TableCell>
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
                    <CardTitle>User Role Assignments</CardTitle>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>User Name</TableHead>
                                <TableHead>Email</TableHead>
                                <TableHead>Current Role</TableHead>
                                <TableHead>Assign Role</TableHead>
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
                                    <TableCell>
                                        <Select
                                            value={user.role}
                                            onValueChange={(value) =>
                                                handleUserRoleChange(
                                                    user.id,
                                                    parseInt(value),
                                                )
                                            }
                                        >
                                            <SelectTrigger className="w-[180px]">
                                                <SelectValue placeholder="Select role" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {roles.map((role) => (
                                                    <SelectItem
                                                        key={role.id}
                                                        value={role.id.toString()}
                                                    >
                                                        {role.name}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    );
}
