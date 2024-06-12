<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'user_id' => 'required|exists:users,id',
            'tags' => 'nullable|string',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('post_images', 'public');
        }

        $post = new Post();
        $post->title = $request->title;
        $post->content = $request->input('content');
        $post->image = $imagePath;
        $post->user_id = $request->user_id;
        $post->tags = $request->tags;
        $post->save();

        return response()->json([
            'message' => 'Post created successfully',
            'data' => $post
        ], 201);
    }

    public function show($id)
    {
        $post = Post::findOrFail($id);
        return response()->json([
            'data' => $post
        ]);
    }

    public function update(Request $request, string $id)
    {
        try {
            $post = Post::findOrFail($id);
            $post->title = $request->title;
            $post->content = $request->content;
            $post->user_id = $request->user_id;
            $post->tags = $request->tags;
    
            if ($request->hasFile('image')) {
                if ($post->image) {
                    Storage::disk('public')->delete($post->image);
                }
                $post->image = $request->file('image')->store('post_images', 'public');
            }
    
            $post->save();
    
            return response()->json([
                'success' => true,
                'data' => $post,
                'msg' => 'Post updated successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => $e->getMessage(),
                'msg' => 'Could not update post'
            ], 500);
        }
    }

    public function destroy($id)
    {
        $post = Post::findOrFail($id);

        // Delete associated image if exists
        if ($post->image) {
            Storage::disk('public')->delete($post->image);
        }

        $post->delete();

        return response()->json([
            'message' => 'Post deleted successfully'
        ]);
    }
}
