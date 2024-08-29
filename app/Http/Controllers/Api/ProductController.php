<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\ProductResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 15);
            
            $search = $request->input('search', '');
            $category_id = $request->input('category_id');

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
                'message' => 'Products retrieved successfully.',
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

    public function store(StoreProductRequest $request)
    {
        try {
            $data = $request->only(['title', 'description', 'price', 'category_id']);
            $product = Product::create($data);

            if ($request->has('image_url') && $request->input('image_url')) {
                $imageUrl = $request->input('image_url');
                $product->attachments()->create(['file_path' => $imageUrl]);
            }

            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $filePath = $file->store('attachments', 'public');
                    $product->attachments()->create(['file_path' => $filePath]);
                }
            }

            return response()->json(new ProductResource($product), 201);

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

    public function update(UpdateProductRequest $request, $id)
    {
        try {
            $product = Product::findOrFail($id);

            $data = $request->only(['title', 'description', 'price', 'category_id']);
            $product->update($data);

            $oldAttachments = $product->attachments;
            foreach ($oldAttachments as $attachment) {
                $filePath = str_replace('storage/', '', $attachment->file_path);
                Storage::disk('public')->delete($filePath);
                $attachment->delete();
            }

            if ($request->has('image_url') && $request->input('image_url')) {
                $imageUrl = $request->input('image_url');
                $product->attachments()->create(['file_path' => $imageUrl]);
            }

            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $filePath = $file->store('attachments', 'public');
                    $product->attachments()->create(['file_path' => $filePath]);
                }
            }

            return response()->json(new ProductResource($product->load('attachments')), 200);
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

    public function show($id)
    {
        try {
            $product = Product::with('attachments', 'category')->find($id);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Product details retrieved successfully.',
                'data' => new ProductResource($product)
            ], 200);
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

    public function destroy($id)
    {
        try {
            $product = Product::find($id);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found.'
                ], 404);
            }

            $product->attachments()->each(function ($attachment) {
                Storage::disk('public')->delete($attachment->file_path);
                $attachment->delete();
            });

            $product->delete();

            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully.'
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
