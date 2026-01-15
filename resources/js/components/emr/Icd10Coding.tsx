import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

interface Icd10CodingProps {
    icd10Code?: {
        id?: number;
        code: string;
        description: string;
        category?: string;
        chapter?: string;
        block?: string;
        notes?: string;
    };
    onSubmit: (data: any) => void;
    onCancel?: () => void;
}

export default function Icd10Coding({
    icd10Code,
    onSubmit,
    onCancel,
}: Icd10CodingProps) {
    const { data, setData, post, put, errors, processing } = useForm({
        code: icd10Code?.code || '',
        description: icd10Code?.description || '',
        category: icd10Code?.category || '',
        chapter: icd10Code?.chapter || '',
        block: icd10Code?.block || '',
        notes: icd10Code?.notes || '',
    });

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();
        onSubmit(data);
    };

    return (
        <Card>
            <CardHeader>
                <CardTitle>
                    {icd10Code?.id ? 'Edit ICD-10 Code' : 'Add ICD-10 Code'}
                </CardTitle>
            </CardHeader>
            <CardContent>
                <form onSubmit={handleSubmit} className="space-y-6">
                    <div>
                        <Label htmlFor="code">ICD-10 Code</Label>
                        <Input
                            id="code"
                            value={data.code}
                            onChange={(e) => setData('code', e.target.value)}
                            required
                        />
                        {errors.code && (
                            <p className="text-sm text-red-500">
                                {errors.code}
                            </p>
                        )}
                    </div>

                    <div>
                        <Label htmlFor="description">Description</Label>
                        <Textarea
                            id="description"
                            value={data.description}
                            onChange={(e) =>
                                setData('description', e.target.value)
                            }
                            rows={3}
                            required
                        />
                        {errors.description && (
                            <p className="text-sm text-red-500">
                                {errors.description}
                            </p>
                        )}
                    </div>

                    <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <Label htmlFor="category">Category</Label>
                            <Input
                                id="category"
                                value={data.category}
                                onChange={(e) =>
                                    setData('category', e.target.value)
                                }
                            />
                            {errors.category && (
                                <p className="text-sm text-red-500">
                                    {errors.category}
                                </p>
                            )}
                        </div>
                        <div>
                            <Label htmlFor="chapter">Chapter</Label>
                            <Input
                                id="chapter"
                                value={data.chapter}
                                onChange={(e) =>
                                    setData('chapter', e.target.value)
                                }
                            />
                            {errors.chapter && (
                                <p className="text-sm text-red-500">
                                    {errors.chapter}
                                </p>
                            )}
                        </div>
                        <div>
                            <Label htmlFor="block">Block</Label>
                            <Input
                                id="block"
                                value={data.block}
                                onChange={(e) =>
                                    setData('block', e.target.value)
                                }
                            />
                            {errors.block && (
                                <p className="text-sm text-red-500">
                                    {errors.block}
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
                                : icd10Code?.id
                                  ? 'Update ICD-10 Code'
                                  : 'Add ICD-10 Code'}
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    );
}
