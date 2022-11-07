<?php

namespace App\Http\Controllers;

use App\Http\Requests\Request;
use App\Repositories\Repository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;

/**
 * @property Request[] $requests
 */

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected Repository $repository;
    protected JsonResource $resource;

    protected  $requests = [ // each form request for each action is here
        'index' => null,
        'show' => null,
        'create' => null,
        'update' => null,
        'delete' => null
    ];

   

    public function index()
    {
        $user = Auth::user();
        if ($user->can('viewAny', $this->repository->model)) {
            return $this->resource->collection($this->repository->getAll());
        } else
            return response()->json(['message' => __('resource.actionUnauthorized')], 403);
    }

    public function show(int $id)
    {
        $user = Auth::user();
        $model = $this->repository->get($id);

        if (!$model)
            return response()->json(['message' => __('resource.notFound')], 404);

        if ($user->can('view', $model)) {

            return $this->resource->make($model);

        } else
            return response()->json(['message' => __('resource.actionUnauthorized')], 403);
    }

    public function destroy(int $id)
    {
        $user = Auth::user();
        $model = $this->repository->get($id);

        if (!$model)
            return response()->json(['message' => __('resource.notFound')], 404);

        if ($user->can('delete', $model)) {

            $this->repository->delete($id);
            return response()->json(['message' => __('resource.deleted')]);
        } else
            return response()->json(['message' => __('resource.actionUnauthorized')], 403);
    }

    public function store()
    {
        request()->validate($this->requests['create']->rules());

        $attributes = $this->getFormInputs();
        /**
         * @var Model $model
         */
        $model = $this->repository->create($attributes);
        $model = $model->load($model->getAllRelationships());
        
        return $this->resource->make($model);
    }

    public function update(int $id)
    {
        request()->validate($this->requests['update']->rules());

        $user = Auth::user();
        $model = $this->repository->get($id);

        if (!$model)
            return response()->json(['message' => __('resource.notFound')], 404);

        if ($user->can('update', $model)) {
            $attributes = $this->getFormInputs();
            /**
             * @var Model $model
             */
            $model = $this->repository->update($id, $attributes);

            $model = $model->load($model->getAllRelationships());

            return $this->resource->make($model);

        } else
            return response()->json(['message' => __('resource.actionUnauthorized')], 403);
    }

    protected function getFormInputs()
    {
        $keys = $this->repository->model->getFillable();
        $attributes = request()->only($keys);
        return $attributes;
    }
}
