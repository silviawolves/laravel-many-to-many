<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Post;
use App\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PostController extends Controller
{
    private function findBySlug($slug)
    {
        $post = Post::where('slug', $slug)->first();

        if (!$post) {
            abort(404);
        }
        return $post;
    }

    private function generateSlug($text)
    {
        $toReturn = null;
        $counter = 0;

        do {
            $slug = Str::slug($text);

            if ($counter > 0) {
                $slug .= "-" . $counter;
            }

            $slug_esiste = Post::where('slug', $slug)->first();

            if ($slug_esiste) {
                $counter++;
            } else {
                $toReturn = $slug;
            }
        } while ($slug_esiste);

        return $toReturn;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            $posts = Post::orderBy('created_at', 'desc')->get();
        } else {
            $posts = $user->posts;
        }

        return view('admin.posts.index', compact('posts'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $tags = Tag::all();
        return view('admin.posts.create', compact('tags'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|min:10',
            'content' => 'required|min:10',
            'tags' => 'nullable|exists:tags,id',
            'cover_img' => 'required|image'
        ]);

        $post = new Post();
        $post->fill($validatedData);
        $post->user_id = Auth::user()->id;

        $coverImg = Storage::put('/post_covers', $validatedData['cover_img']);
        $post->cover_img = $coverImg;

        $post->slug = $this->generateSlug($post->title);
        $post->save();

        if (key_exists('tags', $validatedData)) {
            $post->tags()->attach($validatedData['tags']);
        }

        return redirect()->route('admin.posts.show', $post->slug);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($slug)
    {
        $post = $this->findBySlug($slug);
        return view('admin.posts.show', compact('post'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($slug)
    {
        $post = $this->findBySlug($slug);
        $tags = Tag::all();

        return view('admin.posts.edit', compact('post', 'tags'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $slug)
    {
        $validatedData = $request->validate([
            'title' => 'required|min:10',
            'content' => 'required|min:10',
            'tags' => 'nullable|exists:tags,id',
            'cover_img' => 'nullable|image'
        ]);
        $post = $this->findBySlug($slug);

        if (key_exists('cover_img', $validatedData)) {
            if ($post->cover_img) {
                Storage::delete($post->cover_img);
            }
            $coverImg = Storage::put('/post_covers', $validatedData['cover_img']);
            $post->cover_img = $coverImg;
        }

        if ($validatedData['title'] !== $post->title) {
            $post->slug = $this->generateSlug($validatedData['title']);
        }

        if (key_exists('tags', $validatedData)) {
            $post->tags()->sync($validatedData['tags']);
        } else {
            $post->tags()->sync([]);
        }

        $post->update($validatedData);
        return redirect()->route('admin.posts.show', $post->slug);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($slug)
    {
        $post = $this->findBySlug($slug);
        $post->tags()->detach();
        $post->delete();
        return redirect()->route('admin.posts.index');
    }
}