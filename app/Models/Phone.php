<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Phone extends Model
{
    use HasFactory;

    /** Contextos de uso del teléfono (módulo único por rol/entidad). */
    public const CONTEXT_PERSONAL = 'personal';
    public const CONTEXT_COMMERCE = 'commerce';
    public const CONTEXT_DELIVERY_COMPANY = 'delivery_company';
    public const CONTEXT_ADMIN = 'admin';

    /**
     * Los atributos que se pueden asignar masivamente.
     */
    protected $fillable = [
        'profile_id',
        'context',
        'commerce_id',
        'delivery_company_id',
        'operator_code_id',
        'number',
        'is_primary',
        'status',
    ];

    /**
     * Definición de las relaciones.
     */

    // Relación con Profile: Un teléfono pertenece a un perfil.
    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    public function operatorCode()
    {
        return $this->belongsTo(OperatorCode::class, 'operator_code_id');
    }

    public function commerce()
    {
        return $this->belongsTo(Commerce::class);
    }

    public function deliveryCompany()
    {
        return $this->belongsTo(DeliveryCompany::class);
    }

    /**
     * Número completo: código de operador + número (ej. 04121234567).
     */
    public function getFullNumberAttribute(): string
    {
        $code = $this->operatorCode?->code ?? '';
        return $code . $this->number;
    }




    /**
     * Scope para obtener solo teléfonos principales.
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * Asegura que solo un teléfono sea principal por perfil.
     */
    public static function boot()
    {
        parent::boot();

        // Un solo principal por (profile + context + entidad cuando aplique).
        static::saving(function ($phone) {
            if (! $phone->is_primary) {
                return;
            }
            $q = Phone::where('profile_id', $phone->profile_id)
                ->where('context', $phone->context ?? self::CONTEXT_PERSONAL)
                ->where('id', '!=', $phone->id);
            if ($phone->context === self::CONTEXT_COMMERCE && $phone->commerce_id) {
                $q->where('commerce_id', $phone->commerce_id);
            } elseif ($phone->context === self::CONTEXT_DELIVERY_COMPANY && $phone->delivery_company_id) {
                $q->where('delivery_company_id', $phone->delivery_company_id);
            } else {
                $q->whereNull('commerce_id')->whereNull('delivery_company_id');
            }
            $q->update(['is_primary' => false]);
        });
    }
}
