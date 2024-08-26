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
            // 'category_id' => $this->category_id,
            'category' => new CategoryResource($this->whenLoaded('category')), 
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'attachments' => $this->attachments->map(function ($attachment) {
                return [
                    'id' => $attachment->id,
                    'file_path' => $this->isFullUrl($attachment->file_path) ? $attachment->file_path : asset('storage/' . $attachment->file_path),
                    'created_at' => $attachment->created_at,
                    'updated_at' => $attachment->updated_at,
                ];
            }),
        ];
    }

    private function isFullUrl($url)
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
}
