<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;

class PostPublicController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/posts",
     *     summary="Get all public posts",
     *     tags={"Posts"},
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="List of posts",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Post"))
     *         )
     *     )
     * )
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
     * @OA\Get(
     *     path="/api/posts/{slug}",
     *     summary="Get public post by slug",
     *     tags={"Posts"},
     *     @OA\Parameter(name="slug", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(
     *         response=200,
     *         description="Post details",
     *         @OA\JsonContent(ref="#/components/schemas/Post")
     *     ),
     *     @OA\Response(response=404, description="Post not found")
     * )
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
