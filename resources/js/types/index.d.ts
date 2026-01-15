import { InertiaLinkProps } from '@inertiajs/react';
import { LucideIcon } from 'lucide-react';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavGroup {
    title: string;
    items: NavItem[];
}

export interface NavItem {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon | null;
    isActive?: boolean;
}

export interface SharedData {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    sidebarOpen: boolean;
    [key: string]: unknown;
}

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    two_factor_enabled?: boolean;
    created_at: string;
    updated_at: string;
    [key: string]: unknown; // This allows for additional properties...
}

export interface Patient {
    id: number;
    patient_id: string;
    user_id: number;
    clinic_id: number;
    first_name: string;
    last_name: string;
    name: string;
    email: string;
    phone: string;
    date_of_birth: string;
    gender: string;
    age?: number;
    blood_type?: string;
    address?: string;
    emergency_contact_name?: string;
    emergency_contact_phone?: string;
    allergies?: string;
    medical_history?: string;
    insurance_provider?: string;
    insurance_policy_number?: string;
    marital_status?: string;
    occupation?: string;
    national_id?: string;
    preferred_language?: string;
    contact_preferences?: string;
    family_medical_history?: string;
    social_history?: string;
    lifestyle_factors?: string;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
}

export interface Doctor {
    id: number;
    user_id: number;
    clinic_id: number;
    name: string;
    email: string;
    phone: string;
    specialization?: string;
    license_number?: string;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
}

export interface Appointment {
    id: number;
    appointment_number: string;
    patient_id: number;
    doctor_id: number;
    clinic_id: number;
    appointment_date: string;
    start_time: string;
    end_time: string;
    status: string;
    reason?: string;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
}

export interface Encounter {
    id: number;
    encounter_number: string;
    patient_id: number;
    doctor_id: number;
    clinic_id: number;
    appointment_id?: number;
    encounter_type: string;
    start_time: string;
    end_time?: string;
    status: string;
    priority: string;
    chief_complaint: string;
    history_of_present_illness: string;
    created_by: number;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
}
