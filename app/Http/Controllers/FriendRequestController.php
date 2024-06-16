<?php

namespace App\Http\Controllers;

use App\Models\FriendRequest;
use Illuminate\Http\Request;
use App\Models\User;

class FriendRequestController extends Controller
{
    public function index()
    {
        $sender_id = auth()->id();
        $receiverIds = FriendRequest::where('sender_id', $sender_id)
                            ->where('status','panding')
                            ->pluck('receiver_id');
    
        if ($receiverIds->isEmpty()) {
            return response()->json([
                'message' => 'There are no requests for friend!'
            ]);
        } else {
            $friends = User::whereIn('id', $receiverIds)
                        ->get();
            return response()->json([
                'message' => 'The user that request for friend',
                'data' => $friends
            ]);
        }
    }
    public function friends()
    {
        $userId = auth()->id();
    
        // Get all accepted friend requests where the current user is either the sender or the receiver
        $friendRequests = FriendRequest::where(function ($query) use ($userId) {
                            $query->where('sender_id', $userId)
                                ->orWhere('receiver_id', $userId);
                        })
                        ->where('status', 'accepted')
                        ->get();
    
        if ($friendRequests->isEmpty()) {
            return response()->json([
                'message' => 'You don\'t have any friends yet.'
            ]);
        } else {
            // Extract the unique friend user IDs
            $friendUserIds = $friendRequests->map(function ($request) use ($userId) {
                                if ($request->sender_id == $userId) {
                                    return $request->receiver_id;
                                } else {
                                    return $request->sender_id;
                                }
                            })
                            ->unique()
                            ->toArray();
    
            // Retrieve the user details for the friend user IDs
            $friends = User::whereIn('id', $friendUserIds)
                        ->get();
    
            return response()->json([
                'message' => 'Your friends:',
                'data' => $friends
            ]);
        }
    }

    public function friendRequests()
    {
        $receiverIds = auth()->id();
        $sender_id = FriendRequest::where('receiver_id', $receiverIds)
                            ->where('status','panding')
                            ->pluck('sender_id');
    
        if ($sender_id->isEmpty()) {
            return response()->json([
                'message' => 'There are no requests for friend!'
            ]);
        } else {
            $friends = User::whereIn('id', $sender_id)
                        ->get();
            return response()->json([
                'message' => 'The user that request to me for friend',
                'data' => $friends
            ]);
        }
    }
    public function store(Request $request)
    {

        $friends = User::where('id', $request->receiver_id)->get();
        if ($friends->isEmpty()) {
            return response()->json([
               'message' => 'This user does not exist!'
            ]);
        }else{

        $validatedData = $request->validate([
            'receiver_id' => 'required|exists:users,id|different:sender_id',
        ]);
    
        $sender_id = auth()->id();
        $receiver_id = $validatedData['receiver_id'];
    
        // Check if a pending or accepted friend request already exists
        $existingRequest = FriendRequest::where(function ($query) use ($sender_id, $receiver_id) {
            $query->where('sender_id', $sender_id)
                  ->where('receiver_id', $receiver_id);
        })->orWhere(function ($query) use ($sender_id, $receiver_id) {
            $query->where('sender_id', $receiver_id)
                  ->where('receiver_id', $sender_id);
        })->first();
    
        if ($existingRequest) {
            if ($existingRequest->status === FriendRequest::STATUS_ACCEPTED) {
                return response()->json(['message' => 'You are already friends with this user.'], 400);
            } else {
                return response()->json(['message' => 'A friend request has already been sent.'], 400);
            }
        }
    
        // Check if sender_id and receiver_id are the same
        if ($sender_id == $receiver_id) {
            return response()->json(['message' => 'You cannot send a friend request to yourself.'], 400);
        }
    
        $friendRequest = FriendRequest::create([
            'sender_id' => $sender_id,
            'receiver_id' => $receiver_id,
            'status' => FriendRequest::STATUS_PENDING,
        ]);
    
        return response()->json(['message' => 'Friend request sent successfully.', 'data' => $friendRequest], 201);
        }
    }
    
    public function destroy($friendRequest)
    {
        $sender_id = auth()->id();
        $receiver_id = $friendRequest;
    
        $friendRequests = FriendRequest::where('sender_id', $sender_id)
                                      ->where('receiver_id', $receiver_id)
                                      ->where('status', 'pending')
                                      ->get();
        $friendRequests->each->delete();
        return response()->json([
           'message' => 'Unrequest friend successfully!'
        ]);
    }
    
    public function response($friendRequestId, $status)
    {
        $receiver_id = auth()->id();
        $friendRequest = FriendRequest::where('sender_id', $friendRequestId)
                                    ->where('receiver_id', $receiver_id)
                                    ->first();
    
        if (!$friendRequest) {
            return response()->json(['message' => 'Friend request not found or not related to the authenticated user.'], 404);
        }
    
        if ($friendRequest->receiver_id === null) {
            return response()->json(['message' => 'The friend request does not have a valid receiver.'], 400);
        }
    
        if ($status === 'accepted') {
            if ($friendRequest->status === 'accepted') {
                return response()->json(['message' => 'You have already accepted this friend request.'], 200);
            }
    
            $friendRequest->status = 'accepted';
            $friendRequest->save();
            return response()->json(['message' => 'Friend request accepted.'], 200);
        } elseif ($status === 'rejected') {
            if ($friendRequest->status === 'panding') {
                $friendRequest->delete();
                return response()->json(['message' => 'Friend request rejected.'], 200);
            }else{
                return response()->json(['message' => 'This user has already be your friend'], 200);
            }
        } else {
            return response()->json(['message' => 'Invalid status.'], 400);
        }
    }

    public function deleteFriend($friendId)
{
    $userId = auth()->id();

    // Find the accepted friend request between the current user and the provided friend ID
    $friendRequest = FriendRequest::where(function ($query) use ($userId, $friendId) {
        $query->where('sender_id', $userId)
              ->where('receiver_id', $friendId)
              ->where('status', 'accepted');
    })
    ->orWhere(function ($query) use ($userId, $friendId) {
        $query->where('sender_id', $friendId)
              ->where('receiver_id', $userId)
              ->where('status', 'accepted');
    })
    ->first();

    if (!$friendRequest) {
        return response()->json([
            'message' => 'No accepted friend request found between you and the provided user.'
        ], 404);
    }

    // Delete the friend request
    $friendRequest->delete();

    // Remove the friend from the user's friends list
    $user = auth()->user();
    $user->friends()->detach($friendId);

    return response()->json([
        'message' => 'Friend successfully deleted.'
    ]);
}

}