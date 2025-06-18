<?php

namespace App\Domain\Uploads\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Exception;

class UploadsService
{
    /**
     * Fazer upload de um arquivo
     * 
     * @param UploadedFile $file
     * @return array
     * @throws Exception
     */
    public function uploadFile(UploadedFile $file): array
    {
        if (!$file->isValid()) {
            throw new Exception('Arquivo não é válido.');
        }

        if ($file->getSize() > 10 * 1024 * 1024) { // 10MB
            throw new Exception('Tamanho máximo permitido é 10MB.');
        }

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'doc', 'docx', 'pdf'];

        if (!in_array($file->extension(), $allowedExtensions)) {
            throw new Exception('Extensão de arquivo não permitida.');
        }

        $path = Storage::disk('carshop')->put(env('AWS_CAMINHO'), $file);
        $url = Storage::url($path);

        return [
            'success' => true,
            'mime' => $file->getMimeType(),
            'path' => $path,
            'url' => $url,
        ];
    }
}
