<?php

namespace App\Http\Controllers;

use App\Models\likes;
use Illuminate\Http\Request;

class LikesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'post_id' =>'required',
            
        ]);

        $user = $request->user();

        $like = like::where('user_id', $user->id)
                    ->where('post_id', $request->post_id)
                    ->first();
        if($like){
            $like->delete();
            return response()->json([
               'message' => 'you Unliked a Post',
            ],200);
        }else{
            $like = new like();
            $like->user_id = $user->id;
            $like->post_id = $request->post_id;
            $like->save();

            if ($like->sabe()) {
                return response()->json([
                   'message' => 'you liked a Post',
                   'like' => $like->load('user')
                ],200);
            } else {
                return response()->json([
                   'message' => 'Some error occurred, please try again',
                ],500);
            }

        }
    }

    /**
     * Display the specified resource.
     */
    public function show(likes $likes)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, likes $likes)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(likes $likes)
    {
        //
    }
}
