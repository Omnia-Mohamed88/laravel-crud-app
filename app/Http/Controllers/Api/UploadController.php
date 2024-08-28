<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    public function uploadImage(Request $request)
{
    $request->validate([
        'files.*' => 'required|image|max:2048',
    ]);

    $urls = [];

    if ($request->hasFile('files')) {
        $files = $request->file('files');
        foreach ($files as $file) {
            $path = $file->store('attachments', 'public');
            $urls[] = Storage::url($path);
        }
        return response()->json(['urls' => $urls, 'count' => count($files)], 200);
    }

    return response()->json(['message' => 'No files uploaded', 'urls' => $urls], 200);
}

public function deleteImage(Request $request)
{
    $request->validate([
        'file_url' => 'required|string',
    ]);

    $fileUrl = $request->input('file_url');

    $filePath = parse_url($fileUrl, PHP_URL_PATH); 
    $filePath = str_replace('/storage/', 'public/', $filePath);

    \Log::info("Attempting to delete file at: " . $filePath);

    if (Storage::exists($filePath)) {
        Storage::delete($filePath);
        return response()->json(['message' => 'File deleted successfully'], 200);
    }

    return response()->json(['message' => 'File not found'], 404);
}



}
//     public function uploadImage(Request $request)
// {
//     $request->validate([
//         'file' => 'required|image|max:2048',
//     ]);

//     $path = $request->file('file')->store('attachments', 'public');
//     $url = url(Storage::url($path)); // Use url() to get the full URL

//     return response()->json(['url' => $url], 200);
// }


