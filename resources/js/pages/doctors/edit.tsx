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
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { ArrowLeft, Save } from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Doctors',
        href: '/doctors',
    },
    {
        title: 'Edit',
        href: `/doctors/${usePage().props.id}/edit`,
    },
];

export default function DoctorsEdit() {
    // Mock data
    const doctor = {
        id: 1,
        name: 'Dr. Sarah Johnson',
        email: 'sarah.johnson@example.com',
        phone: '+1234567890',
        specialization: 'cardiology',
        license_number: 'MD123456',
        experience_years: 15,
        consultation_fee: 150.0,
        address: '123 Medical Center, City, State 12345',
        bio: 'Experienced cardiologist with over 15 years of practice...',
    };

    const { data, setData, put, processing, errors } = useForm({
        name: doctor.name,
        email: doctor.email,
        phone: doctor.phone,
        specialization: doctor.specialization,
        license_number: doctor.license_number,
        experience_years: doctor.experience_years.toString(),
        consultation_fee: doctor.consultation_fee.toString(),
        address: doctor.address,
        bio: doctor.bio,
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(`/api/doctors/${doctor.id}`);
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit Doctor: ${doctor.name}`} />

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
                                    <Label htmlFor="name">Full Name *</Label>
                                    <Input
                                        id="name"
                                        value={data.name}
                                        onChange={(e) =>
                                            setData('name', e.target.value)
                                        }
                                        placeholder="Enter full name"
                                        required
                                    />
                                    {errors.name && (
                                        <p className="text-sm text-red-600">
                                            {errors.name}
                                        </p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="email">Email *</Label>
                                    <Input
                                        id="email"
                                        type="email"
                                        value={data.email}
                                        onChange={(e) =>
                                            setData('email', e.target.value)
                                        }
                                        placeholder="Enter email address"
                                        required
                                    />
                                    {errors.email && (
                                        <p className="text-sm text-red-600">
                                            {errors.email}
                                        </p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="phone">Phone *</Label>
                                    <Input
                                        id="phone"
                                        value={data.phone}
                                        onChange={(e) =>
                                            setData('phone', e.target.value)
                                        }
                                        placeholder="Enter phone number"
                                        required
                                    />
                                    {errors.phone && (
                                        <p className="text-sm text-red-600">
                                            {errors.phone}
                                        </p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="specialization">
                                        Specialization *
                                    </Label>
                                    <Select
                                        value={data.specialization}
                                        onValueChange={(value) =>
                                            setData('specialization', value)
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
                                    {errors.specialization && (
                                        <p className="text-sm text-red-600">
                                            {errors.specialization}
                                        </p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="license_number">
                                        License Number *
                                    </Label>
                                    <Input
                                        id="license_number"
                                        value={data.license_number}
                                        onChange={(e) =>
                                            setData(
                                                'license_number',
                                                e.target.value,
                                            )
                                        }
                                        placeholder="Enter license number"
                                        required
                                    />
                                    {errors.license_number && (
                                        <p className="text-sm text-red-600">
                                            {errors.license_number}
                                        </p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="experience_years">
                                        Years of Experience
                                    </Label>
                                    <Input
                                        id="experience_years"
                                        type="number"
                                        value={data.experience_years}
                                        onChange={(e) =>
                                            setData(
                                                'experience_years',
                                                e.target.value,
                                            )
                                        }
                                        placeholder="Enter years of experience"
                                    />
                                    {errors.experience_years && (
                                        <p className="text-sm text-red-600">
                                            {errors.experience_years}
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
                                        value={data.consultation_fee}
                                        onChange={(e) =>
                                            setData(
                                                'consultation_fee',
                                                e.target.value,
                                            )
                                        }
                                        placeholder="Enter consultation fee"
                                    />
                                    {errors.consultation_fee && (
                                        <p className="text-sm text-red-600">
                                            {errors.consultation_fee}
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
                                        value={data.address}
                                        onChange={(e) =>
                                            setData('address', e.target.value)
                                        }
                                        placeholder="Enter full address"
                                        rows={3}
                                    />
                                    {errors.address && (
                                        <p className="text-sm text-red-600">
                                            {errors.address}
                                        </p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="bio">Biography</Label>
                                    <Textarea
                                        id="bio"
                                        value={data.bio}
                                        onChange={(e) =>
                                            setData('bio', e.target.value)
                                        }
                                        placeholder="Enter professional biography"
                                        rows={4}
                                    />
                                    {errors.bio && (
                                        <p className="text-sm text-red-600">
                                            {errors.bio}
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
