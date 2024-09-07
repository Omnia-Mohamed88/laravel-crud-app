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
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;



class CategoryController extends Controller
{
     /**
     * @OA\Get(
     *     path="/api/categories",
     *     summary="Get a list of categories",
     *     tags={"Category"},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number for pagination",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A paginated list of categories",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="current_page", type="integer", example=1),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="title", type="string", example="Technology"),
     *                     @OA\Property(
     *                         property="attachments",
     *                         type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="file_path", type="string", example="/path/to/file.jpg")
     *                         )
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="first_page_url", type="string", example="http://example.com/api/categories?page=1"),
     *             @OA\Property(property="last_page_url", type="string", example="http://example.com/api/categories?page=5"),
     *             @OA\Property(property="next_page_url", type="string", example="http://example.com/api/categories?page=2"),
     *             @OA\Property(property="prev_page_url", type="string", example="http://example.com/api/categories?page=1"),
     *             @OA\Property(property="total", type="integer", example=100)
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
   
    public function index(){
        $query = Category::query();
        if (request()->per_page){
            $query = $query->paginate(request()->per_page);
        }
        else {
            $query = $query->get();
        }
        $data = CategoryResource::collection($query);
        return $this->respondForResource($data,"Category List");
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
   

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try{
            $Category = Category::create($request->validated());
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            info($e);
            return $this->respondeError($e->getMessage(),"Faild to store Category");
        }
        return $this->respondCreated(CategoryResource::make($Category),'Category created successfully.');

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
    public function show(Category $category) : JsonResponse
    {
        return $this->respondForResource(CategoryResource::make($category),'Category Data');
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
    public function update(StoreCategoryRequest $request, Category $category): JsonResponse
    {
        DB::beginTransaction();
        
        try {
            $category->update($request->validated());
    
            if ($request->has('attachments')) {
                if (!empty($request->attachments["create"])) {
                    foreach ($request->attachments["create"] as $attachment) {
                        $category->attachments()->create($attachment);
                    }
                }
    
                if (!empty($request->attachments["delete"])) {
                    $attachmentsToDelete = $category->attachments()->whereIn('id', $request->attachments["delete"])->get();
                    
                    foreach ($attachmentsToDelete as $attachment) {
                        $filePath = str_replace(config('app.url') . '/storage/', '', $attachment->file_path);
                        Storage::disk('public')->delete($filePath);
                        $attachment->delete();
                    }
                }
            }
    
            DB::commit();
    
        } catch (\Exception $e) {
            DB::rollback();
            return $this->respondError($e->getMessage(), "Failed to update the category.");
        }
    
        return $this->respond(CategoryResource::make($category), 'Category updated successfully.');
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
   public function destroy(Category $category){
    try {
        if ($category->attachments()->exists()){
            $category->attachments->each(function ($attachment){
                $filePath = str_replace(config('app.url') . '/storage/', '', $attachment->file_path);
                    
                Storage::disk('public')->delete($filePath);
                
                $attachment->delete();
            });

        }
        $category->delete();
        return $this->respondSuccess("category deleted successfully.");


    }
    catch (\Exception $e){
        return $this->respondError($e->getMessage(), "Failed to delete the Category.");

    }

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
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv|max:2048',
        ]);

        try {
            Excel::import(new CategoriesImport, $request->file('file'));
            
            \Log::info('File imported successfully');
            return response()->json(['message' => 'Categories imported successfully'], 201);
        } catch (\Exception $e) {
            \Log::error('Error importing file: ' . $e->getMessage());
            return response()->json(['error' => 'Error importing file: ' . $e->getMessage()], 500);
        }
    }
}
