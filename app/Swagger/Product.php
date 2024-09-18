<?php

namespace App\Swagger;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="Product",
 *     type="object",
 *     title="Product",
 *     description="Product model",
 *     @OA\Property(property="id", type="integer", description="Product ID"),
 *     @OA\Property(property="title", type="string", description="Product title"),
 *     @OA\Property(property="description", type="string", description="Product description"),
 *     @OA\Property(property="price", type="number", format="float", description="Product price"),
 *     @OA\Property(property="category_id", type="integer", description="Category ID"),
 *     @OA\Property(
 *         property="attachments",
 *         type="array",
 *         @OA\Items(
 *             @OA\Property(property="id", type="integer", example=3),
 *             @OA\Property(property="file_path", type="string", example="http://localhost:8000/storage/attachments/sample.jpg"),
 *             @OA\Property(property="created_at", type="string", format="date-time", example="2024-09-13T20:39:52.967000Z"),
 *             @OA\Property(property="updated_at", type="string", format="date-time", example="2024-09-13T20:39:52.967000Z")
 *         )
 *     )
 * )
 */
class Product
{
}
