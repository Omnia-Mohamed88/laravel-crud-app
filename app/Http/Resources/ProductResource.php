<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'price' => $this->price,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'attachments' => $this->attachments->map(function ($attachment) {
                return [
                    'id' => $attachment->id,
                    'file_path' => $attachment->file_path,
                    'created_at' => $attachment->created_at,
                    'updated_at' => $attachment->updated_at,
                ];
            }),
        ];
    }

    private function formatFilePath($filePath)
    {
        $filePath = ltrim($filePath, '/');

        if (str_starts_with($filePath, 'storage/')) {
            $filePath = substr($filePath, strlen('storage/'));
        }

        return asset('storage/' . $filePath);
    }
}
