<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UploadService
{
    /**
     * Upload a file securely.
     *
     * @param UploadedFile $file
     * @param string|null $baseFolder
     * @return string   // stored path
     * @throws ValidationException
     */
    public function upload(UploadedFile $file, string $baseFolder = 'uploads'): string
    {
        $this->validateFile($file);
        $year  = date('Y');
        $month = date('m');
        $folderPath = "{$baseFolder}/{$year}/{$month}";
        if (!Storage::exists($folderPath)) {
            Storage::makeDirectory($folderPath);
        }
        $filename = $this->generateSafeFilename($file);
        $storedPath = $file->storeAs($folderPath, $filename, 'public');
        return $storedPath;
    }

    /**
     * Validate uploaded file thoroughly.
     */
    protected function validateFile(UploadedFile $file): void
    {
        if (preg_match('/\.(php|exe|sh|js|html?|bat|cmd)$/i', $file->getClientOriginalName())) {
            throw ValidationException::withMessages([
                'file' => 'Invalid file type.',
            ]);
        }

        $validator = Validator::make(
            ['file' => $file],
            [
                'file' => [
                    'required',
                    'file',
                    'mimes:jpg,jpeg,png,webp,pdf,doc,docx,xlsx',
                    'max:5120',
                ],
            ]
        );

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }


    protected function generateSafeFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeName = Str::slug($name);

        return $safeName . '-' . Str::random(8) . '.' . $extension;
    }
}
