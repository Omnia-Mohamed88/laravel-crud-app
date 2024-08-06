<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\CategoryResource; 

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with('attachments')->get();
        // return response()->json($categories);
        return CategoryResource::collection($categories);

    }

    
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'attachments.*.file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422); // invalid data
        }

        $category = Category::create([
            'title' => $request->input('title'),
        ]);

        if ($request->hasFile('attachments')) { 
            foreach ($request->file('attachments') as $file) {
                $filePath = $file->store('attachments', 'public');
                $category->attachments()->create([
                    'file_path' => $filePath,
                ]);
            }
        }

        return response()->json($category, 201);
    }

    public function show($id)
    {
        $category = Category::with('attachments')->find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        return new CategoryResource($category); 
    }

    public function update(Request $request, $id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'attachments.*.file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $category->update([
            'title' => $request->input('title'),
        ]);

        if ($request->hasFile('attachments')) {
            $category->attachments()->each(function ($attachment) {
                Storage::disk('public')->delete($attachment->file_path);
                $attachment->delete();
            });

            foreach ($request->file('attachments') as $file) {
                $filePath = $file->store('attachments', 'public');
                $category->attachments()->create([
                    'file_path' => $filePath,
                ]);
            }
        }

        return response()->json($category);
    }

    public function destroy($id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $category->attachments()->each(function ($attachment) {
            Storage::disk('public')->delete($attachment->file_path);
            $attachment->delete();
        });

        $category->delete();
        return response()->json(['message' => 'Category deleted successfully']);
    }
}
