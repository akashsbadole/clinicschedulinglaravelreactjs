import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
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
import { Head, router, useForm } from '@inertiajs/react';
import { useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin Dashboard',
        href: '/admin/dashboard',
    },
];

interface User {
    id: number;
    name: string;
    email: string;
}

interface FollowUp {
    id: number;
    type: string;
    content: string;
    created_at: string;
    user: User;
}

interface Enquiry {
    id: number;
    name: string;
    email: string;
    phone?: string;
    message: string;
    status: string;
    created_at: string;
    follow_ups: FollowUp[];
}

interface Contact {
    id: number;
    name: string;
    email: string;
    phone?: string;
    subject: string;
    message: string;
    status: string;
    created_at: string;
    follow_ups: FollowUp[];
}

const statusOptions = [
    {
        value: 'pending',
        label: 'Pending',
        color: 'bg-yellow-100 text-yellow-800',
    },
    {
        value: 'in_progress',
        label: 'In Progress',
        color: 'bg-blue-100 text-blue-800',
    },
    {
        value: 'resolved',
        label: 'Resolved',
        color: 'bg-green-100 text-green-800',
    },
    { value: 'closed', label: 'Closed', color: 'bg-gray-100 text-gray-800' },
];

const followUpTypes = [
    { value: 'note', label: 'Note' },
    { value: 'response', label: 'Response' },
];

