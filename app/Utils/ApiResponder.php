<?php 

namespace App\Utils;

trait ApiResponder {
    
    public function respond($data = [],$message = "",$status = 200)
    {
        return response()->json([
            'message' => $message,
            'data' => $data
        ],$status);
    }

    public function respondCreated($data = [],$message="")
    {
        return response()->json([
            'message' => $message,
            'data' => $data
        ],201);
    }

    public function respondError($errors = [],$message="",$status = 400)
    {
        return response()->json([
            'message' => $message,
            'errors' => [$errors]
        ],$status);
    }

    public function respondForResource($data = [],$message = "",$status = 200)
    {
        return response()->json([
            'message' => $message,
            'data' => $data->response()->getData()
        ],$status);
    }

}