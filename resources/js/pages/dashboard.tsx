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
        href: dashboard().url,
    },
];

export default function Dashboard() {
    const stats = [
        {
            title: 'Total Patients',
            value: '1,234',
            change: '+12%',
            icon: Users,
            color: 'text-blue-600',
            href: '/patients',
        },
        {
            title: 'Active Doctors',
            value: '12',
            change: '+2',
            icon: Stethoscope,
            color: 'text-green-600',
            href: '/doctors',
        },
        {
            title: "Today's Appointments",
            value: '24',
            change: '+8%',
            icon: Calendar,
            color: 'text-orange-600',
            href: '/appointments',
        },
        {
            title: 'Monthly Revenue',
            value: '$12,450',
            change: '+15%',
            icon: TrendingUp,
            color: 'text-purple-600',
            href: '/billing',
        },
    ];

    const recentAppointments = [
        {
            id: 1,
            patient: 'John Doe',
            doctor: 'Dr. Sarah Johnson',
            time: '10:00 AM',
            status: 'Confirmed',
        },
        {
            id: 2,
            patient: 'Jane Smith',
            doctor: 'Dr. Michael Brown',
            time: '11:30 AM',
            status: 'In Progress',
        },
        {
            id: 3,
            patient: 'Bob Wilson',
            doctor: 'Dr. Emily Davis',
            time: '2:00 PM',
            status: 'Waiting',
        },
    ];

    const alerts = [
        {
            id: 1,
            message: 'Appointment reminder for John Doe at 10:00 AM',
            type: 'info',
        },
        {
            id: 2,
            message: 'Payment overdue for invoice #1234',
            type: 'warning',
        },
        {
            id: 3,
            message: 'New patient registration pending approval',
            type: 'success',
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
                    {stats.map((stat) => (
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
                                {recentAppointments.map((appointment) => (
                                    <div
                                        key={appointment.id}
                                        className="flex items-center justify-between"
                                    >
                                        <div>
                                            <p className="font-medium">
                                                {appointment.patient}
                                            </p>
                                            <p className="text-sm text-muted-foreground">
                                                {appointment.doctor} •{' '}
                                                {appointment.time}
                                            </p>
                                        </div>
                                        <span
                                            className={`inline-flex items-center rounded-full px-2 py-1 text-xs font-medium ${
                                                appointment.status ===
                                                'Confirmed'
                                                    ? 'bg-green-100 text-green-800'
                                                    : appointment.status ===
                                                        'In Progress'
                                                      ? 'bg-blue-100 text-blue-800'
                                                      : 'bg-yellow-100 text-yellow-800'
                                            }`}
                                        >
                                            {appointment.status}
                                        </span>
                                    </div>
                                ))}
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
                                {alerts.map((alert) => (
                                    <div
                                        key={alert.id}
                                        className="flex items-start space-x-2"
                                    >
                                        <AlertCircle
                                            className={`mt-0.5 h-4 w-4 ${
                                                alert.type === 'warning'
                                                    ? 'text-yellow-600'
                                                    : alert.type === 'success'
                                                      ? 'text-green-600'
                                                      : 'text-blue-600'
                                            }`}
                                        />
                                        <p className="text-sm">
                                            {alert.message}
                                        </p>
                                    </div>
                                ))}
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
