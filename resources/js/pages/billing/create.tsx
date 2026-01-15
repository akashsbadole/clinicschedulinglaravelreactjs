import Controllers from '@/actions/App/Http/Controllers';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
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
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

import apiService from '@/lib/api';
import { ArrowLeft, Plus, Trash2 } from 'lucide-react';
import { useEffect, useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Billing', href: '/billing' },
    { title: 'Create', href: '/billing/create' },
];

interface BillingItem {
    id: string;
    type: 'service' | 'procedure' | 'medication';
    name: string;
    quantity: number;
    price: number;
    total: number;
}

export default function BillingCreate() {
    const toast = (message: {
        title: string;
        description: string;
        variant?: string;
    }) => {
        if (message.variant === 'destructive') {
            alert('ERROR: ' + message.title + ': ' + message.description);
        } else {
            alert(message.title + ': ' + message.description);
        }
    };

    const [patients, setPatients] = useState<
        { id: number; name: string; phone: string }[]
    >([]);
    const [serviceCatalog, setServiceCatalog] = useState<
        {
            id: number;
            name: string;
            type: 'service' | 'procedure' | 'medication';
            price: number;
        }[]
    >([]);
    const [isLoading, setIsLoading] = useState<boolean>(false);
    const [formData, setFormData] = useState({
        patient_id: '',
        billing_items: [] as BillingItem[],
        payment_method: '',
        due_date: '',
        notes: '',
        tax_rate: 0,
        discount_amount: 0,
    });
    const [subtotal, setSubtotal] = useState<number>(0);
    const [total, setTotal] = useState<number>(0);

    useEffect(() => {
        const fetchData = async () => {
            try {
                setIsLoading(true);
                // Fetch patients
                const patientsData = await apiService.get(
                    Controllers.PatientController.index.url(),
                );
                setPatients((patientsData as any).data || patientsData);

                // Try to fetch service catalog
                try {
                    const catalogData = await apiService.get(
                        '/api/bills/service-catalog',
                    );
                    console.log('Service catalog response:', catalogData);

                    // Handle different response formats
                    const services = (catalogData as any).data ||
                        (catalogData as any).serviceCatalog ||
                        catalogData;

                    if (Array.isArray(services) && services.length > 0) {
                        // Map the response to match our expected format
                        const mappedServices = services.map((service: any) => ({
                            id: service.id || service.service_code,
                            name: service.name,
                            type: (service.category || 'service') as 'service' | 'procedure' | 'medication',
                            price: parseFloat(service.base_price || service.price || 0),
                        }));
                        setServiceCatalog(mappedServices);
                    } else {
                        console.warn('No services found in catalog');
                        setServiceCatalog(getDefaultServices());
                    }
                } catch (catalogError) {
                    console.warn(
                        'Service catalog not available:',
                        catalogError,
                    );
                    setServiceCatalog(getDefaultServices());
                }
            } catch (error: unknown) {
                console.error('Error fetching data:', error);
                toast({
                    title: 'Error',
                    description: 'Failed to fetch patients data',
                    variant: 'destructive',
                });
            } finally {
                setIsLoading(false);
            }
        };

        fetchData();
    }, []);

    const getDefaultServices = (): {
        id: number;
        name: string;
        type: 'service' | 'procedure' | 'medication';
        price: number;
    }[] => [
            {
                id: 1,
                name: 'Consultation',
                type: 'service',
                price: 100,
            },
            {
                id: 2,
                name: 'Follow-up Visit',
                type: 'service',
                price: 75,
            },
            {
                id: 3,
                name: 'Lab Test',
                type: 'procedure',
                price: 50,
            },
            {
                id: 4,
                name: 'X-Ray',
                type: 'procedure',
                price: 150,
            },
            {
                id: 5,
                name: 'Prescription',
                type: 'medication',
                price: 25,
            },
        ];

    useEffect(() => {
        const calculateTotals = () => {
            const sub = formData.billing_items.reduce(
                (sum, item) => sum + item.total,
                0,
            );
            setSubtotal(sub);

            const tax = (sub * formData.tax_rate) / 100;
            const discount = Math.min(formData.discount_amount, sub + tax);

            setTotal(sub + tax - discount);
        };

        calculateTotals();
    }, [formData.billing_items, formData.tax_rate, formData.discount_amount]);

    const handleInputChange = (
        e: React.ChangeEvent<
            HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement
        >,
    ) => {
        const { name, value } = e.target;
        setFormData((prev) => ({
            ...prev,
            [name]: value,
        }));
    };

    const handleSelectChange = (name: string, value: string) => {
        setFormData((prev) => ({
            ...prev,
            [name]: value,
        }));
    };

    const addBillingItem = () => {
        const newItem: BillingItem = {
            id: Date.now().toString(),
            type: 'service',
            name: '',
            quantity: 1,
            price: 0,
            total: 0,
        };
        setFormData((prev) => ({
            ...prev,
            billing_items: [...prev.billing_items, newItem],
        }));
    };

    const removeBillingItem = (id: string) => {
        setFormData((prev) => ({
            ...prev,
            billing_items: prev.billing_items.filter((item) => item.id !== id),
        }));
    };

    const updateBillingItem = (
        id: string,
        field: keyof BillingItem,
        value: any,
    ) => {
        setFormData((prev) => ({
            ...prev,
            billing_items: prev.billing_items.map((item) => {
                if (item.id === id) {
                    const updatedItem = { ...item, [field]: value };
                    if (field === 'quantity' || field === 'price') {
                        updatedItem.total =
                            updatedItem.quantity * updatedItem.price;
                    }
                    return updatedItem;
                }
                return item;
            }),
        }));
    };

    const handleItemSelect = (id: string, serviceId: string) => {
        const service = serviceCatalog.find(
            (s) => s.id.toString() === serviceId,
        );
        if (service) {
            updateBillingItem(id, 'name', service.name);
            updateBillingItem(id, 'type', service.type);
            updateBillingItem(id, 'price', service.price);
        }
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();

        if (!formData.patient_id) {
            toast({
                title: 'Validation Error',
                description: 'Please select a patient',
                variant: 'destructive',
            });
            return;
        }

        if (formData.billing_items.length === 0) {
            toast({
                title: 'Validation Error',
                description: 'Please add at least one billing item',
                variant: 'destructive',
            });
            return;
        }

        if (!formData.payment_method) {
            toast({
                title: 'Validation Error',
                description: 'Please select a payment method',
                variant: 'destructive',
            });
            return;
        }

        try {
            setIsLoading(true);
            const payload = {
                patient_id: formData.patient_id,
                billing_items: formData.billing_items.map((item) => ({
                    type: item.type,
                    name: item.name,
                    quantity: item.quantity,
                    price: item.price,
                })),
                payment_method: formData.payment_method,
                due_date: formData.due_date,
                notes: formData.notes,
                tax_rate: formData.tax_rate,
                discount_amount: formData.discount_amount,
                total_amount: total,
            };

            await apiService.post(
                Controllers.BillController.store.url(),
                payload,
            );
            toast({
                title: 'Success',
                description: 'Bill created successfully',
            });
            window.history.back();
        } catch (error: unknown) {
            const errorMessage =
                error instanceof Error
                    ? error.message
                    : 'Failed to create bill';
            toast({
                title: 'Error',
                description: errorMessage,
                variant: 'destructive',
            });
        } finally {
            setIsLoading(false);
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create Bill" />
            <div className="flex items-center justify-between">
                <div>
                    <h1 className="text-3xl font-bold tracking-tight">
                        Create Bill
                    </h1>
                    <p className="text-muted-foreground">
                        Create a new bill for a patient.
                    </p>
                </div>
                <Button variant="outline" onClick={() => window.history.back()}>
                    <ArrowLeft className="mr-2 h-4 w-4" />
                    Back
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Bill Details</CardTitle>
                    <CardDescription>
                        Enter the information for the new bill.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form onSubmit={handleSubmit} className="space-y-6">
                        <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div className="space-y-2">
                                <Label htmlFor="patient_id">Patient</Label>
                                <Select
                                    name="patient_id"
                                    value={formData.patient_id}
                                    onValueChange={(value) =>
                                        handleSelectChange('patient_id', value)
                                    }
                                    required
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select a patient" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {patients.map((patient) => (
                                            <SelectItem
                                                key={patient.id}
                                                value={patient.id.toString()}
                                            >
                                                {patient.name} - {patient.phone}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="payment_method">
                                    Payment Method
                                </Label>
                                <Select
                                    name="payment_method"
                                    value={formData.payment_method}
                                    onValueChange={(value) =>
                                        handleSelectChange(
                                            'payment_method',
                                            value,
                                        )
                                    }
                                    required
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select payment method" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="cash">
                                            Cash
                                        </SelectItem>
                                        <SelectItem value="card">
                                            Credit/Debit Card
                                        </SelectItem>
                                        <SelectItem value="insurance">
                                            Insurance
                                        </SelectItem>
                                        <SelectItem value="bank_transfer">
                                            Bank Transfer
                                        </SelectItem>
                                        <SelectItem value="check">
                                            Check
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="due_date">Due Date</Label>
                                <Input
                                    id="due_date"
                                    name="due_date"
                                    type="date"
                                    value={formData.due_date}
                                    onChange={handleInputChange}
                                />
                            </div>

                            <div className="space-y-2 md:col-span-2">
                                <Label htmlFor="notes">Notes</Label>
                                <Textarea
                                    id="notes"
                                    name="notes"
                                    value={formData.notes}
                                    onChange={handleInputChange}
                                    placeholder="Enter any additional notes"
                                    rows={3}
                                />
                            </div>
                        </div>

                        {/* Billing Items Section */}
                        <div className="space-y-4">
                            <div className="flex items-center justify-between">
                                <Label>Billing Items</Label>
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    onClick={addBillingItem}
                                >
                                    <Plus className="mr-2 h-4 w-4" />
                                    Add Item
                                </Button>
                            </div>

                            {formData.billing_items.map((item) => (
                                <div
                                    key={item.id}
                                    className="grid grid-cols-1 gap-4 rounded-lg border p-4 md:grid-cols-6"
                                >
                                    <div className="space-y-2">
                                        <Label>Service</Label>
                                        <Select
                                            value={item.name}
                                            onValueChange={(value) =>
                                                handleItemSelect(item.id, value)
                                            }
                                        >
                                            <SelectTrigger>
                                                <SelectValue placeholder="Select service" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {serviceCatalog.map(
                                                    (service) => (
                                                        <SelectItem
                                                            key={service.id}
                                                            value={service.id.toString()}
                                                        >
                                                            {service.name} - $
                                                            {service.price}
                                                        </SelectItem>
                                                    ),
                                                )}
                                            </SelectContent>
                                        </Select>
                                    </div>

                                    <div className="space-y-2">
                                        <Label>Qty</Label>
                                        <Input
                                            type="number"
                                            min="1"
                                            value={item.quantity}
                                            onChange={(e) =>
                                                updateBillingItem(
                                                    item.id,
                                                    'quantity',
                                                    parseInt(e.target.value) ||
                                                    1,
                                                )
                                            }
                                        />
                                    </div>

                                    <div className="space-y-2">
                                        <Label>Price</Label>
                                        <Input
                                            type="number"
                                            step="0.01"
                                            value={item.price}
                                            onChange={(e) =>
                                                updateBillingItem(
                                                    item.id,
                                                    'price',
                                                    parseFloat(
                                                        e.target.value,
                                                    ) || 0,
                                                )
                                            }
                                        />
                                    </div>

                                    <div className="space-y-2">
                                        <Label>Total</Label>
                                        <Input
                                            value={`$${item.total.toFixed(2)}`}
                                            readOnly
                                        />
                                    </div>

                                    <div className="space-y-2">
                                        <Label>Type</Label>
                                        <Input value={item.type} readOnly />
                                    </div>

                                    <div className="flex items-end">
                                        <Button
                                            type="button"
                                            variant="outline"
                                            size="sm"
                                            onClick={() =>
                                                removeBillingItem(item.id)
                                            }
                                        >
                                            <Trash2 className="h-4 w-4" />
                                        </Button>
                                    </div>
                                </div>
                            ))}
                        </div>

                        {/* Totals Section */}
                        <div className="grid grid-cols-1 gap-4 md:grid-cols-4">
                            <div className="space-y-2">
                                <Label htmlFor="tax_rate">Tax Rate (%)</Label>
                                <Input
                                    id="tax_rate"
                                    name="tax_rate"
                                    type="number"
                                    step="0.01"
                                    value={formData.tax_rate}
                                    onChange={handleInputChange}
                                />
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="discount_amount">
                                    Discount ($)
                                </Label>
                                <Input
                                    id="discount_amount"
                                    name="discount_amount"
                                    type="number"
                                    step="0.01"
                                    value={formData.discount_amount}
                                    onChange={handleInputChange}
                                />
                            </div>

                            <div className="space-y-2">
                                <Label>Subtotal</Label>
                                <Input
                                    value={`$${subtotal.toFixed(2)}`}
                                    readOnly
                                />
                            </div>

                            <div className="space-y-2">
                                <Label>Total</Label>
                                <Input
                                    value={`$${total.toFixed(2)}`}
                                    readOnly
                                />
                            </div>
                        </div>

                        <div className="flex justify-end">
                            <Button type="submit" disabled={isLoading}>
                                {isLoading ? 'Creating...' : 'Create Bill'}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </AppLayout>
    );
}
