<?php

namespace App\Http\Controllers\Web\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
// use App\Models\Order;

class HomeController extends Controller
{
    /**
     * Display the dashboard with statistics.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $stats = [
            'users' => User::count(),
            'roles' => Role::count(),
            // 'orders' => Order::count(), // Descomentar cuando el modelo Order esté migrado y listo
        ];

        // Usuarios recientes (últimos 5)
        $recent_users = User::with('roles')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('dashboard.home', compact('stats', 'recent_users'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Toggle light/dark theme for the authenticated user.
     * Implementación basada en uniblockx
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $id = auth()->user()->id;
        $user = User::find($id);

        // Comparar con strings como en uniblockx
        switch ($user->light) {
            case '1':
                $light = '0';
                break;

            case '0':
                $light = '1';
                break;
            
            default:
                $light = '1';
                break;
        }

        // Usar update directo en query builder como en uniblockx
        User::where('id', '=', $id)
            ->update(['light' => $light]);

        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
