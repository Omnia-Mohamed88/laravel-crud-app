<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use Exception;
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
   

    public function index(): JsonResponse
    {
        $query = Category::query();

        if (request()->has('title')) {
            $query->whereTitle(request()->title);
        }

        if (request()->per_page){
            $query = $query->paginate(request()->per_page);
        }
        else {
            $query = $query->get();
        }
        $data = CategoryResource::collection($query);
        return $this->respondForResource($data,"Category List");
    }
 


    public function store(StoreCategoryRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try{
            $Category = Category::create($request->validated());
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            info($e);
            return $this->respondError($e->getMessage(),"Failed to store Category");
        }
        return $this->respondCreated(CategoryResource::make($Category),'Category created successfully.');

    }


    public function show(Category $category) : JsonResponse
    {
        return $this->respondForResource(CategoryResource::make($category),'Category Data');
    }

    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
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

        } catch (Exception $e) {
            DB::rollback();
            return $this->respondError($e->getMessage(), "Failed to update the category.");
        }

        return $this->respond(CategoryResource::make($category), 'Category updated successfully.');
    }

   public function destroy(Category $category): JsonResponse
   {
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
    catch (Exception $e){
        return $this->respondError($e->getMessage(), "Failed to delete the Category.");

    }

   }

   
    public function import(Request $request): JsonResponse
{
    $request->validate([
        'file' => 'required|file|mimes:xlsx,csv|max:2048',
    ]);

    try {
        Excel::import(new CategoriesImport, $request->file('file'));

        info('File imported successfully');
        return $this->respondSuccess('Categories imported successfully.');
    } catch (Exception $e) {
        info('Error importing file: ' . $e->getMessage());
        return $this->respondError($e->getMessage(), 'Failed to import categories.');
    }
}

}
