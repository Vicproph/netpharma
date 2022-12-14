<?php

namespace App\Http\Controllers;

use App\Http\Requests\Post\CreatePostRequest;
use App\Http\Requests\Post\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Repositories\PostRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{

    public function __construct(
        PostRepository $postRepository,
    )
    {
        $this->repository = $postRepository;
        $this->resource = new PostResource(null);
        $this->requests = [
            'create' => new CreatePostRequest,
            'update' => new UpdatePostRequest
        ];
    }

    public function store()
    {
        request()['user_id'] = Auth::id();
        return parent::store();
    }
}
