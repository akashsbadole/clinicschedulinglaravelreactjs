import { useState } from 'react';
import apiService from '../lib/api';

export interface ApiState<T> {
    data: T | null;
    loading: boolean;
    error: string | null;
    success: boolean;
}

export interface ApiResponse<T> {
    data: T | null;
    error: string | null;
}

export function useApi<T = any>() {
    const [state, setState] = useState<ApiState<T>>({
        data: null,
        loading: false,
        error: null,
        success: false,
    });

    const resetState = () => {
        setState({
            data: null,
            loading: false,
            error: null,
            success: false,
        });
    };

    const execute = async (
        apiCall: () => Promise<T>,
        onSuccess?: (data: T) => void,
        onError?: (error: string) => void,
    ): Promise<ApiResponse<T>> => {
        try {
            setState((prev) => ({
                ...prev,
                loading: true,
                error: null,
                success: false,
            }));

            const data = await apiCall();

            setState((prev) => ({
                ...prev,
                data,
                loading: false,
                error: null,
                success: true,
            }));

            if (onSuccess) {
                onSuccess(data);
            }

            return { data, error: null };
        } catch (error) {
            const errorMessage =
                error instanceof Error
                    ? error.message
                    : 'Unknown error occurred';

            setState((prev) => ({
                ...prev,
                data: null,
                loading: false,
                error: errorMessage,
                success: false,
            }));

            if (onError) {
                onError(errorMessage);
            }

            return { data: null, error: errorMessage };
        }
    };

    // EMR API Methods

    // Encounter methods
    const getEncounters = async (params?: any) => {
        return execute(() => apiService.getEncounters(params));
    };

    const getEncounter = async (id: number) => {
        return execute(() => apiService.getEncounter(id));
    };

    const createEncounter = async (data: any) => {
        return execute(() => apiService.createEncounter(data));
    };

    const updateEncounter = async (id: number, data: any) => {
        return execute(() => apiService.updateEncounter(id, data));
    };

    const completeEncounter = async (id: number) => {
        return execute(() => apiService.completeEncounter(id));
    };

    // Vital Signs methods
    const getVitalSigns = async (params?: any) => {
        return execute(() => apiService.getVitalSigns(params));
    };

    const getVitalSign = async (id: number) => {
        return execute(() => apiService.getVitalSign(id));
    };

    const createVitalSign = async (data: any) => {
        return execute(() => apiService.createVitalSign(data));
    };

    const updateVitalSign = async (id: number, data: any) => {
        return execute(() => apiService.updateVitalSign(id, data));
    };

    const getEncounterVitalSigns = async (encounterId: number) => {
        return execute(() => apiService.getEncounterVitalSigns(encounterId));
    };

    // Clinical Notes methods
    const getClinicalNotes = async (params?: any) => {
        return execute(() => apiService.getClinicalNotes(params));
    };

    const getClinicalNote = async (id: number) => {
        return execute(() => apiService.getClinicalNote(id));
    };

    const createClinicalNote = async (data: any) => {
        return execute(() => apiService.createClinicalNote(data));
    };

    const updateClinicalNote = async (id: number, data: any) => {
        return execute(() => apiService.updateClinicalNote(id, data));
    };

    const getEncounterClinicalNotes = async (encounterId: number) => {
        return execute(() => apiService.getEncounterClinicalNotes(encounterId));
    };

    // Diagnosis methods
    const getDiagnoses = async (params?: any) => {
        return execute(() => apiService.getDiagnoses(params));
    };

    const getDiagnosis = async (id: number) => {
        return execute(() => apiService.getDiagnosis(id));
    };

    const createDiagnosis = async (data: any) => {
        return execute(() => apiService.createDiagnosis(data));
    };

    const updateDiagnosis = async (id: number, data: any) => {
        return execute(() => apiService.updateDiagnosis(id, data));
    };

    const getEncounterDiagnoses = async (encounterId: number) => {
        return execute(() => apiService.getEncounterDiagnoses(encounterId));
    };

    // ICD-10 Codes methods
    const getIcd10Codes = async (params?: any) => {
        return execute(() => apiService.getIcd10Codes(params));
    };

    const getIcd10Code = async (id: number) => {
        return execute(() => apiService.getIcd10Code(id));
    };

    const searchIcd10Codes = async (query: string) => {
        return execute(() => apiService.searchIcd10Codes(query));
    };

    // Lab Orders methods
    const getLabOrders = async (params?: any) => {
        return execute(() => apiService.getLabOrders(params));
    };

    const getLabOrder = async (id: number) => {
        return execute(() => apiService.getLabOrder(id));
    };

    const createLabOrder = async (data: any) => {
        return execute(() => apiService.createLabOrder(data));
    };

    const updateLabOrder = async (id: number, data: any) => {
        return execute(() => apiService.updateLabOrder(id, data));
    };

    const getEncounterLabOrders = async (encounterId: number) => {
        return execute(() => apiService.getEncounterLabOrders(encounterId));
    };

    // Treatment Plans methods
    const getTreatmentPlans = async (params?: any) => {
        return execute(() => apiService.getTreatmentPlans(params));
    };

    const getTreatmentPlan = async (id: number) => {
        return execute(() => apiService.getTreatmentPlan(id));
    };

    const createTreatmentPlan = async (data: any) => {
        return execute(() => apiService.createTreatmentPlan(data));
    };

    const updateTreatmentPlan = async (id: number, data: any) => {
        return execute(() => apiService.updateTreatmentPlan(id, data));
    };

    const getEncounterTreatmentPlans = async (encounterId: number) => {
        return execute(() =>
            apiService.getEncounterTreatmentPlans(encounterId),
        );
    };

    return {
        ...state,
        resetState,
        execute,
        // Encounter methods
        getEncounters,
        getEncounter,
        createEncounter,
        updateEncounter,
        completeEncounter,
        // Vital Signs methods
        getVitalSigns,
        getVitalSign,
        createVitalSign,
        updateVitalSign,
        getEncounterVitalSigns,
        // Clinical Notes methods
        getClinicalNotes,
        getClinicalNote,
        createClinicalNote,
        updateClinicalNote,
        getEncounterClinicalNotes,
        // Diagnosis methods
        getDiagnoses,
        getDiagnosis,
        createDiagnosis,
        updateDiagnosis,
        getEncounterDiagnoses,
        // ICD-10 Codes methods
        getIcd10Codes,
        getIcd10Code,
        searchIcd10Codes,
        // Lab Orders methods
        getLabOrders,
        getLabOrder,
        createLabOrder,
        updateLabOrder,
        getEncounterLabOrders,
        // Treatment Plans methods
        getTreatmentPlans,
        getTreatmentPlan,
        createTreatmentPlan,
        updateTreatmentPlan,
        getEncounterTreatmentPlans,
    };
}

export default useApi;
