import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { type Encounter, type Patient } from '@/types';

interface ClinicalDecisionSupportProps {
    patient: Patient;
    encounter: Encounter;
    alerts: Array<{
        id: number;
        type: string;
        message: string;
        severity: 'low' | 'medium' | 'high' | 'critical';
        recommendations: string[];
    }>;
    guidelines: Array<{
        id: number;
        title: string;
        description: string;
        category: string;
    }>;
}

export default function ClinicalDecisionSupport({
    patient,
    encounter,
    alerts,
    guidelines,
}: ClinicalDecisionSupportProps) {
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
            <Card>
                <CardHeader>
                    <CardTitle>Clinical Decision Support Dashboard</CardTitle>
                </CardHeader>
                <CardContent>
                    <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <h3 className="mb-2 font-semibold">
                                Patient Information
                            </h3>
                            <p>
                                <strong>Name:</strong> {patient.name}
                            </p>
                            <p>
                                <strong>Age:</strong> {patient.age}
                            </p>
                            <p>
                                <strong>Gender:</strong> {patient.gender}
                            </p>
                            <p>
                                <strong>Allergies:</strong>{' '}
                                {patient.allergies || 'None'}
                            </p>
                        </div>
                        <div>
                            <h3 className="mb-2 font-semibold">
                                Current Encounter
                            </h3>
                            <p>
                                <strong>Type:</strong>{' '}
                                {encounter.encounter_type}
                            </p>
                            <p>
                                <strong>Status:</strong> {encounter.status}
                            </p>
                            <p>
                                <strong>Chief Complaint:</strong>{' '}
                                {encounter.chief_complaint}
                            </p>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Clinical Alerts</CardTitle>
                </CardHeader>
                <CardContent>
                    {alerts.length === 0 ? (
                        <Alert variant="default">
                            <AlertTitle>No Alerts</AlertTitle>
                            <AlertDescription>
                                No clinical alerts for this patient at this
                                time.
                            </AlertDescription>
                        </Alert>
                    ) : (
                        <div className="space-y-4">
                            {alerts.map((alert) => (
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
                                            </AlertTitle>
                                            <AlertDescription>
                                                {alert.message}
                                            </AlertDescription>
                                            {alert.recommendations.length >
                                                0 && (
                                                <div className="mt-2">
                                                    <strong>
                                                        Recommendations:
                                                    </strong>
                                                    <ul className="mt-1 list-inside list-disc">
                                                        {alert.recommendations.map(
                                                            (rec, index) => (
                                                                <li key={index}>
                                                                    {rec}
                                                                </li>
                                                            ),
                                                        )}
                                                    </ul>
                                                </div>
                                            )}
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
                    <CardTitle>Clinical Guidelines</CardTitle>
                </CardHeader>
                <CardContent>
                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                        {guidelines.map((guideline) => (
                            <Card
                                key={guideline.id}
                                className="transition-shadow hover:shadow-md"
                            >
                                <CardHeader className="pb-2">
                                    <CardTitle className="text-sm font-medium">
                                        {guideline.title}
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <p className="mb-2 text-sm text-muted-foreground">
                                        {guideline.description}
                                    </p>
                                    <Badge
                                        variant="outline"
                                        className="text-xs"
                                    >
                                        {guideline.category}
                                    </Badge>
                                </CardContent>
                            </Card>
                        ))}
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Decision Support Tools</CardTitle>
                </CardHeader>
                <CardContent>
                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                        <Button
                            variant="outline"
                            className="flex h-auto flex-col items-center justify-center py-4"
                        >
                            <span className="text-sm font-medium">
                                Drug Interaction Checker
                            </span>
                        </Button>
                        <Button
                            variant="outline"
                            className="flex h-auto flex-col items-center justify-center py-4"
                        >
                            <span className="text-sm font-medium">
                                Dosage Calculator
                            </span>
                        </Button>
                        <Button
                            variant="outline"
                            className="flex h-auto flex-col items-center justify-center py-4"
                        >
                            <span className="text-sm font-medium">
                                Clinical Pathways
                            </span>
                        </Button>
                        <Button
                            variant="outline"
                            className="flex h-auto flex-col items-center justify-center py-4"
                        >
                            <span className="text-sm font-medium">
                                Risk Assessment
                            </span>
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>
    );
}
