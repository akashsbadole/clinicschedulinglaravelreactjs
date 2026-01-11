import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import {
    BarChart3,
    Calendar,
    FileText,
    Receipt,
    TrendingUp,
    Users,
} from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Reports',
        href: '/reports',
    },
];

export default function ReportsIndex() {
    const reportCards = [
        {
            title: 'Financial Reports',
            description: 'Revenue, expenses, and financial analytics',
            href: '/reports/financial',
            icon: TrendingUp,
            color: 'text-green-600',
        },
        {
            title: 'Operational Reports',
            description: 'Appointment statistics and operational metrics',
            href: '/reports/operational',
            icon: BarChart3,
            color: 'text-blue-600',
        },
        {
            title: 'Patient Reports',
            description: 'Patient demographics and visit patterns',
            href: '/reports/patients',
            icon: Users,
            color: 'text-purple-600',
        },
        {
            title: 'Appointment Reports',
            description: 'Appointment scheduling and utilization',
            href: '/reports/appointments',
            icon: Calendar,
            color: 'text-orange-600',
        },
        {
            title: 'Billing Reports',
            description: 'Payment status and billing analytics',
            href: '/reports/billing',
            icon: Receipt,
            color: 'text-red-600',
        },
        {
            title: 'Medical Records Reports',
            description: 'Treatment outcomes and medical statistics',
            href: '/reports/medical',
            icon: FileText,
            color: 'text-teal-600',
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Reports" />

            <div className="flex flex-col gap-6 p-6">
                <div>
                    <h1 className="text-2xl font-bold">Reports & Analytics</h1>
                    <p className="text-muted-foreground">
                        Generate comprehensive reports and analyze clinic
                        performance
                    </p>
                </div>

                <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    {reportCards.map((report) => (
                        <Card
                            key={report.title}
                            className="transition-shadow hover:shadow-md"
                        >
                            <CardHeader>
                                <div className="flex items-center space-x-2">
                                    <report.icon
                                        className={`h-6 w-6 ${report.color}`}
                                    />
                                    <CardTitle className="text-lg">
                                        {report.title}
                                    </CardTitle>
                                </div>
                                <CardDescription>
                                    {report.description}
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <Button asChild className="w-full">
                                    <Link href={report.href}>View Report</Link>
                                </Button>
                            </CardContent>
                        </Card>
                    ))}
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Quick Stats</CardTitle>
                        <CardDescription>
                            Overview of key metrics
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="grid gap-4 md:grid-cols-4">
                            <div className="text-center">
                                <div className="text-2xl font-bold text-green-600">
                                    $12,450
                                </div>
                                <div className="text-sm text-muted-foreground">
                                    Monthly Revenue
                                </div>
                            </div>
                            <div className="text-center">
                                <div className="text-2xl font-bold text-blue-600">
                                    245
                                </div>
                                <div className="text-sm text-muted-foreground">
                                    Total Appointments
                                </div>
                            </div>
                            <div className="text-center">
                                <div className="text-2xl font-bold text-purple-600">
                                    89
                                </div>
                                <div className="text-sm text-muted-foreground">
                                    Active Patients
                                </div>
                            </div>
                            <div className="text-center">
                                <div className="text-2xl font-bold text-orange-600">
                                    12
                                </div>
                                <div className="text-sm text-muted-foreground">
                                    Doctors
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
