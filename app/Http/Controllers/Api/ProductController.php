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
use OpenApi\Annotations as OA;


/**
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="JWT Authorization header using the Bearer scheme."
 * )
 */
class ProductController extends Controller
{


    /**
     * @OA\Get(
     *     path="/api/products",
     *     tags={"Products"},
     *     summary="Get list of products",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Product")
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        $filtered = $this->filterData();
        $data = ProductResource::collection($filtered);
        return $this->respondForResource($data, "Products List");
    }


 /**
     * @OA\Post(
     *     path="/api/products",
     *     tags={"Products"},
     *     summary="Create a new product",
     *     operationId="createProduct",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="Sample Products testtt34"),
     *             @OA\Property(property="description", type="string", example="This is a sample product."),
     *             @OA\Property(property="price", type="number", format="float", example=99.99),
     *             @OA\Property(property="category_id", type="integer", example=1),
     *             @OA\Property(
     *                 property="attachments",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="file_path", type="string", example="http://localhost:8000/storage/attachments/20240913082706-skincarecategory.jpg")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Product created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Product created successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=4),
     *                 @OA\Property(property="title", type="string", example="Sample Products testtt34"),
     *                 @OA\Property(property="description", type="string", example="This is a sample product."),
     *                 @OA\Property(property="price", type="number", format="float", example=99.99),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-09-13T20:39:52.948000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-09-13T20:39:52.948000Z"),
     *                 @OA\Property(
     *                     property="attachments",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=3),
     *                         @OA\Property(property="file_path", type="string", example="http://localhost:8000/storage/attachments/20240913082706-skincarecategory.jpg"),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2024-09-13T20:39:52.967000Z"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time", example="2024-09-13T20:39:52.967000Z")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Unauthenticated"),
     *             @OA\Property(property="message", type="string", example="You must be logged in to access this resource.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden: You do not have the required permissions",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Forbidden"),
     *             @OA\Property(property="message", type="string", example="You do not have permission to create this product.")
     *         )
     *     )
     * )
     */

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
/**
 * @OA\Put(
 *     path="/api/products/{id}",
 *     tags={"Products"},
 *     summary="Update an existing product",
 *     operationId="updateProduct",
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of the product to update",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="title", type="string", example="Updated Product Title"),
 *             @OA\Property(property="description", type="string", example="Updated description for the product."),
 *             @OA\Property(property="price", type="number", format="float", example=89.99),
 *             @OA\Property(property="category_id", type="integer", example=2),
 *             @OA\Property(
 *                 property="attachments",
 *                 type="object",
 *                 @OA\Property(
 *                     property="create",
 *                     type="array",
 *                     @OA\Items(
 *                         type="object",
 *                         @OA\Property(property="file_path", type="string", example="http://localhost:8000/storage/attachments/updated-product.jpg")
 *                     )
 *                 ),
 *                 @OA\Property(
 *                     property="delete",
 *                     type="array",
 *                     @OA\Items(type="integer", example=3)
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Product updated successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Product updated successfully."),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 ref="#/components/schemas/Product"
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Bad Request - Validation error",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Bad Request"),
 *             @OA\Property(property="message", type="string", example="The request could not be processed due to validation errors.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Product not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Not Found"),
 *             @OA\Property(property="message", type="string", example="Product not found.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal Server Error",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Internal Server Error"),
 *             @OA\Property(property="message", type="string", example="Failed to update the product.")
 *         )
 *     )
 * )
 */
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

/**
 * @OA\Get(
 *     path="/api/products/{id}",
 *     tags={"Products"},
 *     summary="Get a single product by ID",
 *     operationId="getProductById",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of the product to retrieve",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(ref="#/components/schemas/Product")
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Product not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Not Found"),
 *             @OA\Property(property="message", type="string", example="Product not found.")
 *         )
 *     )
 * )
 */
    public function show(Product $product): JsonResponse
    {
        $product->load('category');
        return $this->respondForResource(ProductResource::make($product), 'Product Data');
    }
    
/**
 * @OA\Delete(
 *     path="/api/products/{id}",
 *     tags={"Products"},
 *     summary="Delete a product",
 *     operationId="deleteProduct",
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of the product to delete",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Product deleted successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Product deleted successfully.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Product not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Not Found"),
 *             @OA\Property(property="message", type="string", example="Product not found.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal Server Error",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Internal Server Error"),
 *             @OA\Property(property="message", type="string", example="Failed to delete the product.")
 *         )
 *     )
 * )
 */
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
        $query = Product::query()->with("category");

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
