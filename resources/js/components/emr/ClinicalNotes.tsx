import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
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

interface ClinicalNotesProps {
    clinicalNote?: {
        id?: number;
        encounter_id: number;
        patient_id: number;
        doctor_id?: number;
        note_type: string;
        subjective: string;
        objective: string;
        assessment: string;
        plan: string;
        clinical_impression?: string;
        is_confidential: boolean;
    };
    doctors?: Array<{ id: number; name: string }>;
    onSubmit: (data: any) => void;
    onCancel?: () => void;
}

export default function ClinicalNotes({
    clinicalNote,
    doctors = [],
    onSubmit,
    onCancel,
}: ClinicalNotesProps) {
    const { data, setData, post, put, errors, processing } = useForm({
        encounter_id: clinicalNote?.encounter_id || '',
        patient_id: clinicalNote?.patient_id || '',
        doctor_id: clinicalNote?.doctor_id
            ? clinicalNote.doctor_id.toString()
            : '',
        note_type: clinicalNote?.note_type || 'progress',
        subjective: clinicalNote?.subjective || '',
        objective: clinicalNote?.objective || '',
        assessment: clinicalNote?.assessment || '',
        plan: clinicalNote?.plan || '',
        clinical_impression: clinicalNote?.clinical_impression || '',
        is_confidential: clinicalNote?.is_confidential || false,
    });

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();
        onSubmit(data);
    };

    return (
        <Card>
            <CardHeader>
                <CardTitle>
                    {clinicalNote?.id
                        ? 'Edit Clinical Note'
                        : 'Create Clinical Note'}
                </CardTitle>
            </CardHeader>
            <CardContent>
                <form onSubmit={handleSubmit} className="space-y-6">
                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <Label htmlFor="note_type">Note Type</Label>
                            <Select
                                value={data.note_type}
                                onValueChange={(value) =>
                                    setData('note_type', value)
                                }
                                required
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="Select note type" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="progress">
                                        Progress Note
                                    </SelectItem>
                                    <SelectItem value="soap">
                                        SOAP Note
                                    </SelectItem>
                                    <SelectItem value="consultation">
                                        Consultation Note
                                    </SelectItem>
                                    <SelectItem value="discharge">
                                        Discharge Summary
                                    </SelectItem>
                                    <SelectItem value="procedure">
                                        Procedure Note
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            {errors.note_type && (
                                <p className="text-sm text-red-500">
                                    {errors.note_type}
                                </p>
                            )}
                        </div>
                        <div>
                            <Label htmlFor="doctor_id">Doctor (Optional)</Label>
                            <Select
                                value={data.doctor_id}
                                onValueChange={(value) =>
                                    setData('doctor_id', value)
                                }
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

                    <div>
                        <Label htmlFor="subjective">Subjective</Label>
                        <Textarea
                            id="subjective"
                            value={data.subjective}
                            onChange={(e) =>
                                setData('subjective', e.target.value)
                            }
                            rows={4}
                            required
                        />
                        {errors.subjective && (
                            <p className="text-sm text-red-500">
                                {errors.subjective}
                            </p>
                        )}
                    </div>

                    <div>
                        <Label htmlFor="objective">Objective</Label>
                        <Textarea
                            id="objective"
                            value={data.objective}
                            onChange={(e) =>
                                setData('objective', e.target.value)
                            }
                            rows={4}
                            required
                        />
                        {errors.objective && (
                            <p className="text-sm text-red-500">
                                {errors.objective}
                            </p>
                        )}
                    </div>

                    <div>
                        <Label htmlFor="assessment">Assessment</Label>
                        <Textarea
                            id="assessment"
                            value={data.assessment}
                            onChange={(e) =>
                                setData('assessment', e.target.value)
                            }
                            rows={4}
                            required
                        />
                        {errors.assessment && (
                            <p className="text-sm text-red-500">
                                {errors.assessment}
                            </p>
                        )}
                    </div>

                    <div>
                        <Label htmlFor="plan">Plan</Label>
                        <Textarea
                            id="plan"
                            value={data.plan}
                            onChange={(e) => setData('plan', e.target.value)}
                            rows={4}
                            required
                        />
                        {errors.plan && (
                            <p className="text-sm text-red-500">
                                {errors.plan}
                            </p>
                        )}
                    </div>

                    <div>
                        <Label htmlFor="clinical_impression">
                            Clinical Impression (Optional)
                        </Label>
                        <Textarea
                            id="clinical_impression"
                            value={data.clinical_impression}
                            onChange={(e) =>
                                setData('clinical_impression', e.target.value)
                            }
                            rows={3}
                        />
                        {errors.clinical_impression && (
                            <p className="text-sm text-red-500">
                                {errors.clinical_impression}
                            </p>
                        )}
                    </div>

                    <div className="flex items-center space-x-2">
                        <Checkbox
                            id="is_confidential"
                            checked={data.is_confidential}
                            onCheckedChange={(checked) =>
                                setData('is_confidential', checked === true)
                            }
                        />
                        <Label htmlFor="is_confidential">
                            Confidential Note
                        </Label>
                        {errors.is_confidential && (
                            <p className="text-sm text-red-500">
                                {errors.is_confidential}
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
                                : clinicalNote?.id
                                  ? 'Update Clinical Note'
                                  : 'Create Clinical Note'}
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    );
}
