<?php

namespace App\Http\Controllers;

use App\Http\Requests\Post\CreatePostRequest;
use App\Http\Requests\Post\UpdatePostRequest;
use App\Repositories\PostRepository;
use Illuminate\Http\Request;

class PostController extends Controller
{

    public function __construct(PostRepository $postRepository)
    {
        $this->repository = $postRepository;
        $this->requests = [
            'create' => new CreatePostRequest,
            'update' => new UpdatePostRequest
        ];
    }

    
}
