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

interface ProceduresInterventionsProps {
    procedure?: {
        id?: number;
        encounter_id: number;
        patient_id: number;
        procedure_type: string;
        procedure_name: string;
        procedure_code?: string;
        procedure_date: string;
        status?: string;
        performed_by?: string;
        notes?: string;
        results?: string;
    };
    onSubmit: (data: any) => void;
    onCancel?: () => void;
}

export default function ProceduresInterventions({
    procedure,
    onSubmit,
    onCancel,
}: ProceduresInterventionsProps) {
    const { data, setData, post, put, errors, processing } = useForm({
        encounter_id: procedure?.encounter_id || '',
        patient_id: procedure?.patient_id || '',
        procedure_type: procedure?.procedure_type || 'surgical',
        procedure_name: procedure?.procedure_name || '',
        procedure_code: procedure?.procedure_code || '',
        procedure_date: procedure?.procedure_date || '',
        status: procedure?.status || 'planned',
        performed_by: procedure?.performed_by || '',
        notes: procedure?.notes || '',
        results: procedure?.results || '',
    });

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();
        onSubmit(data);
    };

    return (
        <Card>
            <CardHeader>
                <CardTitle>
                    {procedure?.id ? 'Edit Procedure' : 'Add Procedure'}
                </CardTitle>
            </CardHeader>
            <CardContent>
                <form onSubmit={handleSubmit} className="space-y-6">
                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <Label htmlFor="procedure_type">
                                Procedure Type
                            </Label>
                            <Select
                                value={data.procedure_type}
                                onValueChange={(value) =>
                                    setData('procedure_type', value)
                                }
                                required
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="Select procedure type" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="surgical">
                                        Surgical
                                    </SelectItem>
                                    <SelectItem value="diagnostic">
                                        Diagnostic
                                    </SelectItem>
                                    <SelectItem value="therapeutic">
                                        Therapeutic
                                    </SelectItem>
                                    <SelectItem value="minimally_invasive">
                                        Minimally Invasive
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            {errors.procedure_type && (
                                <p className="text-sm text-red-500">
                                    {errors.procedure_type}
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
                                    <SelectItem value="planned">
                                        Planned
                                    </SelectItem>
                                    <SelectItem value="in_progress">
                                        In Progress
                                    </SelectItem>
                                    <SelectItem value="completed">
                                        Completed
                                    </SelectItem>
                                    <SelectItem value="cancelled">
                                        Cancelled
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
                        <Label htmlFor="procedure_name">Procedure Name</Label>
                        <Input
                            id="procedure_name"
                            value={data.procedure_name}
                            onChange={(e) =>
                                setData('procedure_name', e.target.value)
                            }
                            required
                        />
                        {errors.procedure_name && (
                            <p className="text-sm text-red-500">
                                {errors.procedure_name}
                            </p>
                        )}
                    </div>

                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <Label htmlFor="procedure_code">
                                Procedure Code
                            </Label>
                            <Input
                                id="procedure_code"
                                value={data.procedure_code}
                                onChange={(e) =>
                                    setData('procedure_code', e.target.value)
                                }
                            />
                            {errors.procedure_code && (
                                <p className="text-sm text-red-500">
                                    {errors.procedure_code}
                                </p>
                            )}
                        </div>
                        <div>
                            <Label htmlFor="procedure_date">
                                Procedure Date
                            </Label>
                            <Input
                                id="procedure_date"
                                type="datetime-local"
                                value={data.procedure_date}
                                onChange={(e) =>
                                    setData('procedure_date', e.target.value)
                                }
                                required
                            />
                            {errors.procedure_date && (
                                <p className="text-sm text-red-500">
                                    {errors.procedure_date}
                                </p>
                            )}
                        </div>
                    </div>

                    <div>
                        <Label htmlFor="performed_by">Performed By</Label>
                        <Input
                            id="performed_by"
                            value={data.performed_by}
                            onChange={(e) =>
                                setData('performed_by', e.target.value)
                            }
                        />
                        {errors.performed_by && (
                            <p className="text-sm text-red-500">
                                {errors.performed_by}
                            </p>
                        )}
                    </div>

                    <div>
                        <Label htmlFor="notes">Procedure Notes</Label>
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
                        <Label htmlFor="results">Results/Outcome</Label>
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
                                : procedure?.id
                                  ? 'Update Procedure'
                                  : 'Add Procedure'}
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    );
}
