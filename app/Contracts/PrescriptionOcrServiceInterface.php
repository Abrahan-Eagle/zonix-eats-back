<?php

namespace App\Contracts;

use App\Models\Prescription;

interface PrescriptionOcrServiceInterface
{
    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed> Structured draft fields (not persisted)
     */
    public function extractDraft(array $context): array;
}
