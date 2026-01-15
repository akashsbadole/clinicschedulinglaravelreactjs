import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { ArrowLeft, FileText, Save, Edit, Upload, File, Trash2 } from 'lucide-react';
import { useEffect, useState } from 'react';
import apiService from '@/lib/api';
import { Badge } from '@/components/ui/badge';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Medical Records',
        href: '/medical-records',
    },
    {
        title: 'Create Record',
        href: '/medical-records/create',
    },
];

interface Patient {
    id: number;
    user: {
        name: string;
    };
}

interface Doctor {
    id: number;
    user: {
        name: string;
    };
}

export default function MedicalRecordCreate() {
    const { auth } = usePage().props as any;
    const [formData, setFormData] = useState({
        clinic_id: auth.user.clinic_id || '',
        patient_id: '',
        doctor_id: '',
        record_type: 'notes',
        content: '',
        severity: 'medium',
        is_confidential: false,
    });

    const [patients, setPatients] = useState<Patient[]>([]);
    const [doctors, setDoctors] = useState<Doctor[]>([]);
    const [loading, setLoading] = useState(true);
    const [fetchingData, setFetchingData] = useState(true);
    const [processing, setProcessing] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const [success, setSuccess] = useState(false);
    const [newAttachments, setNewAttachments] = useState<File[]>([]);

    useEffect(() => {
        const queryParams = new URLSearchParams(window.location.search);
        const patientIdParam = queryParams.get('patient_id');
        if (patientIdParam) {
            setFormData(prev => ({ ...prev, patient_id: patientIdParam }));
        }

        const fetchData = async () => {
            try {
                setFetchingData(true);
                const [patientsRes, doctorsRes] = await Promise.all([
                    apiService.get('/patients'),
                    apiService.get('/doctors')
                ]);
                
                setPatients((patientsRes as any).data || []);
                setDoctors((doctorsRes as any).data || []);
            } catch (err: any) {
                console.error('Failed to fetch patients/doctors:', err);
                setError('Failed to load patients or doctors. Please try again.');
            } finally {
                setFetchingData(false);
                setLoading(false);
            }
        };

        fetchData();
    }, []);

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        
        if (!formData.patient_id || !formData.doctor_id) {
            setError('Please select both a patient and a doctor.');
            return;
        }

        try {
            setProcessing(true);
            setError(null);
            setSuccess(false);

            const data = new FormData();
            data.append('clinic_id', formData.clinic_id);
            data.append('patient_id', formData.patient_id);
            data.append('doctor_id', formData.doctor_id);
            data.append('record_type', formData.record_type);
            data.append('content', formData.content);
            data.append('severity', formData.severity);
            data.append('is_confidential', formData.is_confidential ? '1' : '0');

            newAttachments.forEach((file) => {
                data.append('attachments[]', file);
            });

            await apiService.post('/medical-records', data, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            });

            setSuccess(true);
            setTimeout(() => {
                window.location.href = '/medical-records';
            }, 1500);
        } catch (err: any) {
            setError(err.message || 'Failed to create medical record');
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

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create Medical Record" />

            <div className="flex flex-col gap-6 p-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold">
                            Create Medical Record
                        </h1>
                        <p className="text-muted-foreground">
                            Create comprehensive electronic medical record
                        </p>
                    </div>
                    <div className="flex gap-2">
                        <Button variant="outline" asChild>
                            <Link href="/medical-records">
                                <ArrowLeft className="mr-2 h-4 w-4" />
                                Cancel
                            </Link>
                        </Button>
                        <Button onClick={handleSubmit} disabled={processing}>
                            <Save className="mr-2 h-4 w-4" />
                            {processing ? 'Creating...' : 'Create Record'}
                        </Button>
                    </div>
                </div>

                <form onSubmit={handleSubmit} className="space-y-6">
                    {success && (
                        <div className="rounded-lg bg-green-500/10 p-4 text-green-500">
                            Medical record created successfully! Redirecting...
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
                                Patient & Doctor Selection
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div>
                                    <Label htmlFor="patient_id">Select Patient</Label>
                                    <select
                                        id="patient_id"
                                        value={formData.patient_id}
                                        onChange={(e) =>
                                            setFormData({
                                                ...formData,
                                                patient_id: e.target.value,
                                            })
                                        }
                                        className="mt-1 flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                        required
                                        disabled={fetchingData}
                                    >
                                        <option value="">Select a patient...</option>
                                        {patients.map((p) => (
                                            <option key={p.id} value={p.id}>
                                                {p.user.name}
                                            </option>
                                        ))}
                                    </select>
                                </div>
                                <div>
                                    <Label htmlFor="doctor_id">Select Doctor</Label>
                                    <select
                                        id="doctor_id"
                                        value={formData.doctor_id}
                                        onChange={(e) =>
                                            setFormData({
                                                ...formData,
                                                doctor_id: e.target.value,
                                            })
                                        }
                                        className="mt-1 flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                        required
                                        disabled={fetchingData}
                                    >
                                        <option value="">Select a doctor...</option>
                                        {doctors.map((d) => (
                                            <option key={d.id} value={d.id}>
                                                {d.user.name}
                                            </option>
                                        ))}
                                    </select>
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
                            <div className="space-y-2">
                                <Label>Upload Attachments</Label>
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
                            <Link href="/medical-records">
                                Cancel
                            </Link>
                        </Button>
                        <Button type="submit" disabled={processing || fetchingData}>
                            <Save className="mr-2 h-4 w-4" />
                            {processing ? 'Creating Record...' : 'Create Medical Record'}
                        </Button>
                    </div>
                </form>
            </div>
        </AppLayout>
    );
}
