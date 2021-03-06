<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Post;
use App\Comment;
use Auth;
use Gate;

class CommentController extends Controller
{

    public function __construct()
    {

        $this->middleware('auth')->except(["fromPost"]);
    }

    /**
     * Get the comments for a post
     */
    public function fromPost(Post $post, Request $request)
    {
        $comments = Comment::with(["user" => function ($query) {
            $query->select('name', "id");
        }])->where("post_id", $post->id)->orderBy('created_at', 'desc')->paginate(5);
        foreach ($comments as $comment) {
            //We don't use the auth middleware, so we have to get the user this way
            //so we can't use the gates
            $user = $request->user('api');
            $comment->canEdit = $this->canEdit($comment, $user);
        }
        return $comments;
    }

    /**
     * Check to see if the current user can edit this comment
     */
    public function canEdit($comment, $user)
    {

        if ($user) {
            if ($user->admin) {
                return true;
            } else if (!$comment->post->tags->whereIn("tag", $user->admins->pluck("tag")->toArray())->isEmpty()) {
                return true;
            } else {
                return $user->id == $comment->user_id;
            }
        } else {
            return false;
        }
    }

    /**
     * Store a newly created comment in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Post $post, Request $request)
    {
        //check post exists
        if (!$post) {
            return response('', 404);
        }


        $validatedData = $request->validate([
            "comment" => "required|min:5|max:1000"
        ]);
        $comment = strip_tags($validatedData["comment"]);
        $newComment = new Comment;
        $newComment->comment = $comment;
        $newComment->post_id = $post->id;
        $newComment->user_id = Auth::id();
        $newComment->save();
        return response($newComment, 200);
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Comment $post)
    {
        $comment = $post;


        if (Gate::allows("edit-comment", $comment)) {
            //get the comment

            $comment->delete();
        } else {
            abort(403);
        }
    }
    /**
     * Update the comment
     */
    public function update(Comment $post, Request $request)
    {

        $validatedData = $request->validate([
            "comment" => "required|min:5|max:1000"
        ]);

        if (Gate::allows("edit-comment", $post)) {
            $post->comment = $validatedData["comment"];
            $post->save();
        } else {
            abort(403);
        }
    }
}
