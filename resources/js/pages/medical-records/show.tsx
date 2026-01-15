import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import {
    ArrowLeft,
    Edit,
    FileText,
    HeartPulse,
    Microscope,
    Pill,
} from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Medical Records',
        href: '/medical-records',
    },
    {
        title: 'Record Details',
        href: '/medical-records/1',
    },
];

import { useEffect, useState } from 'react';
import apiService from '@/lib/api';

interface MedicalRecord {
    id: number;
    patient: {
        id: number;
        user: {
            name: string;
            date_of_birth: string;
            gender: string;
        };
    };
    doctor: {
        id: number;
        user: {
            name: string;
        };
    };
    record_type: string;
    created_at: string;
    content: string;
    severity: string;
    is_confidential: boolean;
    attachments: string[];
}

export default function MedicalRecordShow() {
    const id = window.location.pathname.split('/').pop();
    
    const [record, setRecord] = useState<MedicalRecord | null>(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        const fetchRecord = async () => {
            try {
                setLoading(true);
                setError(null);
                const response = (await apiService.get(`/medical-records/${id}`)) as {
                    success: boolean;
                    data: MedicalRecord;
                };
                setRecord(response.data);
            } catch (err: any) {
                setError(err.message || 'Failed to load medical record');
            } finally {
                setLoading(false);
            }
        };

        if (id) {
            fetchRecord();
        }
    }, [id]);

    if (loading) {
        return (
            <AppLayout breadcrumbs={breadcrumbs}>
                <div className="flex h-[400px] items-center justify-center">
                    <div className="h-8 w-8 animate-spin rounded-full border-4 border-primary border-t-transparent"></div>
                </div>
            </AppLayout>
        );
    }

    if (error || !record) {
        return (
            <AppLayout breadcrumbs={breadcrumbs}>
                <div className="flex flex-col items-center justify-center gap-4 p-12">
                    <p className="text-xl font-semibold text-red-500">{error || 'Record not found'}</p>
                    <Button asChild>
                        <Link href="/medical-records">Back to Records</Link>
                    </Button>
                </div>
            </AppLayout>
        );
    }

    // Calculate age from date_of_birth
    const calculateAge = (dob: string) => {
        if (!dob) return 'N/A';
        const birthDate = new Date(dob);
        const today = new Date();
        let age = today.getFullYear() - birthDate.getFullYear();
        const m = today.getMonth() - birthDate.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        return age;
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Medical Record #${record.id}`} />

            <div className="flex flex-col gap-6 p-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold">
                            Medical Record #{record.id}
                        </h1>
                        <p className="text-muted-foreground">
                            Comprehensive Electronic Medical Record for{' '}
                            {record.patient.user.name}
                        </p>
                    </div>
                            <div className="flex gap-2">
                        <Button variant="outline" asChild>
                            <Link href="/medical-records">
                                <ArrowLeft className="mr-2 h-4 w-4" />
                                Back to Records
                            </Link>
                        </Button>
                        <Button variant="secondary" asChild>
                            <Link href={`/medical-records/${record.id}/edit`}>
                                <Edit className="mr-2 h-4 w-4" />
                                Edit Record
                            </Link>
                        </Button>
                    </div>
                </div>

                <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    {/* Patient Information */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <FileText className="h-5 w-5" />
                                Patient Information
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <Label className="text-sm font-medium">
                                        Name
                                    </Label>
                                    <p className="text-lg font-semibold">
                                        {record.patient.user.name}
                                    </p>
                                </div>
                                <div>
                                    <Label className="text-sm font-medium">
                                        Age / Gender
                                    </Label>
                                    <p className="text-lg">
                                        {calculateAge(record.patient.user.date_of_birth)} years /{' '}
                                        {record.patient.user.gender}
                                    </p>
                                </div>
                            </div>
                            <div>
                                <Label className="text-sm font-medium">
                                    Doctor
                                </Label>
                                <p className="text-lg">{record.doctor.user.name}</p>
                            </div>
                            <div>
                                <Label className="text-sm font-medium">
                                    Record Type
                                </Label>
                                <Badge variant="outline" className="mt-1 capitalize">
                                    {record.record_type}
                                </Badge>
                            </div>
                            <div>
                                <Label className="text-sm font-medium">
                                    Date
                                </Label>
                                <p className="text-lg">{new Date(record.created_at).toLocaleDateString()}</p>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Quick Stats/Metadata */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <HeartPulse className="h-5 w-5" />
                                Record Status
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div>
                                <Label className="text-sm font-medium">Severity</Label>
                                <div className="mt-1">
                                    <Badge 
                                        variant={
                                            record.severity === 'high' || record.severity === 'critical' 
                                                ? 'destructive' 
                                                : record.severity === 'medium' 
                                                    ? 'default' 
                                                    : 'secondary'
                                        }
                                        className="capitalize"
                                    >
                                        {record.severity}
                                    </Badge>
                                </div>
                            </div>
                            <div>
                                <Label className="text-sm font-medium">Confidentiality</Label>
                                <p className="text-sm mt-1">
                                    {record.is_confidential ? '🔒 Confidential Record' : '🔓 Standard Record'}
                                </p>
                            </div>
                            <div>
                                <Label className="text-sm font-medium">Attachments</Label>
                                <p className="text-sm mt-1 text-muted-foreground">
                                    {record.attachments?.length || 0} file(s) attached
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Metadata */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <FileText className="h-5 w-5" />
                                Additional Info
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div>
                                <Label className="text-sm font-medium">Record ID</Label>
                                <p className="text-sm">{record.id}</p>
                            </div>
                            <div>
                                <Label className="text-sm font-medium">Last Updated</Label>
                                <p className="text-sm">{new Date(record.created_at).toLocaleString()}</p>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Main Content / Clinical Notes */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <FileText className="h-5 w-5" />
                            Clinical Notes & Observations
                        </CardTitle>
                        <CardDescription>
                            Primary content and clinical findings for this record
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="prose prose-sm max-w-none dark:prose-invert">
                            <div className="whitespace-pre-wrap rounded-lg border bg-muted/30 p-4 font-mono text-sm leading-relaxed">
                                {record.content || 'No clinical notes provided.'}
                            </div>
                        </div>
                    </CardContent>
                </Card>


            </div>

        </AppLayout>
    );
}
