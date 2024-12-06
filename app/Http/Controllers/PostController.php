<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Post::with('user:id,first_name,last_name,profile_photo_path', 'media', 'postLikes', 'comments')->get();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'content' => 'required|string',
            'media' => 'required|array', 
            'media.*.file_path' => 'required|string|max:255',
            'media.*.file_type' => 'required|in:image,video'
        ]);

        $post = auth()->user()->posts()->create([
            'title' => $validated['title'],
            'content' => $validated['content'],
        ]);

        $post->media()->createMany($validated['media']);

        return $post->load('media','user:id,first_name,last_name,profile_photo_path');
    }


    /**
     * Display the specified resource.
     */
    public function show(Post $postId)
    {
        if (!$postId) {
            return response()->json(['message' => 'Post not found'], 404);
        }
        $sessionKey = 'post_' . $postId->id . '_viewed';

        if (!session()->has($sessionKey)) {
            session()->put($sessionKey, true);
            $postId->increment('view_count');
        }

        return $postId->load(['user:id,first_name,last_name,profile_photo_path','media','postLikes','comments']);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Post $post)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Post $postId)
    {
        if (!$postId) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string',
            'content' => 'sometimes|string',
            'media' => 'nullable|array', 
            'media.*.file_path' => 'required_with:media|string|max:255',
            'media.*.file_type' => 'required_with:media|in:image,video'
        ]);
        if (auth()->id() !== $postId->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $postId->update($validated);
        if (isset($validated['media'])) {
            $postId->media()->delete();
            $postId->media()->createMany($validated['media']);
        }
        return $postId->load(['media', 'user:id,first_name,last_name,profile_photo_path']);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $postId)
    {
        if (!$postId) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        if (auth()->id() !== $postId->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $postId->delete();
        return response()->json(['message' => 'Post deleted successfully.'], 200);
    }

    public function showComments(Post $postId){
        if (!$postId) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        return $postId->comments()->with('user:id,first_name,last_name,profile_photo_path')->get();
    }

    public function showComment(Post $postId, Comment $commentId)
    {
        if (!$postId) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        $comment = $postId->comments()->find($commentId->id);

        if (!$comment) {
            return response()->json(['message' => 'Comment not found'], 404);
        }

    return response()->json($comment->load("user:id,first_name,last_name,profile_photo_path"));
    }

    public function storeComment(Request $request, Post $postId)
    {
        if (!$postId) {
            return response()->json(['message' => 'Post not found'], 404);
        } 
        
        $request->validate([
            'content' => 'required|string|max:1000',  
        ]);

        $comment=auth()->user()->comments()->create([
            'content' => $request->input('content'),
            'post_id' => $postId->id 
        ]);
        
        return response()->json([
            'message' => 'Comment created successfully',
            'comment' => $comment->load("user","post")
        ], 201);
    }
    public function updateComment(Request $request, Post $postId, Comment $commentId){
        if (!$postId) {
            return response()->json(['message' => 'Post not found'], 404);
        }
        if (!$commentId) {
            return response()->json(['message' => 'Comment not found'], 404);
        }
        $request->validate([
            'content' => 'required|string|max:1000',  
        ]);

        if (auth()->id() !== $commentId->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $commentId->update([
            'content' => $request->input('content'),
        ]);
        return response()->json([
            'message' => 'Comment updated successfully',
            'comment' => $commentId->load('user:id,first_name,last_name,profile_photo_path', 'post:id,title,content') 
        ],200);

    }
    public function destroyComment(Post $postId, Comment $commentId){
        if (!$postId) {
            return response()->json(['message' => 'Post not found'], 404);
        }
        if (!$commentId) {
            return response()->json(['message' => 'Comment not found'], 404);
        }
        if (auth()->id() !== $commentId->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $commentId->delete();
        return response()->json(['message' => 'Comment deleted successfully'], 200);
    }

    public function toggleLike(Post $postId)
    {
        if (!$postId) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        $user = auth()->user();

        // Check if the user has already liked the post
        $like = $postId->postLikes()->where('user_id', $user->id)->first();

        if ($like) {
            // Unlike if already liked
            $like->delete();
            return response()->json(['message' => 'Post unliked successfully'], 200);
        } else {
            // Like if not liked yet
            $postId->postLikes()->create(['user_id' => $user->id]);
            return response()->json(['message' => 'Post liked successfully'], 201);
        }
    }

    public function getPostLikes(Post $postId)
    {
        if (!$postId) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        // Load the users who liked the post
        $likes = $postId->postLikes()->with('user:id,first_name,last_name,profile_photo_path')->get();

        return response()->json(['likes' => $likes]);
    }

    public function countPostLikes(Post $postId)
    {
        if (!$postId) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        $likesCount = $postId->postLikes()->count();

        return response()->json(['likesCount' => $likesCount]);
    }

    public function search(Request $request)
    {
        $query = $request->input('query');

        $posts = Post::where('title', 'like', "%{$query}%")
            ->orWhere('content', 'like', "%{$query}%")
            ->with(['user:id,first_name,last_name'])
            ->latest()
            ->get();

        return response()->json(['posts' => $posts]);
    }


}
