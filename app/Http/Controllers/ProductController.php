<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\ProductResource;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('attachments')->get();
        return ProductResource::collection($products);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric',
            'category_id' => 'nullable|exists:categories,id',
            'attachments.*' => 'nullable|file|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $product = Product::create([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'price' => $request->input('price'),
            'category_id' => $request->input('category_id'),
        ]);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $filePath = $file->store('attachments', 'public');
                $product->attachments()->create([
                    'file_path' => $filePath,
                ]);
            }
        }

        return response()->json(new ProductResource($product), 201);
    }

    public function show($id)
    {
        $product = Product::with('attachments')->find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return new ProductResource($product);
    }

    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }
         // note:- Indicates that the field is optional and should only be validated if it's present in the request.
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255', //partial updates.
            'description' => 'sometimes|string',
            'price' => 'sometimes|numeric',
            'category_id' => 'sometimes|exists:categories,id',
            'attachments.*' => 'nullable|file|mimes:jpg,jpeg,png,gif|max:2048', 
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $product->update([
            'title' => $request->input('title', $product->title),
            'description' => $request->input('description', $product->description),
            'price' => $request->input('price', $product->price),
            'category_id' => $request->input('category_id', $product->category_id),
        ]);

        if ($request->hasFile('attachments')) {
            $product->attachments()->each(function ($attachment) {
                Storage::disk('public')->delete($attachment->file_path);
                $attachment->delete();
            });

            foreach ($request->file('attachments') as $file) {
                $filePath = $file->store('attachments', 'public');
                $product->attachments()->create([
                    'file_path' => $filePath,
                ]);
            }
        }

        return response()->json(new ProductResource($product));
    }

    public function destroy($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $product->attachments()->each(function ($attachment) {
            Storage::disk('public')->delete($attachment->file_path);
            $attachment->delete();
        });

        $product->delete();
        return response()->json(['message' => 'Product deleted successfully']);
    }
}
