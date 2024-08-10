<?php
namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\CategoryResource;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\CategoriesImport;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with('attachments')->get();
        return CategoryResource::collection($categories);
    }

    public function store(StoreCategoryRequest $request)
    {
        // Access validated data
        $validated = $request->validated();

        // Create the category
        $category = Category::create([
            'title' => $validated['title'],
        ]);

        // Handle attachments if any
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

    public function update(StoreCategoryRequest $request, $id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        // Access validated data
        $validated = $request->validated();

        // Update the category title if present
        if ($request->has('title')) {
            $category->update(['title' => $validated['title']]);
        }

        // Handle attachments
        if ($request->hasFile('attachments')) {
            // Delete existing attachments
            $category->attachments()->each(function ($attachment) {
                Storage::disk('public')->delete($attachment->file_path);
                $attachment->delete();
            });

            // Store new attachments
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

        // Delete attachments
        $category->attachments()->each(function ($attachment) {
            Storage::disk('public')->delete($attachment->file_path);
            $attachment->delete();
        });

        // Delete the category
        $category->delete();
        return response()->json(['message' => 'Category deleted successfully']);
    }

    public function import(Request $request)
    {
        // Validate that a file is provided and it's of the correct type
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv|max:2048',
        ]);

        try {
            // Import the file using Laravel Excel
            Excel::import(new CategoriesImport, $request->file('file'));
            
            // Log success message
            \Log::info('File imported successfully');
            return response()->json(['message' => 'Categories imported successfully'], 201);
        } catch (\Exception $e) {
            // Log the error
            \Log::error('Error importing file: ' . $e->getMessage());
            return response()->json(['error' => 'Error importing file: ' . $e->getMessage()], 500);
        }
    }
}
