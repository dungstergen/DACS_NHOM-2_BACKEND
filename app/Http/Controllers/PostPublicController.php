<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;

class PostPublicController extends Controller
{
    /**
     * Display a listing of published posts.
     */
    public function index(Request $request)
    {
        $posts = Post::with('author')
            ->where('status', 'published')
            ->latest()
            ->paginate(10);
            
        return response()->json($posts);
    }

    /**
     * Display the specified post by slug.
     */
    public function show(string $slug)
    {
        $post = Post::with('author')
            ->where('status', 'published')
            ->where('slug', $slug)
            ->firstOrFail();
            
        return response()->json($post);
    }
}
