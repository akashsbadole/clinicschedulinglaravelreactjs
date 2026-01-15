// Data transformation utilities for EMR API integration

/**
 * Transform patient data from form to API format
 */
export function transformPatientToApi(data: any): any {
    return {
        first_name: data.first_name,
        last_name: data.last_name,
        email: data.email,
        phone: data.phone,
        date_of_birth: data.date_of_birth,
        gender: data.gender,
        blood_type: data.blood_type,
        address: data.address,
        emergency_contact_name: data.emergency_contact_name,
        emergency_contact_phone: data.emergency_contact_phone,
        allergies: data.allergies,
        medical_history: data.medical_history,
        insurance_provider: data.insurance_provider,
        insurance_policy_number: data.insurance_policy_number,
        marital_status: data.marital_status,
        occupation: data.occupation,
        national_id: data.national_id,
        preferred_language: data.preferred_language,
        contact_preferences: data.contact_preferences,
        family_medical_history: data.family_medical_history,
        social_history: data.social_history,
        lifestyle_factors: data.lifestyle_factors,
    };
}

/**
 * Transform patient data from API to form format
 */
export function transformPatientFromApi(data: any): any {
    return {
        first_name: data.first_name || '',
        last_name: data.last_name || '',
        email: data.email || '',
        phone: data.phone || '',
        date_of_birth: data.date_of_birth || '',
        gender: data.gender || '',
        blood_type: data.blood_type || '',
        address: data.address || '',
        emergency_contact_name: data.emergency_contact_name || '',
        emergency_contact_phone: data.emergency_contact_phone || '',
        allergies: data.allergies || '',
        medical_history: data.medical_history || '',
        insurance_provider: data.insurance_provider || '',
        insurance_policy_number: data.insurance_policy_number || '',
        marital_status: data.marital_status || '',
        occupation: data.occupation || '',
        national_id: data.national_id || '',
        preferred_language: data.preferred_language || '',
        contact_preferences: data.contact_preferences || '',
        family_medical_history: data.family_medical_history || '',
        social_history: data.social_history || '',
        lifestyle_factors: data.lifestyle_factors || '',
    };
}

/**
 * Transform encounter data from form to API format
 */
export function transformEncounterToApi(data: any): any {
    return {
        patient_id: data.patient_id,
        doctor_id: data.doctor_id,
        appointment_id: data.appointment_id || null,
        encounter_type: data.encounter_type,
        start_time: data.start_time,
        end_time: data.end_time || null,
        status: data.status,
        priority: data.priority,
        chief_complaint: data.chief_complaint,
        history_of_present_illness: data.history_of_present_illness,
    };
}

/**
 * Transform encounter data from API to form format
 */
export function transformEncounterFromApi(data: any): any {
    return {
        patient_id: data.patient_id || '',
        doctor_id: data.doctor_id || '',
        appointment_id: data.appointment_id || '',
        encounter_type: data.encounter_type || 'outpatient',
        start_time: data.start_time || '',
        end_time: data.end_time || '',
        status: data.status || 'active',
        priority: data.priority || 'normal',
        chief_complaint: data.chief_complaint || '',
        history_of_present_illness: data.history_of_present_illness || '',
    };
}

/**
 * Transform vital signs data from form to API format
 */
export function transformVitalSignsToApi(data: any): any {
    return {
        encounter_id: data.encounter_id,
        patient_id: data.patient_id,
        height: data.height ? parseFloat(data.height) : null,
        weight: data.weight ? parseFloat(data.weight) : null,
        temperature: data.temperature ? parseFloat(data.temperature) : null,
        systolic_bp: data.systolic_bp ? parseInt(data.systolic_bp) : null,
        diastolic_bp: data.diastolic_bp ? parseInt(data.diastolic_bp) : null,
        pulse_rate: data.pulse_rate ? parseInt(data.pulse_rate) : null,
        respiratory_rate: data.respiratory_rate
            ? parseInt(data.respiratory_rate)
            : null,
        oxygen_saturation: data.oxygen_saturation
            ? parseInt(data.oxygen_saturation)
            : null,
        notes: data.notes || null,
    };
}

/**
 * Transform vital signs data from API to form format
 */
export function transformVitalSignsFromApi(data: any): any {
    return {
        encounter_id: data.encounter_id || '',
        patient_id: data.patient_id || '',
        height: data.height ? data.height.toString() : '',
        weight: data.weight ? data.weight.toString() : '',
        temperature: data.temperature ? data.temperature.toString() : '',
        systolic_bp: data.systolic_bp ? data.systolic_bp.toString() : '',
        diastolic_bp: data.diastolic_bp ? data.diastolic_bp.toString() : '',
        pulse_rate: data.pulse_rate ? data.pulse_rate.toString() : '',
        respiratory_rate: data.respiratory_rate
            ? data.respiratory_rate.toString()
            : '',
        oxygen_saturation: data.oxygen_saturation
            ? data.oxygen_saturation.toString()
            : '',
        notes: data.notes || '',
    };
}

/**
 * Transform clinical note data from form to API format
 */
export function transformClinicalNoteToApi(data: any): any {
    return {
        encounter_id: data.encounter_id,
        patient_id: data.patient_id,
        doctor_id: data.doctor_id || null,
        note_type: data.note_type,
        subjective: data.subjective,
        objective: data.objective,
        assessment: data.assessment,
        plan: data.plan,
        clinical_impression: data.clinical_impression || null,
        is_confidential: data.is_confidential || false,
    };
}

/**
 * Transform clinical note data from API to form format
 */
