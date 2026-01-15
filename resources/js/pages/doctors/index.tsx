import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import apiService from '@/lib/api';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { Edit, Eye, Plus, Search } from 'lucide-react';
import { useEffect, useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Doctors',
        href: '/doctors',
    },
];

interface Doctor {
    id: number;
    user: {
        name: string;
        email: string;
    };
    phone: string;
    specialization: string;
    license_number: string;
    status: string;
}

export default function DoctorsIndex() {
    const [doctors, setDoctors] = useState<Doctor[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);

    const fetchDoctors = async () => {
        try {
            setLoading(true);
            setError(null);
            const response = (await apiService.get('/doctors')) as {
                success: boolean;
                data: Doctor[];
            };
            setDoctors(response.data || []);
        } catch (err) {
            setError(
                err instanceof Error ? err.message : 'Failed to load doctors',
            );
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchDoctors();
    }, []);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Doctors" />

            <div className="flex flex-col gap-6 p-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold">Doctors</h1>
                        <p className="text-muted-foreground">
                            Manage doctor profiles and information
                        </p>
                    </div>
                    <Button asChild>
                        <Link href="/doctors/create">
                            <Plus className="mr-2 h-4 w-4" />
                            Add Doctor
                        </Link>
                    </Button>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Doctor List</CardTitle>
                        <CardDescription>
                            A list of all doctors in the system
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="mb-4 flex items-center space-x-2">
                            <div className="relative flex-1">
                                <Search className="absolute top-2.5 left-2 h-4 w-4 text-muted-foreground" />
                                <Input
                                    placeholder="Search doctors..."
                                    className="pl-8"
                                />
                            </div>
                        </div>

                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Name</TableHead>
                                    <TableHead>Email</TableHead>
                                    <TableHead>Phone</TableHead>
                                    <TableHead>Specialization</TableHead>
                                    <TableHead>License</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead className="text-right">
                                        Actions
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {doctors.map((doctor) => (
                                    <TableRow key={doctor.id}>
                                        <TableCell className="font-medium">
                                            {doctor.user.name}
                                        </TableCell>
                                        <TableCell>{doctor.user.email}</TableCell>
                                        <TableCell>{doctor.phone}</TableCell>
                                        <TableCell>
                                            {doctor.specialization}
                                        </TableCell>
                                        <TableCell>
                                            {doctor.license_number}
                                        </TableCell>
                                        <TableCell>
                                            <span className="inline-flex items-center rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-800">
                                                {doctor.status}
                                            </span>
                                        </TableCell>
                                        <TableCell className="text-right">
                                            <div className="flex justify-end space-x-2">
                                                <Button
                                                    variant="ghost"
                                                    size="sm"
                                                    asChild
                                                >
                                                    <Link
                                                        href={`/doctors/${doctor.id}`}
                                                    >
                                                        <Eye className="h-4 w-4" />
                                                    </Link>
                                                </Button>
                                                <Button
                                                    variant="ghost"
                                                    size="sm"
                                                    asChild
                                                >
                                                    <Link
                                                        href={`/doctors/${doctor.id}/edit`}
                                                    >
                                                        <Edit className="h-4 w-4" />
                                                    </Link>
                                                </Button>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
