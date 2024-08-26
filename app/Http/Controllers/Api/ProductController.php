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
    // public function index(Request $request)
    // {
    //     $perPage = $request->input('per_page', 15);

    //     $products = Product::with('attachments', 'category')->paginate($perPage);

    //     return ProductResource::collection($products);
    // }


    // public function index(Request $request)
    // {
    //     $perPage = (int) $request->input('per_page', 15); 
    //     $search = $request->input('search', ''); 
    //     $category_id = $request->input('category_id');
    
    //     $query = Product::with('attachments', 'category');
    
    //     // Apply search filter
    //     if ($search) {
    //         $query->where(function ($q) use ($search) {
    //             $q->where('title', 'like', "%{$search}%")
    //               ->orWhere('description', 'like', "%{$search}%");
    //         });
    //     }
    
    //     // // Apply category filter
    //     // if ($category_id) {
    //     //     $query->where('category_id', $category_id);
    //     // }
    
    //     $products = $query->paginate($perPage);
    
    //     return ProductResource::collection($products);
    // }
    
    public function index(Request $request)
{
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
    
    return ProductResource::collection($products);
}

// public function store(StoreProductRequest $request)
// {
//     try {
//         $data = $request->only(['title', 'description', 'price', 'category_id']);
        
//         $product = Product::create($data);

//         Log::info($request) ;
//         if ($request->has('image_url')) {
//             $imageUrl = $request->input('image_url');
            
//             $product->attachments()->create(['file_path' => $imageUrl]);
//         }

//         // if ($request->hasFile('attachments')) {
//         //     foreach ($request->file('attachments') as $file) {
//         //         $filePath = $file->store('attachments', 'public');
//         //         $product->attachments()->create(['file_path' => $filePath]);
//         //     }
//         // }

//         return response()->json(new ProductResource($product), 201);

//     } catch (\Exception $e) {
//         return response()->json([
//             'success' => false,
//             'message' => 'An error occurred',
//             'error' => $e->getMessage()
//         ], 500);
//     }
// }

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
        return response()->json([
            'success' => false,
            'message' => 'An error occurred',
            'error' => $e->getMessage()
        ], 500);
    }
}



public function update(UpdateProductRequest $request, $id)
{
    try {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $data = $request->only(['title', 'description', 'price', 'category_id']);

        $product->update($data);

        if ($request->has('image_url') && $request->input('image_url')) {
            $imageUrl = $request->input('image_url');

            $product->attachments()->where('file_path', $product->image_url)->delete();

            $product->attachments()->create(['file_path' => $imageUrl]);
        }

        if ($request->hasFile('attachments')) {
            $product->attachments()->each(function ($attachment) {
                Storage::disk('public')->delete($attachment->file_path);
                $attachment->delete();
            });

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


    public function show($id)
    {
        $product = Product::with('attachments', 'category')->find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return new ProductResource($product);
    }

   
       
    public function destroy($id)
    {
        try {
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

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
