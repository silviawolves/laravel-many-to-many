<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Post;
use App\User;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth');
    }

    public function index()
    {
        $usersCount = User::count();
        $postsCount = Post::count();
        return view('admin.index', [
            'users_count' => $usersCount,
            'posts_count' => $postsCount
        ]);
    }

    public function test()
    {
        return 'pagina admin senza autenticazione';
    }
}
