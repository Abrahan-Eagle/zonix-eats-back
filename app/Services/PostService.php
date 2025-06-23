<?php

namespace App\Services;

use App\Models\Post;

class PostService
{
    /**
     * Obtener todos los posts.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllPosts()
    {
        return Post::latest()->get();
    }

    /**
     * Obtener un post por su ID.
     *
     * @param int $id
     * @return Post|null
     */
    public function getPostById($id)
    {
        return Post::find($id);
    }
}
