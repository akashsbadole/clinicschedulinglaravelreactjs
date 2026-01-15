<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Diagnosis;
use App\Models\MedicationOrder;
use App\Models\LabTest;
use App\Models\VitalSign;
use App\Models\ClinicalNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ClinicalDecisionSupportController extends Controller
{
    /**
     * Get drug interaction alerts for a patient
     */
    public function getDrugInteractionAlerts($patientId)
    {
        $patient = Patient::find($patientId);
        
        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'Patient not found'
            ], 404);
        }
        
        $user = Auth::user();
        
        // Check if user has access to this patient
        if (!$user->isAdmin() && $patient->clinic_id != $user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to access this patient'
            ], 403);
        }
        
        try {
            // Get all active medication orders for the patient
            $medicationOrders = MedicationOrder::with(['medication'])
                ->where('patient_id', $patientId)
                ->where('status', 'active')
                ->get();
            
            $alerts = [];
            
            // Check for potential drug interactions
            // This is a simplified example - in a real system, you would integrate with a drug interaction API
            foreach ($medicationOrders as $order) {
                $medication = $order->medication;
                
                if ($medication) {
                    // Check for known interactions with other medications
                    $interactions = $this->checkDrugInteractions($medication, $medicationOrders);
                    
                    if (!empty($interactions)) {
                        $alerts[] = [
                            'type' => 'drug_interaction',
                            'severity' => 'high',
                            'medication' => $medication->generic_name,
                            'interactions' => $interactions,
                            'message' => 'Potential drug interaction detected',
                        ];
                    }
                    
                    // Check for allergies
                    if ($patient->allergies && $medication->generic_name && 
                        stripos($patient->allergies, $medication->generic_name) !== false) {
                        $alerts[] = [
                            'type' => 'allergy',
                            'severity' => 'critical',
                            'medication' => $medication->generic_name,
                            'message' => 'Patient may be allergic to this medication',
                        ];
                    }
                    
                    // Check for contraindications based on patient's conditions
                    if ($medication->contraindications && $patient->medical_history) {
                        $conditions = explode(',', $patient->medical_history);
                        foreach ($conditions as $condition) {
                            if (stripos($medication->contraindications, trim($condition)) !== false) {
                                $alerts[] = [
                                    'type' => 'contraindication',
                                    'severity' => 'high',
                                    'medication' => $medication->generic_name,
                                    'condition' => trim($condition),
                                    'message' => 'Medication is contraindicated for patient condition',
                                ];
                            }
                        }
                    }
                }
            }
            
            return response()->json([
                'success' => true,
                'data' => [
                    'patient_id' => $patientId,
                    'alerts' => $alerts,
                    'alert_count' => count($alerts),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get drug interaction alerts',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check for drug interactions (simplified example)
     */
    private function checkDrugInteractions($medication, $allOrders)
    {
        $interactions = [];
        
        // Known interactions (this would be replaced with a proper drug interaction database)
        $knownInteractions = [
            'WARFARIN' => ['ASPIRIN', 'IBUPROFEN', 'NAPROXEN'],
            'ASPIRIN' => ['WARFARIN', 'IBUPROFEN'],
            'LISINOPRIL' => ['POTASSIUM', 'SPIRONOLACTONE'],
            'SIMVASTATIN' => ['GEMFIBROZIL', 'ERYTHROMYCIN'],
        ];
        
        $medicationName = strtoupper($medication->generic_name);
        
        if (isset($knownInteractions[$medicationName])) {
            foreach ($allOrders as $order) {
                if ($order->medication && $order->id != $medication->id) {
                    $otherMedName = strtoupper($order->medication->generic_name);
                    if (in_array($otherMedName, $knownInteractions[$medicationName])) {
                        $interactions[] = [
                            'medication' => $order->medication->generic_name,
                            'severity' => 'high',
                            'description' => 'Potential interaction between ' . $medication->generic_name . ' and ' . $order->medication->generic_name,
                        ];
                    }
                }
            }
        }
        
        return $interactions;
    }

    /**
     * Get clinical guidelines for a diagnosis
     */
    public function getClinicalGuidelines(Request $request)
    {
        $request->validate([
            'diagnosis_id' => 'nullable|exists:diagnoses,id',
            'icd10_code' => 'nullable|string|max:20',
            'condition' => 'nullable|string|max:100',
        ]);
        
        try {
            // This would integrate with a clinical guidelines API or database
            // For now, we'll return some sample guidelines
            $guidelines = [];
            
            if ($request->diagnosis_id) {
                $diagnosis = Diagnosis::find($request->diagnosis_id);
                if ($diagnosis) {
                    $guidelines = $this->getSampleGuidelines($diagnosis->clinical_description);
                }
            } elseif ($request->icd10_code) {
                $guidelines = $this->getSampleGuidelines($request->icd10_code);
            } elseif ($request->condition) {
                $guidelines = $this->getSampleGuidelines($request->condition);
            }
            
            return response()->json([
                'success' => true,
                'data' => $guidelines
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get clinical guidelines',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get sample clinical guidelines (placeholder)
     */
    private function getSampleGuidelines($condition)
    {
        $condition = strtoupper($condition);
        
        $sampleGuidelines = [
            'HYPERTENSION' => [
                [
                    'title' => 'Hypertension Management Guidelines',
                    'source' => 'American Heart Association',
                    'recommendations' => [
                        'Lifestyle modifications: low-sodium diet, regular exercise, weight management',
                        'First-line medications: Thiazide diuretics, ACE inhibitors, ARBs, or CCBs',
                        'Target BP: <130/80 mmHg for most patients',
                        'Monitor BP regularly and adjust treatment as needed',
                    ],
                    'severity' => 'high',
                ],
            ],
            'DIABETES' => [
                [
                    'title' => 'Type 2 Diabetes Management',
                    'source' => 'American Diabetes Association',
                    'recommendations' => [
                        'HbA1c target: <7% for most patients',
                        'First-line therapy: Metformin',
                        'Lifestyle interventions: medical nutrition therapy, physical activity',
                        'Monitor for complications: retinopathy, nephropathy, neuropathy',
                    ],
                    'severity' => 'high',
                ],
            ],
            'ASTHMA' => [
                [
                    'title' => 'Asthma Management Guidelines',
                    'source' => 'Global Initiative for Asthma (GINA)',
                    'recommendations' => [
                        'Stepwise approach based on severity',
                        'Inhaled corticosteroids as controller therapy',
                        'Short-acting beta agonists for rescue',
                        'Avoid triggers and monitor peak flow',
                    ],
                    'severity' => 'medium',
                ],
            ],
        ];
        
        return $sampleGuidelines[$condition] ?? [
            [
                'title' => 'General Clinical Guidelines',
                'source' => 'Clinical Decision Support System',
                'recommendations' => [
                    'Follow evidence-based practice guidelines',
                    'Consider patient-specific factors',
                    'Monitor treatment response and adjust as needed',
                    'Consult specialists when necessary',
                ],
                'severity' => 'low',
            ],
        ];
    }

    /**
     * Get patient risk assessment
     */
    public function getPatientRiskAssessment($patientId)
    {
        $patient = Patient::find($patientId);
        
        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'Patient not found'
            ], 404);
        }
        
        $user = Auth::user();
        
        // Check if user has access to this patient
        if (!$user->isAdmin() && $patient->clinic_id != $user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to access this patient'
            ], 403);
        }
        
        try {
            // Calculate risk scores based on patient data
            $riskAssessment = [
                'cardiovascular_risk' => $this->calculateCardiovascularRisk($patient),
                'diabetes_risk' => $this->calculateDiabetesRisk($patient),
                'fall_risk' => $this->calculateFallRisk($patient),
                'medication_risk' => $this->calculateMedicationRisk($patient),
            ];
            
            return response()->json([
                'success' => true,
                'data' => [
                    'patient_id' => $patientId,
                    'risk_assessment' => $riskAssessment,
                    'overall_risk_level' => $this->determineOverallRiskLevel($riskAssessment),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate risk assessment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate cardiovascular risk (simplified)
     */
    private function calculateCardiovascularRisk($patient)
    {
        $riskScore = 0;
        $factors = [];
        
        // Age
        if ($patient->date_of_birth) {
            $age = $patient->date_of_birth->age;
            if ($age >= 45) $riskScore += 1;
            if ($age >= 65) $riskScore += 2;
            $factors[] = 'Age: ' . $age;
        }
        
        // Gender
        if ($patient->gender === 'male') $riskScore += 1;
        
        // Blood pressure
        $latestVitals = $patient->vitalSigns()->orderBy('created_at', 'desc')->first();
        if ($latestVitals) {
            if ($latestVitals->systolic_bp >= 140 || $latestVitals->diastolic_bp >= 90) {
                $riskScore += 2;
                $factors[] = 'Hypertension detected';
            }
        }
        
        // Medical history
        if ($patient->medical_history) {
            $history = strtoupper($patient->medical_history);
            if (strpos($history, 'HYPERTENSION') !== false) $riskScore += 1;
            if (strpos($history, 'DIABETES') !== false) $riskScore += 2;
            if (strpos($history, 'SMOK') !== false) $riskScore += 2;
        }
        
        // Determine risk level
        $riskLevel = 'low';
        if ($riskScore >= 3) $riskLevel = 'moderate';
        if ($riskScore >= 5) $riskLevel = 'high';
        
        return [
            'risk_score' => $riskScore,
            'risk_level' => $riskLevel,
            'factors' => $factors,
            'recommendations' => $this->getCardiovascularRecommendations($riskLevel),
        ];
    }

    /**
     * Get cardiovascular recommendations based on risk level
     */
    private function getCardiovascularRecommendations($riskLevel)
    {
        $recommendations = [
            'Monitor blood pressure regularly',
            'Encourage healthy diet and exercise',
        ];
        
        if ($riskLevel === 'moderate' || $riskLevel === 'high') {
            $recommendations[] = 'Consider lipid profile testing';
            $recommendations[] = 'Evaluate for hypertension management';
        }
        
        if ($riskLevel === 'high') {
            $recommendations[] = 'Consider cardiovascular risk reduction strategies';
            $recommendations[] = 'Refer to cardiology if needed';
        }
        
        return $recommendations;
    }

    /**
     * Calculate diabetes risk (simplified)
     */
    private function calculateDiabetesRisk($patient)
    {
        $riskScore = 0;
        $factors = [];
        
        // Age
        if ($patient->date_of_birth) {
            $age = $patient->date_of_birth->age;
            if ($age >= 45) $riskScore += 2;
            if ($age >= 65) $riskScore += 3;
        }
        
        // BMI
        $latestVitals = $patient->vitalSigns()->orderBy('created_at', 'desc')->first();
        if ($latestVitals && $latestVitals->bmi) {
            if ($latestVitals->bmi >= 25) $riskScore += 1;
            if ($latestVitals->bmi >= 30) $riskScore += 2;
            $factors[] = 'BMI: ' . $latestVitals->bmi;
        }
        
        // Family history
        if ($patient->family_medical_history && 
            stripos(strtoupper($patient->family_medical_history), 'DIABETES') !== false) {
            $riskScore += 2;
            $factors[] = 'Family history of diabetes';
        }
        
        // Determine risk level
        $riskLevel = 'low';
        if ($riskScore >= 3) $riskLevel = 'moderate';
        if ($riskScore >= 5) $riskLevel = 'high';
        
        return [
            'risk_score' => $riskScore,
            'risk_level' => $riskLevel,
            'factors' => $factors,
            'recommendations' => $this->getDiabetesRecommendations($riskLevel),
        ];
    }

    /**
     * Get diabetes recommendations based on risk level
     */
    private function getDiabetesRecommendations($riskLevel)
    {
        $recommendations = [
            'Encourage healthy lifestyle',
            'Monitor blood glucose if symptoms present',
        ];
        
        if ($riskLevel === 'moderate' || $riskLevel === 'high') {
            $recommendations[] = 'Consider HbA1c testing';
            $recommendations[] = 'Screen for prediabetes';
        }
        
        if ($riskLevel === 'high') {
            $recommendations[] = 'Consider diabetes prevention program';
            $recommendations[] = 'Monitor for complications';
        }
        
        return $recommendations;
    }

    /**
     * Calculate fall risk (simplified)
     */
    private function calculateFallRisk($patient)
    {
        $riskScore = 0;
        $factors = [];
        
        // Age
        if ($patient->date_of_birth) {
            $age = $patient->date_of_birth->age;
            if ($age >= 65) $riskScore += 1;
            if ($age >= 75) $riskScore += 2;
        }
        
        // Medications
        $medicationOrders = MedicationOrder::where('patient_id', $patient->id)
            ->where('status', 'active')
            ->get();
        
        foreach ($medicationOrders as $order) {
            if ($order->medication) {
                $medName = strtoupper($order->medication->generic_name);
                if (strpos($medName, 'BENZODIAZEPINE') !== false || 
                    strpos($medName, 'ANTIPSYCHOTIC') !== false ||
                    strpos($medName, 'ANTIDEPRESSANT') !== false) {
                    $riskScore += 1;
                    $factors[] = 'High-risk medication: ' . $order->medication->generic_name;
                }
            }
        }
        
        // Determine risk level
        $riskLevel = 'low';
        if ($riskScore >= 2) $riskLevel = 'moderate';
        if ($riskScore >= 4) $riskLevel = 'high';
        
        return [
            'risk_score' => $riskScore,
            'risk_level' => $riskLevel,
            'factors' => $factors,
            'recommendations' => $this->getFallRiskRecommendations($riskLevel),
        ];
    }

    /**
     * Get fall risk recommendations based on risk level
     */
    private function getFallRiskRecommendations($riskLevel)
    {
        $recommendations = [
            'Assess home environment for fall hazards',
            'Encourage regular exercise for balance and strength',
        ];
        
        if ($riskLevel === 'moderate' || $riskLevel === 'high') {
            $recommendations[] = 'Consider physical therapy evaluation';
            $recommendations[] = 'Review medications for fall risk';
        }
        
        if ($riskLevel === 'high') {
            $recommendations[] = 'Consider assistive devices if needed';
            $recommendations[] = 'Implement fall prevention strategies';
        }
        
        return $recommendations;
    }

    /**
     * Calculate medication risk (simplified)
     */
    private function calculateMedicationRisk($patient)
    {
        $riskScore = 0;
        $factors = [];
        
        $medicationOrders = MedicationOrder::where('patient_id', $patient->id)
            ->where('status', 'active')
            ->get();
        
        $medicationCount = $medicationOrders->count();
        
        // Polypharmacy
        if ($medicationCount >= 5) $riskScore += 1;
        if ($medicationCount >= 10) $riskScore += 2;
        $factors[] = 'Active medications: ' . $medicationCount;
        
        // High-risk medications
        $highRiskMeds = 0;
        foreach ($medicationOrders as $order) {
            if ($order->medication) {
                $medName = strtoupper($order->medication->generic_name);
                if (strpos($medName, 'WARFARIN') !== false ||
                    strpos($medName, 'DIGOXIN') !== false ||
                    strpos($medName, 'INSULIN') !== false ||
                    strpos($medName, 'OPIOID') !== false) {
                    $highRiskMeds++;
                    $riskScore += 1;
                }
            }
        }
        
        if ($highRiskMeds > 0) {
            $factors[] = 'High-risk medications: ' . $highRiskMeds;
        }
        
        // Determine risk level
        $riskLevel = 'low';
        if ($riskScore >= 2) $riskLevel = 'moderate';
        if ($riskScore >= 4) $riskLevel = 'high';
        
        return [
            'risk_score' => $riskScore,
            'risk_level' => $riskLevel,
            'factors' => $factors,
            'recommendations' => $this->getMedicationRiskRecommendations($riskLevel),
        ];
    }

    /**
     * Get medication risk recommendations based on risk level
     */
    private function getMedicationRiskRecommendations($riskLevel)
    {
        $recommendations = [
            'Review medication list regularly',
            'Monitor for adverse effects',
        ];
        
        if ($riskLevel === 'moderate' || $riskLevel === 'high') {
            $recommendations[] = 'Consider medication reconciliation';
            $recommendations[] = 'Evaluate for potential deprescribing';
        }
        
        if ($riskLevel === 'high') {
            $recommendations[] = 'Consult clinical pharmacist for review';
            $recommendations[] = 'Monitor renal and hepatic function';
        }
        
        return $recommendations;
    }

    /**
     * Determine overall risk level
     */
    private function determineOverallRiskLevel($riskAssessment)
    {
        $highRisks = 0;
        $moderateRisks = 0;
        
        foreach ($riskAssessment as $risk) {
            if ($risk['risk_level'] === 'high') $highRisks++;
            if ($risk['risk_level'] === 'moderate') $moderateRisks++;
        }
        
        if ($highRisks >= 2) return 'high';
        if ($highRisks >= 1 || $moderateRisks >= 2) return 'moderate';
        if ($moderateRisks >= 1) return 'low-moderate';
        
        return 'low';
    }

    /**
     * Get clinical reminders for a patient
     */
    public function getClinicalReminders($patientId)
    {
        $patient = Patient::find($patientId);
        
        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'Patient not found'
            ], 404);
        }
        
        $user = Auth::user();
        
        // Check if user has access to this patient
        if (!$user->isAdmin() && $patient->clinic_id != $user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to access this patient'
            ], 403);
        }
        
        try {
            $reminders = [];
            
            // Preventive care reminders
            $preventiveReminders = $this->getPreventiveCareReminders($patient);
            if (!empty($preventiveReminders)) {
                $reminders = array_merge($reminders, $preventiveReminders);
            }
            
            // Chronic disease management reminders
            $chronicReminders = $this->getChronicDiseaseReminders($patient);
            if (!empty($chronicReminders)) {
                $reminders = array_merge($reminders, $chronicReminders);
            }
            
            // Medication monitoring reminders
            $medicationReminders = $this->getMedicationMonitoringReminders($patient);
            if (!empty($medicationReminders)) {
                $reminders = array_merge($reminders, $medicationReminders);
            }
            
            return response()->json([
                'success' => true,
                'data' => [
                    'patient_id' => $patientId,
                    'reminders' => $reminders,
                    'reminder_count' => count($reminders),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get clinical reminders',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get preventive care reminders
     */
    private function getPreventiveCareReminders($patient)
    {
        $reminders = [];
        
        if ($patient->date_of_birth) {
            $age = $patient->date_of_birth->age;
            $gender = $patient->gender;
            
            // Age-based screening reminders
            if ($age >= 18) {
                $reminders[] = [
                    'type' => 'screening',
                    'category' => 'preventive',
                    'description' => 'Annual wellness visit',
                    'frequency' => 'annual',
                    'priority' => 'medium',
                ];
            }
            
            if ($age >= 50) {
                $reminders[] = [
                    'type' => 'screening',
                    'category' => 'preventive',
                    'description' => 'Colorectal cancer screening',
                    'frequency' => 'as recommended',
                    'priority' => 'high',
                ];
            }
            
            if ($age >= 40) {
                $reminders[] = [
                    'type' => 'screening',
                    'category' => 'preventive',
                    'description' => 'Mammogram (for women)',
                    'frequency' => 'annual',
                    'priority' => 'high',
                    'gender_specific' => 'female',
                ];
            }
            
            if ($age >= 65) {
                $reminders[] = [
                    'type' => 'screening',
                    'category' => 'preventive',
                    'description' => 'Bone density scan',
                    'frequency' => 'as recommended',
                    'priority' => 'medium',
                ];
            }
            
            // Immunization reminders
            if ($age >= 50) {
                $reminders[] = [
                    'type' => 'immunization',
                    'category' => 'preventive',
                    'description' => 'Shingles vaccine',
                    'frequency' => 'one-time',
                    'priority' => 'high',
                ];
            }
            
            if ($age >= 65) {
                $reminders[] = [
                    'type' => 'immunization',
                    'category' => 'preventive',
                    'description' => 'Pneumococcal vaccine',
                    'frequency' => 'as recommended',
                    'priority' => 'high',
                ];
            }
        }
        
        return $reminders;
    }

    /**
     * Get chronic disease management reminders
     */
    private function getChronicDiseaseReminders($patient)
    {
        $reminders = [];
        
        if ($patient->medical_history) {
            $history = strtoupper($patient->medical_history);
            
            if (strpos($history, 'HYPERTENSION') !== false) {
                $reminders[] = [
                    'type' => 'monitoring',
                    'category' => 'chronic',
                    'description' => 'Blood pressure check',
                    'frequency' => 'every 3-6 months',
                    'priority' => 'high',
                ];
            }
            
            if (strpos($history, 'DIABETES') !== false) {
                $reminders[] = [
                    'type' => 'monitoring',
                    'category' => 'chronic',
                    'description' => 'HbA1c test',
                    'frequency' => 'every 3-6 months',
                    'priority' => 'high',
                ];
                
                $reminders[] = [
                    'type' => 'monitoring',
                    'category' => 'chronic',
                    'description' => 'Foot exam',
                    'frequency' => 'annual',
                    'priority' => 'medium',
                ];
            }
            
            if (strpos($history, 'ASTHMA') !== false) {
                $reminders[] = [
                    'type' => 'monitoring',
                    'category' => 'chronic',
                    'description' => 'Pulmonary function test',
                    'frequency' => 'annual',
                    'priority' => 'medium',
                ];
            }
        }
        
        return $reminders;
    }

    /**
     * Get medication monitoring reminders
     */
    private function getMedicationMonitoringReminders($patient)
    {
        $reminders = [];
        
        $medicationOrders = MedicationOrder::where('patient_id', $patient->id)
            ->where('status', 'active')
            ->get();
        
        foreach ($medicationOrders as $order) {
            if ($order->medication) {
                $medName = strtoupper($order->medication->generic_name);
                
                if (strpos($medName, 'WARFARIN') !== false) {
                    $reminders[] = [
                        'type' => 'monitoring',
                        'category' => 'medication',
                        'description' => 'INR monitoring for Warfarin',
                        'frequency' => 'as recommended',
                        'priority' => 'high',
                        'medication' => $order->medication->generic_name,
                    ];
                }
                
                if (strpos($medName, 'LITHIUM') !== false) {
                    $reminders[] = [
                        'type' => 'monitoring',
                        'category' => 'medication',
                        'description' => 'Lithium level monitoring',
                        'frequency' => 'as recommended',
                        'priority' => 'high',
                        'medication' => $order->medication->generic_name,
                    ];
                }
                
                if (strpos($medName, 'DIGOXIN') !== false) {
                    $reminders[] = [
                        'type' => 'monitoring',
                        'category' => 'medication',
                        'description' => 'Digoxin level monitoring',
                        'frequency' => 'as recommended',
                        'priority' => 'high',
                        'medication' => $order->medication->generic_name,
                    ];
                }
            }
        }
        
        return $reminders;
    }

    /**
     * Get clinical decision support summary for a patient
     */
    public function getDecisionSupportSummary($patientId)
    {
        $patient = Patient::find($patientId);
        
        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'Patient not found'
            ], 404);
        }
        
        $user = Auth::user();
        
        // Check if user has access to this patient
        if (!$user->isAdmin() && $patient->clinic_id != $user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to access this patient'
            ], 403);
        }
        
        try {
            // Get all decision support data
            $drugAlerts = $this->getDrugInteractionAlerts($patientId);
            $riskAssessment = $this->getPatientRiskAssessment($patientId);
            $clinicalReminders = $this->getClinicalReminders($patientId);
            
            $summary = [
                'patient_id' => $patientId,
                'drug_interaction_alerts' => $drugAlerts['data']['alerts'] ?? [],
                'drug_alert_count' => $drugAlerts['data']['alert_count'] ?? 0,
                'risk_assessment' => $riskAssessment['data']['risk_assessment'] ?? [],
                'overall_risk_level' => $riskAssessment['data']['overall_risk_level'] ?? 'unknown',
                'clinical_reminders' => $clinicalReminders['data']['reminders'] ?? [],
                'reminder_count' => $clinicalReminders['data']['reminder_count'] ?? 0,
            ];
            
            // Determine overall clinical decision support level
            $summary['cds_level'] = $this->determineCdsLevel($summary);
            
            return response()->json([
                'success' => true,
                'data' => $summary
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get decision support summary',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Determine overall CDS level
     */
    private function determineCdsLevel($summary)
    {
        $highPriorityItems = $summary['drug_alert_count'] + 
                            ($summary['overall_risk_level'] === 'high' ? 1 : 0) + 
                            count(array_filter($summary['clinical_reminders'], function($reminder) {
                                return $reminder['priority'] === 'high';
                            }));
        
        if ($highPriorityItems >= 3) return 'critical';
        if ($highPriorityItems >= 2) return 'high';
        if ($highPriorityItems >= 1) return 'moderate';
        
        return 'low';
    }
}