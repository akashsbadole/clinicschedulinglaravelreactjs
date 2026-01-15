import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import { ArrowLeft, Calendar, Edit, FileText, Receipt } from 'lucide-react';

import apiService from '@/lib/api';
import { useEffect, useState } from 'react';

interface User {
    name: string;
    email: string;
    phone: string;
}

interface Patient {
    id: number;
    patient_id: string;
    user: User;
    date_of_birth: string;
    gender: string;
    blood_type: string;
    allergies: string;
    medical_history: string;
    address: string;
    emergency_contact_name: string;
    emergency_contact_phone: string;
    status: string;
    created_at: string;
    updated_at: string;
}

export default function PatientsShow() {
    const { id } = usePage().props as unknown as { id: string };
    const [patient, setPatient] = useState<Patient | null>(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        const fetchPatient = async () => {
            try {
                setLoading(true);
                const response = (await apiService.get(`/patients/${id}`)) as {
                    success: boolean;
                    data: Patient;
                };
                setPatient(response.data);
            } catch (err: any) {
                setError(err.message || 'Failed to fetch patient data');
            } finally {
                setLoading(false);
            }
        };

        if (id) {
            fetchPatient();
        }
    }, [id]);

    if (loading) {
        return (
            <AppLayout breadcrumbs={[]}>
                <div className="flex h-64 items-center justify-center">
                    <div className="h-8 w-8 animate-spin rounded-full border-4 border-primary border-t-transparent"></div>
                </div>
            </AppLayout>
        );
    }

    if (error || !patient) {
        return (
            <AppLayout breadcrumbs={[]}>
                <div className="flex flex-col items-center justify-center gap-4 p-12 text-center text-red-500">
                    <p>{error || 'Patient not found'}</p>
                    <Button asChild variant="outline">
                        <Link href="/patients">Back to Patients</Link>
                    </Button>
                </div>
            </AppLayout>
        );
    }

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Patients',
            href: '/patients',
        },
        {
            title: 'View',
            href: `/patients/${patient.id}`,
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Patient: ${patient.user?.name}`} />

            <div className="flex flex-col gap-6 p-6">
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Button variant="ghost" size="sm" asChild>
                            <Link href="/patients">
                                <ArrowLeft className="mr-2 h-4 w-4" />
                                Back to Patients
                            </Link>
                        </Button>
                                <div>
                                    <h1 className="text-2xl font-bold">
                                        {patient.user?.name}
                                    </h1>
                                    <p className="text-muted-foreground">
                                        Patient ID: {patient.patient_id}
                                    </p>
                                </div>
                            </div>
                            <div className="flex gap-2">
                                <Button variant="outline" asChild>
                                    <Link href={`/patients/${patient.id}/edit`}>
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
                                        <CardTitle>Basic Information</CardTitle>
                                    </CardHeader>
                                    <CardContent className="grid gap-4 md:grid-cols-2">
                                        <div>
                                            <label className="text-sm font-medium text-muted-foreground">
                                                Full Name
                                            </label>
                                            <p className="text-sm">
                                                {patient.user?.name}
                                            </p>
                                        </div>
                                        <div>
                                            <label className="text-sm font-medium text-muted-foreground">
                                                Email
                                            </label>
                                            <p className="text-sm">
                                                {patient.user?.email}
                                            </p>
                                        </div>
                                        <div>
                                            <label className="text-sm font-medium text-muted-foreground">
                                                Phone
                                            </label>
                                            <p className="text-sm">
                                                {patient.user?.phone}
                                            </p>
                                        </div>
                                        <div>
                                            <label className="text-sm font-medium text-muted-foreground">
                                                Date of Birth
                                            </label>
                                            <p className="text-sm">
                                                {patient.date_of_birth}
                                            </p>
                                        </div>
                                        <div>
                                            <label className="text-sm font-medium text-muted-foreground">
                                                Gender
                                            </label>
                                            <p className="text-sm capitalize">
                                                {patient.gender}
                                            </p>
                                        </div>
                                        <div>
                                            <label className="text-sm font-medium text-muted-foreground">
                                                Blood Group
                                            </label>
                                            <p className="text-sm">
                                                {patient.blood_type}
                                            </p>
                                        </div>
                                        <div>
                                            <label className="text-sm font-medium text-muted-foreground">
                                                Status
                                            </label>
                                            <Badge
                                                variant={
                                                    patient.status === 'Active'
                                                        ? 'default'
                                                        : 'secondary'
                                                }
                                            >
                                                {patient.status}
                                            </Badge>
                                        </div>
                                    </CardContent>
                                </Card>

                                <Card>
                                    <CardHeader>
                                        <CardTitle>Medical Information</CardTitle>
                                    </CardHeader>
                                    <CardContent className="space-y-4">
                                        <div>
                                            <label className="text-sm font-medium text-muted-foreground">
                                                Allergies
                                            </label>
                                            <p className="text-sm">
                                                {patient.allergies ||
                                                    'None specified'}
                                            </p>
                                        </div>
                                        <div>
                                            <label className="text-sm font-medium text-muted-foreground">
                                                Medical History
                                            </label>
                                            <p className="text-sm">
                                                {patient.medical_history ||
                                                    'None specified'}
                                            </p>
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
                                        Address
                                    </label>
                                    <p className="text-sm">{patient.address}</p>
                                </div>
                                <div className="grid gap-4 md:grid-cols-2">
                                    <div>
                                        <label className="text-sm font-medium text-muted-foreground">
                                            Emergency Contact
                                        </label>
                                        <p className="text-sm">
                                            {patient.emergency_contact_name}
                                        </p>
                                    </div>
                                    <div>
                                        <label className="text-sm font-medium text-muted-foreground">
                                            Emergency Phone
                                        </label>
                                        <p className="text-sm">
                                            {patient.emergency_contact_phone}
                                        </p>
                                    </div>
                                </div>
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
                                        href={`/appointments/create?patient_id=${patient.id}`}
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
                                        href={`/billing/create?patient_id=${patient.id}`}
                                    >
                                        <Receipt className="mr-2 h-4 w-4" />
                                        Create Bill
                                    </Link>
                                </Button>
                                <Button
                                    className="w-full"
                                    variant="outline"
                                    asChild
                                >
                                    <Link
                                        href={`/medical-records/create?patient_id=${patient.id}`}
                                    >
                                        <FileText className="mr-2 h-4 w-4" />
                                        Add Medical Record
                                    </Link>
                                </Button>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>Recent Activity</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-3">
                                    <div className="flex items-center gap-2 text-sm">
                                        <div className="h-2 w-2 rounded-full bg-green-500"></div>
                                        <span>Patient registered</span>
                                        <span className="ml-auto text-muted-foreground">
                                            {patient.created_at}
                                        </span>
                                    </div>
                                    <div className="flex items-center gap-2 text-sm">
                                        <div className="h-2 w-2 rounded-full bg-blue-500"></div>
                                        <span>Profile updated</span>
                                        <span className="ml--auto text-muted-foreground">
                                            {patient.updated_at}
                                        </span>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
