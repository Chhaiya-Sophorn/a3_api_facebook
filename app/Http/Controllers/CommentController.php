<?php

namespace App\Http\Controllers;


use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;

class CommentController extends Controller
{

    public function show($postId)
    {
        $post = Post::findOrFail($postId);
        $comments = $post->comments()->get();
        return response()->json([
            'data' => CommentResource::collection($comments),
        ]);
    }

    public function store(Request $request, $postId)
    {
        $post = Post::findOrFail($postId);
        $comment = $post->comments()->create([
            'user_id' => $post->user_id,
            'content' => $request->input('content'),
        ]);
        return response()->json($comment, 201);
    }

    public function update(Request $request, $commentId)
    {
        $request->validate([
            'content' => [
               'required',
               'string',
               'max:255',
            ],
        ]);

        $comment = Comment::findOrFail($commentId);
        $comment->content = $request->content;
        $comment->save();

        return response()->json([
            'data' => new CommentResource($comment),
            'message' => 'Comment updated successfully',
        ]);
    }

   
    public function destroy ($id){
        $comment = Comment::findOrFail($id);
        $comment->delete();
        return response()->json([
           'message' => 'Comment deleted successfully'
        ]);
    }
}
