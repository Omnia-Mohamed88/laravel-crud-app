<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    // public function saveOnDisk(Request $request): JsonResponse
    // {
    //     $request->validate([
    //         'files.*' => 'required|image|max:2048',
    //     ]);

    //     $urls = [];

    //     if ($request->hasFile('files')) {
    //         $files = $request->file('files');
    //         foreach ($files as $file) {
    //             $path = $file->storeAs('public/attachments', date("Ymdhis") . '-' . $file->getClientOriginalName());
    //             $urls[] = config('app.url') . Storage::url($path);
    //         }
    //         return $this->respond(["urls" => $urls, "count" => count($files)]);

    //     }

    //     return $this->respond(["urls" => $urls],'No files uploaded');
    // }

    public function saveOnDisk(Request $request): JsonResponse
{
    $request->validate([
        'files.*' => 'required|image|max:2048',
    ]);

    $folder = $request->input('folder', ''); 
    
    $fullFolderPath = 'attachments' . ($folder ? "/$folder" : '');

    $urls = [];

    if ($request->hasFile('files')) {
        $files = $request->file('files');
        foreach ($files as $file) {
            $path = $file->storeAs("public/$fullFolderPath", date("Ymdhis") . '-' . $file->getClientOriginalName());
            $urls[] = config('app.url') . Storage::url($path);
        }

        return $this->respond(["urls" => $urls, "count" => count($files)]);
    }

    return $this->respond(["urls" => $urls], 'No files uploaded');
}






    public function deleteImage(Request $request): JsonResponse
    {
        $request->validate([
            'file_url' => 'required|string',
        ]);

        $fileUrl = $request->input('file_url');

        $filePath = parse_url($fileUrl, PHP_URL_PATH);
        $filePath = str_replace('/storage/', 'public/', $filePath);

        info("Attempting to delete file at: " . $filePath);

        if (Storage::exists($filePath)) {
            Storage::delete($filePath);
            return $this->respondSuccess("File deleted successfully");
        }

        return $this->respondError('File not found', 404);
    }


}


