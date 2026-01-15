import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import {
    AlertCircle,
    Calendar,
    Receipt,
    Stethoscope,
    TrendingUp,
    Users,
} from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard.url(),
    },
];

interface DashboardStats {
    total_patients: number;
    total_doctors: number;
    total_appointments_this_month: number;
    total_revenue_this_month: number;
    pending_bills: number;
    today_appointments: number;
}

interface Appointment {
    id: number;
    appointment_number: string;
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
    start_time: string;
    status: string;
}

interface Notification {
    id: number;
    title: string;
    message: string;
    type: string;
    created_at: string;
}

interface DashboardProps {
    stats: DashboardStats;
    recentAppointments: Appointment[];
    notifications: Notification[];
}

export default function Dashboard({ stats, recentAppointments, notifications }: DashboardProps) {
    const statsCards = [
        {
            title: 'Total Patients',
            value: stats.total_patients.toString(),
            change: '+12%', // This would need to be calculated from previous period
            icon: Users,
            color: 'text-blue-600',
            href: '/patients',
        },
        {
            title: 'Active Doctors',
            value: stats.total_doctors.toString(),
            change: '+2', // This would need to be calculated
            icon: Stethoscope,
            color: 'text-green-600',
            href: '/doctors',
        },
        {
            title: "Today's Appointments",
            value: stats.today_appointments.toString(),
            change: '+8%', // This would need to be calculated
            icon: Calendar,
            color: 'text-orange-600',
            href: '/appointments',
        },
        {
            title: 'Monthly Revenue',
            value: `${stats.total_revenue_this_month.toFixed(2)}`,
            change: '+15%', // This would need to be calculated
            icon: TrendingUp,
            color: 'text-purple-600',
            href: '/billing',
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />

            <div className="flex flex-col gap-6 p-6">
                <div>
                    <h1 className="text-2xl font-bold">Dashboard</h1>
                    <p className="text-muted-foreground">
                        Welcome back! Here's an overview of your clinic.
                    </p>
                </div>

                {/* Stats Grid */}
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    {statsCards.map((stat) => (
                        <Card
                            key={stat.title}
                            className="transition-shadow hover:shadow-md"
                        >
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">
                                    {stat.title}
                                </CardTitle>
                                <stat.icon
                                    className={`h-4 w-4 ${stat.color}`}
                                />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">
                                    {stat.value}
                                </div>
                                <p className="text-xs text-muted-foreground">
                                    <span className="text-green-600">
                                        {stat.change}
                                    </span>{' '}
                                    from last month
                                </p>
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    className="mt-2 h-auto p-0"
                                    asChild
                                >
                                    <Link href={stat.href}>View Details →</Link>
                                </Button>
                            </CardContent>
                        </Card>
                    ))}
                </div>

                <div className="grid gap-6 md:grid-cols-2">
                    {/* Recent Appointments */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Today's Appointments</CardTitle>
                            <CardDescription>
                                Upcoming appointments for today
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {recentAppointments.length > 0 ? (
                                    recentAppointments.map(
                                        (appointment: Appointment) => (
                                            <div
                                                key={appointment.id}
                                                className="flex items-center justify-between"
                                            >
                                                <div>
                                                    <p className="font-medium">
                                                        {
                                                            appointment.patient
                                                                .user.name
                                                        }
                                                    </p>
                                                    <p className="text-sm text-muted-foreground">
                                                        {
                                                            appointment.doctor
                                                                .user.name
                                                        }{' '}
                                                        •{' '}
                                                        {appointment.start_time}
                                                    </p>
                                                </div>
                                                <span
                                                    className={`inline-flex items-center rounded-full px-2 py-1 text-xs font-medium ${appointment.status ===
                                                        'confirmed'
                                                        ? 'bg-green-100 text-green-800'
                                                        : appointment.status ===
                                                            'in_progress'
                                                            ? 'bg-blue-100 text-blue-800'
                                                            : appointment.status ===
                                                                'waiting'
                                                                ? 'bg-yellow-100 text-yellow-800'
                                                                : 'bg-gray-100 text-gray-800'
                                                        }`}
                                                >
                                                    {appointment.status.replace(
                                                        '_',
                                                        ' ',
                                                    )}
                                                </span>
                                            </div>
                                        ),
                                    )
                                ) : (
                                    <p className="text-sm text-muted-foreground">
                                        No appointments today
                                    </p>
                                )}
                            </div>
                            <Button
                                variant="outline"
                                className="mt-4 w-full"
                                asChild
                            >
                                <Link href="/appointments">
                                    View All Appointments
                                </Link>
                            </Button>
                        </CardContent>
                    </Card>

                    {/* Alerts & Notifications */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Alerts & Notifications</CardTitle>
                            <CardDescription>
                                Important updates and reminders
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {notifications.length > 0 ? (
                                    notifications.map((notification: Notification) => (
                                        <div
                                            key={notification.id}
                                            className="flex items-start space-x-2"
                                        >
                                            <AlertCircle
                                                className={`mt-0.5 h-4 w-4 ${notification.type === 'warning'
                                                    ? 'text-yellow-600'
                                                    : notification.type ===
                                                        'success'
                                                        ? 'text-green-600'
                                                        : 'text-blue-600'
                                                    }`}
                                            />
                                            <p className="text-sm">
                                                {notification.message}
                                            </p>
                                        </div>
                                    ))
                                ) : (
                                    <p className="text-sm text-muted-foreground">
                                        No alerts at this time
                                    </p>
                                )}
                            </div>
                            <Button
                                variant="outline"
                                className="mt-4 w-full"
                                asChild
                            >
                                <Link href="/notifications">
                                    View All Notifications
                                </Link>
                            </Button>
                        </CardContent>
                    </Card>
                </div>

                {/* Quick Actions */}
                <Card>
                    <CardHeader>
                        <CardTitle>Quick Actions</CardTitle>
                        <CardDescription>
                            Commonly used features
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="grid gap-4 md:grid-cols-4">
                            <Button asChild>
                                <Link href="/patients/create">
                                    <Users className="mr-2 h-4 w-4" />
                                    Add Patient
                                </Link>
                            </Button>
                            <Button asChild variant="outline">
                                <Link href="/appointments/create">
                                    <Calendar className="mr-2 h-4 w-4" />
                                    Schedule Appointment
                                </Link>
                            </Button>
                            <Button asChild variant="outline">
                                <Link href="/billing/create">
                                    <Receipt className="mr-2 h-4 w-4" />
                                    Create Bill
                                </Link>
                            </Button>
                            <Button asChild variant="outline">
                                <Link href="/reports">
                                    <TrendingUp className="mr-2 h-4 w-4" />
                                    View Reports
                                </Link>
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
