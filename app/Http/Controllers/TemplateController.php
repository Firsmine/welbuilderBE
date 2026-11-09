<?php

namespace App\Http\Controllers;

use App\Models\Template;
use Illuminate\Http\Request;

class TemplateController extends Controller
{
    public function index()
    {
        $templates = Template::with('templateField')->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Get all template successful',
            'data' => [
                'templates' => $templates
            ]
        ], 200);
    }
    public function show(Request $request, string $templateSlug)
    {
        $template = Template::with('templateField')->where('slug', $templateSlug)->first();

        if (!$template) {
            return response()->json([
                'status' => 'error',
                'message' => 'Not found'
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Get template successful',
            'data' => $template
        ]);
    }
}
