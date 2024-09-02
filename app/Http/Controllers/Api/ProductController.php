<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\ProductResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->per_page ?? 15;
            $search = $request->search ?? '';
            $category_id = $request->category_id;

            $query = Product::with('attachments', 'category');

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }
            
            if ($category_id) {
                $query->where('category_id', $category_id);
            }
            
            $products = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Products List',
                'data' => ProductResource::collection($products),
                'pagination' => [
                    'total' => $products->total(),
                    'count' => $products->count(),
                    'per_page' => $products->perPage(),
                    'current_page' => $products->currentPage(),
                    'total_pages' => $products->lastPage(),
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error fetching products:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch products.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        try {
            $data = $request->validated(); 

            $product = Product::create($data);

            if ($request->image_url && is_array($request->image_url)) {
                foreach ($request->image_url as $imageUrl) {
                    $product->attachments()->create(['file_path' => $imageUrl]);
                }
            }

            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $filePath = $file->store('attachments', 'public');
                    $product->attachments()->create(['file_path' => $filePath]);
                }
            }

            return response()->json([
                'message' => 'Product created successfully.',
                'data' => new ProductResource($product)
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error storing product:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to store the product.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(UpdateProductRequest $request, $id): JsonResponse
    {
        try {
            $product = Product::findOrFail($id);

            $data = $request->validated(); 

            $product->update($data);

            $oldAttachments = $product->attachments;
            foreach ($oldAttachments as $attachment) {
                $filePath = str_replace('storage/', '', $attachment->file_path);
                Storage::disk('public')->delete($filePath);
                $attachment->delete();
            }

            if ($request->image_url && is_array($request->image_url)) {
                foreach ($request->image_url as $imageUrl) {
                    $product->attachments()->create(['file_path' => $imageUrl]);
                }
            }

            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $filePath = $file->store('attachments', 'public');
                    $product->attachments()->create(['file_path' => $filePath]);
                }
            }

            return response()->json([
                'message' => 'Product updated successfully.',
                'data' => new ProductResource($product->load('attachments'))
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found.'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error updating product:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update the product.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $product = Product::with('attachments', 'category')->findOrFail($id);

            return response()->json([
                'message' => 'Product details retrieved successfully.',
                'data' => new ProductResource($product)
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found.'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error fetching product details:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch product details.',
                'error' => $e->getMessage()
            ], 500);
        }
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
}
