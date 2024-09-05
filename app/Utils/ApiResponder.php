<?php

namespace App\Utils;

use Illuminate\Http\JsonResponse;

trait ApiResponder {

    public function respond($data = [],$message = "",$status = 200): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'data' => $data
        ],$status);
    }
    public function respondSuccess($message = "",$status = 200): JsonResponse
    {
        return response()->json([
            'message' => $message,
        ],$status);
    }

    public function respondCreated($data = [],$message=""): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'data' => $data
        ],201);
    }

    public function respondError($errors = [],$message="",$status = 400): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'errors' => [$errors]
        ],$status);
    }

    public function respondForResource($data = [],$message = "",$status = 200): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'data' => $data->response()->getData()
        ],$status);
    }

}
