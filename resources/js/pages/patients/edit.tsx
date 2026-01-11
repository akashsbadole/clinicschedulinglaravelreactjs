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
        title: 'Patients',
        href: '/patients',
    },
    {
        title: 'Edit',
        href: `/patients/${usePage().props.id}/edit`,
    },
];

export default function PatientsEdit() {
    // Mock data - in real app, this would come from props
    const patient = {
        id: 1,
        name: 'John Doe',
        email: 'john@example.com',
        phone: '+1234567890',
        date_of_birth: '1990-01-01',
        gender: 'male',
        address: '123 Main St, City, State 12345',
        blood_group: 'A+',
        allergies: 'Penicillin',
        chronic_conditions: 'Diabetes',
        emergency_contact_name: 'Jane Doe',
        emergency_contact_phone: '+0987654321',
    };

    const { data, setData, put, processing, errors } = useForm({
        name: patient.name,
        email: patient.email,
        phone: patient.phone,
        date_of_birth: patient.date_of_birth,
        gender: patient.gender,
        address: patient.address,
        blood_group: patient.blood_group,
        allergies: patient.allergies,
        chronic_conditions: patient.chronic_conditions,
        emergency_contact_name: patient.emergency_contact_name,
        emergency_contact_phone: patient.emergency_contact_phone,
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(`/api/patients/${patient.id}`);
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit Patient: ${patient.name}`} />

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
                                    <Label htmlFor="email">Email</Label>
                                    <Input
                                        id="email"
                                        type="email"
                                        value={data.email}
                                        onChange={(e) =>
                                            setData('email', e.target.value)
                                        }
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
                                    <Label htmlFor="date_of_birth">
                                        Date of Birth
                                    </Label>
                                    <Input
                                        id="date_of_birth"
                                        type="date"
                                        value={data.date_of_birth}
                                        onChange={(e) =>
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
                                    <Label htmlFor="blood_group">
                                        Blood Group
                                    </Label>
                                    <Select
                                        value={data.blood_group}
                                        onValueChange={(value) =>
                                            setData('blood_group', value)
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
                                    {errors.blood_group && (
                                        <p className="text-sm text-red-600">
                                            {errors.blood_group}
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
                                        value={data.allergies}
                                        onChange={(e) =>
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
                                    <Label htmlFor="chronic_conditions">
                                        Chronic Conditions
                                    </Label>
                                    <Textarea
                                        id="chronic_conditions"
                                        value={data.chronic_conditions}
                                        onChange={(e) =>
                                            setData(
                                                'chronic_conditions',
                                                e.target.value,
                                            )
                                        }
                                        placeholder="List any chronic conditions"
                                        rows={3}
                                    />
                                    {errors.chronic_conditions && (
                                        <p className="text-sm text-red-600">
                                            {errors.chronic_conditions}
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

                                <div className="grid gap-4 md:grid-cols-2">
                                    <div className="space-y-2">
                                        <Label htmlFor="emergency_contact_name">
                                            Emergency Contact Name
                                        </Label>
                                        <Input
                                            id="emergency_contact_name"
                                            value={data.emergency_contact_name}
                                            onChange={(e) =>
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
                                            onChange={(e) =>
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
