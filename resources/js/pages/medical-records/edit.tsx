import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, FileText, Save, Edit } from 'lucide-react';

import { useEffect, useState } from 'react';
import apiService from '@/lib/api';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Medical Records',
        href: '/medical-records',
    },
    {
        title: 'Edit Record',
        href: '#', // This will be dynamically updated or not used directly
    },
];
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

export default function MedicalRecordEdit() {
    const id = window.location.pathname.split('/')[2];
    
    const [record, setRecord] = useState<MedicalRecord | null>(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const [processing, setProcessing] = useState(false);
    const [success, setSuccess] = useState(false);

    const [formData, setFormData] = useState({
        record_type: '',
        content: '',
        severity: 'medium',
        is_confidential: false,
    });

    useEffect(() => {
        const fetchRecord = async () => {
            try {
                setLoading(true);
                const response = (await apiService.get(`/medical-records/${id}`)) as {
                    success: boolean;
                    data: MedicalRecord;
                };
                setRecord(response.data);
                setFormData({
                    record_type: response.data.record_type,
                    content: response.data.content,
                    severity: response.data.severity,
                    is_confidential: response.data.is_confidential,
                });
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

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        try {
            setProcessing(true);
            setError(null);
            setSuccess(false);
            await apiService.put(`/medical-records/${id}`, formData);
            setSuccess(true);
            setTimeout(() => {
                window.location.href = `/medical-records/${id}`;
            }, 1500);
        } catch (err: any) {
            setError(err.message || 'Failed to update medical record');
        } finally {
            setProcessing(false);
        }
    };

    if (loading) {
        return (
            <AppLayout breadcrumbs={breadcrumbs}>
                <div className="flex h-[400px] items-center justify-center">
                    <div className="h-8 w-8 animate-spin rounded-full border-4 border-primary border-t-transparent"></div>
                </div>
            </AppLayout>
        );
    }

    if (error && !record) {
        return (
            <AppLayout breadcrumbs={breadcrumbs}>
                <div className="flex flex-col items-center justify-center gap-4 p-12">
                    <p className="text-xl font-semibold text-red-500">{error}</p>
                    <Button asChild>
                        <Link href="/medical-records">Back to Records</Link>
                    </Button>
                </div>
            </AppLayout>
        );
    }

    if (!record) {
        return (
            <AppLayout breadcrumbs={breadcrumbs}>
                <div className="flex flex-col items-center justify-center gap-4 p-12">
                    <p className="text-xl font-semibold text-red-500">Medical record not found.</p>
                    <Button asChild>
                        <Link href="/medical-records">Back to Records</Link>
                    </Button>
                </div>
            </AppLayout>
        );
    }

    const currentBreadcrumbs = breadcrumbs.map((item) =>
        item.title === 'Edit Record' ? { ...item, href: `/medical-records/${record.id}/edit` } : item
    );

    return (
        <AppLayout breadcrumbs={currentBreadcrumbs}>
            <Head title="Edit Medical Record" />

            <div className="flex flex-col gap-6 p-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold">
                            Edit Medical Record
                        </h1>
                        <p className="text-muted-foreground">
                            Update comprehensive electronic medical record
                        </p>
                    </div>
                    <div className="flex gap-2">
                        <Button variant="outline" asChild>
                            <Link href={`/medical-records/${record.id}`}>
                                <ArrowLeft className="mr-2 h-4 w-4" />
                                Cancel
                            </Link>
                        </Button>
                        <Button onClick={handleSubmit} disabled={processing}>
                            <Save className="mr-2 h-4 w-4" />
                            {processing ? 'Saving...' : 'Save Changes'}
                        </Button>
                    </div>
                </div>

                <form onSubmit={handleSubmit} className="space-y-6">
                    {success && (
                        <div className="rounded-lg bg-green-500/10 p-4 text-green-500">
                            Medical record updated successfully! Redirecting...
                        </div>
                    )}
                    
                    {error && (
                        <div className="rounded-lg bg-red-500/10 p-4 text-red-500">
                            {error}
                        </div>
                    )}

                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <FileText className="h-5 w-5" />
                                Patient & Doctor Information
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div>
                                    <Label>Patient</Label>
                                    <p className="mt-1 text-lg font-semibold">{record.patient.user.name}</p>
                                </div>
                                <div>
                                    <Label>Doctor</Label>
                                    <p className="mt-1 text-lg font-semibold">{record.doctor.user.name}</p>
                                </div>
                            </div>
                            <Separator />
                            <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div>
                                    <Label htmlFor="record_type">Record Type</Label>
                                    <select
                                        id="record_type"
                                        className="mt-1 flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                        value={formData.record_type}
                                        onChange={(e) =>
                                            setFormData({
                                                ...formData,
                                                record_type: e.target.value,
                                            })
                                        }
                                        required
                                    >
                                        <option value="notes">Clinical Notes</option>
                                        <option value="diagnosis">Diagnosis</option>
                                        <option value="prescription">Prescription</option>
                                        <option value="lab_result">Lab Result</option>
                                        <option value="imaging">Imaging</option>
                                        <option value="consultation">Consultation</option>
                                    </select>
                                </div>
                                <div>
                                    <Label htmlFor="severity">Severity</Label>
                                    <select
                                        id="severity"
                                        value={formData.severity}
                                        onChange={(e) =>
                                            setFormData({
                                                ...formData,
                                                severity: e.target.value,
                                            })
                                        }
                                        className="mt-1 flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                    >
                                        <option value="low">Low</option>
                                        <option value="medium">Medium</option>
                                        <option value="high">High</option>
                                        <option value="critical">Critical</option>
                                    </select>
                                </div>
                            </div>
                            <div className="flex items-center space-x-2">
                                <input
                                    type="checkbox"
                                    id="is_confidential"
                                    checked={formData.is_confidential}
                                    onChange={(e) =>
                                        setFormData({
                                            ...formData,
                                            is_confidential: e.target.checked,
                                        })
                                    }
                                    className="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                                />
                                <Label htmlFor="is_confidential">Mark as Confidential</Label>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Edit className="h-5 w-5" />
                                Clinical Content
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-2">
                                <Label htmlFor="content">Medical Notes & Findings</Label>
                                <Textarea
                                    id="content"
                                    value={formData.content}
                                    onChange={(e) =>
                                        setFormData({
                                            ...formData,
                                            content: e.target.value,
                                        })
                                    }
                                    className="min-h-[400px] font-mono text-sm leading-relaxed"
                                    placeholder="Enter detailed clinical findings, observations, and recommendations..."
                                    required
                                />
                            </div>
                        </CardContent>
                    </Card>

                    <div className="flex justify-end gap-3">
                        <Button
                            type="button"
                            variant="outline"
                            asChild
                        >
                            <Link href={`/medical-records/${record.id}`}>
                                Cancel
                            </Link>
                        </Button>
                        <Button type="submit" disabled={processing}>
                            <Save className="mr-2 h-4 w-4" />
                            {processing ? 'Saving Changes...' : 'Save Medical Record'}
                        </Button>
                    </div>
                </form>
            </div>
        </AppLayout>
    );
}
