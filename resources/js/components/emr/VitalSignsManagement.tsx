import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

interface VitalSignsManagementProps {
    vitalSigns?: {
        id?: number;
        encounter_id: number;
        patient_id: number;
        height?: number;
        weight?: number;
        bmi?: number;
        temperature?: number;
        systolic_bp?: number;
        diastolic_bp?: number;
        pulse_rate?: number;
        respiratory_rate?: number;
        oxygen_saturation?: number;
        notes?: string;
    };
    onSubmit: (data: any) => void;
    onCancel?: () => void;
}

export default function VitalSignsManagement({
    vitalSigns,
    onSubmit,
    onCancel,
}: VitalSignsManagementProps) {
    const { data, setData, post, put, errors, processing } = useForm({
        encounter_id: vitalSigns?.encounter_id || '',
        patient_id: vitalSigns?.patient_id || '',
        height: vitalSigns?.height || '',
        weight: vitalSigns?.weight || '',
        temperature: vitalSigns?.temperature || '',
        systolic_bp: vitalSigns?.systolic_bp || '',
        diastolic_bp: vitalSigns?.diastolic_bp || '',
        pulse_rate: vitalSigns?.pulse_rate || '',
        respiratory_rate: vitalSigns?.respiratory_rate || '',
        oxygen_saturation: vitalSigns?.oxygen_saturation || '',
        notes: vitalSigns?.notes || '',
    });

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();
        onSubmit(data);
    };

    return (
        <Card>
            <CardHeader>
                <CardTitle>
                    {vitalSigns?.id ? 'Edit Vital Signs' : 'Record Vital Signs'}
                </CardTitle>
            </CardHeader>
            <CardContent>
                <form onSubmit={handleSubmit} className="space-y-6">
                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <Label htmlFor="height">Height (cm)</Label>
                            <Input
                                id="height"
                                type="number"
                                value={data.height}
                                onChange={(e) =>
                                    setData('height', e.target.value)
                                }
                                step="0.1"
                            />
                            {errors.height && (
                                <p className="text-sm text-red-500">
                                    {errors.height}
                                </p>
                            )}
                        </div>
                        <div>
                            <Label htmlFor="weight">Weight (kg)</Label>
                            <Input
                                id="weight"
                                type="number"
                                value={data.weight}
                                onChange={(e) =>
                                    setData('weight', e.target.value)
                                }
                                step="0.1"
                            />
                            {errors.weight && (
                                <p className="text-sm text-red-500">
                                    {errors.weight}
                                </p>
                            )}
                        </div>
                    </div>

                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <Label htmlFor="temperature">
                                Temperature (°C)
                            </Label>
                            <Input
                                id="temperature"
                                type="number"
                                value={data.temperature}
                                onChange={(e) =>
                                    setData('temperature', e.target.value)
                                }
                                step="0.1"
                            />
                            {errors.temperature && (
                                <p className="text-sm text-red-500">
                                    {errors.temperature}
                                </p>
                            )}
                        </div>
                        <div>
                            <Label htmlFor="oxygen_saturation">
                                Oxygen Saturation (%)
                            </Label>
                            <Input
                                id="oxygen_saturation"
                                type="number"
                                value={data.oxygen_saturation}
                                onChange={(e) =>
                                    setData('oxygen_saturation', e.target.value)
                                }
                                step="1"
                            />
                            {errors.oxygen_saturation && (
                                <p className="text-sm text-red-500">
                                    {errors.oxygen_saturation}
                                </p>
                            )}
                        </div>
                    </div>

                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <Label htmlFor="systolic_bp">
                                Systolic BP (mmHg)
                            </Label>
                            <Input
                                id="systolic_bp"
                                type="number"
                                value={data.systolic_bp}
                                onChange={(e) =>
                                    setData('systolic_bp', e.target.value)
                                }
                                step="1"
                            />
                            {errors.systolic_bp && (
                                <p className="text-sm text-red-500">
                                    {errors.systolic_bp}
                                </p>
                            )}
                        </div>
                        <div>
                            <Label htmlFor="diastolic_bp">
                                Diastolic BP (mmHg)
                            </Label>
                            <Input
                                id="diastolic_bp"
                                type="number"
                                value={data.diastolic_bp}
                                onChange={(e) =>
                                    setData('diastolic_bp', e.target.value)
                                }
                                step="1"
                            />
                            {errors.diastolic_bp && (
                                <p className="text-sm text-red-500">
                                    {errors.diastolic_bp}
                                </p>
                            )}
                        </div>
                    </div>

                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <Label htmlFor="pulse_rate">Pulse Rate (bpm)</Label>
                            <Input
                                id="pulse_rate"
                                type="number"
                                value={data.pulse_rate}
                                onChange={(e) =>
                                    setData('pulse_rate', e.target.value)
                                }
                                step="1"
                            />
                            {errors.pulse_rate && (
                                <p className="text-sm text-red-500">
                                    {errors.pulse_rate}
                                </p>
                            )}
                        </div>
                        <div>
                            <Label htmlFor="respiratory_rate">
                                Respiratory Rate (bpm)
                            </Label>
                            <Input
                                id="respiratory_rate"
                                type="number"
                                value={data.respiratory_rate}
                                onChange={(e) =>
                                    setData('respiratory_rate', e.target.value)
                                }
                                step="1"
                            />
                            {errors.respiratory_rate && (
                                <p className="text-sm text-red-500">
                                    {errors.respiratory_rate}
                                </p>
                            )}
                        </div>
                    </div>

                    <div>
                        <Label htmlFor="notes">Notes</Label>
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
                                : vitalSigns?.id
                                  ? 'Update Vital Signs'
                                  : 'Record Vital Signs'}
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    );
}
