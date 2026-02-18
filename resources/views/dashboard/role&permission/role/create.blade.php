@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header"><h3>Create</h3></div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif


                    <form action="{{ route('roles.store') }}" method="post">
                        @csrf

                        <div class="container">
                            <h3>Required data</h3>

                            <div class="form-group">
                                <input type="text" class="form-control" value=" {{ old('name') }} " name="name" id="name" placeholder="Nombre">
                            </div>
                            <div class="form-group">
                                <label for=""></label>
                                <input type="text" class="form-control" value=" {{ old('slug') }} " name="slug" id="slug" placeholder="Slug">
                            </div>
                            <div class="form-group">
                                <label for=""></label>
                                <textarea name="description" id="description" cols="100%" rows="10" placeholder="Description">{{ old('description') }} </textarea>
                            </div>

                            <hr>

                            <h4>Full Access</h4>
                              
                                <div class="custom-control custom-radio custom-control-inline">
                                    <input class="custom-control-input" type="radio" name="full-access" id="customRadioInline1" value="yes"
                                    @if (old('full-access')== 'yes') checked @endif >

                                    <label class="custom-control-label" for="customRadioInline1">Yes</label>
                                </div>

                                <div class="custom-control custom-radio custom-control-inline">
                                    <input class="custom-control-input" type="radio" name="full-access" id="customRadioInline2" value="no" 
                                    @if (old('full-access')=='no') checked @endif @if (old('full-access')== null) checked @endif >
                                    <label class="custom-control-label" for="customRadioInline2">No</label>
                                </div>
                          
                               <hr>

                               <h3>Permissions List</h3>


                               @foreach ($permissions as $permission)

                               <div class="custom-control custom-checkbox">
                                <input class="custom-control-input" type="checkbox" name="permission[]" id="permission_{{$permission->id}}" value="{{$permission->id}}"
                                @if ( is_array(old('permission')) && in_array("$permission->id", old('permission')))
                                    checked
                                @endif >
                                <label class="custom-control-label" for="permission_{{$permission->id}}">
                                    {{ $permission -> id }} - {{ $permission -> name }} <em> {{ $permission -> description }} </em>
                                </label>
                               </div>
                                   
                               @endforeach

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
