<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Email extends Model
{
    use HasFactory;

    protected $fillable = ['profile_id', 'email', 'is_primary'];

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }


}
