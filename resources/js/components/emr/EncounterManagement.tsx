import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
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
import {
    type Appointment,
    type Doctor,
    type Encounter,
    type Patient,
} from '@/types';
import { useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

interface EncounterManagementProps {
    encounter?: Encounter;
    patients: Patient[];
    doctors: Doctor[];
    appointments: Appointment[];
    onSubmit: (data: any) => void;
    onCancel?: () => void;
}

export default function EncounterManagement({
    encounter,
    patients,
    doctors,
    appointments,
    onSubmit,
    onCancel,
}: EncounterManagementProps) {
    const { data, setData, post, put, errors, processing } = useForm({
        patient_id: encounter?.patient_id || '',
        doctor_id: encounter?.doctor_id || '',
        appointment_id: encounter?.appointment_id || '',
        encounter_type: encounter?.encounter_type || 'outpatient',
        start_time: encounter?.start_time || '',
        end_time: encounter?.end_time || '',
        status: encounter?.status || 'active',
        priority: encounter?.priority || 'normal',
        chief_complaint: encounter?.chief_complaint || '',
        history_of_present_illness: encounter?.history_of_present_illness || '',
    });

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();
        onSubmit(data);
    };

    return (
        <Card>
            <CardHeader>
                <CardTitle>
                    {encounter ? 'Edit Encounter' : 'Create New Encounter'}
                </CardTitle>
            </CardHeader>
            <CardContent>
                <form onSubmit={handleSubmit} className="space-y-6">
                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <Label htmlFor="patient_id">Patient</Label>
                            <Select
                                value={data.patient_id}
                                onValueChange={(value) =>
                                    setData('patient_id', value)
                                }
                                required
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="Select patient" />
                                </SelectTrigger>
                                <SelectContent>
                                    {patients.map((patient) => (
                                        <SelectItem
                                            key={patient.id}
                                            value={patient.id.toString()}
                                        >
                                            {patient.name} ({patient.patient_id}
                                            )
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            {errors.patient_id && (
                                <p className="text-sm text-red-500">
                                    {errors.patient_id}
                                </p>
                            )}
                        </div>
                        <div>
                            <Label htmlFor="doctor_id">Doctor</Label>
                            <Select
                                value={data.doctor_id}
                                onValueChange={(value) =>
                                    setData('doctor_id', value)
                                }
                                required
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="Select doctor" />
                                </SelectTrigger>
                                <SelectContent>
                                    {doctors.map((doctor) => (
                                        <SelectItem
                                            key={doctor.id}
                                            value={doctor.id.toString()}
                                        >
                                            {doctor.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            {errors.doctor_id && (
                                <p className="text-sm text-red-500">
                                    {errors.doctor_id}
                                </p>
                            )}
                        </div>
                    </div>

                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <Label htmlFor="appointment_id">
                                Appointment (Optional)
                            </Label>
                            <Select
                                value={data.appointment_id}
                                onValueChange={(value) =>
                                    setData('appointment_id', value)
                                }
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="Select appointment" />
                                </SelectTrigger>
                                <SelectContent>
                                    {appointments.map((appointment) => (
                                        <SelectItem
                                            key={appointment.id}
                                            value={appointment.id.toString()}
                                        >
                                            {appointment.appointment_number} -{' '}
                                            {appointment.appointment_date}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            {errors.appointment_id && (
                                <p className="text-sm text-red-500">
                                    {errors.appointment_id}
                                </p>
                            )}
                        </div>
                        <div>
                            <Label htmlFor="encounter_type">
                                Encounter Type
                            </Label>
                            <Select
                                value={data.encounter_type}
                                onValueChange={(value) =>
                                    setData('encounter_type', value)
                                }
                                required
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="Select encounter type" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="outpatient">
                                        Outpatient
                                    </SelectItem>
                                    <SelectItem value="inpatient">
                                        Inpatient
                                    </SelectItem>
                                    <SelectItem value="emergency">
                                        Emergency
                                    </SelectItem>
                                    <SelectItem value="telemedicine">
                                        Telemedicine
                                    </SelectItem>
                                    <SelectItem value="follow_up">
                                        Follow-up
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            {errors.encounter_type && (
                                <p className="text-sm text-red-500">
                                    {errors.encounter_type}
                                </p>
                            )}
                        </div>
                    </div>

                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <Label htmlFor="start_time">Start Time</Label>
                            <Input
                                id="start_time"
                                type="datetime-local"
                                value={data.start_time}
                                onChange={(e) =>
                                    setData('start_time', e.target.value)
                                }
                                required
                            />
                            {errors.start_time && (
                                <p className="text-sm text-red-500">
                                    {errors.start_time}
                                </p>
                            )}
                        </div>
                        <div>
                            <Label htmlFor="end_time">End Time</Label>
                            <Input
                                id="end_time"
                                type="datetime-local"
                                value={data.end_time}
                                onChange={(e) =>
                                    setData('end_time', e.target.value)
                                }
                            />
                            {errors.end_time && (
                                <p className="text-sm text-red-500">
                                    {errors.end_time}
                                </p>
                            )}
                        </div>
                    </div>

                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <Label htmlFor="status">Status</Label>
                            <Select
                                value={data.status}
                                onValueChange={(value) =>
                                    setData('status', value)
                                }
                                required
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="Select status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="active">
                                        Active
                                    </SelectItem>
                                    <SelectItem value="completed">
                                        Completed
                                    </SelectItem>
                                    <SelectItem value="cancelled">
                                        Cancelled
                                    </SelectItem>
                                    <SelectItem value="pending">
                                        Pending
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            {errors.status && (
                                <p className="text-sm text-red-500">
                                    {errors.status}
                                </p>
                            )}
                        </div>
                        <div>
                            <Label htmlFor="priority">Priority</Label>
                            <Select
                                value={data.priority}
                                onValueChange={(value) =>
                                    setData('priority', value)
                                }
                                required
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="Select priority" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="normal">
                                        Normal
                                    </SelectItem>
                                    <SelectItem value="urgent">
                                        Urgent
                                    </SelectItem>
                                    <SelectItem value="emergency">
                                        Emergency
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            {errors.priority && (
                                <p className="text-sm text-red-500">
                                    {errors.priority}
                                </p>
                            )}
                        </div>
                    </div>

                    <div>
                        <Label htmlFor="chief_complaint">Chief Complaint</Label>
                        <Textarea
                            id="chief_complaint"
                            value={data.chief_complaint}
                            onChange={(e) =>
                                setData('chief_complaint', e.target.value)
                            }
                            rows={3}
                            required
                        />
                        {errors.chief_complaint && (
                            <p className="text-sm text-red-500">
                                {errors.chief_complaint}
                            </p>
                        )}
                    </div>

                    <div>
                        <Label htmlFor="history_of_present_illness">
                            History of Present Illness
                        </Label>
                        <Textarea
                            id="history_of_present_illness"
                            value={data.history_of_present_illness}
                            onChange={(e) =>
                                setData(
                                    'history_of_present_illness',
                                    e.target.value,
                                )
                            }
                            rows={4}
                            required
                        />
                        {errors.history_of_present_illness && (
                            <p className="text-sm text-red-500">
                                {errors.history_of_present_illness}
                            </p>
                        )}
                    </div>

                    <div className="flex justify-end gap-4">
                        {onCancel && (
                            <Button
                                type="button"
                                variant="outline"
                                onClick={onCancel}
                                disabled={processing}
                            >
                                Cancel
                            </Button>
                        )}
                        <Button type="submit" disabled={processing}>
                            {processing
                                ? 'Processing...'
                                : encounter
                                  ? 'Update Encounter'
                                  : 'Create Encounter'}
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    );
}
