import { Badge } from '@/components/ui/badge';
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
import { Calendar, Edit, Eye, Plus, Search } from 'lucide-react';
import { useEffect, useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Appointments',
        href: '/appointments',
    },
];

interface Appointment {
    id: number;
    patient: {
        user: {
            name: string;
        };
    };
    doctor: {
        user: {
            name: string;
        };
    };
    appointment_date: string;
    appointment_time: string;
    type: string;
    status: string;
}

export default function AppointmentsIndex() {
    const [appointments, setAppointments] = useState<Appointment[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);

    const fetchAppointments = async () => {
        try {
            setLoading(true);
            setError(null);
            const response = (await apiService.get('/appointments')) as {
                success: boolean;
                data: Appointment[];
            };
            setAppointments(response.data || []);
        } catch (err) {
            setError(
                err instanceof Error
                    ? err.message
                    : 'Failed to load appointments',
            );
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchAppointments();
    }, []);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Appointments" />

            <div className="flex flex-col gap-6 p-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold">Appointments</h1>
                        <p className="text-muted-foreground">
                            Manage appointment scheduling and bookings
                        </p>
                    </div>
                    <Button asChild>
                        <Link href="/appointments/create">
                            <Plus className="mr-2 h-4 w-4" />
                            Schedule Appointment
                        </Link>
                    </Button>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Appointment List</CardTitle>
                        <CardDescription>
                            A list of all appointments in the system
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="mb-4 flex items-center space-x-2">
                            <div className="relative flex-1">
                                <Search className="absolute top-2.5 left-2 h-4 w-4 text-muted-foreground" />
                                <Input
                                    placeholder="Search appointments..."
                                    className="pl-8"
                                />
                            </div>
                            <Button variant="outline" size="sm">
                                <Calendar className="mr-2 h-4 w-4" />
                                Calendar View
                            </Button>
                        </div>

                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Patient</TableHead>
                                    <TableHead>Doctor</TableHead>
                                    <TableHead>Date & Time</TableHead>
                                    <TableHead>Type</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead className="text-right">
                                        Actions
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {appointments.map((appointment) => (
                                    <TableRow key={appointment.id}>
                                        <TableCell className="font-medium">
                                            {appointment.patient?.user?.name ||
                                                'N/A'}
                                        </TableCell>
                                        <TableCell>
                                            {appointment.doctor?.user?.name ||
                                                'N/A'}
                                        </TableCell>
                                        <TableCell>
                                            <div>
                                                <div className="font-medium">
                                                    {appointment.appointment_date}
                                                </div>
                                                <div className="text-sm text-muted-foreground">
                                                    {appointment.appointment_time}
                                                </div>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            {appointment.type}
                                        </TableCell>
                                        <TableCell>
                                            <Badge
                                                variant={
                                                    appointment.status ===
                                                    'Confirmed'
                                                        ? 'default'
                                                        : appointment.status ===
                                                            'Pending'
                                                          ? 'secondary'
                                                          : appointment.status ===
                                                              'Completed'
                                                            ? 'outline'
                                                            : 'destructive'
                                                }
                                            >
                                                {appointment.status}
                                            </Badge>
                                        </TableCell>
                                        <TableCell className="text-right">
                                            <div className="flex justify-end space-x-2">
                                                <Button
                                                    variant="ghost"
                                                    size="sm"
                                                    asChild
                                                >
                                                    <Link
                                                        href={`/appointments/${appointment.id}`}
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
                                                        href={`/appointments/${appointment.id}/edit`}
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
