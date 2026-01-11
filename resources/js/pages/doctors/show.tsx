import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import { ArrowLeft, Calendar, Edit, Users } from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Doctors',
        href: '/doctors',
    },
    {
        title: 'View',
        href: `/doctors/${usePage().props.id}`,
    },
];

export default function DoctorsShow() {
    // Mock data
    const doctor = {
        id: 1,
        name: 'Dr. Sarah Johnson',
        email: 'sarah.johnson@example.com',
        phone: '+1234567890',
        specialization: 'Cardiology',
        license_number: 'MD123456',
        experience_years: 15,
        consultation_fee: 150.0,
        address: '123 Medical Center, City, State 12345',
        bio: 'Experienced cardiologist with over 15 years of practice...',
        status: 'Active',
        created_at: '2020-01-01',
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Doctor: ${doctor.name}`} />

            <div className="flex flex-col gap-6 p-6">
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Button variant="ghost" size="sm" asChild>
                            <Link href="/doctors">
                                <ArrowLeft className="mr-2 h-4 w-4" />
                                Back to Doctors
                            </Link>
                        </Button>
                        <div>
                            <h1 className="text-2xl font-bold">
                                {doctor.name}
                            </h1>
                            <p className="text-muted-foreground">
                                Doctor ID: #{doctor.id}
                            </p>
                        </div>
                    </div>
                    <div className="flex gap-2">
                        <Button variant="outline" asChild>
                            <Link href={`/doctors/${doctor.id}/edit`}>
                                <Edit className="mr-2 h-4 w-4" />
                                Edit
                            </Link>
                        </Button>
                    </div>
                </div>

                <div className="grid gap-6 md:grid-cols-3">
                    <div className="space-y-6 md:col-span-2">
                        <Card>
                            <CardHeader>
                                <CardTitle>Professional Information</CardTitle>
                            </CardHeader>
                            <CardContent className="grid gap-4 md:grid-cols-2">
                                <div>
                                    <label className="text-sm font-medium text-muted-foreground">
                                        Specialization
                                    </label>
                                    <p className="text-sm">
                                        {doctor.specialization}
                                    </p>
                                </div>
                                <div>
                                    <label className="text-sm font-medium text-muted-foreground">
                                        License Number
                                    </label>
                                    <p className="text-sm">
                                        {doctor.license_number}
                                    </p>
                                </div>
                                <div>
                                    <label className="text-sm font-medium text-muted-foreground">
                                        Experience
                                    </label>
                                    <p className="text-sm">
                                        {doctor.experience_years} years
                                    </p>
                                </div>
                                <div>
                                    <label className="text-sm font-medium text-muted-foreground">
                                        Consultation Fee
                                    </label>
                                    <p className="text-sm">
                                        ${doctor.consultation_fee}
                                    </p>
                                </div>
                                <div>
                                    <label className="text-sm font-medium text-muted-foreground">
                                        Status
                                    </label>
                                    <Badge
                                        variant={
                                            doctor.status === 'Active'
                                                ? 'default'
                                                : 'secondary'
                                        }
                                    >
                                        {doctor.status}
                                    </Badge>
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>Contact Information</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div>
                                    <label className="text-sm font-medium text-muted-foreground">
                                        Email
                                    </label>
                                    <p className="text-sm">{doctor.email}</p>
                                </div>
                                <div>
                                    <label className="text-sm font-medium text-muted-foreground">
                                        Phone
                                    </label>
                                    <p className="text-sm">{doctor.phone}</p>
                                </div>
                                <div>
                                    <label className="text-sm font-medium text-muted-foreground">
                                        Address
                                    </label>
                                    <p className="text-sm">{doctor.address}</p>
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>Biography</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <p className="text-sm">{doctor.bio}</p>
                            </CardContent>
                        </Card>
                    </div>

                    <div className="space-y-6">
                        <Card>
                            <CardHeader>
                                <CardTitle>Quick Actions</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-2">
                                <Button
                                    className="w-full"
                                    variant="outline"
                                    asChild
                                >
                                    <Link
                                        href={`/appointments/create?doctor_id=${doctor.id}`}
                                    >
                                        <Calendar className="mr-2 h-4 w-4" />
                                        Schedule Appointment
                                    </Link>
                                </Button>
                                <Button
                                    className="w-full"
                                    variant="outline"
                                    asChild
                                >
                                    <Link
                                        href={`/doctors/${doctor.id}/patients`}
                                    >
                                        <Users className="mr-2 h-4 w-4" />
                                        View Patients
                                    </Link>
                                </Button>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>Statistics</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="flex justify-between">
                                    <span className="text-sm text-muted-foreground">
                                        Total Appointments
                                    </span>
                                    <span className="text-sm font-medium">
                                        245
                                    </span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-sm text-muted-foreground">
                                        This Month
                                    </span>
                                    <span className="text-sm font-medium">
                                        32
                                    </span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-sm text-muted-foreground">
                                        Average Rating
                                    </span>
                                    <span className="text-sm font-medium">
                                        4.8/5
                                    </span>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
