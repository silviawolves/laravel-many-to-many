@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
            <div class="card-header">{{ __('Dashboard') }}</div>

            <div class="card-body">
                @if (session('status'))
                <div class="alert alert-success" role="alert">
                    {{ session('status') }}
                </div>
                @endif


                <div><strong>Utenti registrati:</strong> {{ $users_count }}
                <a href="{{ route('admin.users.index') }}">Vedi tutti gli utenti</a>
                </div>
                <div><strong>Post scritti:</strong> {{ $posts_count }}
                <a href="{{ route('admin.posts.index') }}">Vedi tutti i post</a>
                </div>
            </div>
            </div>
        </div>
        </div>
    </div>
@endsection
