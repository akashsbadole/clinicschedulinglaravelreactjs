import { Badge } from '@/components/ui/badge';
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

interface InteroperabilityProps {
    integrations: Array<{
        id: number;
        name: string;
        type: string;
        status: string;
        last_sync: string;
        connection_status: 'active' | 'inactive' | 'error';
    }>;
    dataExchangeLogs: Array<{
        id: number;
        source: string;
        destination: string;
        data_type: string;
        status: string;
        timestamp: string;
        records_exchanged: number;
    }>;
    onSaveIntegration: (data: any) => void;
}

export default function Interoperability({
    integrations,
    dataExchangeLogs,
    onSaveIntegration,
}: InteroperabilityProps) {
    const { data, setData, post, put, errors, processing } = useForm({
        integrationName: '',
        integrationType: 'hl7',
        endpointUrl: '',
        apiKey: '',
        status: 'active',
    });

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();
        onSaveIntegration(data);
    };

    const getConnectionStatusColor = (status: string) => {
        switch (status) {
            case 'active':
                return 'bg-green-500';
            case 'inactive':
                return 'bg-gray-500';
            case 'error':
                return 'bg-red-500';
            default:
                return 'bg-gray-500';
        }
    };

    return (
        <div className="space-y-6">
            <Card>
                <CardHeader>
                    <CardTitle>Add New Integration</CardTitle>
                </CardHeader>
                <CardContent>
                    <form onSubmit={handleSubmit} className="space-y-6">
                        <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <Label htmlFor="integrationName">
                                    Integration Name
                                </Label>
                                <Input
                                    id="integrationName"
                                    value={data.integrationName}
                                    onChange={(e) =>
                                        setData(
                                            'integrationName',
                                            e.target.value,
                                        )
                                    }
                                    required
                                />
                                {errors.integrationName && (
                                    <p className="text-sm text-red-500">
                                        {errors.integrationName}
                                    </p>
                                )}
                            </div>
                            <div>
                                <Label htmlFor="integrationType">
                                    Integration Type
                                </Label>
                                <Select
                                    value={data.integrationType}
                                    onValueChange={(value) =>
                                        setData('integrationType', value)
                                    }
                                    required
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select integration type" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="hl7">HL7</SelectItem>
                                        <SelectItem value="fhirapi">
                                            FHIR API
                                        </SelectItem>
                                        <SelectItem value="restapi">
                                            REST API
                                        </SelectItem>
                                        <SelectItem value="database">
                                            Database
                                        </SelectItem>
                                        <SelectItem value="sftp">
                                            SFTP
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                {errors.integrationType && (
                                    <p className="text-sm text-red-500">
                                        {errors.integrationType}
                                    </p>
                                )}
                            </div>
                        </div>

                        <div>
                            <Label htmlFor="endpointUrl">Endpoint URL</Label>
                            <Input
                                id="endpointUrl"
                                value={data.endpointUrl}
                                onChange={(e) =>
                                    setData('endpointUrl', e.target.value)
                                }
                                required
                            />
                            {errors.endpointUrl && (
                                <p className="text-sm text-red-500">
                                    {errors.endpointUrl}
                                </p>
                            )}
                        </div>

                        <div>
                            <Label htmlFor="apiKey">
                                API Key / Credentials
                            </Label>
                            <Input
                                id="apiKey"
                                type="password"
                                value={data.apiKey}
                                onChange={(e) =>
                                    setData('apiKey', e.target.value)
                                }
                            />
                            {errors.apiKey && (
                                <p className="text-sm text-red-500">
                                    {errors.apiKey}
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
                                    <SelectItem value="active">
                                        Active
                                    </SelectItem>
                                    <SelectItem value="inactive">
                                        Inactive
                                    </SelectItem>
                                    <SelectItem value="testing">
                                        Testing
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            {errors.status && (
                                <p className="text-sm text-red-500">
                                    {errors.status}
                                </p>
                            )}
                        </div>

                        <Button type="submit" disabled={processing}>
                            {processing ? 'Saving...' : 'Save Integration'}
                        </Button>
                    </form>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Existing Integrations</CardTitle>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Integration Name</TableHead>
                                <TableHead>Type</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Last Sync</TableHead>
                                <TableHead>Connection</TableHead>
                                <TableHead className="text-right">
                                    Actions
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {integrations.map((integration) => (
                                <TableRow key={integration.id}>
                                    <TableCell className="font-medium">
                                        {integration.name}
                                    </TableCell>
                                    <TableCell>
                                        {integration.type.toUpperCase()}
                                    </TableCell>
                                    <TableCell>
                                        <Badge
                                            variant={
                                                integration.status === 'active'
                                                    ? 'default'
                                                    : 'secondary'
                                            }
                                        >
                                            {integration.status}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        {integration.last_sync}
                                    </TableCell>
                                    <TableCell>
                                        <div className="flex items-center gap-2">
                                            <div
                                                className={`h-3 w-3 rounded-full ${getConnectionStatusColor(integration.connection_status)}`}
                                            ></div>
                                            <span className="capitalize">
                                                {integration.connection_status}
                                            </span>
                                        </div>
                                    </TableCell>
                                    <TableCell className="text-right">
                                        <div className="flex justify-end space-x-2">
                                            <Button variant="outline" size="sm">
                                                Test
                                            </Button>
                                            <Button variant="outline" size="sm">
                                                Sync Now
                                            </Button>
                                            <Button
                                                variant="destructive"
                                                size="sm"
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

            <Card>
                <CardHeader>
                    <CardTitle>Data Exchange Logs</CardTitle>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Source</TableHead>
                                <TableHead>Destination</TableHead>
                                <TableHead>Data Type</TableHead>
                                <TableHead>Records</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Timestamp</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {dataExchangeLogs.map((log) => (
                                <TableRow key={log.id}>
                                    <TableCell className="font-medium">
                                        {log.source}
                                    </TableCell>
                                    <TableCell>{log.destination}</TableCell>
                                    <TableCell>{log.data_type}</TableCell>
                                    <TableCell>
                                        {log.records_exchanged}
                                    </TableCell>
                                    <TableCell>
                                        <Badge
                                            variant={
                                                log.status === 'success'
                                                    ? 'default'
                                                    : 'destructive'
                                            }
                                        >
                                            {log.status}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>{log.timestamp}</TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Interoperability Tools</CardTitle>
                </CardHeader>
                <CardContent>
                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                        <Button
                            variant="outline"
                            className="flex h-auto flex-col items-center justify-center py-4"
                        >
                            <span className="text-sm font-medium">
                                Test All Connections
                            </span>
                        </Button>
                        <Button
                            variant="outline"
                            className="flex h-auto flex-col items-center justify-center py-4"
                        >
                            <span className="text-sm font-medium">
                                Sync All Data
                            </span>
                        </Button>
                        <Button
                            variant="outline"
                            className="flex h-auto flex-col items-center justify-center py-4"
                        >
                            <span className="text-sm font-medium">
                                Export Data Package
                            </span>
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>
    );
}
