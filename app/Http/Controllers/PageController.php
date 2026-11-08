<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Validation\Validator;

class PageController extends Controller
{
    public function index(Request $request)
    {
        $pages = $request->user()->page;
        return response()->json([
            'status' => 'success',
            'message' => 'Get all pages successful',
            'data' => ['pages' => $pages]
        ]);
    }
    public function store(Request $request)
    {
        $data = Validator::make($request->all(), [
            'title' => 'required|string',
            'slug' => 'required|string|regex:/^[a-z-0-9-]+$/',
            'summary' => 'nullable|string'
        ]);
        if ($data->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid field',
                'errors' => $data->errors()
            ]);
        }
        $page = $request->user()->page()->create($data->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Page created successful',
            'data' => $page
        ]);
    }
    public function show(Request $request, string $slug)
    {
        //
    }
    public function update(Request $request, string $slug)
    {
        //
    }
    public function destroy(Request $request, string $slug)
    {
        //
    }
}
