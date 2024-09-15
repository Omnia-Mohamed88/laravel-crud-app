<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\ProductResource;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{

    public function index(): JsonResponse
    {
        // $filtered = $this->filterData();
        $filtered = Product::with('category')->get();
        $data = ProductResource::collection($filtered);
        return $this->respondForResource($data, "Products List");
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $product = Product::create($request->validated());
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            info($e);
            return $this->respondError($e->getMessage(), "Failed to store the product.");
        }
        return $this->respondCreated(ProductResource::make($product), 'Product created successfully.');
    }

    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        try {
            $product->update($request->validated());
            if ($request->has('attachments')) {
                if ($request->attachments["create"] && count($request->attachments["create"])) {
                    foreach ($request->attachments["create"] as $attachment) {
                        $product->attachments()->create($attachment);
                    }
                }

                if ($request->attachments["delete"] && count($request->attachments["delete"])) {
                    $attachmentsToDelete = $product->attachments()->whereIn('id', $request->attachments["delete"])->get();

                    foreach ($attachmentsToDelete as $attachment) {
                        $filePath = str_replace(config('app.url') . '/storage/', '', $attachment->file_path);
                        Storage::disk('public')->delete($filePath);
                        $attachment->delete();
                    }
                }
            }
        } catch (Exception $e) {
            DB::rollback();
            return $this->respondError($e->getMessage(), "Failed to update the product.");
        }

        return $this->respond(ProductResource::make($product), 'Product updated successfully.');
    }


    public function show(Product $product): JsonResponse
    {
        $product->load('category');
        return $this->respondForResource(ProductResource::make($product), 'Product Data');
    }
    

    public function destroy(Product $product): JsonResponse
    {
        try {
            if ($product->attachments()->exists()) {
                $product->attachments->each(function ($attachment) {
                    $filePath = str_replace(config('app.url') . '/storage/', '', $attachment->file_path);

                    Storage::disk('public')->delete($filePath);

                    $attachment->delete();
                });
            }
            $product->delete();

            return $this->respondSuccess("Product deleted successfully.");
        } catch (Exception $e) {
            return $this->respondError($e->getMessage(), "Failed to delete the product.");
        }
    }


    public function filterData()
    {
        $query = Product::query();

        if (request()->category_id) {
            $query = $query
                ->where("category_id", request()->category_id);
        }

        if (request()->search) {
            $query = $query->where(function ($q) {
                $q->where("title", "like", "%" . request()->search . "%")
                    ->orWhere("description", "like", "%" . request()->search . "%");
            });

        }

        if (request()->price) {
            $query = $query
                ->where("price", "<=", request()->price);
        }

        if (request()->per_page) {
            $query = $query->paginate(request()->per_page);
        } else {
            $query = $query->get();
        }

        return $query;
    }
}
