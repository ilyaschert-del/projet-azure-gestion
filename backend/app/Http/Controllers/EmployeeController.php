<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EmployeeController extends Controller
{
    private $disk = 'azure_data';
    private $file = 'employees/employees.json';

    /**
     * Add CORS headers to response
     */
    private function addCorsHeaders($response)
    {
        return $response
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Origin, Content-Type, Accept, Authorization, X-Requested-With')
            ->header('Access-Control-Max-Age', '86400');
    }

    public function index()
    {
        $employees = $this->getEmployees();
        return $this->addCorsHeaders(response()->json($employees));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'position' => 'required|string|max:255',
            'department' => 'required|string|max:255',
        ]);

        $employees = $this->getEmployees();

        $employee = [
            'id' => (string) Str::uuid(),
            'name' => $validated['name'],
            'email' => $validated['email'],
            'position' => $validated['position'],
            'department' => $validated['department'],
            'created_at' => now()->toIso8601String(),
            'updated_at' => now()->toIso8601String(),
        ];

        $employees[] = $employee;
        $this->saveEmployees($employees);

        return $this->addCorsHeaders(response()->json($employee, 201));
    }

    public function show($id)
    {
        $employees = $this->getEmployees();
        $employee = collect($employees)->firstWhere('id', $id);

        if (!$employee) {
            return $this->addCorsHeaders(
                response()->json(['message' => 'Employee not found'], 404)
            );
        }

        return $this->addCorsHeaders(response()->json($employee));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email',
            'position' => 'sometimes|string|max:255',
            'department' => 'sometimes|string|max:255',
        ]);

        $employees = $this->getEmployees();
        $index = collect($employees)->search(fn($emp) => $emp['id'] === $id);

        if ($index === false) {
            return $this->addCorsHeaders(
                response()->json(['message' => 'Employee not found'], 404)
            );
        }

        $employees[$index] = array_merge($employees[$index], $validated);
        $employees[$index]['updated_at'] = now()->toIso8601String();

        $this->saveEmployees($employees);

        return $this->addCorsHeaders(response()->json($employees[$index]));
    }

    public function destroy($id)
    {
        $employees = $this->getEmployees();
        $filtered = collect($employees)->reject(fn($emp) => $emp['id'] === $id)->values()->all();

        if (count($employees) === count($filtered)) {
            return $this->addCorsHeaders(
                response()->json(['message' => 'Employee not found'], 404)
            );
        }

        $this->saveEmployees($filtered);

        return $this->addCorsHeaders(
            response()->json(['message' => 'Employee deleted successfully'])
        );
    }

    private function getEmployees()
    {
        if (!Storage::disk($this->disk)->fileExists($this->file)) {
            return [];
        }

        $content = Storage::disk($this->disk)->read($this->file);
        return json_decode($content, true) ?? [];
    }

    private function saveEmployees($employees)
    {
        Storage::disk($this->disk)->write($this->file, json_encode($employees, JSON_PRETTY_PRINT));
    }
}