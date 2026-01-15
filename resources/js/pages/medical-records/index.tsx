import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import apiService from '@/lib/api';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { Edit, Eye, Plus, Search } from 'lucide-react';
import { useEffect, useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Medical Records',
        href: '/medical-records',
    },
];

interface MedicalRecord {
    id: number;
    patient: {
        user: {
            name: string;
        };
    };
    doctor: {
        user: {
            name: string;
        };
    };
    type: string;
    record_date: string;
    description: string;
}

export default function MedicalRecordsIndex() {
    const [records, setRecords] = useState<MedicalRecord[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);

    const fetchRecords = async () => {
        try {
            setLoading(true);
            setError(null);
            const response = (await apiService.get('/medical-records')) as {
                success: boolean;
                data: MedicalRecord[];
            };
            setRecords(response.data || []);
        } catch (err) {
            setError(
                err instanceof Error
                    ? err.message
                    : 'Failed to load medical records',
            );
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchRecords();
    }, []);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Medical Records" />

            <div className="flex flex-col gap-6 p-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold">Medical Records</h1>
                        <p className="text-muted-foreground">
                            Manage patient medical records and documentation
                        </p>
                    </div>
                    <Button asChild>
                        <Link href="/medical-records/create">
                            <Plus className="mr-2 h-4 w-4" />
                            Add Record
                        </Link>
                    </Button>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Medical Records</CardTitle>
                        <CardDescription>
                            A list of all medical records in the system
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="mb-4 flex items-center space-x-2">
                            <div className="relative flex-1">
                                <Search className="absolute top-2.5 left-2 h-4 w-4 text-muted-foreground" />
                                <Input
                                    placeholder="Search records..."
                                    className="pl-8"
                                />
                            </div>
                        </div>

                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Patient</TableHead>
                                    <TableHead>Doctor</TableHead>
                                    <TableHead>Type</TableHead>
                                    <TableHead>Date</TableHead>
                                    <TableHead>Diagnosis</TableHead>
                                    <TableHead className="text-right">
                                        Actions
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {records.map((record) => (
                                    <TableRow key={record.id}>
                                        <TableCell className="font-medium">
                                            {record.patient?.user?.name ||
                                                'N/A'}
                                        </TableCell>
                                        <TableCell>
                                            {record.doctor?.user?.name || 'N/A'}
                                        </TableCell>
                                        <TableCell>
                                            <Badge variant="outline">
                                                {record.type}
                                            </Badge>
                                        </TableCell>
                                        <TableCell>
                                            {record.record_date}
                                        </TableCell>
                                        <TableCell>
                                            {record.description}
                                        </TableCell>
                                        <TableCell className="text-right">
                                            <div className="flex justify-end space-x-2">
                                                <Button
                                                    variant="ghost"
                                                    size="sm"
                                                    asChild
                                                >
                                                    <Link
                                                        href={`/medical-records/${record.id}`}
                                                    >
                                                        <Eye className="h-4 w-4" />
                                                    </Link>
                                                </Button>
                                                <Button
                                                    variant="ghost"
                                                    size="sm"
                                                    asChild
                                                >
                                                    <Link
                                                        href={`/medical-records/${record.id}/edit`}
                                                    >
                                                        <Edit className="h-4 w-4" />
                                                    </Link>
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
        </AppLayout>
    );
}
