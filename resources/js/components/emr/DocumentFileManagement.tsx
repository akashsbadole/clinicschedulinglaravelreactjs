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
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

interface DocumentFileManagementProps {
    documents: Array<{
        id: number;
        name: string;
        type: string;
        size: string;
        uploaded_at: string;
        uploaded_by: string;
    }>;
    onUpload: (data: any) => void;
    onDelete: (id: number) => void;
}

export default function DocumentFileManagement({
    documents,
    onUpload,
    onDelete,
}: DocumentFileManagementProps) {
    const { data, setData, post, put, errors, processing } = useForm({
        file: null as File | null,
        document_type: 'medical_record',
        description: '',
    });

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();
        onUpload(data);
    };

    const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        if (e.target.files && e.target.files[0]) {
            setData('file', e.target.files[0]);
        }
    };

    return (
        <div className="space-y-6">
            <Card>
                <CardHeader>
                    <CardTitle>Upload Document</CardTitle>
                </CardHeader>
                <CardContent>
                    <form onSubmit={handleSubmit} className="space-y-4">
                        <div>
                            <Label htmlFor="file">File</Label>
                            <Input
                                id="file"
                                type="file"
                                onChange={handleFileChange}
                                required
                            />
                            {errors.file && (
                                <p className="text-sm text-red-500">
                                    {errors.file}
                                </p>
                            )}
                        </div>

                        <div>
                            <Label htmlFor="document_type">Document Type</Label>
                            <Select
                                value={data.document_type}
                                onValueChange={(value) =>
                                    setData('document_type', value)
                                }
                                required
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="Select document type" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="medical_record">
                                        Medical Record
                                    </SelectItem>
                                    <SelectItem value="lab_result">
                                        Lab Result
                                    </SelectItem>
                                    <SelectItem value="imaging_study">
                                        Imaging Study
                                    </SelectItem>
                                    <SelectItem value="consent_form">
                                        Consent Form
                                    </SelectItem>
                                    <SelectItem value="referral_letter">
                                        Referral Letter
                                    </SelectItem>
                                    <SelectItem value="other">Other</SelectItem>
                                </SelectContent>
                            </Select>
                            {errors.document_type && (
                                <p className="text-sm text-red-500">
                                    {errors.document_type}
                                </p>
                            )}
                        </div>

                        <div>
                            <Label htmlFor="description">Description</Label>
                            <Input
                                id="description"
                                value={data.description}
                                onChange={(e) =>
                                    setData('description', e.target.value)
                                }
                            />
                            {errors.description && (
                                <p className="text-sm text-red-500">
                                    {errors.description}
                                </p>
                            )}
                        </div>

                        <Button type="submit" disabled={processing}>
                            {processing ? 'Uploading...' : 'Upload Document'}
                        </Button>
                    </form>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Document List</CardTitle>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Name</TableHead>
                                <TableHead>Type</TableHead>
                                <TableHead>Size</TableHead>
                                <TableHead>Uploaded At</TableHead>
                                <TableHead>Uploaded By</TableHead>
                                <TableHead className="text-right">
                                    Actions
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {documents.map((document) => (
                                <TableRow key={document.id}>
                                    <TableCell className="font-medium">
                                        {document.name}
                                    </TableCell>
                                    <TableCell>{document.type}</TableCell>
                                    <TableCell>{document.size}</TableCell>
                                    <TableCell>
                                        {document.uploaded_at}
                                    </TableCell>
                                    <TableCell>
                                        {document.uploaded_by}
                                    </TableCell>
                                    <TableCell className="text-right">
                                        <div className="flex justify-end space-x-2">
                                            <Button variant="outline" size="sm">
                                                Download
                                            </Button>
                                            <Button
                                                variant="destructive"
                                                size="sm"
                                                onClick={() =>
                                                    onDelete(document.id)
                                                }
                                            >
                                                Delete
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    );
}
