<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/admin/posts",
     *     summary="Admin: Get all posts",
     *     tags={"Admin Posts"},
     *     security={{"cookieAuth":{}}},
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="List of posts",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Post"))
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function index(Request $request)
    {
        $posts = Post::with('author')->latest()->paginate(10);
        return response()->json($posts);
    }

    /**
     * @OA\Post(
     *     path="/api/admin/posts",
     *     summary="Admin: Create a new post",
     *     tags={"Admin Posts"},
     *     security={{"cookieAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title","content"},
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="content", type="string"),
     *             @OA\Property(property="thumbnail_url", type="string"),
     *             @OA\Property(property="status", type="string", enum={"draft","published"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Post created",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="post", ref="#/components/schemas/Post")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'thumbnail_url' => 'nullable|url',
            'status' => 'nullable|in:draft,published',
        ]);

        $post = Post::create([
            'title' => $request->title,
            'slug' => Str::slug($request->title) . '-' . time(),
            'content' => $request->content,
            'thumbnail_url' => $request->thumbnail_url,
            'author_id' => Auth::id(),
            'status' => $request->status ?? 'published',
        ]);

        return response()->json([
            'message' => 'Tạo bài viết thành công',
            'post' => $post
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/posts/{post}",
     *     summary="Admin: Get post details by ID",
     *     tags={"Admin Posts"},
     *     security={{"cookieAuth":{}}},
     *     @OA\Parameter(name="post", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Post details",
     *         @OA\JsonContent(ref="#/components/schemas/Post")
     *     ),
     *     @OA\Response(response=404, description="Post not found"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function show(string $id)
    {
        $post = Post::with('author')->findOrFail($id);
        return response()->json($post);
    }

    /**
     * @OA\Put(
     *     path="/api/admin/posts/{post}",
     *     summary="Admin: Update a post",
     *     tags={"Admin Posts"},
     *     security={{"cookieAuth":{}}},
     *     @OA\Parameter(name="post", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="content", type="string"),
     *             @OA\Property(property="status", type="string", enum={"draft","published"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Post updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="post", ref="#/components/schemas/Post")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Post not found"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
            'thumbnail_url' => 'nullable|url',
            'status' => 'nullable|in:draft,published',
        ]);

        $post = Post::findOrFail($id);
        
        $data = $request->only(['title', 'content', 'thumbnail_url', 'status']);
        
        if ($request->has('title') && $request->title !== $post->title) {
            $data['slug'] = Str::slug($request->title) . '-' . time();
        }

        $post->update($data);

        return response()->json([
            'message' => 'Cập nhật bài viết thành công',
            'post' => $post
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/admin/posts/{post}",
     *     summary="Admin: Delete a post",
     *     tags={"Admin Posts"},
     *     security={{"cookieAuth":{}}},
     *     @OA\Parameter(name="post", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Post deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Post not found"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function destroy(string $id)
    {
        $post = Post::findOrFail($id);
        $post->delete();

        return response()->json(['message' => 'Xóa bài viết thành công']);
    }
}
