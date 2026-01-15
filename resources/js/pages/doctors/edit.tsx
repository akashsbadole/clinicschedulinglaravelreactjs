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

interface Doctor {
    id: number;
    user: {
        name: string;
        email: string;
        phone: string;
        address: string;
    };
    specialization: string;
    license_number: string;
    years_of_experience: number;
    consultation_fee: number;
    bio: string;
}

export default function DoctorsEdit() {
    const { id } = usePage().props as unknown as { id: string };
    const [doctor, setDoctor] = useState<Doctor | null>(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const [processing, setProcessing] = useState(false);
    const [formErrors, setFormErrors] = useState<any>({});

    const [formData, setFormData] = useState({
        first_name: '',
        last_name: '',
        email: '',
        phone: '',
        specialization: '',
        license_number: '',
        years_of_experience: '',
        consultation_fee: '',
        address: '',
        bio: '',
    });

    useEffect(() => {
        const fetchDoctor = async () => {
            try {
                setLoading(true);
                const response = (await apiService.get(`/doctors/${id}`)) as {
                    success: boolean;
                    data: Doctor;
                };
                setDoctor(response.data);
                const nameParts = response.data.user.name.split(' ');
                setFormData({
                    first_name: nameParts[0] || '',
                    last_name: nameParts.slice(1).join(' ') || '',
                    email: response.data.user.email,
                    phone: response.data.user.phone,
                    specialization: response.data.specialization,
                    license_number: response.data.license_number,
                    years_of_experience:
                        response.data.years_of_experience?.toString() || '',
                    consultation_fee:
                        response.data.consultation_fee?.toString() || '',
                    address: response.data.user.address || '',
                    bio: response.data.bio || '',
                });
            } catch (err: any) {
                setError(err.message || 'Failed to load doctor data');
            } finally {
                setLoading(false);
            }
        };

        if (id) {
            fetchDoctor();
        }
    }, [id]);

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        try {
            setProcessing(true);
            setFormErrors({});
            await apiService.put(`/doctors/${id}`, formData);
            window.location.href = `/doctors/${id}`;
        } catch (err: any) {
            if (err.response?.data?.errors) {
                setFormErrors(err.response.data.errors);
            } else {
                setError(err.message || 'Failed to update doctor');
            }
        } finally {
            setProcessing(false);
        }
    };

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Doctors',
            href: '/doctors',
        },
        {
            title: 'Edit',
            href: `/doctors/${id}/edit`,
        },
    ];

    if (loading) {
        return (
            <AppLayout breadcrumbs={breadcrumbs}>
                <div className="flex h-64 items-center justify-center">
                    <div className="h-8 w-8 animate-spin rounded-full border-4 border-primary border-t-transparent"></div>
                </div>
            </AppLayout>
        );
    }

    if (error && !doctor) {
        return (
            <AppLayout breadcrumbs={breadcrumbs}>
                <div className="flex flex-col items-center justify-center gap-4 p-12 text-center text-red-500">
                    <p>{error}</p>
                    <Button asChild variant="outline">
                        <Link href="/doctors">Back to Doctors</Link>
                    </Button>
                </div>
            </AppLayout>
        );
    }

    if (!doctor) {
        return null;
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit Doctor: ${doctor.user.name}`} />

            <div className="flex flex-col gap-6 p-6">
                <div className="flex items-center gap-4">
                    <Button variant="ghost" size="sm" asChild>
                        <Link href={`/doctors/${doctor.id}`}>
                            <ArrowLeft className="mr-2 h-4 w-4" />
                            Back to Doctor
                        </Link>
                    </Button>
                    <div>
                        <h1 className="text-2xl font-bold">Edit Doctor</h1>
                        <p className="text-muted-foreground">
                            Update doctor information
                        </p>
                    </div>
                </div>

                <form onSubmit={handleSubmit}>
                    <div className="grid gap-6">
                        <Card>
                            <CardHeader>
                                <CardTitle>Basic Information</CardTitle>
                                <CardDescription>
                                    Update the doctor's basic details
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
                                    <Label htmlFor="email">Email *</Label>
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
                                        required
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
                                    <Label htmlFor="specialization">
                                        Specialization *
                                    </Label>
                                    <Select
                                        value={formData.specialization}
                                        onValueChange={(value) =>
                                            setFormData((prev) => ({
                                                ...prev,
                                                specialization: value,
                                            }))
                                        }
                                    >
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select specialization" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="cardiology">
                                                Cardiology
                                            </SelectItem>
                                            <SelectItem value="dermatology">
                                                Dermatology
                                            </SelectItem>
                                            <SelectItem value="neurology">
                                                Neurology
                                            </SelectItem>
                                            <SelectItem value="orthopedics">
                                                Orthopedics
                                            </SelectItem>
                                            <SelectItem value="pediatrics">
                                                Pediatrics
                                            </SelectItem>
                                            <SelectItem value="general">
                                                General Medicine
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    {formErrors.specialization && (
                                        <p className="text-sm text-red-600">
                                            {formErrors.specialization}
                                        </p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="license_number">
                                        License Number *
                                    </Label>
                                    <Input
                                        id="license_number"
                                        value={formData.license_number}
                                        onChange={(e) =>
                                            setFormData((prev) => ({
                                                ...prev,
                                                license_number: e.target.value,
                                            }))
                                        }
                                        placeholder="Enter license number"
                                        required
                                    />
                                    {formErrors.license_number && (
                                        <p className="text-sm text-red-600">
                                            {formErrors.license_number}
                                        </p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="years_of_experience">
                                        Years of Experience
                                    </Label>
                                    <Input
                                        id="years_of_experience"
                                        type="number"
                                        value={formData.years_of_experience}
                                        onChange={(e) =>
                                            setFormData((prev) => ({
                                                ...prev,
                                                years_of_experience:
                                                    e.target.value,
                                            }))
                                        }
                                        placeholder="Enter years of experience"
                                    />
                                    {formErrors.years_of_experience && (
                                        <p className="text-sm text-red-600">
                                            {formErrors.years_of_experience}
                                        </p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="consultation_fee">
                                        Consultation Fee
                                    </Label>
                                    <Input
                                        id="consultation_fee"
                                        type="number"
                                        step="0.01"
                                        value={formData.consultation_fee}
                                        onChange={(e) =>
                                            setFormData((prev) => ({
                                                ...prev,
                                                consultation_fee:
                                                    e.target.value,
                                            }))
                                        }
                                        placeholder="Enter consultation fee"
                                    />
                                    {formErrors.consultation_fee && (
                                        <p className="text-sm text-red-600">
                                            {formErrors.consultation_fee}
                                        </p>
                                    )}
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>Additional Information</CardTitle>
                                <CardDescription>
                                    Update address and professional details
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

                                <div className="space-y-2">
                                    <Label htmlFor="bio">Biography</Label>
                                    <Textarea
                                        id="bio"
                                        value={formData.bio}
                                        onChange={(e) =>
                                            setFormData((prev) => ({
                                                ...prev,
                                                bio: e.target.value,
                                            }))
                                        }
                                        placeholder="Enter professional biography"
                                        rows={4}
                                    />
                                    {formErrors.bio && (
                                        <p className="text-sm text-red-600">
                                            {formErrors.bio}
                                        </p>
                                    )}
                                </div>
                            </CardContent>
                        </Card>

                        <div className="flex justify-end gap-4">
                            <Button variant="outline" asChild>
                                <Link href={`/doctors/${doctor.id}`}>
                                    Cancel
                                </Link>
                            </Button>
                            <Button type="submit" disabled={processing}>
                                <Save className="mr-2 h-4 w-4" />
                                {processing ? 'Updating...' : 'Update Doctor'}
                            </Button>
                        </div>
                    </div>
                </form>
            </div>
        </AppLayout>
    );
}
