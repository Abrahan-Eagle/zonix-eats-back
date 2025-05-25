<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\PostLike;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostLikeController extends Controller
{

     public function like($postId)
    {
        PostLike::firstOrCreate([
            'user_id' => Auth::id(),
            'post_id' => $postId,
        ]);

        return response()->json(['message' => 'Post liked']);
    }

    public function unlike($postId)
    {
        PostLike::where('user_id', Auth::id())->where('post_id', $postId)->delete();

        return response()->json(['message' => 'Post unliked']);
    }


}
