<?php

namespace App\Http\Controllers\Commerce;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
   public function index()
    {
        return Product::where('commerce_id', Auth::id())->get();
    }

    public function store(Request $request)
    {
        $product = Product::create([
            'commerce_id' => Auth::id(),
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'precio' => $request->precio,
            'disponible' => $request->disponible ?? true,
        ]);

        return response()->json(['message' => 'Producto creado', 'product' => $product]);
    }

    public function update(Request $request, $id)
    {
        $product = Product::where('commerce_id', Auth::id())->findOrFail($id);
        $product->update($request->only(['nombre', 'descripcion', 'precio', 'disponible']));

        return response()->json(['message' => 'Producto actualizado', 'product' => $product]);
    }

    public function destroy($id)
    {
        $product = Product::where('commerce_id', Auth::id())->findOrFail($id);
        $product->delete();

        return response()->json(['message' => 'Producto eliminado']);
    }


}
