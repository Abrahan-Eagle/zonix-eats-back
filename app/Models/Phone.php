<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Phone extends Model
{
    use HasFactory;

    public const CONTEXT_PERSONAL = 'personal';

    public const CONTEXT_ADMIN = 'admin';

    protected $fillable = [
        'profile_id',
        'context',
        'operator_code_id',
        'number',
        'is_primary',
        'status',
    ];

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    public function operatorCode()
    {
        return $this->belongsTo(OperatorCode::class, 'operator_code_id');
    }

    public function getFullNumberAttribute(): string
    {
        $code = $this->operatorCode?->code ?? '';

        return $code.$this->number;
    }

    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    public static function boot()
    {
        parent::boot();

        static::saving(function ($phone) {
            if (! $phone->is_primary) {
                return;
            }
            Phone::where('profile_id', $phone->profile_id)
                ->where('context', $phone->context ?? self::CONTEXT_PERSONAL)
                ->where('id', '!=', $phone->id)
                ->update(['is_primary' => false]);
        });
    }
}
