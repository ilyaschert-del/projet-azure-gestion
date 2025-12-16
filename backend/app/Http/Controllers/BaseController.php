<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class BaseController extends Controller
{
    /**
     * Return JSON response with CORS headers
     */
    protected function sendResponse($data, $statusCode = 200): JsonResponse
    {
        return response()->json($data, $statusCode)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Origin, Content-Type, Accept, Authorization, X-Requested-With')
            ->header('Access-Control-Max-Age', '86400');
    }

    /**
     * Return error response with CORS headers
     */
    protected function sendError($error, $message = null, $statusCode = 500): JsonResponse
    {
        $response = ['error' => $error];
        
        if ($message) {
            $response['message'] = $message;
        }

        return $this->sendResponse($response, $statusCode);
    }
}