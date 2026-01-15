import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

interface AiAssistedFeaturesProps {
    aiSuggestions: Array<{
        id: number;
        type: string;
        suggestion: string;
        confidence: number;
        context: string;
    }>;
    onGenerateSuggestion: (data: any) => void;
    onAcceptSuggestion: (id: number) => void;
    onRejectSuggestion: (id: number) => void;
}

export default function AiAssistedFeatures({
    aiSuggestions,
    onGenerateSuggestion,
    onAcceptSuggestion,
    onRejectSuggestion,
}: AiAssistedFeaturesProps) {
    const { data, setData, post, put, errors, processing } = useForm({
        suggestionType: 'diagnosis',
        patientContext: '',
        clinicalNotes: '',
    });

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();
        onGenerateSuggestion(data);
    };

    return (
        <div className="space-y-6">
            <Card>
                <CardHeader>
                    <CardTitle>AI Assistance Request</CardTitle>
                </CardHeader>
                <CardContent>
                    <form onSubmit={handleSubmit} className="space-y-6">
                        <div>
                            <Label htmlFor="suggestionType">
                                Suggestion Type
                            </Label>
                            <Select
                                value={data.suggestionType}
                                onValueChange={(value) =>
                                    setData('suggestionType', value)
                                }
                                required
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="Select suggestion type" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="diagnosis">
                                        Diagnosis Suggestion
                                    </SelectItem>
                                    <SelectItem value="treatment">
                                        Treatment Plan
                                    </SelectItem>
                                    <SelectItem value="medication">
                                        Medication Recommendation
                                    </SelectItem>
                                    <SelectItem value="lab">
                                        Lab Test Recommendation
                                    </SelectItem>
                                    <SelectItem value="coding">
                                        ICD-10 Coding
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            {errors.suggestionType && (
                                <p className="text-sm text-red-500">
                                    {errors.suggestionType}
                                </p>
                            )}
                        </div>

                        <div>
                            <Label htmlFor="patientContext">
                                Patient Context
                            </Label>
                            <Textarea
                                id="patientContext"
                                value={data.patientContext}
                                onChange={(e) =>
                                    setData('patientContext', e.target.value)
                                }
                                rows={4}
                                placeholder="Enter patient age, gender, symptoms, medical history, etc."
                                required
                            />
                            {errors.patientContext && (
                                <p className="text-sm text-red-500">
                                    {errors.patientContext}
                                </p>
                            )}
                        </div>

                        <div>
                            <Label htmlFor="clinicalNotes">
                                Clinical Notes (Optional)
                            </Label>
                            <Textarea
                                id="clinicalNotes"
                                value={data.clinicalNotes}
                                onChange={(e) =>
                                    setData('clinicalNotes', e.target.value)
                                }
                                rows={4}
                                placeholder="Enter relevant clinical notes, observations, or findings"
                            />
                            {errors.clinicalNotes && (
                                <p className="text-sm text-red-500">
                                    {errors.clinicalNotes}
                                </p>
                            )}
                        </div>

                        <Button type="submit" disabled={processing}>
                            {processing
                                ? 'Generating...'
                                : 'Generate AI Suggestion'}
                        </Button>
                    </form>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>AI Suggestions</CardTitle>
                </CardHeader>
                <CardContent>
                    {aiSuggestions.length === 0 ? (
                        <div className="py-8 text-center">
                            <p className="text-muted-foreground">
                                No AI suggestions available
                            </p>
                            <p className="mt-2 text-sm text-muted-foreground">
                                Use the form above to generate AI-assisted
                                suggestions
                            </p>
                        </div>
                    ) : (
                        <div className="space-y-6">
                            {aiSuggestions.map((suggestion) => (
                                <div
                                    key={suggestion.id}
                                    className="rounded-lg border p-4"
                                >
                                    <div className="mb-3 flex items-center justify-between">
                                        <div className="flex items-center gap-2">
                                            <span className="text-sm font-medium capitalize">
                                                {suggestion.type}
                                            </span>
                                            <span className="text-xs text-muted-foreground">
                                                Confidence:{' '}
                                                {(
                                                    suggestion.confidence * 100
                                                ).toFixed(1)}
                                                %
                                            </span>
                                        </div>
                                        <div className="flex gap-2">
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                onClick={() =>
                                                    onAcceptSuggestion(
                                                        suggestion.id,
                                                    )
                                                }
                                            >
                                                Accept
                                            </Button>
                                            <Button
                                                variant="destructive"
                                                size="sm"
                                                onClick={() =>
                                                    onRejectSuggestion(
                                                        suggestion.id,
                                                    )
                                                }
                                            >
                                                Reject
                                            </Button>
                                        </div>
                                    </div>
                                    <div className="mb-3">
                                        <h4 className="mb-1 font-medium">
                                            Suggestion:
                                        </h4>
                                        <p className="text-sm">
                                            {suggestion.suggestion}
                                        </p>
                                    </div>
                                    <div>
                                        <h4 className="mb-1 font-medium">
                                            Context:
                                        </h4>
                                        <p className="text-sm text-muted-foreground">
                                            {suggestion.context}
                                        </p>
                                    </div>
                                </div>
                            ))}
                        </div>
                    )}
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>AI Tools</CardTitle>
                </CardHeader>
                <CardContent>
                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                        <Button
                            variant="outline"
                            className="flex h-auto flex-col items-center justify-center py-4"
                        >
                            <span className="text-sm font-medium">
                                Clinical Decision Support
                            </span>
                        </Button>
                        <Button
                            variant="outline"
                            className="flex h-auto flex-col items-center justify-center py-4"
                        >
                            <span className="text-sm font-medium">
                                Predictive Analytics
                            </span>
                        </Button>
                        <Button
                            variant="outline"
                            className="flex h-auto flex-col items-center justify-center py-4"
                        >
                            <span className="text-sm font-medium">
                                Natural Language Processing
                            </span>
                        </Button>
                        <Button
                            variant="outline"
                            className="flex h-auto flex-col items-center justify-center py-4"
                        >
                            <span className="text-sm font-medium">
                                Image Analysis
                            </span>
                        </Button>
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
                                Automated Documentation
                            </span>
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>AI Settings</CardTitle>
                </CardHeader>
                <CardContent>
                    <div className="space-y-4">
                        <div className="flex items-center justify-between">
                            <div>
                                <h3 className="font-medium">
                                    AI Assistance Enabled
                                </h3>
                                <p className="text-sm text-muted-foreground">
                                    Enable AI-powered suggestions and
                                    recommendations
                                </p>
                            </div>
                            <Button variant="outline" size="sm">
                                Toggle
                            </Button>
                        </div>

                        <div className="flex items-center justify-between">
                            <div>
                                <h3 className="font-medium">
                                    Confidence Threshold
                                </h3>
                                <p className="text-sm text-muted-foreground">
                                    Minimum confidence level for AI suggestions
                                </p>
                            </div>
                            <Select defaultValue="medium">
                                <SelectTrigger className="w-[120px]">
                                    <SelectValue placeholder="Select threshold" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="low">
                                        Low (50%)
                                    </SelectItem>
                                    <SelectItem value="medium">
                                        Medium (70%)
                                    </SelectItem>
                                    <SelectItem value="high">
                                        High (90%)
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div className="flex items-center justify-between">
                            <div>
                                <h3 className="font-medium">Data Sharing</h3>
                                <p className="text-sm text-muted-foreground">
                                    Allow anonymous data sharing for AI training
                                </p>
                            </div>
                            <Button variant="outline" size="sm">
                                Configure
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    );
}
