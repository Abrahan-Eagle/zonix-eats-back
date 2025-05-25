<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Post::latest()->get();
    }

    /**
     * Display the specified resource.
     */

    public function show($id)
    {
        return Post::findOrFail($id);
    }


 }
