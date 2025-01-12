<?php

namespace App\Http\Controllers\Api;

//import model post
use App\Models\Post;

use App\Http\Controllers\Controller;

//import resource PostSource
use App\Http\Resources\PostResource;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    //
    public function index(){
            //get all post
        $post = Post::latest()->paginate(5);

        //return collection of posts as a resource
        return new PostResource(true, 'List Data Post',$post);
    }

    public function store(Request $request){
        //define validation rules
        $validator = Validator::make($request->all(), [
            'image'     => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:10000',
            'title'     => 'required',
            'content'   => 'required',
        ]);

        //check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //upload image
        $image = $request->file('image');
        $image->storeAs('public/posts', $image->hashName());

        //create post
        $post = Post::create([
            'image'     => $image->hashName(),
            'title'     => $request->title,
            'content'   => $request->content
        ]);

        return new PostResource(true,'Data Berhasil Ditambahkan',$post);
    }

    public function show($id){
        $post = Post::find($id);

        return new PostResource(true,'Detail Data Post',$post);
    }

    public function update(Request $request, $id){
        $validator = Validator::make($request->all(), [
            'title'     => 'required',
            'content'   => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 0);
        }

        $post = Post::find($id);

        if($request->hasFile('image')){
            //upload image
            $image = $request->file('image');
            $image->storeAs('public/posts', $image->hashName());

            //delete old image
            Storage::delete('public/posts/'.basename($post->image));
            $post->update([
                    'image'     => $image->hashName(),
                    'title'     => $request->title,
                    'content'   => $request->content
            ]);
        }else{
            $post->update([
                'title'     => $request->title,
                'content'   => $request->content
            ]);
        }

        return new PostResource(true,'Data Berhasil Diupdate',$post);
    }

    public function destroy($id){
        $post = Post::find($id);
        Storage::delete('public/post'.basename($post->image));
        $post->delete();

        return new PostResource(true,'Data Berhasil Dihapus',null);
    }
}
