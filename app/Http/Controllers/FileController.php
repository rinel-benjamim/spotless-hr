<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function openFile(Request $request): \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $encodedPath = $request->input('path');

        if (! $encodedPath) {
            return response()->json(['error' => 'Path não fornecido'], 400);
        }

        $path = base64_decode($encodedPath);
        $fullPath = Storage::disk('local')->path($path);

        if (! file_exists($fullPath)) {
            return response()->json(['error' => 'Ficheiro não encontrado'], 404);
        }

        if (app()->bound(\Native\Laravel\Facades\Shell::class)) {
            $result = \Native\Laravel\Facades\Shell::openFile($fullPath);
            if ($result === '') {
                return response()->json(['success' => true]);
            }

            return response()->json(['error' => $result], 500);
        }

        $mimeType = Storage::disk('local')->mimeType($path);

        return response()->download($fullPath, basename($path), [
            'Content-Type' => $mimeType,
        ]);
    }
}
