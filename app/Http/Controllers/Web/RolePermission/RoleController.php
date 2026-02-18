<?php

namespace App\Http\Controllers\Web\RolePermission;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Facades\Gate;


class RoleController extends Controller
{
  /**
  * Display a listing of the resource.
  *
  * @return \Illuminate\Http\Response
  */
  public function index(Request $request)
  {
  Gate::authorize('haveaccess', 'roles.index');

  $roles = Role::orderBy('id', 'DESC')->paginate(3);
  
  if ($request->user()) {
    $lightdark = $request->user() -> light;
  }
  //return $roles;
  return view('dashboard.role&permission.role.index', compact('roles', 'lightdark'));
  }

  /**
  * Show the form for creating a new resource.
  *
  * @return \Illuminate\Http\Response
  */
  public function create()
  {
  Gate::authorize('haveaccess', 'roles.create');

  $permissions = Permission::get();
  return view('dashboard.role&permission.role.create', compact('permissions'));
  }

  /**
  * Store a newly created resource in storage.
  *
  * @param \Illuminate\Http\Request $request
  * @return \Illuminate\Http\Response
  */
  public function store(Request $request)
  {

  Gate::authorize('haveaccess', 'roles.create');

  $request -> validate([
  'name' => 'required|max:50|unique:roles,name',
  'slug' => 'required|max:50|unique:roles,slug',
  'full-access' => 'required|in:yes,no',
  ]);

  $role = Role::create($request->all());

  // if ($request->get('permission')){
  //return $request->all();
  $role -> permissions()->sync($request->get('permission'));
  // }
  return redirect()->route('roles.index')->with('status_success', 'Role saved successfully');
  /*else{
  return 'No existe';
  }
  return $request -> all();
  */
  }

  /**
  * Display the specified resource.
  *
  * @param \App\Permission\Models\Role $role
  * @return \Illuminate\Http\Response
  */
  public function show(Role $role)
  {
  $this->authorize('haveaccess', 'roles.show');

  $permissions_role = [];
  foreach ($role -> permissions as $permission) {
  $permissions_role[] = $permission -> id;
  }

  $permissions = Permission::get();
  return view('dashboard.role&permission.role.show', compact('permissions', 'role', 'permissions_role'));
  //return $role;
  }

  /**
  * Show the form for editing the specified resource.
  *
  * @param \App\Permission\Models\Role $role
  * @return \Illuminate\Http\Response
  */
  public function edit(Role $role)
  {
  $this->authorize('haveaccess', 'roles.edit');

  $permissions_role = [];
  foreach ($role -> permissions as $permission) {
  $permissions_role[] = $permission -> id;
  }

  $permissions = Permission::get();
  return view('dashboard.role&permission.role.edit', compact('permissions', 'role', 'permissions_role'));
  //return $role;
  }

  /**
  * Update the specified resource in storage.
  *
  * @param \Illuminate\Http\Request $request
  * @param \App\Permission\Models\Role $role
  * @return \Illuminate\Http\Response
  */
  public function update(Request $request, Role $role)
  {
  $this->authorize('haveaccess', 'roles.edit');

  $request -> validate([
  'name' => 'required|max:50|unique:roles,name,'. $role->id,
  'slug' => 'required|max:50|unique:roles,slug,'. $role->id,
  'full-access' => 'required|in:yes,no',
  ]);

  $role->update($request->all());

  // if ($request->get('permission')){
  //return $request->all();
  $role -> permissions()->sync($request->get('permission'));
  // }
  return redirect()->route('roles.index')->with('status_success', 'Role updated successfully');
  /*else{
  return 'No existe';
  }
  return $request -> all();
  */
  }

  /**
  * Remove the specified resource from storage.
  *
  * @param \App\Permission\Models\Role $role
  * @return \Illuminate\Http\Response
  */
  public function destroy(Role $role)
  {

  $this->authorize('haveaccess', 'roles.destroy');

  $role->delete();
  return redirect()->route('roles.index')->with('status_success', 'Role successfully removed');


  }
  }
