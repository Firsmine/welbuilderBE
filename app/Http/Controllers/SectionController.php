<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Page_section;
use App\Models\Section_field_value;
use App\Models\Template;
use App\Models\User;
use DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SectionController extends Controller
{
    public function store(Request $request, string $pageSlug)
    {
        $check = $this->checkAccess($pageSlug, $request->user());
        if ($check['status'] !== 200)
            return response()->json([
                'status' => 'error',
                'message' => $check['message']
            ], $check['status']);

        $data = Validator::make($request->all(), [
            'template_id' => 'required|exists:templates,id',
            'position' => 'required|integer|min:1',
        ]);
        if ($data->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid field',
                'errors' => $data->errors()
            ], 442);
        }
        $page = $check['page'];

        DB::beginTransaction();
        try {
            $template = Template::with('templateField')->find($request->template_id);
            if (!$template) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Template not found'
                ], 404);
            }

            $section = Page_section::create([
                'page_id' => $page->id,
                'template_id' => $request->template_id,
                'position' => $request->position,
                'name' => $template->name
            ]);
            foreach ($template->templateField as $field) {
                Section_field_value::create([
                    'page_section_id' => $section->id,
                    'template_field_id' => $field->id,
                    'value' => null
                ]);
            }
            DB::commit();
            $formattedFields = $template->templateField->map(function ($f) {
                return [
                    'id' => $f->id,
                    'name' => $f->name,
                    'slug' => $f->slug,
                    'type' => $f->type,
                    'value' => null
                ];
            })->values();

            $resData = $section->toArray();
            $resData['fields'] = $formattedFields;

            return response()->json([
                'status' => 'success',
                'message' => 'Section added successful',
                'data' => $resData
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Server error',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function update(Request $request, string $pageSlug, string $sectionId)
    {
        $check = $this->checkAccess($pageSlug, $request->user());
        if ($check['status'] !== 200)
            return response()->json([
                'status' => 'error',
                'message' => $check['message']
            ], $check['status']);

        $section = Page_section::where('id', $sectionId)->where('page_id', $check['page']->id)->first();
        if (!$section) {
            return response()->json([
                'success' => 'error',
                'message' => 'Not found'
            ]);
        }
        $data = Validator::make($request->all(), [
            'fields' => 'required|array',
            'fields.*.field_id' => 'required|integer|exists:template_fields,id',
            'fields.*.value' => 'nullable|string'
        ]);
        if ($data->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid field',
                'errors' => $data->errors()
            ], 442);
        }
        foreach ($request->fields as $fieldInput) {
            Section_field_value::updateOrCreate(
                [
                    'page_section_id' => $section->id,
                    'template_field_id' => $fieldInput['field_id']
                ],
                ['value' => $fieldInput['value']]
            );
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Section fields updated successful',
            'data' => $section
        ]);
    }
    public function reorder(Request $request, string $pageSlug)
    {
        $check = $this->checkAccess($pageSlug, $request->user());
        if ($check['status'] !== 200)
            return response()->json([
                'status' => 'error',
                'message' => $check['message']
            ], $check['status']);
        $data = Validator::make($request->all(), [
            'section_ids' => 'required|array',
            'section_ids.*' => 'integer|exists:page_sections,id'
        ]);
        if ($data->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid field',
                'errors' => $data->errors()
            ], 442);
        }
        foreach ($request->section_ids as $index => $secId) {
            Page_section::where('id', $secId)
                ->where('page_id', $check['page']->id)
                ->update([
                    'position' => $index + 1
                ]);
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Sections reordered successful'
        ], 200);
    }
    public function destroy(Request $request, string $pageSlug, string $sectionId)
    {
        $check = $this->checkAccess($pageSlug, $request->user());
        if ($check['status'] !== 200)
            return response()->json([
                'status' => 'error',
                'message' => $check['message']
            ], $check['status']);

        $section = Page_section::where('id', $sectionId)->where('page_id', $check['page']->id)->first();
        if (!$section) {
            return response()->json([
                'success' => 'error',
                'message' => 'Not found'
            ], 404);
        }
        $section->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Section removed successful'
        ], 200);
    }

    private function checkAccess($slug, $user)
    {
        $page = Page::where('slug', $slug)->first();

        if (!$page)
            return [
                'status' => 404,
                'message' => 'Not found'
            ];
        if ($page->user_id !== $user->id)
            return [
                'status' => 403,
                'message' => 'Forbidden access'
            ];
        return [
            'status' => 200,
            'page' => $page
        ];
    }
}
