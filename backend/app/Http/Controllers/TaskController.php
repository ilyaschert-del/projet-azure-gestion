<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TaskController extends Controller
{
    private $disk = 'azure_data';
    private $file = 'tasks/tasks.json';

    public function index()
    {
        $tasks = $this->getTasks();
        return response()->json($tasks);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'employee_id' => 'required|string',
            'status' => 'required|in:pending,in_progress,completed',
            'due_date' => 'nullable|date',
        ]);

        $tasks = $this->getTasks();

        $task = [
            'id' => (string) Str::uuid(),
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'employee_id' => $validated['employee_id'],
            'status' => $validated['status'],
            'due_date' => $validated['due_date'] ?? null,
            'created_at' => now()->toIso8601String(),
            'updated_at' => now()->toIso8601String(),
        ];

        $tasks[] = $task;
        $this->saveTasks($tasks);

        return response()->json($task, 201);
    }

    public function show($id)
    {
        $tasks = $this->getTasks();
        $task = collect($tasks)->firstWhere('id', $id);

        if (!$task) {
            return response()->json(['message' => 'Task not found'], 404);
        }

        return response()->json($task);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'employee_id' => 'sometimes|string',
            'status' => 'sometimes|in:pending,in_progress,completed',
            'due_date' => 'sometimes|date',
        ]);

        $tasks = $this->getTasks();
        $index = collect($tasks)->search(fn($task) => $task['id'] === $id);

        if ($index === false) {
            return response()->json(['message' => 'Task not found'], 404);
        }

        $tasks[$index] = array_merge($tasks[$index], $validated);
        $tasks[$index]['updated_at'] = now()->toIso8601String();

        $this->saveTasks($tasks);

        return response()->json($tasks[$index]);
    }

    public function destroy($id)
    {
        $tasks = $this->getTasks();
        $filtered = collect($tasks)->reject(fn($task) => $task['id'] === $id)->values()->all();

        if (count($tasks) === count($filtered)) {
            return response()->json(['message' => 'Task not found'], 404);
        }

        $this->saveTasks($filtered);

        return response()->json(['message' => 'Task deleted successfully']);
    }

    private function getTasks()
    {
        if (!Storage::disk($this->disk)->fileExists($this->file)) {
            return [];
        }

        $content = Storage::disk($this->disk)->read($this->file);
        return json_decode($content, true) ?? [];
    }

    private function saveTasks($tasks)
    {
        Storage::disk($this->disk)->write($this->file, json_encode($tasks, JSON_PRETTY_PRINT));
    }
}