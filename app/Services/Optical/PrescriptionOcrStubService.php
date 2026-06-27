<?php

namespace App\Services\Optical;

use App\Contracts\PrescriptionOcrServiceInterface;

class PrescriptionOcrStubService implements PrescriptionOcrServiceInterface
{
    /**
     * MVP stub: returns placeholder dioptrías when OCR enabled.
     * Production: replace with Gemini/OpenAI provider.
     */
    public function extractDraft(array $context): array
    {
        return [
            'od_sphere' => $context['od_sphere'] ?? -1.00,
            'od_cylinder' => $context['od_cylinder'] ?? 0.00,
            'od_axis' => $context['od_axis'] ?? 180,
            'oi_sphere' => $context['oi_sphere'] ?? -1.00,
            'oi_cylinder' => $context['oi_cylinder'] ?? 0.00,
            'oi_axis' => $context['oi_axis'] ?? 180,
            'addition' => $context['addition'] ?? null,
            'pd' => $context['pd'] ?? null,
            'pd_near' => $context['pd_near'] ?? null,
            'confidence' => 0.5,
            'requires_human_review' => true,
        ];
    }
}
