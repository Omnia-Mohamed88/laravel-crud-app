<?php

namespace App\Swagger;

/**
 * @OA\Schema(
 *     schema="Category",
 *     type="object",
 *     title="Category Resource",
 *     description="A representation of a category",
 *     required={"id", "title"},
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         example=1,
 *         description="The unique identifier of the category"
 *     ),
 *     @OA\Property(
 *         property="title",
 *         type="string",
 *         example="Sample Category",
 *         description="The title of the category"
 *     ),
 *     @OA\Property(
 *         property="attachments",
 *         type="array",
 *         @OA\Items(
 *             type="object",
 *             @OA\Property(
 *                 property="file_path",
 *                 type="string",
 *                 example="attachments/image.jpg",
 *                 description="The path to the attachment file"
 *             )
 *         )
 *     ),
 *     @OA\Property(
 *         property="image",
 *         type="string",
 *         format="url",
 *         example="https://example.com/storage/image.jpg",
 *         nullable=true,
 *         description="The URL of the category's image"
 *     )
 * )
 */
class Category
{
    // This class will only be used to hold the Swagger annotations
}
