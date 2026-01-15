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

interface LabInvestigationManagementProps {
    labOrder?: {
        id?: number;
        encounter_id: number;
        patient_id: number;
        test_type: string;
        test_name: string;
        test_code?: string;
        priority?: string;
        status?: string;
        specimen_type?: string;
        notes?: string;
        results?: string;
    };
    onSubmit: (data: any) => void;
    onCancel?: () => void;
}

export default function LabInvestigationManagement({
    labOrder,
    onSubmit,
    onCancel,
}: LabInvestigationManagementProps) {
    const { data, setData, post, put, errors, processing } = useForm({
        encounter_id: labOrder?.encounter_id || '',
        patient_id: labOrder?.patient_id || '',
        test_type: labOrder?.test_type || 'lab',
        test_name: labOrder?.test_name || '',
        test_code: labOrder?.test_code || '',
        priority: labOrder?.priority || 'routine',
        status: labOrder?.status || 'ordered',
        specimen_type: labOrder?.specimen_type || '',
        notes: labOrder?.notes || '',
        results: labOrder?.results || '',
    });

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();
        onSubmit(data);
    };

    return (
        <Card>
            <CardHeader>
                <CardTitle>
                    {labOrder?.id ? 'Edit Lab Order' : 'Create Lab Order'}
                </CardTitle>
            </CardHeader>
            <CardContent>
                <form onSubmit={handleSubmit} className="space-y-6">
                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <Label htmlFor="test_type">Test Type</Label>
                            <Select
                                value={data.test_type}
                                onValueChange={(value) =>
                                    setData('test_type', value)
                                }
                                required
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="Select test type" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="lab">
                                        Laboratory
                                    </SelectItem>
                                    <SelectItem value="radiology">
                                        Radiology
                                    </SelectItem>
                                    <SelectItem value="pathology">
                                        Pathology
                                    </SelectItem>
                                    <SelectItem value="imaging">
                                        Imaging
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            {errors.test_type && (
                                <p className="text-sm text-red-500">
                                    {errors.test_type}
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
                                    <SelectItem value="routine">
                                        Routine
                                    </SelectItem>
                                    <SelectItem value="urgent">
                                        Urgent
                                    </SelectItem>
                                    <SelectItem value="stat">STAT</SelectItem>
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
                        <Label htmlFor="test_name">Test Name</Label>
                        <Input
                            id="test_name"
                            value={data.test_name}
                            onChange={(e) =>
                                setData('test_name', e.target.value)
                            }
                            required
                        />
                        {errors.test_name && (
                            <p className="text-sm text-red-500">
                                {errors.test_name}
                            </p>
                        )}
                    </div>

                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <Label htmlFor="test_code">Test Code</Label>
                            <Input
                                id="test_code"
                                value={data.test_code}
                                onChange={(e) =>
                                    setData('test_code', e.target.value)
                                }
                            />
                            {errors.test_code && (
                                <p className="text-sm text-red-500">
                                    {errors.test_code}
                                </p>
                            )}
                        </div>
                        <div>
                            <Label htmlFor="specimen_type">Specimen Type</Label>
                            <Input
                                id="specimen_type"
                                value={data.specimen_type}
                                onChange={(e) =>
                                    setData('specimen_type', e.target.value)
                                }
                            />
                            {errors.specimen_type && (
                                <p className="text-sm text-red-500">
                                    {errors.specimen_type}
                                </p>
                            )}
                        </div>
                    </div>

                    <div>
                        <Label htmlFor="notes">Special Instructions</Label>
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

                    <div>
                        <Label htmlFor="results">Results (if available)</Label>
                        <Textarea
                            id="results"
                            value={data.results}
                            onChange={(e) => setData('results', e.target.value)}
                            rows={4}
                        />
                        {errors.results && (
                            <p className="text-sm text-red-500">
                                {errors.results}
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
                                : labOrder?.id
                                  ? 'Update Lab Order'
                                  : 'Create Lab Order'}
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    );
}
