import Controllers from '@/actions/App/Http/Controllers';
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
import { type BreadcrumbItem, type Doctor, type Patient } from '@/types';
import { DayPicker } from 'react-day-picker';

import apiService from '@/lib/api';
import { cn } from '@/lib/utils';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@radix-ui/react-popover';
import { format } from 'date-fns';
import { ArrowLeft, CalendarIcon } from 'lucide-react';
import { useEffect, useState } from 'react';
import AppLayout from '@/layouts/app-layout';
import { Head } from '@inertiajs/react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Appointments',
        href: '/appointments',
    },
    {
        title: 'Create',
        href: '/appointments/create',
    },
];

export default function AppointmentCreate() {
    const toast = (message: {
        title: string;
        description: string;
        variant?: string;
    }) => {
        if (message.variant === 'destructive') {
            alert('ERROR: ' + message.title + ': ' + message.description);
        } else {
            alert(message.title + ': ' + message.description);
        }
    };
    const [patients, setPatients] = useState<Patient[]>([]);
    const [doctors, setDoctors] = useState<Doctor[]>([]);
    const [availableSlots, setAvailableSlots] = useState<any[]>([]);
    const [isLoading, setIsLoading] = useState<boolean>(false);
    const [formData, setFormData] = useState({
        patient_id: '',
        doctor_id: '',
        appointment_date: '',
        start_time: '',
        appointment_type: 'consultation',
        reason: '',
        notes: '',
        priority: 'normal',
        is_first_visit: false,
    });

    useEffect(() => {
        const fetchPatientsAndDoctors = async () => {
            try {
                setIsLoading(true);
                const [patientsData, doctorsData] = await Promise.all([
                    apiService.get(Controllers.PatientController.index.url()),
                    apiService.get(Controllers.DoctorController.index.url()),
                ]);
                setPatients(
                    (patientsData as any).data || (patientsData as Patient[]),
                );
                setDoctors(
                    (doctorsData as any).data || (doctorsData as Doctor[]),
                );
            } catch (error: unknown) {
                console.error('Error fetching data:', error);
                const errorMessage =
                    error instanceof Error
                        ? error.message
                        : 'Failed to fetch patients or doctors. You may not have permission to access this resource.';
                toast({
                    title: 'Error',
                    description: errorMessage,
                    variant: 'destructive',
                });
            } finally {
                setIsLoading(false);
            }
        };

        fetchPatientsAndDoctors();
    }, []);

    useEffect(() => {
        if (formData.doctor_id && formData.appointment_date) {
            const fetchAvailableSlots = async () => {
                try {
                    const slots = await apiService.get(
                        Controllers.AppointmentController.getAvailableSlots.url(
                            {
                                doctorId: formData.doctor_id,
                            },
                            {
                                query: { date: formData.appointment_date },
                            },
                        ),
                    );
                    setAvailableSlots(
                        (slots as any).data?.available_slots || [],
                    );
                } catch (error: unknown) {
                    toast({
                        title: 'Error',
                        description: 'Failed to fetch available slots',
                        variant: 'destructive',
                    });
                }
            };
            fetchAvailableSlots();
        }
    }, [formData.doctor_id, formData.appointment_date]);

    const handleInputChange = (name: string, value: string) => {
        setFormData((prev) => ({
            ...prev,
            [name]: value,
        }));
    };

    const handleDateChange = (date: Date | undefined) => {
        setFormData((prev) => ({
            ...prev,
            appointment_date: date ? format(date, 'yyyy-MM-dd') : '',
        }));
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        try {
            setIsLoading(true);
            await apiService.post(
                Controllers.AppointmentController.store.url(),
                formData,
            );
            toast({
                title: 'Success',
                description: 'Appointment created successfully',
            });
            window.history.back();
        } catch (error: unknown) {
            const errorMessage =
                error instanceof Error
                    ? error.message
                    : 'Failed to create appointment';
            toast({
                title: 'Error',
                description: errorMessage,
                variant: 'destructive',
            });
        } finally {
            setIsLoading(false);
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create Appointment" />
            <div className="flex items-center justify-between">
                <div>
                    <h1 className="text-3xl font-bold tracking-tight">
                        Create Appointment
                    </h1>
                    <p className="text-muted-foreground">
                        Schedule a new appointment for a patient.
                    </p>
                </div>
                <Button variant="outline" onClick={() => window.history.back()}>
                    <ArrowLeft className="mr-2 h-4 w-4" />
                    Back
                </Button>
            </div>

            {isLoading && patients.length === 0 && doctors.length === 0 ? (
                <Card>
                    <CardContent className="flex items-center justify-center p-12">
                        <div className="text-center">
                            <div className="h-8 w-8 animate-spin rounded-full border-4 border-primary border-t-transparent mx-auto mb-4"></div>
                            <p className="text-muted-foreground">Loading patients and doctors...</p>
                        </div>
                    </CardContent>
                </Card>
            ) : patients.length === 0 || doctors.length === 0 ? (
                <Card>
                    <CardContent className="flex items-center justify-center p-12">
                        <div className="text-center text-red-500">
                            <p className="font-semibold mb-2">Unable to load required data</p>
                            <p className="text-sm text-muted-foreground">
                                {patients.length === 0 && 'No patients available. '}
                                {doctors.length === 0 && 'No doctors available. '}
                                Please ensure you have the necessary permissions and that data exists in the system.
                            </p>
                        </div>
                    </CardContent>
                </Card>
            ) : (
                <Card>
                    <CardHeader>
                        <CardTitle>Appointment Details</CardTitle>
                        <CardDescription>
                            Enter the information for the new appointment.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={handleSubmit} className="space-y-6">
                            <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <div className="space-y-2">
                                    <Label htmlFor="patient_id">Patient</Label>
                                    <Select
                                        name="patient_id"
                                        value={formData.patient_id}
                                        onValueChange={(value) =>
                                            handleInputChange('patient_id', value)
                                        }
                                        required
                                    >
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select a patient" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {patients.map((patient) => (
                                                <SelectItem
                                                    key={patient.id}
                                                    value={patient.id.toString()}
                                                >
                                                    {patient.name} - {patient.phone}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="doctor_id">Doctor</Label>
                                    <Select
                                        name="doctor_id"
                                        value={formData.doctor_id}
                                        onValueChange={(value) =>
                                            handleInputChange('doctor_id', value)
                                        }
                                        required
                                    >
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select a doctor" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {doctors.map((doctor) => (
                                                <SelectItem
                                                    key={doctor.id}
                                                    value={doctor.id.toString()}
                                                >
                                                    Dr. {doctor.name} -{' '}
                                                    {doctor.specialization}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="appointment_date">Date</Label>
                                    <Popover>
                                        <PopoverTrigger asChild>
                                            <Button
                                                variant="outline"
                                                className={cn(
                                                    'w-full justify-start text-left font-normal',
                                                    !formData.appointment_date &&
                                                    'text-muted-foreground',
                                                )}
                                            >
                                                <CalendarIcon className="mr-2 h-4 w-4" />
                                                {formData.appointment_date
                                                    ? format(
                                                        new Date(
                                                            formData.appointment_date,
                                                        ),
                                                        'PPP',
                                                    )
                                                    : 'Pick a date'}
                                            </Button>
                                        </PopoverTrigger>
                                        <PopoverContent className="w-auto p-0">
                                            <DayPicker
                                                mode="single"
                                                selected={
                                                    formData.appointment_date
                                                        ? new Date(
                                                            formData.appointment_date,
                                                        )
                                                        : undefined
                                                }
                                                onSelect={handleDateChange}
                                                initialFocus
                                            />
                                        </PopoverContent>
                                    </Popover>
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="start_time">Time</Label>
                                    <Select
                                        name="start_time"
                                        value={formData.start_time}
                                        onValueChange={(value) =>
                                            handleInputChange('start_time', value)
                                        }
                                        required
                                        disabled={
                                            !formData.doctor_id ||
                                            !formData.appointment_date
                                        }
                                    >
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select a time slot" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {availableSlots.map((slot) => (
                                                <SelectItem
                                                    key={slot.start_time}
                                                    value={slot.start_time}
                                                >
                                                    {slot.start_time}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </div>

                                <div className="space-y-2 md:col-span-2">
                                    <Label htmlFor="reason">
                                        Reason for Appointment
                                    </Label>
                                    <Input
                                        id="reason"
                                        name="reason"
                                        value={formData.reason}
                                        onChange={(e) =>
                                            handleInputChange(
                                                e.target.name,
                                                e.target.value,
                                            )
                                        }
                                        placeholder="Enter reason for appointment"
                                    />
                                </div>

                                <div className="space-y-2 md:col-span-2">
                                    <Label htmlFor="notes">Additional Notes</Label>
                                    <Input
                                        id="notes"
                                        name="notes"
                                        value={formData.notes}
                                        onChange={(e) =>
                                            handleInputChange(
                                                e.target.name,
                                                e.target.value,
                                            )
                                        }
                                        placeholder="Enter any additional notes"
                                    />
                                </div>
                            </div>

                            <div className="flex justify-end">
                                <Button type="submit" disabled={isLoading}>
                                    {isLoading
                                        ? 'Creating...'
                                        : 'Create Appointment'}
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            )}
        </AppLayout>
    );
}
