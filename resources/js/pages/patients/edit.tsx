import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import apiService from '@/lib/api';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import { ArrowLeft, Save } from 'lucide-react';
import { useEffect, useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Patients',
        href: '/patients',
    },
    {
        title: 'Edit',
        href: `/patients/${(usePage().props as any).id}/edit`,
    },
];

interface Patient {
    id: number;
    user: {
        name: string;
        email: string;
        phone: string;
        address: string;
    };
    date_of_birth: string;
    gender: string;
    blood_type: string;
    allergies: string;
    medical_history: string;
    emergency_contact_name: string;
    emergency_contact_phone: string;
}

export default function PatientsEdit() {
    const { id } = usePage().props as any;
    const [patient, setPatient] = useState<Patient | null>(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const [processing, setProcessing] = useState(false);
    const [formErrors, setFormErrors] = useState<any>({});

    const [formData, setFormData] = useState({
        first_name: '',
        last_name: '',
        email: '',
        phone: '',
        date_of_birth: '',
        gender: '',
        address: '',
        blood_type: '',
        allergies: '',
        medical_history: '',
        emergency_contact_name: '',
        emergency_contact_phone: '',
    });

    const fetchPatient = async () => {
        try {
            setLoading(true);
            setError(null);
            const response = (await apiService.get(`/patients/${id}`)) as {
                success: boolean;
                data: Patient;
            };
            setPatient(response.data);
            const nameParts = response.data.user.name.split(' ');
            setFormData({
                first_name: nameParts[0] || '',
                last_name: nameParts.slice(1).join(' ') || '',
                email: response.data.user.email,
                phone: response.data.user.phone,
                date_of_birth: response.data.date_of_birth,
                gender: response.data.gender,
                address: response.data.user.address,
                blood_type: response.data.blood_type,
                allergies: response.data.allergies || '',
                medical_history: response.data.medical_history || '',
                emergency_contact_name:
                    response.data.emergency_contact_name || '',
                emergency_contact_phone:
                    response.data.emergency_contact_phone || '',
            });
        } catch (err) {
            setError(
                err instanceof Error ? err.message : 'Failed to load patient',
            );
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchPatient();
    }, [id]);

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        try {
            setProcessing(true);
            setFormErrors({});
            await apiService.put(`/patients/${id}`, formData);
            // Redirect or show success
            window.location.href = `/patients/${id}`;
        } catch (err: any) {
            if (err.response?.data?.errors) {
                setFormErrors(err.response.data.errors);
            } else {
                setError(
                    err instanceof Error
                        ? err.message
                        : 'Failed to update patient',
                );
            }
        } finally {
            setProcessing(false);
        }
    };

    if (loading) {
        return (
            <AppLayout breadcrumbs={breadcrumbs}>
                <Head title="Edit Patient" />
                <div className="flex flex-col gap-6 p-6">
                    <div className="flex items-center gap-4">
                        <div>
                            <h1 className="text-2xl font-bold">Edit Patient</h1>
                            <p className="text-muted-foreground">
                                Loading patient data...
                            </p>
                        </div>
                    </div>
                </div>
            </AppLayout>
        );
    }

    if (error) {
        return (
            <AppLayout breadcrumbs={breadcrumbs}>
                <Head title="Edit Patient" />
                <div className="flex flex-col gap-6 p-6">
                    <div className="flex items-center gap-4">
                        <div>
                            <h1 className="text-2xl font-bold">Edit Patient</h1>
                            <p className="text-red-600">{error}</p>
                        </div>
                    </div>
                </div>
            </AppLayout>
        );
    }

    if (!patient) {
        return (
            <AppLayout breadcrumbs={breadcrumbs}>
                <Head title="Edit Patient" />
                <div className="flex flex-col gap-6 p-6">
                    <div className="flex items-center gap-4">
                        <div>
                            <h1 className="text-2xl font-bold">Edit Patient</h1>
                            <p className="text-muted-foreground">
                                Patient not found
                            </p>
                        </div>
                    </div>
                </div>
            </AppLayout>
        );
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit Patient: ${patient.user.name}`} />

            <div className="flex flex-col gap-6 p-6">
                <div className="flex items-center gap-4">
                    <Button variant="ghost" size="sm" asChild>
                        <Link href={`/patients/${patient.id}`}>
                            <ArrowLeft className="mr-2 h-4 w-4" />
                            Back to Patient
                        </Link>
                    </Button>
                    <div>
                        <h1 className="text-2xl font-bold">Edit Patient</h1>
                        <p className="text-muted-foreground">
                            Update patient information
                        </p>
                    </div>
                </div>

                <form onSubmit={handleSubmit}>
                    <div className="grid gap-6">
                        <Card>
                            <CardHeader>
                                <CardTitle>Basic Information</CardTitle>
                                <CardDescription>
                                    Update the patient's basic details
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="grid gap-4 md:grid-cols-2">
                                <div className="space-y-2">
                                    <Label htmlFor="first_name">
                                        First Name *
                                    </Label>
                                    <Input
                                        id="first_name"
                                        value={formData.first_name}
                                        onChange={(e) =>
                                            setFormData((prev) => ({
                                                ...prev,
                                                first_name: e.target.value,
                                            }))
                                        }
                                        placeholder="Enter first name"
                                        required
                                    />
                                    {formErrors.first_name && (
                                        <p className="text-sm text-red-600">
                                            {formErrors.first_name}
                                        </p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="last_name">
                                        Last Name *
                                    </Label>
                                    <Input
                                        id="last_name"
                                        value={formData.last_name}
                                        onChange={(e) =>
                                            setFormData((prev) => ({
                                                ...prev,
                                                last_name: e.target.value,
                                            }))
                                        }
                                        placeholder="Enter last name"
                                        required
                                    />
                                    {formErrors.last_name && (
                                        <p className="text-sm text-red-600">
                                            {formErrors.last_name}
                                        </p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="email">Email</Label>
                                    <Input
                                        id="email"
                                        type="email"
                                        value={formData.email}
                                        onChange={(e) =>
                                            setFormData((prev) => ({
                                                ...prev,
                                                email: e.target.value,
                                            }))
                                        }
                                        placeholder="Enter email address"
                                    />
                                    {formErrors.email && (
                                        <p className="text-sm text-red-600">
                                            {formErrors.email}
                                        </p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="phone">Phone *</Label>
                                    <Input
                                        id="phone"
                                        value={formData.phone}
                                        onChange={(e) =>
                                            setFormData((prev) => ({
                                                ...prev,
                                                phone: e.target.value,
                                            }))
                                        }
                                        placeholder="Enter phone number"
                                        required
                                    />
                                    {formErrors.phone && (
                                        <p className="text-sm text-red-600">
                                            {formErrors.phone}
                                        </p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="date_of_birth">
                                        Date of Birth
                                    </Label>
                                    <Input
                                        id="date_of_birth"
                                        type="date"
                                        value={formData.date_of_birth}
                                        onChange={(e) =>
                                            setFormData((prev) => ({
                                                ...prev,
                                                date_of_birth: e.target.value,
                                            }))
                                        }
                                    />
                                    {formErrors.date_of_birth && (
                                        <p className="text-sm text-red-600">
                                            {formErrors.date_of_birth}
                                        </p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="gender">Gender</Label>
                                    <Select
                                        value={formData.gender}
                                        onValueChange={(value) =>
                                            setFormData((prev) => ({
                                                ...prev,
                                                gender: value,
                                            }))
                                        }
                                    >
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select gender" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="male">
                                                Male
                                            </SelectItem>
                                            <SelectItem value="female">
                                                Female
                                            </SelectItem>
                                            <SelectItem value="other">
                                                Other
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    {formErrors.gender && (
                                        <p className="text-sm text-red-600">
                                            {formErrors.gender}
                                        </p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="blood_type">
                                        Blood Group
                                    </Label>
                                    <Select
                                        value={formData.blood_type}
                                        onValueChange={(value) =>
                                            setFormData((prev) => ({
                                                ...prev,
                                                blood_type: value,
                                            }))
                                        }
                                    >
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select blood group" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="A+">
                                                A+
                                            </SelectItem>
                                            <SelectItem value="A-">
                                                A-
                                            </SelectItem>
                                            <SelectItem value="B+">
                                                B+
                                            </SelectItem>
                                            <SelectItem value="B-">
                                                B-
                                            </SelectItem>
                                            <SelectItem value="AB+">
                                                AB+
                                            </SelectItem>
                                            <SelectItem value="AB-">
                                                AB-
                                            </SelectItem>
                                            <SelectItem value="O+">
                                                O+
                                            </SelectItem>
                                            <SelectItem value="O-">
                                                O-
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    {formErrors.blood_type && (
                                        <p className="text-sm text-red-600">
                                            {formErrors.blood_type}
                                        </p>
                                    )}
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>Medical Information</CardTitle>
                                <CardDescription>
                                    Update medical details and history
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="grid gap-4">
                                <div className="space-y-2">
                                    <Label htmlFor="allergies">Allergies</Label>
                                    <Textarea
                                        id="allergies"
                                        value={formData.allergies}
                                        onChange={(e) =>
                                            setFormData((prev) => ({
                                                ...prev,
                                                allergies: e.target.value,
                                            }))
                                        }
                                        placeholder="List any known allergies"
                                        rows={3}
                                    />
                                    {formErrors.allergies && (
                                        <p className="text-sm text-red-600">
                                            {formErrors.allergies}
                                        </p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="medical_history">
                                        Medical History
                                    </Label>
                                    <Textarea
                                        id="medical_history"
                                        value={formData.medical_history}
                                        onChange={(e) =>
                                            setFormData((prev) => ({
                                                ...prev,
                                                medical_history: e.target.value,
                                            }))
                                        }
                                        placeholder="List any chronic conditions or medical history"
                                        rows={3}
                                    />
                                    {formErrors.medical_history && (
                                        <p className="text-sm text-red-600">
                                            {formErrors.medical_history}
                                        </p>
                                    )}
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>Contact Information</CardTitle>
                                <CardDescription>
                                    Update address and emergency contact details
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="grid gap-4">
                                <div className="space-y-2">
                                    <Label htmlFor="address">Address</Label>
                                    <Textarea
                                        id="address"
                                        value={formData.address}
                                        onChange={(e) =>
                                            setFormData((prev) => ({
                                                ...prev,
                                                address: e.target.value,
                                            }))
                                        }
                                        placeholder="Enter full address"
                                        rows={3}
                                    />
                                    {formErrors.address && (
                                        <p className="text-sm text-red-600">
                                            {formErrors.address}
                                        </p>
                                    )}
                                </div>

                                <div className="grid gap-4 md:grid-cols-2">
                                    <div className="space-y-2">
                                        <Label htmlFor="emergency_contact_name">
                                            Emergency Contact Name
                                        </Label>
                                        <Input
                                            id="emergency_contact_name"
                                            value={
                                                formData.emergency_contact_name
                                            }
                                            onChange={(e) =>
                                                setFormData((prev) => ({
                                                    ...prev,
                                                    emergency_contact_name:
                                                        e.target.value,
                                                }))
                                            }
                                            placeholder="Enter emergency contact name"
                                        />
                                        {formErrors.emergency_contact_name && (
                                            <p className="text-sm text-red-600">
                                                {
                                                    formErrors.emergency_contact_name
                                                }
                                            </p>
                                        )}
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="emergency_contact_phone">
                                            Emergency Contact Phone
                                        </Label>
                                        <Input
                                            id="emergency_contact_phone"
                                            value={
                                                formData.emergency_contact_phone
                                            }
                                            onChange={(e) =>
                                                setFormData((prev) => ({
                                                    ...prev,
                                                    emergency_contact_phone:
                                                        e.target.value,
                                                }))
                                            }
                                            placeholder="Enter emergency contact phone"
                                        />
                                        {formErrors.emergency_contact_phone && (
                                            <p className="text-sm text-red-600">
                                                {
                                                    formErrors.emergency_contact_phone
                                                }
                                            </p>
                                        )}
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <div className="flex justify-end gap-4">
                            <Button variant="outline" asChild>
                                <Link href={`/patients/${patient.id}`}>
                                    Cancel
                                </Link>
                            </Button>
                            <Button type="submit" disabled={processing}>
                                <Save className="mr-2 h-4 w-4" />
                                {processing ? 'Updating...' : 'Update Patient'}
                            </Button>
                        </div>
                    </div>
                </form>
            </div>
        </AppLayout>
    );
}
