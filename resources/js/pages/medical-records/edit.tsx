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
import { ArrowLeft, FileText, Save, Edit, Upload, File, Trash2, Clock, CheckCircle2 } from 'lucide-react';

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
    const [history, setHistory] = useState<MedicalRecord[]>([]);
    const [newAttachments, setNewAttachments] = useState<File[]>([]);

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

        const fetchHistory = async (patientId: number) => {
            try {
                const response = (await apiService.get(`/medical-records/patient/${patientId}`)) as {
                    success: boolean;
                    data: MedicalRecord[];
                };
                // Filter out current record and sort by date
                const otherRecords = response.data
                    .filter((r) => r.id !== parseInt(id))
                    .sort((a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime());
                setHistory(otherRecords);
            } catch (err) {
                console.error('Failed to fetch record history:', err);
            }
        };

        if (id) {
            fetchRecord();
        }
    }, [id]);

    useEffect(() => {
        if (record?.patient?.id) {
            const fetchHistory = async (patientId: number) => {
                try {
                    const response = (await apiService.get(`/medical-records/patient/${patientId}`)) as {
                        success: boolean;
                        data: MedicalRecord[];
                    };
                    const otherRecords = response.data
                        .filter((r) => r.id !== parseInt(id))
                        .sort((a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime());
                    setHistory(otherRecords);
                } catch (err) {
                    console.error('Failed to fetch record history:', err);
                }
            };
            fetchHistory(record.patient.id);
        }
    }, [record?.patient?.id, id]);

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        try {
            setProcessing(true);
            setError(null);
            setSuccess(false);

            const data = new FormData();
            data.append('_method', 'PUT'); // Laravel method spoofing for FormData with PUT
            data.append('record_type', formData.record_type);
            data.append('content', formData.content);
            data.append('severity', formData.severity);
            data.append('is_confidential', formData.is_confidential ? '1' : '0');

            newAttachments.forEach((file) => {
                data.append('attachments[]', file);
            });

            await apiService.post(`/medical-records/${id}`, data, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            });

            setSuccess(true);
            setNewAttachments([]);
            // Refresh record to show new attachments
            const refreshed = (await apiService.get(`/medical-records/${id}`)) as {
                success: boolean;
                data: MedicalRecord;
            };
            setRecord(refreshed.data);
            
            setTimeout(() => {
                setSuccess(false);
            }, 3000);
        } catch (err: any) {
            setError(err.message || 'Failed to update medical record');
        } finally {
            setProcessing(false);
        }
    };

    const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        if (e.target.files) {
            const files = Array.from(e.target.files);
            setNewAttachments((prev) => [...prev, ...files]);
        }
    };

    const removeNewAttachment = (index: number) => {
        setNewAttachments((prev) => prev.filter((_, i) => i !== index));
    };

    const getAttachmentName = (path: string) => {
        return path.split('/').pop() || 'Attachment';
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
                                <Upload className="h-5 w-5" />
                                Attachments (Images, X-rays, Documents)
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div className="space-y-2">
                                    <Label>New Attachments</Label>
                                    <div className="flex flex-col gap-2">
                                        <div className="relative flex items-center justify-center rounded-lg border-2 border-dashed border-muted-foreground/25 p-6 transition-colors hover:border-muted-foreground/50">
                                            <input
                                                type="file"
                                                multiple
                                                onChange={handleFileChange}
                                                className="absolute inset-0 cursor-pointer opacity-0"
                                            />
                                            <div className="flex flex-col items-center gap-1 text-center">
                                                <Upload className="h-8 w-8 text-muted-foreground" />
                                                <p className="text-sm text-muted-foreground">
                                                    Click or drag and drop to upload
                                                </p>
                                                <p className="text-xs text-muted-foreground/75">
                                                    PDF, JPG, PNG, DOCX up to 10MB
                                                </p>
                                            </div>
                                        </div>

                                        {newAttachments.length > 0 && (
                                            <div className="mt-2 space-y-2">
                                                {newAttachments.map((file, index) => (
                                                    <div key={index} className="flex items-center justify-between rounded-md bg-muted/50 p-2">
                                                        <div className="flex items-center gap-2 overflow-hidden">
                                                            <File className="h-4 w-4 shrink-0" />
                                                            <span className="truncate text-sm">{file.name}</span>
                                                            <span className="text-xs text-muted-foreground">({(file.size / 1024).toFixed(0)} KB)</span>
                                                        </div>
                                                        <Button
                                                            type="button"
                                                            variant="ghost"
                                                            size="icon"
                                                            className="h-8 w-8 text-red-500 hover:bg-red-500/10 hover:text-red-500"
                                                            onClick={() => removeNewAttachment(index)}
                                                        >
                                                            <Trash2 className="h-4 w-4" />
                                                        </Button>
                                                    </div>
                                                ))}
                                            </div>
                                        )}
                                    </div>
                                </div>

                                <div className="space-y-2">
                                    <Label>Existing Attachments</Label>
                                    <div className="flex flex-col gap-2">
                                        {record.attachments && record.attachments.length > 0 ? (
                                            record.attachments.map((path, index) => (
                                                <div key={index} className="flex items-center justify-between rounded-md border p-2">
                                                    <div className="flex items-center gap-2 overflow-hidden">
                                                        <FileText className="h-4 w-4 shrink-0" />
                                                        <span className="truncate text-sm">{getAttachmentName(path)}</span>
                                                    </div>
                                                    <div className="flex gap-1">
                                                        <Button
                                                            type="button"
                                                            variant="ghost"
                                                            size="sm"
                                                            asChild
                                                        >
                                                            <a href={`/storage/${path}`} target="_blank" rel="noopener noreferrer">
                                                                View
                                                            </a>
                                                        </Button>
                                                    </div>
                                                </div>
                                            ))
                                        ) : (
                                            <div className="py-4 text-center text-sm text-muted-foreground">
                                                No attachments found.
                                            </div>
                                        )}
                                    </div>
                                </div>
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

                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Clock className="h-5 w-5" />
                                Patient Record History
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            {history.length > 0 ? (
                                <div className="space-y-4">
                                    {history.map((prevRecord) => (
                                        <div key={prevRecord.id} className="relative rounded-lg border p-4 pl-10">
                                            <div className="absolute left-4 top-5 flex h-4 w-4 items-center justify-center">
                                                <CheckCircle2 className="h-3 w-3 text-primary" />
                                            </div>
                                            <div className="flex items-center justify-between">
                                                <div className="flex items-center gap-2">
                                                    <Badge variant="outline">{prevRecord.record_type}</Badge>
                                                    <span className="text-xs text-muted-foreground">
                                                        {new Date(prevRecord.created_at).toLocaleDateString()}
                                                    </span>
                                                </div>
                                                <Button variant="ghost" size="sm" asChild>
                                                    <Link href={`/medical-records/${prevRecord.id}`}>View Full</Link>
                                                </Button>
                                            </div>
                                            <p className="mt-2 line-clamp-3 text-sm text-muted-foreground">
                                                {prevRecord.content}
                                            </p>
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                <div className="py-8 text-center text-muted-foreground">
                                    No previous medical records found for this patient.
                                </div>
                            )}
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
