<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Post;
use App\Tag;
use Auth;
use File;
use Gate;
use App\Image;

class PostController extends Controller
{

    /**
     * display a post
     */
    public function perPost(Post $id)
    {
        $post = $id;

        return view("post", ["title" => $post->title, "post" => $post, "edit" => Gate::allows("edit-post", $post)]);
    }
    /**
     * Get the raw data for a post
     */
    public function data($id)
    {
        $post = Post::with(["images", "tags" => function ($query) {
            $query->select('tag', "id", "confirmed");
        }])->where("id", $id)->first();
        if ($post == null) {
            abort(404);
        }
        return $post;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        if ($request->has("post")) {
            return view("post/create", ["title" => "Post editor", "post" => $request->post]);
        } else {
            return view("post/create", ["title" => "Post editor", "post" => -1]);
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

        $data = $request->validate([
            "title" => 'required|min:5|max:100',
            "content" => "required|min:5",
            "tags" => "required",
        ]);
        if ($request->code == -1) {
            $post = new Post;
            $post->title = $data["title"];
            $post->content = $data["content"];
            $post->user_id = Auth::id();
            $post->save();

            //Now we can create the new tags
            foreach ($data["tags"] as $tag) {
                $tag = Tag::where("tag", $tag)->first();
                if ($tag != null) {
                    $post->tags()->attach($tag);
                }
            }
            return Post::with(["images", "tags" => function ($query) {
                $query->select('tag', "id", "confirmed");
            }])->where("id", $post->id)->first();;
        } else {

            $post = Post::find($request->code);
            if (Gate::allows("edit-post", $post)) {
                $post->title = $data["title"];
                $post->content = $data["content"];
                $post->save();
                $curTags = $post->tags;
                foreach ($curTags as $tag) {
                    if (in_array($tag->tag, $data["tags"])) {
                        //It's already a tag
                        unset($data["tags"][array_search($tag->tag, $data["tags"])]);
                    } else {
                        $post->tags()->detach($tag->id);
                    }
                }
                foreach ($data["tags"] as $tag) {
                    $tag = Tag::where("tag", $tag)->first();
                    if ($tag != null) {
                        $post->tags()->attach($tag);
                    }
                }

                return Post::with(["images", "tags" => function ($query) {
                    $query->select('tag', "id", "confirmed");
                }])->where("id", $post->id)->first();;
            }
        }
    }

    /**
     * Delete an image
     */
    public function imageDelete($img)
    {

        $image = Image::where("location", $img)->first();
        if (Gate::allows("edit-post", $image->post)) {
            $path = "../public/publicImg/" . $img;
            if (File::exists($path)) {
                File::delete($path);
                $image->delete();
            }
        }
    }
    /**
     * Upload an image and add it to a post
     */
    public function imageUpload(Request $request)
    {
        $post = Post::find($request->post);
        if (Gate::allows("edit-post", $post)) {
            request()->validate([

                'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',

            ]);



            $imageName = time() . '.' . request()->image->getClientOriginalExtension();



            request()->image->move(public_path('publicImg'), $imageName);

            $image = new Image;
            $image->location = $imageName;
            $image->post_id = $request->post;
            $image->save();

            return $image;
        }
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Post $post)
    {
        $tag = $request->tag;
        $t = Tag::Where("tag", $tag)->first();
        //Update the tags
        if (Gate::allows("edit-tag", $t)) {



            if ($request->confirm == "Confirm") {
                $tagToUpdate =  $post->tags->where("tag", $tag)->first();
                $tagToUpdate->pivot->confirmed = true;
                $tagToUpdate->pivot->save();
                //$post->tags()->sync([$tag->id=>["confirmed"=>true]],false);
            } else {
                $post->tags()->detach($tag->id);
            }
            return redirect()->back();
        } else {
            return response("", 403);
        }
    }

    /**
     * Delete a post
     */
    public function destroy(Post $post)
    {

        if (Gate::allows("edit-post", $post)) {
            //Delete the images
            foreach ($post->images as $img) {
                $path = "../public/publicImg/" . $img->location;
                if (File::exists($path)) {
                    File::delete($path);
                    $img->delete();
                }
            }


            $post->delete();
        }
    }
}
