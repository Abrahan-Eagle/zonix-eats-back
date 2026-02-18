@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header"><h3>Edit User</h3></div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" user="alert">
                            {{ session('status') }}
                        </div>
                    @endif


                    <form action="{{ route('users.update', $user->id) }}" method="post">
                        @csrf
                        @method('PUT')

                        <div class="container">
                            <h3>Required data</h3>

                            <div class="form-group">
                                <input type="text" class="form-control" value="{{old('name', $user->name)}}" name="name" id="name" placeholder="Nombre">
                            </div>
                            <div class="form-group">
                                <label for=""></label>
                                <input type="text" class="form-control" value="{{old('email' , $user->email)}}" name="email" id="email" placeholder="E-mail">
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
                                            >{{ $role->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                          
                               <hr>

                               <input class="btn btn-primary" type="submit" value="Guardar">
                            

                        </div>
                        
                    </form>

                    
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
