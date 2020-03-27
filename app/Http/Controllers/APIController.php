<?php

namespace App\Http\Controllers;

use App\Models\Root;
use Illuminate\Http\Request;

class APIController extends Controller
{
    protected $model;
    protected $filter_input = [];

    public function __construct()
    {
    }

    public function index(Request $request)
    {
        $total_size = PHP_INT_MAX - 1;
        $page_size = 10;
        if ($request->has('limit')) {
            $page_size = $request->input('limit');
        }
        $query = $this->model;
        $query = $query->take($total_size)->orderBy('created_at', 'DESC');
        foreach ($request->except($this->filter_input) as $key => $value) {
            switch ($key) {
                case 'q':
                    $query = $this->model->applySearchQuery($query, $value);
                    break;
                case 'p':
                case 'l':
                    break;
                case 'with':
                    $with = explode(',', $value);
                    $query = $query->with($with);
                    break;
                case 'o':
                    $parts = explode(',', $value);
                    if (count($parts) == 2) {
                        $column =  $parts[0];
                        $order =  $parts[1];
                        $query = $query->orderBy($column, $order);
                    }
                    break;
                case 'g':
                    $query->groupBy($value);
                    break;
                case 'd':
                    $query->withTrashed();
                    break;
                default:
                    $parts = explode(',', $value);
                    if (count($parts) === 1) {
                        $query->where($key, '=', $value);
                    } else {
                        $query->where($key, '>=', $parts[0])->where($key, '<=', $parts[1]);
                    }
                    break;
            }
        }
        $total_count = $query->get()->count();

        if ($request->has('p')) {
            $query->skip(($request->p - 1) * ($request->has('l') ? $request->l : $page_size));
        }

        if ($request->has('l')) {
            $query->take($request->l);
        } else {
            $query->take($total_size);
        }

        $query_items = $query->get();
        $page_count = $query_items->count();

        return [
            'total_count' => $total_count,
            'page_count' => $page_count,
            'items' => $query_items
        ];
    }

    /*
        Get a single resource
    */
    public function show(Request $request, $uuid)
    {
        if ($this->isValidUUID($uuid)) {
            $this->model = $this->model->where('uuid', $uuid);
        } else {
            $this->model = $this->model->whereSlug($uuid);
        }

        if ($request->has('with')) {
            $with = $request['with'];
            $with = explode(',', $with);
            $this->model = $this->model->with($with);
        }
        $model = $this->model->first();

        $this->authorize('show', $model);
        if (empty($model->uuid)) {
            app()->abort(404, "Resource does not exist");
        }

        
        return $model;
    }

    /*
        Store a resource
    */
    public function store(Request $request)
    {
        $this->authorize('store', $this->model);
        $record = $this->model->create($request->except($this->filter_input));
        if (!$record->uuid) {
            app()->abort(500, "The operation requested couldn't be completed");
        }

        return $record;
    }

    /*
        Update a resource
    */
    public function update(Request $request, $uuid)
    {
        $model = $this->model->find($uuid);
        if (empty($model->uuid)) {
            app()->abort(404, "Resource does not exist");
        }
        $this->authorize('update', $model);
        $model->fill($request->except($this->filter_input));
        $model->save();

        return $this->model->find($uuid);
    }

    public function destroy(Request $request, $uuid)
    {
        $model = $this->model->find($uuid);
        if (empty($model->uuid)) {
            app()->abort(404, "Resource does not exist");
        }
        $this->authorize('destroy', $model);
        $response = array('success' => false);
        $response["success"] = $model->delete();
        return $response;
    }

    protected function isValidUuid($uuid)
    {
        if (!is_string($uuid) || (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $uuid) !== 1)) {
            return false;
        }
        return true;
    }
}