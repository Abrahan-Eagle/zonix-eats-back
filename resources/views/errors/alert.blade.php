{{-- Alertas de sesión --}}
@if (session('success'))
    <div class="container">
        <div class="row">
            <div class="col-md-12 col-md-offset-2">
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif

@if (session('info'))
    <div class="container">
        <div class="row">
            <div class="col-md-12 col-md-offset-2">
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    {{ session('info') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif

@if (session('warning'))
    <div class="container">
        <div class="row">
            <div class="col-md-12 col-md-offset-2">
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    {{ session('warning') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif

@if (session('error'))
    <div class="container">
        <div class="row">
            <div class="col-md-12 col-md-offset-2">
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif

{{-- Errores de validación --}}
@if ($errors->any())
    <div class="container">
        <div class="row">
            <div class="col-md-12 col-md-offset-2">
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Por favor, corrige los siguientes errores:</strong>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif
