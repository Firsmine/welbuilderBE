<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use App\Models\Page_section;

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
        // $page = Page::with(['section.template.templateField', 'section.sectionField'])->where('slug', $pageSlug)->first();

        // if (!$page) {
        //     return response()->json([
        //         'status' => 'error',
        //         'message' => 'Not found'
        //     ]);
        // }
        // if ($page->user_id !== $request->user()->id) {
        //     return response()->json([
        //         'status' => 'error',
        //         'message' => 'Forbidden access'
        //     ]);
        // }
        // $formattedSections = $page->section->map(function ($section) {
        //     return [
        //         'id' => $section->id,
        //         'position' => $section->position,
        //         'template' => $section->template,
        //         'field' => $section->template->templateField->map(function ($templateField) use ($section) {
        //             $val = $section->sectionField->where('template_field_id', $templateField->id)->first();
        //             return [
        //                 'id' => $templateField->id,
        //                 'name' => $templateField->name,
        //                 'slug' => $templateField->slug,
        //                 'type' => $templateField->type,
        //                 'value' => $val ? $val->value : null
        //             ];
        //         })->values()
        //     ];
        // });

        // return response()->json([
        //     'status' => 'success',
        //     'message' => 'Get page successful',
        //     'data' => $formattedSections
        // ], 200);

        $page = Page::where('slug', $pageSlug)->first();

        if (!$page) {
            return response()->json([
                'error' => 'Page not found'
            ], 404);
        }
        $sections = Page_section::where('page_id', $page->id)->get();

        return response()->json([
            'debug' => [
                'page_id' => $page->id,
                'page_slug' => $page->slug,
                'sections_count' => $sections->count(),
                'sections' => $sections
            ]
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
