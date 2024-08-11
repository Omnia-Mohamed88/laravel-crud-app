<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'attachments' => $this->attachments->map(function ($attachment) {
                return [
                    'id' => $attachment->id,
                    'file_path' => asset('storage/' . $attachment->file_path),
                    'attachable_type' => $attachment->attachable_type,
                    'attachable_id' => $attachment->attachable_id,
                    'created_at' => $attachment->created_at,
                    'updated_at' => $attachment->updated_at,
                ];
            }),
        ];
    }
}
