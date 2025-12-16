<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ReminderController extends Controller
{
    private $disk = 'azure_data';
    private $file = 'reminders/reminders.json';

    public function index()
    {
        $reminders = $this->getReminders();
        return response()->json($reminders);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'reminder_date' => 'required|date',
            'employee_id' => 'required|string',
        ]);

        $reminders = $this->getReminders();

        $reminder = [
            'id' => (string) Str::uuid(),
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'reminder_date' => $validated['reminder_date'],
            'employee_id' => $validated['employee_id'],
            'created_at' => now()->toIso8601String(),
            'updated_at' => now()->toIso8601String(),
        ];

        $reminders[] = $reminder;
        $this->saveReminders($reminders);

        return response()->json($reminder, 201);
    }

    public function show($id)
    {
        $reminders = $this->getReminders();
        $reminder = collect($reminders)->firstWhere('id', $id);

        if (!$reminder) {
            return response()->json(['message' => 'Reminder not found'], 404);
        }

        return response()->json($reminder);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'reminder_date' => 'sometimes|date',
            'employee_id' => 'sometimes|string',
        ]);

        $reminders = $this->getReminders();
        $index = collect($reminders)->search(fn($rem) => $rem['id'] === $id);

        if ($index === false) {
            return response()->json(['message' => 'Reminder not found'], 404);
        }

        $reminders[$index] = array_merge($reminders[$index], $validated);
        $reminders[$index]['updated_at'] = now()->toIso8601String();

        $this->saveReminders($reminders);

        return response()->json($reminders[$index]);
    }

    public function destroy($id)
    {
        $reminders = $this->getReminders();
        $filtered = collect($reminders)->reject(fn($rem) => $rem['id'] === $id)->values()->all();

        if (count($reminders) === count($filtered)) {
            return response()->json(['message' => 'Reminder not found'], 404);
        }

        $this->saveReminders($filtered);

        return response()->json(['message' => 'Reminder deleted successfully']);
    }

    private function getReminders()
    {
        if (!Storage::disk($this->disk)->fileExists($this->file)) {
            return [];
        }

        $content = Storage::disk($this->disk)->read($this->file);
        return json_decode($content, true) ?? [];
    }

    private function saveReminders($reminders)
    {
        Storage::disk($this->disk)->write($this->file, json_encode($reminders, JSON_PRETTY_PRINT));
    }
}