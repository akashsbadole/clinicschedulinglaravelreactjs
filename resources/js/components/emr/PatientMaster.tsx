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
import { Textarea } from '@/components/ui/textarea';
import { type Patient } from '@/types';
import { useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

interface PatientMasterProps {
    patient?: Patient;
    onSubmit: (data: any) => void;
    onCancel?: () => void;
}

export default function PatientMaster({
    patient,
    onSubmit,
    onCancel,
}: PatientMasterProps) {
    const { data, setData, post, put, errors, processing } = useForm({
        first_name: patient?.first_name || '',
        last_name: patient?.last_name || '',
        email: patient?.email || '',
        phone: patient?.phone || '',
        date_of_birth: patient?.date_of_birth || '',
        gender: patient?.gender || '',
        blood_type: patient?.blood_type || '',
        address: patient?.address || '',
        emergency_contact_name: patient?.emergency_contact_name || '',
        emergency_contact_phone: patient?.emergency_contact_phone || '',
        allergies: patient?.allergies || '',
        medical_history: patient?.medical_history || '',
        insurance_provider: patient?.insurance_provider || '',
        insurance_policy_number: patient?.insurance_policy_number || '',
        marital_status: patient?.marital_status || '',
        occupation: patient?.occupation || '',
        national_id: patient?.national_id || '',
        preferred_language: patient?.preferred_language || '',
        contact_preferences: patient?.contact_preferences || '',
        family_medical_history: patient?.family_medical_history || '',
        social_history: patient?.social_history || '',
        lifestyle_factors: patient?.lifestyle_factors || '',
    });

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();
        onSubmit(data);
    };

    return (
        <Card>
            <CardHeader>
                <CardTitle>
                    {patient ? 'Edit Patient' : 'Create New Patient'}
                </CardTitle>
            </CardHeader>
            <CardContent>
                <form onSubmit={handleSubmit} className="space-y-6">
                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <Label htmlFor="first_name">First Name</Label>
                            <Input
                                id="first_name"
                                value={data.first_name}
                                onChange={(e) =>
                                    setData('first_name', e.target.value)
                                }
                                required
                            />
                            {errors.first_name && (
                                <p className="text-sm text-red-500">
                                    {errors.first_name}
                                </p>
                            )}
                        </div>
                        <div>
                            <Label htmlFor="last_name">Last Name</Label>
                            <Input
                                id="last_name"
                                value={data.last_name}
                                onChange={(e) =>
                                    setData('last_name', e.target.value)
                                }
                                required
                            />
                            {errors.last_name && (
                                <p className="text-sm text-red-500">
                                    {errors.last_name}
                                </p>
                            )}
                        </div>
                    </div>

                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <Label htmlFor="email">Email</Label>
                            <Input
                                id="email"
                                type="email"
                                value={data.email}
                                onChange={(e) =>
                                    setData('email', e.target.value)
                                }
                                required
                            />
                            {errors.email && (
                                <p className="text-sm text-red-500">
                                    {errors.email}
                                </p>
                            )}
                        </div>
                        <div>
                            <Label htmlFor="phone">Phone</Label>
                            <Input
                                id="phone"
                                value={data.phone}
                                onChange={(e) =>
                                    setData('phone', e.target.value)
                                }
                                required
                            />
                            {errors.phone && (
                                <p className="text-sm text-red-500">
                                    {errors.phone}
                                </p>
                            )}
                        </div>
                    </div>

                    <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <Label htmlFor="date_of_birth">Date of Birth</Label>
                            <Input
                                id="date_of_birth"
                                type="date"
                                value={data.date_of_birth}
                                onChange={(e) =>
                                    setData('date_of_birth', e.target.value)
                                }
                                required
                            />
                            {errors.date_of_birth && (
                                <p className="text-sm text-red-500">
                                    {errors.date_of_birth}
                                </p>
                            )}
                        </div>
                        <div>
                            <Label htmlFor="gender">Gender</Label>
                            <Select
                                value={data.gender}
                                onValueChange={(value) =>
                                    setData('gender', value)
                                }
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="Select gender" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="male">Male</SelectItem>
                                    <SelectItem value="female">
                                        Female
                                    </SelectItem>
                                    <SelectItem value="other">Other</SelectItem>
                                </SelectContent>
                            </Select>
                            {errors.gender && (
                                <p className="text-sm text-red-500">
                                    {errors.gender}
                                </p>
                            )}
                        </div>
                        <div>
                            <Label htmlFor="blood_type">Blood Type</Label>
                            <Input
                                id="blood_type"
                                value={data.blood_type}
                                onChange={(e) =>
                                    setData('blood_type', e.target.value)
                                }
                            />
                            {errors.blood_type && (
                                <p className="text-sm text-red-500">
                                    {errors.blood_type}
                                </p>
                            )}
                        </div>
                    </div>

                    <div>
                        <Label htmlFor="address">Address</Label>
                        <Textarea
                            id="address"
                            value={data.address}
                            onChange={(e) => setData('address', e.target.value)}
                            rows={3}
                        />
                        {errors.address && (
                            <p className="text-sm text-red-500">
                                {errors.address}
                            </p>
                        )}
                    </div>

                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <Label htmlFor="emergency_contact_name">
                                Emergency Contact Name
                            </Label>
                            <Input
                                id="emergency_contact_name"
                                value={data.emergency_contact_name}
                                onChange={(e) =>
                                    setData(
                                        'emergency_contact_name',
                                        e.target.value,
                                    )
                                }
                            />
                            {errors.emergency_contact_name && (
                                <p className="text-sm text-red-500">
                                    {errors.emergency_contact_name}
                                </p>
                            )}
                        </div>
                        <div>
                            <Label htmlFor="emergency_contact_phone">
                                Emergency Contact Phone
                            </Label>
                            <Input
                                id="emergency_contact_phone"
                                value={data.emergency_contact_phone}
                                onChange={(e) =>
                                    setData(
                                        'emergency_contact_phone',
                                        e.target.value,
                                    )
                                }
                            />
                            {errors.emergency_contact_phone && (
                                <p className="text-sm text-red-500">
                                    {errors.emergency_contact_phone}
                                </p>
                            )}
                        </div>
                    </div>

                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <Label htmlFor="insurance_provider">
                                Insurance Provider
                            </Label>
                            <Input
                                id="insurance_provider"
                                value={data.insurance_provider}
                                onChange={(e) =>
                                    setData(
                                        'insurance_provider',
                                        e.target.value,
                                    )
                                }
                            />
                            {errors.insurance_provider && (
                                <p className="text-sm text-red-500">
                                    {errors.insurance_provider}
                                </p>
                            )}
                        </div>
                        <div>
                            <Label htmlFor="insurance_policy_number">
                                Insurance Policy Number
                            </Label>
                            <Input
                                id="insurance_policy_number"
                                value={data.insurance_policy_number}
                                onChange={(e) =>
                                    setData(
                                        'insurance_policy_number',
                                        e.target.value,
                                    )
                                }
                            />
                            {errors.insurance_policy_number && (
                                <p className="text-sm text-red-500">
                                    {errors.insurance_policy_number}
                                </p>
                            )}
                        </div>
                    </div>

                    <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <Label htmlFor="marital_status">
                                Marital Status
                            </Label>
                            <Input
                                id="marital_status"
                                value={data.marital_status}
                                onChange={(e) =>
                                    setData('marital_status', e.target.value)
                                }
                            />
                            {errors.marital_status && (
                                <p className="text-sm text-red-500">
                                    {errors.marital_status}
                                </p>
                            )}
                        </div>
                        <div>
                            <Label htmlFor="occupation">Occupation</Label>
                            <Input
                                id="occupation"
                                value={data.occupation}
                                onChange={(e) =>
                                    setData('occupation', e.target.value)
                                }
                            />
                            {errors.occupation && (
                                <p className="text-sm text-red-500">
                                    {errors.occupation}
                                </p>
                            )}
                        </div>
                        <div>
                            <Label htmlFor="national_id">National ID</Label>
                            <Input
                                id="national_id"
                                value={data.national_id}
                                onChange={(e) =>
                                    setData('national_id', e.target.value)
                                }
                            />
                            {errors.national_id && (
                                <p className="text-sm text-red-500">
                                    {errors.national_id}
                                </p>
                            )}
                        </div>
                    </div>

                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <Label htmlFor="preferred_language">
                                Preferred Language
                            </Label>
                            <Input
                                id="preferred_language"
                                value={data.preferred_language}
                                onChange={(e) =>
                                    setData(
                                        'preferred_language',
                                        e.target.value,
                                    )
                                }
                            />
                            {errors.preferred_language && (
                                <p className="text-sm text-red-500">
                                    {errors.preferred_language}
                                </p>
                            )}
                        </div>
                        <div>
                            <Label htmlFor="contact_preferences">
                                Contact Preferences
                            </Label>
                            <Input
                                id="contact_preferences"
                                value={data.contact_preferences}
                                onChange={(e) =>
                                    setData(
                                        'contact_preferences',
                                        e.target.value,
                                    )
                                }
                            />
                            {errors.contact_preferences && (
                                <p className="text-sm text-red-500">
                                    {errors.contact_preferences}
                                </p>
                            )}
                        </div>
                    </div>

                    <div>
                        <Label htmlFor="allergies">Allergies</Label>
                        <Textarea
                            id="allergies"
                            value={data.allergies}
                            onChange={(e) =>
                                setData('allergies', e.target.value)
                            }
                            rows={3}
                        />
                        {errors.allergies && (
                            <p className="text-sm text-red-500">
                                {errors.allergies}
                            </p>
                        )}
                    </div>

                    <div>
                        <Label htmlFor="medical_history">Medical History</Label>
                        <Textarea
                            id="medical_history"
                            value={data.medical_history}
                            onChange={(e) =>
                                setData('medical_history', e.target.value)
                            }
                            rows={4}
                        />
                        {errors.medical_history && (
                            <p className="text-sm text-red-500">
                                {errors.medical_history}
                            </p>
                        )}
                    </div>

                    <div>
                        <Label htmlFor="family_medical_history">
                            Family Medical History
                        </Label>
                        <Textarea
                            id="family_medical_history"
                            value={data.family_medical_history}
                            onChange={(e) =>
                                setData(
                                    'family_medical_history',
                                    e.target.value,
                                )
                            }
                            rows={3}
                        />
                        {errors.family_medical_history && (
                            <p className="text-sm text-red-500">
                                {errors.family_medical_history}
                            </p>
                        )}
                    </div>

                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <Label htmlFor="social_history">
                                Social History
                            </Label>
                            <Textarea
                                id="social_history"
                                value={data.social_history}
                                onChange={(e) =>
                                    setData('social_history', e.target.value)
                                }
                                rows={3}
                            />
                            {errors.social_history && (
                                <p className="text-sm text-red-500">
                                    {errors.social_history}
                                </p>
                            )}
                        </div>
                        <div>
                            <Label htmlFor="lifestyle_factors">
                                Lifestyle Factors
                            </Label>
                            <Textarea
                                id="lifestyle_factors"
                                value={data.lifestyle_factors}
                                onChange={(e) =>
                                    setData('lifestyle_factors', e.target.value)
                                }
                                rows={3}
                            />
                            {errors.lifestyle_factors && (
                                <p className="text-sm text-red-500">
                                    {errors.lifestyle_factors}
                                </p>
                            )}
                        </div>
                    </div>

                    <div className="flex justify-end gap-4">
                        {onCancel && (
                            <Button
                                type="button"
                                variant="outline"
                                onClick={onCancel}
                                disabled={processing}
                            >
                                Cancel
                            </Button>
                        )}
                        <Button type="submit" disabled={processing}>
                            {processing
                                ? 'Processing...'
                                : patient
                                  ? 'Update Patient'
                                  : 'Create Patient'}
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    );
}
