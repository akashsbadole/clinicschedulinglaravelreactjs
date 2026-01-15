import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

interface DashboardAnalyticsProps {
    stats: {
        totalPatients: number;
        activeEncounters: number;
        pendingLabOrders: number;
        upcomingAppointments: number;
    };
    charts: {
        patientAgeDistribution: Array<{ ageGroup: string; count: number }>;
        encounterTypes: Array<{ type: string; count: number }>;
        monthlyVisits: Array<{ month: string; count: number }>;
    };
}

export default function DashboardAnalytics({
    stats,
    charts,
}: DashboardAnalyticsProps) {
    return (
        <div className="space-y-6">
            <div className="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">
                            Total Patients
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold">
                            {stats.totalPatients}
                        </div>
                        <p className="text-xs text-muted-foreground">
                            Active patients in the system
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">
                            Active Encounters
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold">
                            {stats.activeEncounters}
                        </div>
                        <p className="text-xs text-muted-foreground">
                            Currently active patient encounters
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">
                            Pending Lab Orders
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold">
                            {stats.pendingLabOrders}
                        </div>
                        <p className="text-xs text-muted-foreground">
                            Lab orders awaiting results
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">
                            Upcoming Appointments
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold">
                            {stats.upcomingAppointments}
                        </div>
                        <p className="text-xs text-muted-foreground">
                            Appointments scheduled for today
                        </p>
                    </CardContent>
                </Card>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Clinical Reports</CardTitle>
                </CardHeader>
                <CardContent>
                    <div className="space-y-4">
                        <div className="flex items-center justify-between rounded-lg border p-4">
                            <div>
                                <h3 className="font-medium">
                                    Patient Demographics Report
                                </h3>
                                <p className="text-sm text-muted-foreground">
                                    Age, gender, and location distribution
                                </p>
                            </div>
                            <Button variant="outline" size="sm">
                                Generate Report
                            </Button>
                        </div>

                        <div className="flex items-center justify-between rounded-lg border p-4">
                            <div>
                                <h3 className="font-medium">
                                    Diagnosis Frequency Report
                                </h3>
                                <p className="text-sm text-muted-foreground">
                                    Most common diagnoses and trends
                                </p>
                            </div>
                            <Button variant="outline" size="sm">
                                Generate Report
                            </Button>
                        </div>

                        <div className="flex items-center justify-between rounded-lg border p-4">
                            <div>
                                <h3 className="font-medium">
                                    Treatment Outcomes Report
                                </h3>
                                <p className="text-sm text-muted-foreground">
                                    Effectiveness of treatment plans
                                </p>
                            </div>
                            <Button variant="outline" size="sm">
                                Generate Report
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    );
}
