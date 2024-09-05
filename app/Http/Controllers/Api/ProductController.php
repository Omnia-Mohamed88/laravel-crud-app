<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\ProductResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{

    public function index()
    {
        $filtered = $this->filterData();
        $data = ProductResource::collection($filtered);
        return $this->respondForResource($data,"Products List");
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $product = Product::create($request->validated());
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            info($e);
            return $this->respondError($e->getMessage(),"Failed to store the product.");
        }
        return $this->respondCreated(ProductResource::make($product),'Product created successfully.');
    }

    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        try {
            $product->update($request->validated());
            if($request->has("attachments")){
                if($request->attachments["create"] && count($request->attachments["create"]))
                {
                    foreach($request->attachments["create"] as $attachment)
                    {
                        $product->attachments()->create($attachment);
                    }
                }

                if($request->attachments["delete"] && count($request->attachments["delete"]))
                {
                    //remove from disk
                    $product->attachments()->whereIn("id",$request->attachments["delete"])->get()->each->delete();
                }
            }

        }  catch (\Exception $e) {
            DB::rollback();
            info($e);
            return $this->respondError($e->getMessage(),"Failed to store the product.");
        }
        return $this->respond(ProductResource::make($product),'Product updated successfully.');
    }

    public function show(Product $product): JsonResponse
    {
        return $this->respondForResource(ProductResource::make($product),'Product Data');
    }

    public function destroy(Product $product): JsonResponse
    {
        try {
            $product->attachments()->each(function ($attachment) {
                Storage::disk('public')->delete($attachment->file_path);
                $attachment->delete();
            });

            $product->delete();

            return response()->json([
                'message' => 'Product deleted successfully.',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error deleting product:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete the product.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function filterData() {
        $query = Product::query();

        if(request()->category_id){
            $query = $query
            ->where("category_id",request()->category_id);
        }

        if(request()->search){
            $query = $query->where(function($q) {
                $q->where("title","like","%".request()->search."%")
                ->orWhere("description","like","%".request()->search."%");
            });

        }

        if(request()->price){
            $query = $query
            ->where("price","<=",request()->price);
        }

        if(request()->per_page){
         $query = $query->paginate(request()->per_page);
        }else{
            $query = $query->get();
        }

        return $query;
    }
}
