<?php

namespace App\Http\Controllers;

use Illuminate\Pagination\LengthAwarePaginator;

class BaseController extends Controller
{
    public function successResponse($data = null, $message = 'success', $code = 200)
    {
        $response = [
            'status' => true,
            'message' => $message,
        ];

        // لو البيانات pagination
        if ($data instanceof LengthAwarePaginator) {
            $response['data'] = $data->items(); // الداتا بس

            $response['meta'] = [
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
                'from' => $data->firstItem(),
                'to' => $data->lastItem(),
            ];
        } else {
            $response['data'] = $data;
        }

        return response()->json($response, $code);
    }

    public function errorResponse($message = 'error', $errors = null, $code = 400)
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'errors' => $errors
        ], $code);
    }
}
