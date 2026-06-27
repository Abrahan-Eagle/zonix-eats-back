<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Models\PatientProfile;
use App\Models\Prescription;
use App\Policies\PatientProfilePolicy;
use App\Policies\PrescriptionPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Prescription::class => PrescriptionPolicy::class,
        PatientProfile::class => PatientProfilePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
