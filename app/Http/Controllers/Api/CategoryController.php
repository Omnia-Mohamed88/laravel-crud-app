<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\CategoryResource;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\CategoriesImport;
use OpenApi\Annotations as OA;

class CategoryController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/categories",
     *     summary="Get a list of categories",
     *     tags={"Category"},
     *     @OA\Response(
     *         response=200,
     *         description="A list of categories",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="title", type="string", example="Technology"),
     *                 @OA\Property(
     *                     property="attachments",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="file_path", type="string", example="/path/to/file.jpg")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Server error")
     *         )
     *     )
     * )
     */
    public function index()
    {
        $categories = Category::with('attachments')->get();
        return CategoryResource::collection($categories);
    }

    /**
     * @OA\Post(
     *     path="/api/categories",
     *     summary="Create a new category",
     *     tags={"Category"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="New Category"),
     *             @OA\Property(
     *                 property="attachments",
     *                 type="array",
     *                 @OA\Items(
     *                     type="string",
     *                     format="binary"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Category created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="title", type="string", example="New Category"),
     *             @OA\Property(
     *                 property="attachments",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="file_path", type="string", example="/path/to/file.jpg")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Server error")
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/categories/{id}",
     *     summary="Get a single category by ID",
     *     tags={"Category"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category details",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="title", type="string", example="Technology"),
     *             @OA\Property(
     *                 property="attachments",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="file_path", type="string", example="/path/to/file.jpg")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Category not found")
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        $category = Category::with('attachments')->find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        return new CategoryResource($category);
    }

    /**
     * @OA\Put(
     *     path="/api/categories/{id}",
     *     summary="Update a category by ID",
     *     tags={"Category"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="Updated Category"),
     *             @OA\Property(
     *                 property="attachments",
     *                 type="array",
     *                 @OA\Items(
     *                     type="string",
     *                     format="binary"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="title", type="string", example="Updated Category"),
     *             @OA\Property(
     *                 property="attachments",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="file_path", type="string", example="/path/to/file.jpg")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Category not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Server error")
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Delete(
     *     path="/api/categories/{id}",
     *     summary="Delete a category by ID",
     *     tags={"Category"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Category deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Category not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Server error")
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Post(
     *     path="/api/categories/import",
     *     summary="Import categories from a file",
     *     tags={"Category"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="file",
     *                 type="string",
     *                 format="binary"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Categories imported successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Categories imported successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Error importing file: some error message")
     *         )
     *     )
     * )
     */
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
