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
     * Display a listing of the posts.
     */
    public function index(Request $request)
    {
        $posts = Post::with('author')->latest()->paginate(10);
        return response()->json($posts);
    }

    /**
     * Store a newly created post.
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
     * Display the specified post.
     */
    public function show(string $id)
    {
        $post = Post::with('author')->findOrFail($id);
        return response()->json($post);
    }

    /**
     * Update the specified post.
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
     * Remove the specified post.
     */
    public function destroy(string $id)
    {
        $post = Post::findOrFail($id);
        $post->delete();

        return response()->json(['message' => 'Xóa bài viết thành công']);
    }
}
