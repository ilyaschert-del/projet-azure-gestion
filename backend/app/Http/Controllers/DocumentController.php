<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    private $disk = 'azure';
    private $metadataDisk = 'azure_data';
    private $metadataFile = 'documents/documents.json';

    public function index()
    {
        $documents = $this->getDocuments();
        return response()->json($documents);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'file' => 'required|file|max:10240', // Max 10MB
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'employee_id' => 'required|string',
        ]);

        $file = $request->file('file');
        $fileName = time() . '_' . $file->getClientOriginalName();
        
        // Upload file to Azure Blob Storage (documents container)
        $fileContents = file_get_contents($file->getRealPath());
        Storage::disk($this->disk)->write($fileName, $fileContents);
        $path = $fileName;

        $documents = $this->getDocuments();

        $document = [
            'id' => (string) Str::uuid(),
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'file_name' => $fileName,
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'employee_id' => $validated['employee_id'],
            'created_at' => now()->toIso8601String(),
        ];

        $documents[] = $document;
        $this->saveDocuments($documents);

        return response()->json($document, 201);
    }

    public function show($id)
    {
        $documents = $this->getDocuments();
        $document = collect($documents)->firstWhere('id', $id);

        if (!$document) {
            return response()->json(['message' => 'Document not found'], 404);
        }

        // Generate download URL
        $url = Storage::disk($this->disk)->url($document['file_path']);
        $document['download_url'] = $url;

        return response()->json($document);
    }

    public function destroy($id)
    {
        $documents = $this->getDocuments();
        $document = collect($documents)->firstWhere('id', $id);

        if (!$document) {
            return response()->json(['message' => 'Document not found'], 404);
        }

        // Delete file from Azure
        Storage::disk($this->disk)->delete($document['file_path']);

        // Remove from metadata
        $filtered = collect($documents)->reject(fn($doc) => $doc['id'] === $id)->values()->all();
        $this->saveDocuments($filtered);

        return response()->json(['message' => 'Document deleted successfully']);
    }

    private function getDocuments()
    {
        if (!Storage::disk($this->metadataDisk)->fileExists($this->metadataFile)) {
            return [];
        }

        $content = Storage::disk($this->metadataDisk)->read($this->metadataFile);
        return json_decode($content, true) ?? [];
    }

    private function saveDocuments($documents)
    {
        Storage::disk($this->metadataDisk)->write($this->metadataFile, json_encode($documents, JSON_PRETTY_PRINT));
    }
}