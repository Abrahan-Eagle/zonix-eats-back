@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header"><h3>SHOW USER</h3></div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif


                    <form action="" method="post">
                        @csrf
                        @method('PUT')

                        <div class="container">
                          
                            <h3>Required data</h3>

                            <div class="form-group">
                                <input type="text" class="form-control" value="{{old('name', $user->name)}}" name="name" id="name" placeholder="Nombre" disabled>
                            </div>
                            <div class="form-group">
                                <label for=""></label>
                                <input type="text" class="form-control" value="{{old('email' , $user->email)}}" name="email" id="email" placeholder="E-mail" disabled>
                            </div>
                            <div class="form-group">
                                <label for=""></label>
                                <select class="form-control" name="roles" id="roles">
                                    @foreach ($roles as $role)
                                        <option value="{{$role->id}}"
                                            @isset($user->roles[0]->name)
                                                @if ($role->name == $user->roles[0]->name)
                                                    Selected
                                                @endif
                                            @endisset
                                            disabled>{{ $role->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                          

                               <hr>

                               
                               <a class="btn btn-success" href="{{ route('users.edit', $role->id) }}"> Edit </a>
                               <a class="btn btn-danger" href="{{ route('users.index') }}">Back</a>
                               
                            

                        </div>
                        
                    </form>

                    


                    
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
