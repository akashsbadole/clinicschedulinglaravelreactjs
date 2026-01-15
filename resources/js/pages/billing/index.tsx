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
        title: 'Billing',
        href: '/billing',
    },
];

interface Bill {
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
    total_amount: number;
    paid_amount: number;
    status: string;
    bill_date: string;
}

export default function BillingIndex() {
    const [bills, setBills] = useState<Bill[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);

    const fetchBills = async () => {
        try {
            setLoading(true);
            setError(null);
            const response = (await apiService.get('/bills')) as {
                success: boolean;
                data: Bill[];
            };
            setBills(response.data || []);
        } catch (err) {
            setError(
                err instanceof Error ? err.message : 'Failed to load bills',
            );
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchBills();
    }, []);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Billing" />

            <div className="flex flex-col gap-6 p-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold">Billing</h1>
                        <p className="text-muted-foreground">
                            Manage invoices, payments, and financial records
                        </p>
                    </div>
                    <Button asChild>
                        <Link href="/billing/create">
                            <Plus className="mr-2 h-4 w-4" />
                            Create Bill
                        </Link>
                    </Button>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Bill List</CardTitle>
                        <CardDescription>
                            A list of all bills and invoices
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="mb-4 flex items-center space-x-2">
                            <div className="relative flex-1">
                                <Search className="absolute top-2.5 left-2 h-4 w-4 text-muted-foreground" />
                                <Input
                                    placeholder="Search bills..."
                                    className="pl-8"
                                />
                            </div>
                        </div>

                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Patient</TableHead>
                                    <TableHead>Doctor</TableHead>
                                    <TableHead>Total Amount</TableHead>
                                    <TableHead>Paid Amount</TableHead>
                                    <TableHead>Balance</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead className="text-right">
                                        Actions
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {bills.map((bill) => (
                                    <TableRow key={bill.id}>
                                        <TableCell className="font-medium">
                                            {bill.patient?.user?.name || 'N/A'}
                                        </TableCell>
                                        <TableCell>
                                            {bill.doctor?.user?.name || 'N/A'}
                                        </TableCell>
                                        <TableCell>
                                            ${bill.total_amount.toFixed(2)}
                                        </TableCell>
                                        <TableCell>
                                            ${bill.paid_amount.toFixed(2)}
                                        </TableCell>
                                        <TableCell>
                                            $
                                            {(
                                                bill.total_amount -
                                                bill.paid_amount
                                            ).toFixed(2)}
                                        </TableCell>
                                        <TableCell>
                                            <Badge
                                                variant={
                                                    bill.status === 'Paid'
                                                        ? 'default'
                                                        : bill.status ===
                                                            'Partially Paid'
                                                          ? 'secondary'
                                                          : 'destructive'
                                                }
                                            >
                                                {bill.status}
                                            </Badge>
                                        </TableCell>
                                        <TableCell className="text-right">
                                            <div className="flex justify-end space-x-2">
                                                <Button
                                                    variant="ghost"
                                                    size="sm"
                                                    asChild
                                                >
                                                    <Link
                                                        href={`/billing/${bill.id}`}
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
                                                        href={`/billing/${bill.id}/edit`}
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
