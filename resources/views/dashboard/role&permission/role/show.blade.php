@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header"><h3>Edit</h3></div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif


                    <form action="{{ route('roles.update', $role->id) }}" method="post">
                        @csrf
                        @method('PUT')

                        <div class="container">
                            <h3>Required data</h3>

                            <div class="form-group">
                                <input type="text" class="form-control" value=" {{ old('name', $role->name) }} " name="name" id="name" placeholder="Nombre" readonly>
                            </div>
                            <div class="form-group">
                                <label for=""></label>
                                <input type="text" class="form-control" value=" {{ old('slug' , $role->slug) }} " name="slug" id="slug" placeholder="Slug" readonly>
                            </div>
                            <div class="form-group">
                                <label for=""></label>
                                <textarea readonly disabled name="description" id="description" cols="100%" rows="10" placeholder="Description">{{ old('description' , $role->description) }} </textarea>
                            </div>

                            <hr>

                            <h4>Full Access</h4>
                              
                                <div class="custom-control custom-radio custom-control-inline">
                                    <input disabled class="custom-control-input" type="radio" name="full-access" id="customRadioInline1" value="yes"
                                    @if ($role['full-access'] == 'yes')
                                     checked 
                                    @elseif (old('full-access')== 'yes')
                                     checked 
                                    @endif >

                                    <label class="custom-control-label" for="customRadioInline1">Yes</label>
                                </div>

                                <div class="custom-control custom-radio custom-control-inline">
                                    <input disabled class="custom-control-input" type="radio" name="full-access" id="customRadioInline2" value="no" 
                                    @if ($role['full-access'] == 'no')
                                     checked 
                                    @elseif (old('full-access')== 'no')
                                     checked 
                                    @endif >
                                     
                                    <label class="custom-control-label" for="customRadioInline2">No</label>
                                </div>
                          
                               <hr>

                               <h3>Permissions List</h3>


                               @foreach ($permissions as $permission)

                               <div class="custom-control custom-checkbox">
                                <input disabled class="custom-control-input" type="checkbox" name="permission[]" id="permission_{{$permission->id}}" value="{{$permission->id}}"
                                @if ( is_array(old('permission')) && in_array("$permission->id", old('permission')))
                                    checked
                                @elseif ( is_array($permissions_role) && in_array("$permission->id", $permissions_role))
                                    checked
                                @endif >
                                <label class="custom-control-label" for="permission_{{$permission->id}}">
                                    {{ $permission -> id }} - {{ $permission -> name }} <em> {{ $permission -> description }} </em>
                                </label>
                               </div>
                                   
                               @endforeach

                               <hr>

                               
                               <a class="btn btn-success" href="{{ route('roles.edit', $role->id) }}"> Edit </a>
                               <a class="btn btn-danger" href="{{ route('roles.index') }}">Back</a>
                               
                            

                        </div>
                        
                    </form>

                    


                    
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
