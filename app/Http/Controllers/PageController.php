<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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
    public function show(Request $request, string $pageSlug)
    {
        $page = Page::with(['section.template', 'section.fieldValues.templateFields'])->where('slug', $pageSlug)->first();

        if (!$page) {
            return response()->json([
                'status' => 'error',
                'message' => 'Not found'
            ]);
        }
        if ($page->user_id !== $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Forbidden access'
            ]);
        }
        $formattedSections = $page->section->map(function ($section) {
            return [
                'id' => $section->id,
                'position' => $section->position,
                'template' => $section->template,
                'field' => $section->template->field->map(function ($templateField) use ($section) {
                    $val = $section->fieldValues->where('template_field_id', $templateField->id)->first();
                    return [
                        'id' => $templateField->id,
                        'name' => $templateField->name,
                        'slug' => $templateField->slug,
                        'type' => $templateField->type,
                        'value' => $templateField->value
                    ];
                })->value()
            ];
        });
        $pageData = $page->toArray();
        $pageData['section'] = $formattedSections;

        return response()->json([
            'status' => 'success',
            'message' => 'Get page successful',
            'data' => $formattedSections
        ]);
    }
    public function update(Request $request, string $pageSlug)
    {
        $page = Page::where('slug', $pageSlug)->first();
        if (!$pageSlug) {
            return response()->json([
                'status' => 'error',
                'message' => 'Not found'
            ]);
        }
        if ($page->user_id !== $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Forbidden access'
            ]);
        }

        $data = Validator::make($request->all(), [
            'title' => 'sometimes|string',
            'slug' => ['sometimes', 'string', 'regex:/^[a-z-0-9-]+$/', Rule::unique('pages')->ignore($page->id)],
            'summary' => 'nullable|string'
        ]);
        if ($data->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid field',
                'errors' => $data->errors()
            ]);
        }

        $page->update($data->validated());
        return response()->json([
            'status' => 'success',
            'message' => 'Page updated successful',
            'data' => $page
        ]);
    }
    public function destroy(Request $request, string $pageSlug)
    {
        $page = Page::where('slug', $pageSlug)->first();

        if (!$pageSlug) {
            return response()->json([
                'status' => 'error',
                'message' => 'Not found'
            ]);
        }
        if ($page->user_id !== $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Forbidden access'
            ]);
        }

        $page->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Page deleted successful'
        ]);
    }
}