export default function AdminDashboard({
    enquiries: initialEnquiries,
    contacts: initialContacts,
}: {
    enquiries: Enquiry[];
    contacts: Contact[];
}) {
    const [enquiries, setEnquiries] = useState(initialEnquiries);
    const [contacts, setContacts] = useState(initialContacts);
    const [expandedEnquiry, setExpandedEnquiry] = useState<number | null>(null);
    const [expandedContact, setExpandedContact] = useState<number | null>(null);

    const { patch } = useForm({});
    const {
        data: followUpData,
        setData: setFollowUpData,
        post,
        reset,
    } = useForm({
        type: 'note',
        content: '',
    });

    const updateEnquiryStatus = (enquiryId: number, status: string) => {
        router.patch(
            `/admin/enquiries/${enquiryId}/status`,
            { status },
            {
                onSuccess: () => {
                    setEnquiries(
                        enquiries.map((enquiry) =>
                            enquiry.id === enquiryId
                                ? { ...enquiry, status }
                                : enquiry,
                        ),
                    );
                },
            },
        );
    };

    const updateContactStatus = (contactId: number, status: string) => {
        router.patch(
            `/admin/contacts/${contactId}/status`,
            { status },
            {
                onSuccess: () => {
                    setContacts(
                        contacts.map((contact) =>
                            contact.id === contactId
                                ? { ...contact, status }
                                : contact,
                        ),
                    );
                },
            },
        );
    };

    const addEnquiryFollowUp = (enquiryId: number) => {
        post(`/admin/enquiries/${enquiryId}/follow-ups`, {
            onSuccess: () => {
                reset();
                // Refresh the page to get updated follow-ups
                window.location.reload();
            },
        });
    };

    const addContactFollowUp = (contactId: number) => {
        post(`/admin/contacts/${contactId}/follow-ups`, {
            onSuccess: () => {
                reset();
                // Refresh the page to get updated follow-ups
                window.location.reload();
            },
        });
    };

    const getStatusBadge = (status: string) => {
        const statusOption = statusOptions.find(
            (option) => option.value === status,
        );
        return statusOption ? (
            <Badge className={statusOption.color}>{statusOption.label}</Badge>
        ) : null;
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Admin Dashboard" />

            <div className="flex flex-col gap-6 p-6">
                <div>
                    <h1 className="text-2xl font-bold">Admin Dashboard</h1>
                    <p className="text-muted-foreground">
                        Manage enquiries and contacts from the website.
                    </p>
                </div>

                {/* Enquiries Section */}
                <div className="rounded-lg border bg-card p-6 shadow-sm">
                    <h2 className="mb-4 text-xl font-semibold">Enquiries</h2>
                    {enquiries.length === 0 ? (
                        <p className="text-muted-foreground">
                            No enquiries yet.
                        </p>
                    ) : (
                        <div className="space-y-4">
                            {enquiries.map((enquiry) => (
                                <Card key={enquiry.id}>
                                    <CardHeader>
                                        <div className="flex items-start justify-between">
                                            <div>
                                                <h3 className="font-medium">
                                                    {enquiry.name}
                                                </h3>
                                                <p className="text-sm text-muted-foreground">
                                                    {enquiry.email}
                                                </p>
                                                {enquiry.phone && (
                                                    <p className="text-sm text-muted-foreground">
                                                        {enquiry.phone}
                                                    </p>
                                                )}
                                                <div className="mt-2">
                                                    {getStatusBadge(
                                                        enquiry.status,
                                                    )}
                                                </div>
                                            </div>
                                            <div className="flex flex-col items-end gap-2">
                                                <span className="text-sm text-muted-foreground">
                                                    {new Date(
                                                        enquiry.created_at,
                                                    ).toLocaleDateString()}
                                                </span>
                                                <Select
                                                    value={enquiry.status}
                                                    onValueChange={(value) =>
                                                        updateEnquiryStatus(
                                                            enquiry.id,
                                                            value,
                                                        )
                                                    }
                                                >
                                                    <SelectTrigger className="w-32">
                                                        <SelectValue />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        {statusOptions.map(
                                                            (option) => (
                                                                <SelectItem
                                                                    key={
                                                                        option.value
                                                                    }
                                                                    value={
                                                                        option.value
                                                                    }
                                                                >
                                                                    {
                                                                        option.label
                                                                    }
                                                                </SelectItem>
                                                            ),
                                                        )}
                                                    </SelectContent>
                                                </Select>
                                            </div>
                                        </div>
                                    </CardHeader>
                                    <CardContent>
                                        <p className="mb-4 text-sm">
                                            {enquiry.message}
                                        </p>

                                        {/* Follow-ups */}
                                        {enquiry.follow_ups &&
                                            enquiry.follow_ups.length > 0 && (
                                                <div className="mb-4">
                                                    <h4 className="mb-2 text-sm font-medium">
                                                        Follow-ups:
                                                    </h4>
                                                    <div className="space-y-2">
                                                        {enquiry.follow_ups.map(
                                                            (followUp) => (
                                                                <div
                                                                    key={
                                                                        followUp.id
                                                                    }
                                                                    className="rounded bg-gray-50 p-3 text-sm"
                                                                >
                                                                    <div className="mb-1 flex items-start justify-between">
                                                                        <span className="font-medium">
                                                                            {
                                                                                followUp
                                                                                    .user
                                                                                    .name
                                                                            }
                                                                        </span>
                                                                        <span className="text-xs text-muted-foreground">
                                                                            {new Date(
                                                                                followUp.created_at,
                                                                            ).toLocaleDateString()}
                                                                        </span>
                                                                    </div>
                                                                    <p>
                                                                        {
                                                                            followUp.content
                                                                        }
                                                                    </p>
                                                                </div>
                                                            ),
                                                        )}
                                                    </div>
                                                </div>
                                            )}

                                        {/* Add Follow-up */}
                                        <div className="border-t pt-4">
                                            <h4 className="mb-2 text-sm font-medium">
                                                Add Follow-up:
                                            </h4>
                                            <div className="space-y-2">
                                                <Select
                                                    value={followUpData.type}
                                                    onValueChange={(value) =>
                                                        setFollowUpData(
                                                            'type',
                                                            value,
                                                        )
                                                    }
                                                >
                                                    <SelectTrigger className="w-32">
                                                        <SelectValue />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        {followUpTypes.map(
                                                            (type) => (
                                                                <SelectItem
                                                                    key={
                                                                        type.value
                                                                    }
                                                                    value={
                                                                        type.value
                                                                    }
                                                                >
                                                                    {type.label}
                                                                </SelectItem>
                                                            ),
                                                        )}
                                                    </SelectContent>
                                                </Select>
                                                <Textarea
                                                    placeholder="Enter your note or response..."
                                                    value={followUpData.content}
                                                    onChange={(e) =>
                                                        setFollowUpData(
                                                            'content',
                                                            e.target.value,
                                                        )
                                                    }
                                                    rows={3}
                                                />
                                                <Button
                                                    size="sm"
                                                    onClick={() =>
                                                        addEnquiryFollowUp(
                                                            enquiry.id,
                                                        )
                                                    }
                                                    disabled={
                                                        !followUpData.content.trim()
                                                    }
                                                >
                                                    Add Follow-up
                                                </Button>
                                            </div>
                                        </div>
                                    </CardContent>
                                </Card>
                            ))}
                        </div>
                    )}
                </div>

                {/* Contacts Section */}
                <div className="rounded-lg border bg-card p-6 shadow-sm">
                    <h2 className="mb-4 text-xl font-semibold">Contacts</h2>
                    {contacts.length === 0 ? (
                        <p className="text-muted-foreground">
                            No contacts yet.
                        </p>
                    ) : (
                        <div className="space-y-4">
                            {contacts.map((contact) => (
                                <Card key={contact.id}>
                                    <CardHeader>
                                        <div className="flex items-start justify-between">
                                            <div>
                                                <h3 className="font-medium">
                                                    {contact.name}
                                                </h3>
                                                <p className="text-sm text-muted-foreground">
                                                    {contact.email}
                                                </p>
                                                {contact.phone && (
                                                    <p className="text-sm text-muted-foreground">
                                                        {contact.phone}
                                                    </p>
                                                )}
                                                <p className="text-sm font-medium text-muted-foreground">
                                                    {contact.subject}
                                                </p>
                                                <div className="mt-2">
                                                    {getStatusBadge(
                                                        contact.status,
                                                    )}
                                                </div>
                                            </div>
                                            <div className="flex flex-col items-end gap-2">
                                                <span className="text-sm text-muted-foreground">
                                                    {new Date(
                                                        contact.created_at,
                                                    ).toLocaleDateString()}
                                                </span>
                                                <Select
                                                    value={contact.status}
                                                    onValueChange={(value) =>
                                                        updateContactStatus(
                                                            contact.id,
                                                            value,
                                                        )
                                                    }
                                                >
                                                    <SelectTrigger className="w-32">
                                                        <SelectValue />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        {statusOptions.map(
                                                            (option) => (
                                                                <SelectItem
                                                                    key={
                                                                        option.value
                                                                    }
                                                                    value={
                                                                        option.value
                                                                    }
                                                                >
                                                                    {
                                                                        option.label
                                                                    }
                                                                </SelectItem>
                                                            ),
                                                        )}
                                                    </SelectContent>
                                                </Select>
                                            </div>
                                        </div>
                                    </CardHeader>
                                    <CardContent>
                                        <p className="mb-4 text-sm">
                                            {contact.message}
                                        </p>

                                        {/* Follow-ups */}
                                        {contact.follow_ups &&
                                            contact.follow_ups.length > 0 && (
                                                <div className="mb-4">
                                                    <h4 className="mb-2 text-sm font-medium">
                                                        Follow-ups:
                                                    </h4>
                                                    <div className="space-y-2">
                                                        {contact.follow_ups.map(
                                                            (followUp) => (
                                                                <div
                                                                    key={
                                                                        followUp.id
                                                                    }
                                                                    className="rounded bg-gray-50 p-3 text-sm"
                                                                >
                                                                    <div className="mb-1 flex items-start justify-between">
                                                                        <span className="font-medium">
                                                                            {
                                                                                followUp
                                                                                    .user
                                                                                    .name
                                                                            }
                                                                        </span>
                                                                        <span className="text-xs text-muted-foreground">
                                                                            {new Date(
                                                                                followUp.created_at,
                                                                            ).toLocaleDateString()}
                                                                        </span>
                                                                    </div>
                                                                    <p>
                                                                        {
                                                                            followUp.content
                                                                        }
                                                                    </p>
                                                                </div>
                                                            ),
                                                        )}
                                                    </div>
                                                </div>
                                            )}

                                        {/* Add Follow-up */}
                                        <div className="border-t pt-4">
                                            <h4 className="mb-2 text-sm font-medium">
                                                Add Follow-up:
                                            </h4>
                                            <div className="space-y-2">
                                                <Select
                                                    value={followUpData.type}
                                                    onValueChange={(value) =>
                                                        setFollowUpData(
                                                            'type',
                                                            value,
                                                        )
                                                    }
                                                >
                                                    <SelectTrigger className="w-32">
                                                        <SelectValue />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        {followUpTypes.map(
                                                            (type) => (
                                                                <SelectItem
                                                                    key={
                                                                        type.value
                                                                    }
                                                                    value={
                                                                        type.value
                                                                    }
                                                                >
                                                                    {type.label}
                                                                </SelectItem>
                                                            ),
                                                        )}
                                                    </SelectContent>
                                                </Select>
                                                <Textarea
                                                    placeholder="Enter your note or response..."
                                                    value={followUpData.content}
                                                    onChange={(e) =>
                                                        setFollowUpData(
                                                            'content',
                                                            e.target.value,
                                                        )
                                                    }
                                                    rows={3}
                                                />
                                                <Button
                                                    size="sm"
                                                    onClick={() =>
                                                        addContactFollowUp(
                                                            contact.id,
                                                        )
                                                    }
                                                    disabled={
                                                        !followUpData.content.trim()
                                                    }
                                                >
                                                    Add Follow-up
                                                </Button>
                                            </div>
                                        </div>
                                    </CardContent>
                                </Card>
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
