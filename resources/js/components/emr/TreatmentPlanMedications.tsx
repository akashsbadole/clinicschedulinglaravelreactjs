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
import { useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

interface TreatmentPlanMedicationsProps {
    treatmentPlan?: {
        id?: number;
        encounter_id: number;
        patient_id: number;
        plan_type: string;
        diagnosis_id?: number;
        start_date: string;
        end_date?: string;
        status?: string;
        notes?: string;
        medications?: Array<{
            id?: number;
            medication_name: string;
            dosage: string;
            frequency: string;
            route: string;
            duration: string;
            instructions?: string;
        }>;
    };
    onSubmit: (data: any) => void;
    onCancel?: () => void;
}

export default function TreatmentPlanMedications({
    treatmentPlan,
    onSubmit,
    onCancel,
}: TreatmentPlanMedicationsProps) {
    const { data, setData, post, put, errors, processing } = useForm({
        encounter_id: treatmentPlan?.encounter_id || '',
        patient_id: treatmentPlan?.patient_id || '',
        plan_type: treatmentPlan?.plan_type || 'medication',
        diagnosis_id: treatmentPlan?.diagnosis_id || '',
        start_date: treatmentPlan?.start_date || '',
        end_date: treatmentPlan?.end_date || '',
        status: treatmentPlan?.status || 'active',
        notes: treatmentPlan?.notes || '',
        medications: treatmentPlan?.medications || [
            {
                medication_name: '',
                dosage: '',
                frequency: '',
                route: '',
                duration: '',
                instructions: '',
            },
        ],
    });

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();
        onSubmit(data);
    };

    const addMedication = () => {
        setData('medications', [
            ...data.medications,
            {
                medication_name: '',
                dosage: '',
                frequency: '',
                route: '',
                duration: '',
                instructions: '',
            },
        ]);
    };

    const removeMedication = (index: number) => {
        const updatedMedications = [...data.medications];
        updatedMedications.splice(index, 1);
        setData('medications', updatedMedications);
    };

    const updateMedication = (index: number, field: string, value: string) => {
        const updatedMedications = [...data.medications];
        updatedMedications[index] = {
            ...updatedMedications[index],
            [field]: value,
        };
        setData('medications', updatedMedications);
    };

    return (
        <Card>
            <CardHeader>
                <CardTitle>
                    {treatmentPlan?.id
                        ? 'Edit Treatment Plan'
                        : 'Create Treatment Plan'}
                </CardTitle>
            </CardHeader>
            <CardContent>
                <form onSubmit={handleSubmit} className="space-y-6">
                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <Label htmlFor="plan_type">Plan Type</Label>
                            <Select
                                value={data.plan_type}
                                onValueChange={(value) =>
                                    setData('plan_type', value)
                                }
                                required
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="Select plan type" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="medication">
                                        Medication
                                    </SelectItem>
                                    <SelectItem value="therapy">
                                        Therapy
                                    </SelectItem>
                                    <SelectItem value="rehabilitation">
                                        Rehabilitation
                                    </SelectItem>
                                    <SelectItem value="preventive">
                                        Preventive
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            {errors.plan_type && (
                                <p className="text-sm text-red-500">
                                    {errors.plan_type}
                                </p>
                            )}
                        </div>
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
                                    <SelectItem value="discontinued">
                                        Discontinued
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            {errors.status && (
                                <p className="text-sm text-red-500">
                                    {errors.status}
                                </p>
                            )}
                        </div>
                    </div>

                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <Label htmlFor="start_date">Start Date</Label>
                            <Input
                                id="start_date"
                                type="date"
                                value={data.start_date}
                                onChange={(e) =>
                                    setData('start_date', e.target.value)
                                }
                                required
                            />
                            {errors.start_date && (
                                <p className="text-sm text-red-500">
                                    {errors.start_date}
                                </p>
                            )}
                        </div>
                        <div>
                            <Label htmlFor="end_date">
                                End Date (Optional)
                            </Label>
                            <Input
                                id="end_date"
                                type="date"
                                value={data.end_date}
                                onChange={(e) =>
                                    setData('end_date', e.target.value)
                                }
                            />
                            {errors.end_date && (
                                <p className="text-sm text-red-500">
                                    {errors.end_date}
                                </p>
                            )}
                        </div>
                    </div>

                    <div>
                        <Label htmlFor="notes">Treatment Plan Notes</Label>
                        <Textarea
                            id="notes"
                            value={data.notes}
                            onChange={(e) => setData('notes', e.target.value)}
                            rows={3}
                        />
                        {errors.notes && (
                            <p className="text-sm text-red-500">
                                {errors.notes}
                            </p>
                        )}
                    </div>

                    <div className="border-t pt-6">
                        <h3 className="mb-4 text-lg font-semibold">
                            Medications
                        </h3>
                        {data.medications.map(
                            (medication: any, index: number) => (
                                <div
                                    key={index}
                                    className="relative mb-4 rounded-lg border p-4"
                                >
                                    {index > 0 && (
                                        <Button
                                            type="button"
                                            variant="destructive"
                                            size="sm"
                                            className="absolute top-2 right-2"
                                            onClick={() =>
                                                removeMedication(index)
                                            }
                                        >
                                            Remove
                                        </Button>
                                    )}
                                    <div className="mb-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                                        <div>
                                            <Label
                                                htmlFor={`medication_name_${index}`}
                                            >
                                                Medication Name
                                            </Label>
                                            <Input
                                                id={`medication_name_${index}`}
                                                value={
                                                    medication.medication_name
                                                }
                                                onChange={(e) =>
                                                    updateMedication(
                                                        index,
                                                        'medication_name',
                                                        e.target.value,
                                                    )
                                                }
                                                required
                                            />
                                        </div>
                                        <div>
                                            <Label htmlFor={`dosage_${index}`}>
                                                Dosage
                                            </Label>
                                            <Input
                                                id={`dosage_${index}`}
                                                value={medication.dosage}
                                                onChange={(e) =>
                                                    updateMedication(
                                                        index,
                                                        'dosage',
                                                        e.target.value,
                                                    )
                                                }
                                                required
                                            />
                                        </div>
                                    </div>
                                    <div className="mb-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                                        <div>
                                            <Label
                                                htmlFor={`frequency_${index}`}
                                            >
                                                Frequency
                                            </Label>
                                            <Input
                                                id={`frequency_${index}`}
                                                value={medication.frequency}
                                                onChange={(e) =>
                                                    updateMedication(
                                                        index,
                                                        'frequency',
                                                        e.target.value,
                                                    )
                                                }
                                                required
                                            />
                                        </div>
                                        <div>
                                            <Label htmlFor={`route_${index}`}>
                                                Route
                                            </Label>
                                            <Input
                                                id={`route_${index}`}
                                                value={medication.route}
                                                onChange={(e) =>
                                                    updateMedication(
                                                        index,
                                                        'route',
                                                        e.target.value,
                                                    )
                                                }
                                                required
                                            />
                                        </div>
                                    </div>
                                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                        <div>
                                            <Label
                                                htmlFor={`duration_${index}`}
                                            >
                                                Duration
                                            </Label>
                                            <Input
                                                id={`duration_${index}`}
                                                value={medication.duration}
                                                onChange={(e) =>
                                                    updateMedication(
                                                        index,
                                                        'duration',
                                                        e.target.value,
                                                    )
                                                }
                                                required
                                            />
                                        </div>
                                        <div>
                                            <Label
                                                htmlFor={`instructions_${index}`}
                                            >
                                                Special Instructions
                                            </Label>
                                            <Input
                                                id={`instructions_${index}`}
                                                value={medication.instructions}
                                                onChange={(e) =>
                                                    updateMedication(
                                                        index,
                                                        'instructions',
                                                        e.target.value,
                                                    )
                                                }
                                            />
                                        </div>
                                    </div>
                                </div>
                            ),
                        )}
                        <Button
                            type="button"
                            variant="outline"
                            onClick={addMedication}
                        >
                            Add Another Medication
                        </Button>
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
                                : treatmentPlan?.id
                                  ? 'Update Treatment Plan'
                                  : 'Create Treatment Plan'}
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    );
}
