<?php
namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\ProductResource;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('attachments')->get();
        return ProductResource::collection($products);
    }

    public function store(StoreProductRequest $request)
    {
        try {
            // Create the product using validated data
            $product = Product::create($request->only(['title', 'description', 'price', 'category_id']));

            // Handle file attachments
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $filePath = $file->store('attachments', 'public');
                    $product->attachments()->create(['file_path' => $filePath]);
                }
            }

            return response()->json(new ProductResource($product), 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $product = Product::with('attachments')->find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return new ProductResource($product);
    }

    public function update(UpdateProductRequest $request, $id)
    {
        try {
            $product = Product::find($id);

            if (!$product) {
                return response()->json(['message' => 'Product not found'], 404);
            }

            // Update the product with validated data
            $product->update($request->only(['title', 'description', 'price', 'category_id']));

            // Handle file attachments
            if ($request->hasFile('attachments')) {
                // Delete old attachments
                $product->attachments()->each(function ($attachment) {
                    Storage::disk('public')->delete($attachment->file_path);
                    $attachment->delete();
                });

                // Store new attachments
                foreach ($request->file('attachments') as $file) {
                    $filePath = $file->store('attachments', 'public');
                    $product->attachments()->create(['file_path' => $filePath]);
                }
            }

            return response()->json(new ProductResource($product));

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $product = Product::find($id);

            if (!$product) {
                return response()->json(['message' => 'Product not found'], 404);
            }

            // Delete attachments
            $product->attachments()->each(function ($attachment) {
                Storage::disk('public')->delete($attachment->file_path);
                $attachment->delete();
            });

            $product->delete();
            return response()->json(['message' => 'Product deleted successfully']);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
