<?php

namespace App\Domain\Uploads\Controllers;

use App\Http\Controllers\Controller;
use App\Domain\Uploads\Services\UploadsService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Exception;

class UploadsController extends Controller
{
    public function __construct(
        private UploadsService $uploadsService
    ) {}

    public function store(Request $request): JsonResponse
    {
        try {
            if (!$request->hasFile('file')) {
                return response()->json(['error' => 'Nenhum arquivo enviado.'], Response::HTTP_BAD_REQUEST);
            }

            $file = $request->file('file');
            $result = $this->uploadsService->uploadFile($file);

            return response()->json($result, Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Falha ao fazer upload.',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
