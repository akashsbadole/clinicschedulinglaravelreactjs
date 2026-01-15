import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

interface DataSecurityComplianceProps {
    auditLogs: Array<{
        id: number;
        user: string;
        action: string;
        module: string;
        timestamp: string;
        ip_address: string;
        status: string;
    }>;
    complianceStatus: {
        hipaa: boolean;
        gdpr: boolean;
        data_encryption: boolean;
        access_controls: boolean;
        audit_logging: boolean;
    };
    securityAlerts: Array<{
        id: number;
        type: string;
        message: string;
        severity: 'low' | 'medium' | 'high' | 'critical';
        timestamp: string;
        resolved: boolean;
    }>;
}

export default function DataSecurityCompliance({
    auditLogs,
    complianceStatus,
    securityAlerts,
}: DataSecurityComplianceProps) {
    const getSeverityColor = (severity: string) => {
        switch (severity) {
            case 'critical':
                return 'bg-red-500';
            case 'high':
                return 'bg-orange-500';
            case 'medium':
                return 'bg-yellow-500';
            case 'low':
                return 'bg-blue-500';
            default:
                return 'bg-gray-500';
        }
    };

    return (
        <div className="space-y-6">
            <div className="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">
                            HIPAA Compliance
                        </CardTitle>
                        <Badge
                            variant={
                                complianceStatus.hipaa
                                    ? 'default'
                                    : 'destructive'
                            }
                        >
                            {complianceStatus.hipaa
                                ? 'Compliant'
                                : 'Non-Compliant'}
                        </Badge>
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold">
                            {complianceStatus.hipaa ? '✓' : '✗'}
                        </div>
                        <p className="text-xs text-muted-foreground">
                            Health Insurance Portability and Accountability Act
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">
                            GDPR Compliance
                        </CardTitle>
                        <Badge
                            variant={
                                complianceStatus.gdpr
                                    ? 'default'
                                    : 'destructive'
                            }
                        >
                            {complianceStatus.gdpr
                                ? 'Compliant'
                                : 'Non-Compliant'}
                        </Badge>
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold">
                            {complianceStatus.gdpr ? '✓' : '✗'}
                        </div>
                        <p className="text-xs text-muted-foreground">
                            General Data Protection Regulation
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">
                            Data Encryption
                        </CardTitle>
                        <Badge
                            variant={
                                complianceStatus.data_encryption
                                    ? 'default'
                                    : 'destructive'
                            }
                        >
                            {complianceStatus.data_encryption
                                ? 'Enabled'
                                : 'Disabled'}
                        </Badge>
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold">
                            {complianceStatus.data_encryption ? '✓' : '✗'}
                        </div>
                        <p className="text-xs text-muted-foreground">
                            Data encryption at rest and in transit
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">
                            Access Controls
                        </CardTitle>
                        <Badge
                            variant={
                                complianceStatus.access_controls
                                    ? 'default'
                                    : 'destructive'
                            }
                        >
                            {complianceStatus.access_controls
                                ? 'Enabled'
                                : 'Disabled'}
                        </Badge>
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold">
                            {complianceStatus.access_controls ? '✓' : '✗'}
                        </div>
                        <p className="text-xs text-muted-foreground">
                            Role-based access control system
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">
                            Audit Logging
                        </CardTitle>
                        <Badge
                            variant={
                                complianceStatus.audit_logging
                                    ? 'default'
                                    : 'destructive'
                            }
                        >
                            {complianceStatus.audit_logging
                                ? 'Enabled'
                                : 'Disabled'}
                        </Badge>
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold">
                            {complianceStatus.audit_logging ? '✓' : '✗'}
                        </div>
                        <p className="text-xs text-muted-foreground">
                            Comprehensive audit trail logging
                        </p>
                    </CardContent>
                </Card>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Security Alerts</CardTitle>
                </CardHeader>
                <CardContent>
                    {securityAlerts.length === 0 ? (
                        <Alert variant="default">
                            <AlertTitle>No Security Alerts</AlertTitle>
                            <AlertDescription>
                                No active security alerts at this time.
                            </AlertDescription>
                        </Alert>
                    ) : (
                        <div className="space-y-4">
                            {securityAlerts.map((alert) => (
                                <Alert
                                    key={alert.id}
                                    variant={
                                        alert.severity === 'critical'
                                            ? 'destructive'
                                            : 'default'
                                    }
                                >
                                    <div className="flex items-start gap-4">
                                        <div
                                            className={`h-3 w-3 rounded-full ${getSeverityColor(alert.severity)} mt-1`}
                                        ></div>
                                        <div className="flex-1">
                                            <AlertTitle className="flex items-center gap-2">
                                                {alert.type}
                                                <Badge
                                                    variant={
                                                        alert.severity ===
                                                        'critical'
                                                            ? 'destructive'
                                                            : 'default'
                                                    }
                                                >
                                                    {alert.severity.toUpperCase()}
                                                </Badge>
                                                {!alert.resolved && (
                                                    <Badge variant="outline">
                                                        NEW
                                                    </Badge>
                                                )}
                                            </AlertTitle>
                                            <AlertDescription>
                                                {alert.message}
                                            </AlertDescription>
                                            <p className="mt-2 text-xs text-muted-foreground">
                                                {alert.timestamp}
                                            </p>
                                        </div>
                                    </div>
                                </Alert>
                            ))}
                        </div>
                    )}
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Audit Logs</CardTitle>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>User</TableHead>
                                <TableHead>Action</TableHead>
                                <TableHead>Module</TableHead>
                                <TableHead>Timestamp</TableHead>
                                <TableHead>IP Address</TableHead>
                                <TableHead>Status</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {auditLogs.map((log) => (
                                <TableRow key={log.id}>
                                    <TableCell className="font-medium">
                                        {log.user}
                                    </TableCell>
                                    <TableCell>{log.action}</TableCell>
                                    <TableCell>{log.module}</TableCell>
                                    <TableCell>{log.timestamp}</TableCell>
                                    <TableCell>{log.ip_address}</TableCell>
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
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Compliance Actions</CardTitle>
                </CardHeader>
                <CardContent>
                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                        <Button
                            variant="outline"
                            className="flex h-auto flex-col items-center justify-center py-4"
                        >
                            <span className="text-sm font-medium">
                                Run Compliance Check
                            </span>
                        </Button>
                        <Button
                            variant="outline"
                            className="flex h-auto flex-col items-center justify-center py-4"
                        >
                            <span className="text-sm font-medium">
                                Generate Compliance Report
                            </span>
                        </Button>
                        <Button
                            variant="outline"
                            className="flex h-auto flex-col items-center justify-center py-4"
                        >
                            <span className="text-sm font-medium">
                                Export Audit Logs
                            </span>
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>
    );
}
