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
import { Head, Link, router, useForm } from '@inertiajs/react';
import { ArrowLeft, Save } from 'lucide-react';
import { useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Patients',
        href: '/patients',
    },
    {
        title: 'Create',
        href: '/patients/create',
    },
];

export default function PatientsCreate() {
    const { data, setData, post, processing, errors } = useForm({
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

    const [apiProcessing, setApiProcessing] = useState(false);

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setApiProcessing(true);
        try {
            await apiService.post('/patients', data);
            router.visit('/patients');
        } catch (err: any) {
            // Error handling is handled by apiService interceptors or locally
            console.error('Registration failed:', err);
        } finally {
            setApiProcessing(false);
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create Patient" />

            <div className="flex flex-col gap-6 p-6">
                <div className="flex items-center gap-4">
                    <Button variant="ghost" size="sm" asChild>
                        <Link href="/patients">
                            <ArrowLeft className="mr-2 h-4 w-4" />
                            Back to Patients
                        </Link>
                    </Button>
                    <div>
                        <h1 className="text-2xl font-bold">Create Patient</h1>
                        <p className="text-muted-foreground">
                            Add a new patient to the system
                        </p>
                    </div>
                </div>

                <form onSubmit={handleSubmit}>
                    <div className="grid gap-6">
                        <Card>
                            <CardHeader>
                                <CardTitle>Basic Information</CardTitle>
                                <CardDescription>
                                    Enter the patient's basic details
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="grid gap-4 md:grid-cols-2">
                                <div className="space-y-2">
                                    <Label htmlFor="first_name">
                                        First Name *
                                    </Label>
                                    <Input
                                        id="first_name"
                                        value={data.first_name}
                                        onChange={(
                                            e: React.ChangeEvent<HTMLInputElement>,
                                        ) =>
                                            setData('first_name', e.target.value)
                                        }
                                        placeholder="Enter first name"
                                        required
                                    />
                                    {errors.first_name && (
                                        <p className="text-sm text-red-600">
                                            {errors.first_name}
                                        </p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="last_name">
                                        Last Name *
                                    </Label>
                                    <Input
                                        id="last_name"
                                        value={data.last_name}
                                        onChange={(
                                            e: React.ChangeEvent<HTMLInputElement>,
                                        ) =>
                                            setData('last_name', e.target.value)
                                        }
                                        placeholder="Enter last name"
                                        required
                                    />
                                    {errors.last_name && (
                                        <p className="text-sm text-red-600">
                                            {errors.last_name}
                                        </p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="email">Email</Label>
                                    <Input
                                        id="email"
                                        type="email"
                                        value={data.email}
                                        onChange={(
                                            e: React.ChangeEvent<HTMLInputElement>,
                                        ) => setData('email', e.target.value)}
                                        placeholder="Enter email address"
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
                                        onChange={(
                                            e: React.ChangeEvent<HTMLInputElement>,
                                        ) => setData('phone', e.target.value)}
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
                                    <Label htmlFor="date_of_birth">
                                        Date of Birth
                                    </Label>
                                    <Input
                                        id="date_of_birth"
                                        type="date"
                                        value={data.date_of_birth}
                                        onChange={(
                                            e: React.ChangeEvent<HTMLInputElement>,
                                        ) =>
                                            setData(
                                                'date_of_birth',
                                                e.target.value,
                                            )
                                        }
                                    />
                                    {errors.date_of_birth && (
                                        <p className="text-sm text-red-600">
                                            {errors.date_of_birth}
                                        </p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="gender">Gender</Label>
                                    <Select
                                        value={data.gender}
                                        onValueChange={(value) =>
                                            setData('gender', value)
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
                                    {errors.gender && (
                                        <p className="text-sm text-red-600">
                                            {errors.gender}
                                        </p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="blood_type">
                                        Blood Group
                                    </Label>
                                    <Select
                                        value={data.blood_type}
                                        onValueChange={(value) =>
                                            setData('blood_type', value)
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
                                    {errors.blood_type && (
                                        <p className="text-sm text-red-600">
                                            {errors.blood_type}
                                        </p>
                                    )}
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>Medical Information</CardTitle>
                                <CardDescription>
                                    Enter medical details and history
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="grid gap-4">
                                <div className="space-y-2">
                                    <Label htmlFor="allergies">Allergies</Label>
                                    <Textarea
                                        id="allergies"
                                        value={data.allergies}
                                        onChange={(
                                            e: React.ChangeEvent<HTMLTextAreaElement>,
                                        ) =>
                                            setData('allergies', e.target.value)
                                        }
                                        placeholder="List any known allergies"
                                        rows={3}
                                    />
                                    {errors.allergies && (
                                        <p className="text-sm text-red-600">
                                            {errors.allergies}
                                        </p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="medical_history">
                                        Medical History
                                    </Label>
                                    <Textarea
                                        id="medical_history"
                                        value={data.medical_history}
                                        onChange={(
                                            e: React.ChangeEvent<HTMLTextAreaElement>,
                                        ) =>
                                            setData(
                                                'medical_history',
                                                e.target.value,
                                            )
                                        }
                                        placeholder="List any chronic conditions or medical history"
                                        rows={3}
                                    />
                                    {errors.medical_history && (
                                        <p className="text-sm text-red-600">
                                            {errors.medical_history}
                                        </p>
                                    )}
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>Contact Information</CardTitle>
                                <CardDescription>
                                    Address and emergency contact details
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="grid gap-4">
                                <div className="space-y-2">
                                    <Label htmlFor="address">Address</Label>
                                    <Textarea
                                        id="address"
                                        value={data.address}
                                        onChange={(
                                            e: React.ChangeEvent<HTMLTextAreaElement>,
                                        ) => setData('address', e.target.value)}
                                        placeholder="Enter full address"
                                        rows={3}
                                    />
                                    {errors.address && (
                                        <p className="text-sm text-red-600">
                                            {errors.address}
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
                                            value={data.emergency_contact_name}
                                            onChange={(
                                                e: React.ChangeEvent<HTMLInputElement>,
                                            ) =>
                                                setData(
                                                    'emergency_contact_name',
                                                    e.target.value,
                                                )
                                            }
                                            placeholder="Enter emergency contact name"
                                        />
                                        {errors.emergency_contact_name && (
                                            <p className="text-sm text-red-600">
                                                {errors.emergency_contact_name}
                                            </p>
                                        )}
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="emergency_contact_phone">
                                            Emergency Contact Phone
                                        </Label>
                                        <Input
                                            id="emergency_contact_phone"
                                            value={data.emergency_contact_phone}
                                            onChange={(
                                                e: React.ChangeEvent<HTMLInputElement>,
                                            ) =>
                                                setData(
                                                    'emergency_contact_phone',
                                                    e.target.value,
                                                )
                                            }
                                            placeholder="Enter emergency contact phone"
                                        />
                                        {errors.emergency_contact_phone && (
                                            <p className="text-sm text-red-600">
                                                {errors.emergency_contact_phone}
                                            </p>
                                        )}
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <div className="flex justify-end gap-4">
                            <Button variant="outline" asChild>
                                <Link href="/patients">Cancel</Link>
                            </Button>
                            <Button
                                type="submit"
                                disabled={processing || apiProcessing}
                            >
                                <Save className="mr-2 h-4 w-4" />
                                {processing || apiProcessing
                                    ? 'Creating...'
                                    : 'Create Patient'}
                            </Button>
                        </div>
                    </div>
                </form>
            </div>
        </AppLayout>
    );
}
