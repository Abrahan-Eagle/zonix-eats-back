<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;

class UserController extends Controller
{
     /**
      * Display a listing of the resource.
      *
      * @return \Illuminate\Http\Response
      */
     public function index()
     {
          $users = User::with('roles')->orderBy('id', 'DESC')->paginate(4);
          //return $roles;
          return view('dashboard.role&permission.user.index', compact('users'));
     }

     /**
      * Show the form for creating a new resource.
      *
      * @return \Illuminate\Http\Response
      */
     public function create()
     {
          //$this -> authorize('create', User::class);
          //return 'create';

     }

     /**
      * Store a newly created resource in storage.
      *
      * @param \Illuminate\Http\Request $request
      * @return \Illuminate\Http\Response
      */
     public function store(Request $request)
     {
          //
     }

     /**
      * Display the specified resource.
      *
      * @param int $id
      * @return \Illuminate\Http\Response
      */
     public function show(User $user)
     {
          // $this -> authorize('view', [$user, ['users.show', 'usersown.show'] ] );

          $roles = Role::orderBy('name')->get();
          return view('dashboard.role&permission.user.show', compact('roles', 'user'));
     }

     /**
      * Show the form for editing the specified resource.
      *
      * @param int $id
      * @return \Illuminate\Http\Response
      */
     public function edit(User $user)
     {
          //$this -> authorize('update', $user);
          //$this -> authorize('update', [$user, ['users.edit', 'usersown.edit'] ] );

          $roles = Role::orderBy('name')->get();
          return view('dashboard.role&permission.user.edit', compact('roles', 'user'));
     }

     /**
      * Update the specified resource in storage.
      *
      * @param \Illuminate\Http\Request $request
      * @param int $id
      * @return \Illuminate\Http\Response
      */
     public function update(Request $request, User $user)
     {
          $request->validate([
               'name' => 'required|max:50|unique:users,name,' . $user->id,
               'email' => 'required|max:50|unique:users,email,' . $user->id,
          ]);

          $user->update($request->all());
          $user->roles()->sync($request->get('roles'));
          return redirect()->route('users.index')->with('status_success', 'User updated successfully');
     }

     /**
      * Remove the specified resource from storage.
      *
      * @param int $id
      * @return \Illuminate\Http\Response
      */

     public function destroy(Request $request, User $user)
     {

          $this->authorize('haveaccess', 'users.destroy');

          $user->delete();
          return redirect()->route('users.index')->with('status_success', 'User successfully removed');
     }
}

