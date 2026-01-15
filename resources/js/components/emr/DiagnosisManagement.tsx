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

interface DiagnosisManagementProps {
    diagnosis?: {
        id?: number;
        encounter_id: number;
        patient_id: number;
        diagnosis_type: string;
        diagnosis_code?: string;
        diagnosis_description: string;
        diagnosis_date: string;
        severity?: string;
        status?: string;
        notes?: string;
    };
    onSubmit: (data: any) => void;
    onCancel?: () => void;
}

export default function DiagnosisManagement({
    diagnosis,
    onSubmit,
    onCancel,
}: DiagnosisManagementProps) {
    const { data, setData, post, put, errors, processing } = useForm({
        encounter_id: diagnosis?.encounter_id || '',
        patient_id: diagnosis?.patient_id || '',
        diagnosis_type: diagnosis?.diagnosis_type || 'primary',
        diagnosis_code: diagnosis?.diagnosis_code || '',
        diagnosis_description: diagnosis?.diagnosis_description || '',
        diagnosis_date: diagnosis?.diagnosis_date || '',
        severity: diagnosis?.severity || 'mild',
        status: diagnosis?.status || 'active',
        notes: diagnosis?.notes || '',
    });

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();
        onSubmit(data);
    };

    return (
        <Card>
            <CardHeader>
                <CardTitle>
                    {diagnosis?.id ? 'Edit Diagnosis' : 'Add Diagnosis'}
                </CardTitle>
            </CardHeader>
            <CardContent>
                <form onSubmit={handleSubmit} className="space-y-6">
                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <Label htmlFor="diagnosis_type">
                                Diagnosis Type
                            </Label>
                            <Select
                                value={data.diagnosis_type}
                                onValueChange={(value) =>
                                    setData('diagnosis_type', value)
                                }
                                required
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="Select diagnosis type" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="primary">
                                        Primary
                                    </SelectItem>
                                    <SelectItem value="secondary">
                                        Secondary
                                    </SelectItem>
                                    <SelectItem value="differential">
                                        Differential
                                    </SelectItem>
                                    <SelectItem value="provisional">
                                        Provisional
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            {errors.diagnosis_type && (
                                <p className="text-sm text-red-500">
                                    {errors.diagnosis_type}
                                </p>
                            )}
                        </div>
                        <div>
                            <Label htmlFor="diagnosis_date">
                                Diagnosis Date
                            </Label>
                            <Input
                                id="diagnosis_date"
                                type="date"
                                value={data.diagnosis_date}
                                onChange={(e) =>
                                    setData('diagnosis_date', e.target.value)
                                }
                                required
                            />
                            {errors.diagnosis_date && (
                                <p className="text-sm text-red-500">
                                    {errors.diagnosis_date}
                                </p>
                            )}
                        </div>
                    </div>

                    <div>
                        <Label htmlFor="diagnosis_code">
                            Diagnosis Code (ICD-10)
                        </Label>
                        <Input
                            id="diagnosis_code"
                            value={data.diagnosis_code}
                            onChange={(e) =>
                                setData('diagnosis_code', e.target.value)
                            }
                        />
                        {errors.diagnosis_code && (
                            <p className="text-sm text-red-500">
                                {errors.diagnosis_code}
                            </p>
                        )}
                    </div>

                    <div>
                        <Label htmlFor="diagnosis_description">
                            Diagnosis Description
                        </Label>
                        <Textarea
                            id="diagnosis_description"
                            value={data.diagnosis_description}
                            onChange={(e) =>
                                setData('diagnosis_description', e.target.value)
                            }
                            rows={3}
                            required
                        />
                        {errors.diagnosis_description && (
                            <p className="text-sm text-red-500">
                                {errors.diagnosis_description}
                            </p>
                        )}
                    </div>

                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <Label htmlFor="severity">Severity</Label>
                            <Select
                                value={data.severity}
                                onValueChange={(value) =>
                                    setData('severity', value)
                                }
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="Select severity" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="mild">Mild</SelectItem>
                                    <SelectItem value="moderate">
                                        Moderate
                                    </SelectItem>
                                    <SelectItem value="severe">
                                        Severe
                                    </SelectItem>
                                    <SelectItem value="critical">
                                        Critical
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            {errors.severity && (
                                <p className="text-sm text-red-500">
                                    {errors.severity}
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
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="Select status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="active">
                                        Active
                                    </SelectItem>
                                    <SelectItem value="resolved">
                                        Resolved
                                    </SelectItem>
                                    <SelectItem value="chronic">
                                        Chronic
                                    </SelectItem>
                                    <SelectItem value="recurrent">
                                        Recurrent
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

                    <div>
                        <Label htmlFor="notes">Additional Notes</Label>
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
                                : diagnosis?.id
                                  ? 'Update Diagnosis'
                                  : 'Add Diagnosis'}
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    );
}
