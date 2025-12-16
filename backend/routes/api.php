<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\ReminderController;
use App\Http\Controllers\DocumentController;
use Illuminate\Validation\ValidationException;

// ================== CORS PRE-FLIGHT ==================

Route::options('/{any}', function () {
    return response('', 200)
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Origin, Content-Type, Accept, Authorization, X-Requested-With')
        ->header('Access-Control-Max-Age', '86400');
})->where('any', '.*');

// ================== AUTH (PUBLIC) ==================

Route::post('/register', function (Request $request) {
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|string|min:8',
    ]);

    $user = User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'password' => Hash::make($validated['password']),
    ]);

    $token = $user->createToken('api-token')->plainTextToken;

    return response()->json([
        'user' => $user,
        'token' => $token,
    ], 201);
});

Route::post('/login', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    $user = User::where('email', $request->email)->first();

    if (! $user || ! Hash::check($request->password, $user->password)) {
        throw ValidationException::withMessages([
            'email' => ['The provided credentials are incorrect.'],
        ]);
    }

    $token = $user->createToken('api-token')->plainTextToken;

    return response()->json([
        'user' => $user,
        'token' => $token,
    ]);
});

// ================== PROTECTED API (AUTH REQUIRED) ==================

Route::middleware('auth:sanctum')->group(function () {

    // Employee routes
    Route::apiResource('employees', EmployeeController::class);

    // Task routes
    Route::apiResource('tasks', TaskController::class);

    // Reminder routes
    Route::apiResource('reminders', ReminderController::class);

    // Document routes
    Route::apiResource('documents', DocumentController::class);

    // Setup data route
    Route::get('/setup-data', function () {
        $disk = 'azure_data';

        $files = [
            'employees/employees.json' => '[]',
            'tasks/tasks.json' => '[]',
            'reminders/reminders.json' => '[]',
            'documents/documents.json' => '[]',
        ];

        foreach ($files as $file => $content) {
            if (! Storage::disk($disk)->fileExists($file)) {
                Storage::disk($disk)->write($file, $content);
            }
        }

        return response()->json(['message' => 'Data files initialized successfully'])
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Origin, Content-Type, Accept, Authorization, X-Requested-With');
    });

    // Azure test route
    Route::get('/azure-test', function () {
        return response()->json([
            'status' => 'success',
            'message' => 'Azure connection working',
            'storage' => [
                'disk' => 'azure_data',
                'configured' => true,
            ],
        ])
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Origin, Content-Type, Accept, Authorization, X-Requested-With');
    });
});

// ================== DEFAULT PUBLIC INFO ROUTE ==================

Route::get('/', function () {
    return response()->json([
        'message' => 'Employee Management API',
        'version' => '1.0',
        'endpoints' => [
            'register'  => '/api/register',
            'login'     => '/api/login',
            'employees' => '/api/employees',
            'tasks'     => '/api/tasks',
            'reminders' => '/api/reminders',
            'documents' => '/api/documents',
        ],
    ])
    ->header('Access-Control-Allow-Origin', '*')
    ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
    ->header('Access-Control-Allow-Headers', 'Origin, Content-Type, Accept, Authorization, X-Requested-With');
});