export function transformClinicalNoteFromApi(data: any): any {
    return {
        encounter_id: data.encounter_id || '',
        patient_id: data.patient_id || '',
        doctor_id: data.doctor_id ? data.doctor_id.toString() : '',
        note_type: data.note_type || 'progress',
        subjective: data.subjective || '',
        objective: data.objective || '',
        assessment: data.assessment || '',
        plan: data.plan || '',
        clinical_impression: data.clinical_impression || '',
        is_confidential: data.is_confidential || false,
    };
}

/**
 * Transform diagnosis data from form to API format
 */
export function transformDiagnosisToApi(data: any): any {
    return {
        encounter_id: data.encounter_id,
        patient_id: data.patient_id,
        diagnosis_type: data.diagnosis_type,
        diagnosis_code: data.diagnosis_code || null,
        diagnosis_description: data.diagnosis_description,
        diagnosis_date: data.diagnosis_date,
        severity: data.severity,
        status: data.status,
        notes: data.notes || null,
    };
}

/**
 * Transform diagnosis data from API to form format
 */
export function transformDiagnosisFromApi(data: any): any {
    return {
        encounter_id: data.encounter_id || '',
        patient_id: data.patient_id || '',
        diagnosis_type: data.diagnosis_type || 'primary',
        diagnosis_code: data.diagnosis_code || '',
        diagnosis_description: data.diagnosis_description || '',
        diagnosis_date: data.diagnosis_date || '',
        severity: data.severity || 'mild',
        status: data.status || 'active',
        notes: data.notes || '',
    };
}

/**
 * Transform ICD-10 code data from form to API format
 */
export function transformIcd10CodeToApi(data: any): any {
    return {
        code: data.code,
        description: data.description,
        category: data.category || null,
        chapter: data.chapter || null,
        block: data.block || null,
        notes: data.notes || null,
    };
}

/**
 * Transform ICD-10 code data from API to form format
 */
export function transformIcd10CodeFromApi(data: any): any {
    return {
        code: data.code || '',
        description: data.description || '',
        category: data.category || '',
        chapter: data.chapter || '',
        block: data.block || '',
        notes: data.notes || '',
    };
}

/**
 * Transform lab order data from form to API format
 */
export function transformLabOrderToApi(data: any): any {
    return {
        encounter_id: data.encounter_id,
        patient_id: data.patient_id,
        test_type: data.test_type,
        test_name: data.test_name,
        test_code: data.test_code || null,
        priority: data.priority,
        status: data.status,
        specimen_type: data.specimen_type || null,
        notes: data.notes || null,
        results: data.results || null,
    };
}

/**
 * Transform lab order data from API to form format
 */
export function transformLabOrderFromApi(data: any): any {
    return {
        encounter_id: data.encounter_id || '',
        patient_id: data.patient_id || '',
        test_type: data.test_type || 'lab',
        test_name: data.test_name || '',
        test_code: data.test_code || '',
        priority: data.priority || 'routine',
        status: data.status || 'ordered',
        specimen_type: data.specimen_type || '',
        notes: data.notes || '',
        results: data.results || '',
    };
}

/**
 * Transform treatment plan data from form to API format
 */
export function transformTreatmentPlanToApi(data: any): any {
    return {
        encounter_id: data.encounter_id,
        patient_id: data.patient_id,
        plan_type: data.plan_type,
        diagnosis_id: data.diagnosis_id || null,
        start_date: data.start_date,
        end_date: data.end_date || null,
        status: data.status,
        notes: data.notes || null,
        medications: data.medications || [],
    };
}

/**
 * Transform treatment plan data from API to form format
 */
export function transformTreatmentPlanFromApi(data: any): any {
    return {
        encounter_id: data.encounter_id || '',
        patient_id: data.patient_id || '',
        plan_type: data.plan_type || 'medication',
        diagnosis_id: data.diagnosis_id || '',
        start_date: data.start_date || '',
        end_date: data.end_date || '',
        status: data.status || 'active',
        notes: data.notes || '',
        medications: data.medications || [
            {
                medication_name: '',
                dosage: '',
                frequency: '',
                route: '',
                duration: '',
                instructions: '',
            },
        ],
    };
}

/**
 * Transform API error to user-friendly message
 */
export function transformErrorMessage(error: any): string {
    if (typeof error === 'string') {
        return error;
    }

    if (error && error.message) {
        return error.message;
    }

    if (error && error.errors) {
        const errors = Object.values(error.errors);
        return errors.join(', ');
    }

    return 'An unknown error occurred';
}

/**
 * Transform API date to display format
 */
export function formatDateForDisplay(dateString: string): string {
    if (!dateString) return '';

    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
}

/**
 * Transform display date to API format
 */
export function formatDateForApi(dateString: string): string {
    if (!dateString) return '';

    const date = new Date(dateString);
    return date.toISOString().split('T')[0];
}

export default {
    transformPatientToApi,
    transformPatientFromApi,
    transformEncounterToApi,
    transformEncounterFromApi,
    transformVitalSignsToApi,
    transformVitalSignsFromApi,
    transformClinicalNoteToApi,
    transformClinicalNoteFromApi,
    transformDiagnosisToApi,
    transformDiagnosisFromApi,
    transformIcd10CodeToApi,
    transformIcd10CodeFromApi,
    transformLabOrderToApi,
    transformLabOrderFromApi,
    transformTreatmentPlanToApi,
    transformTreatmentPlanFromApi,
    transformErrorMessage,
    formatDateForDisplay,
    formatDateForApi,
};
