<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryCompany;
use Illuminate\Http\Request;

class DeliveryCompanyController extends Controller
{
    public function index(Request $request)
    {
        $perPage = min((int) $request->get('per_page', 15), 100);
        $perPage = $perPage > 0 ? $perPage : 15;

        $query = DeliveryCompany::with('profile');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        $paginator = $query->orderBy('id', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $paginator->items(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    public function show($id)
    {
        $company = DeliveryCompany::with(['profile', 'deliveryAgents', 'paymentMethods'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $company,
        ]);
    }

    public function agents($id)
    {
        $company = DeliveryCompany::findOrFail($id);

        $agents = $company->deliveryAgents()->with('profile')->get();

        return response()->json([
            'success' => true,
            'data' => $agents,
        ]);
    }
}
