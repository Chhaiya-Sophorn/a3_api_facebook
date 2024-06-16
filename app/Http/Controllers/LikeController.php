<?php

namespace App\Http\Controllers;

use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class LikeController extends Controller
{

    public function likePost(Request $request, $postId)
    {
        $userId = $request->input('user_id');
        $user = User::find($userId);

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $post = Post::find($postId);

        if (!$post) {
            return response()->json(['message' => 'Post not found.'], 404);
        }

        if ($post->likes()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'User already liked this post.'], 400);
        }

        $like = new Like();
        $like->user_id = $user->id;

        $post->likes()->save($like);

        return response()->json(['message' => 'Post liked successfully.'], 200);
    }

    public function unlikePost(Request $request, $postId)
    {
        $userId = $request->input('user_id');
        $user = User::find($userId);

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $post = Post::find($postId);

        $like = $post->likes()->where('user_id', $user->id)->first();

        if (!$like) {
            return response()->json(['message' => 'User has not liked this post.'], 400);
        }

        $like->delete();
        return response()->json(['message' => 'Post unliked successfully.'], 200);
    }
}
