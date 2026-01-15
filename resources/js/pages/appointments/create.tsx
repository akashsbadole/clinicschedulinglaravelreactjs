import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { ArrowLeft } from 'lucide-react';

export default function AppointmentCreate() {
    return (
        <div className="space-y-6 p-6">
            <div className="flex items-center justify-between">
                <div>
                    <h1 className="text-3xl font-bold tracking-tight">Create Appointment</h1>
                    <p className="text-muted-foreground">Schedule a new appointment for a patient.</p>
                </div>
                <Button variant="outline" onClick={() => window.history.back()}>
                    <ArrowLeft className="mr-2 h-4 w-4" />
                    Back
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Appointment Details</CardTitle>
                    <CardDescription>Enter the information for the new appointment.</CardDescription>
                </CardHeader>
                <CardContent>
                    <div className="py-12 text-center text-muted-foreground">
                        Appointment creation form is coming soon.
                    </div>
                </CardContent>
            </Card>
        </div>
    );
}
