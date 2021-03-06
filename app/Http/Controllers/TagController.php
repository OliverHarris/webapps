<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Tag;
use App\Post;
use App\Quote\Qod;
use Gate;

class TagController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, Qod $q)
    {

        if ($request->has("search")) {
            $tagText = $request->search;
            $tag = Tag::where("tag", $tagText)->first();

            if ($tag == null) {
                return view("tags")->withErrors(["The tag $tag was not found"]);
            } else {
                $posts = Post::whereHas("tags", function ($query) use ($tag) {
                    $query->where("confirmed", 1);
                    $query->where("tag_id", $tag->id);
                })->paginate(10)->appends(["search" => $tag->tag]);

                return view("tags", ["title" => $tag->tag, "tag" => $tag, "posts" => $posts]);
            }
        } else {
            $tagFound = false;
            while (!$tagFound) {
                $tag = Tag::all()->random();
                if (count($tag->posts) > 0) {
                    $tagFound = true;
                }
            }
            return redirect()->route('tag.index', ['search' => $tag->tag]);
        }
    }

    /**
     * Display the posts that require moderation for this tag
     */
    public function mod(Tag $tag)
    {
        if (Gate::allows("edit-tag", $tag)) {
            $posts = $tag->posts->where("pivot.confirmed", false);
            return view("admin/modTags", ["title" => "Tag moderation", "tag" => $tag, "posts" => $posts]);
        }
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (Gate::allows("admin-tasks")) {
            $tag = new Tag;
            $tag->tag = $request->tag;
            $tag->save();
            return redirect()->back();
        } else {
            abort(403);
        }
    }



    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        if (Gate::allows("admin-tasks")) {
            $tag = Tag::where("tag", $request->tag)->first();
            $tag->delete();
            return redirect()->back();
        } else {
            abort(403);
        }
    }
}
