<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class Media
{
    public static function url(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        $url = asset('storage/'.$path);
        $fullPath = Storage::disk('public')->path($path);

        if (is_file($fullPath)) {
            $url .= (str_contains($url, '?') ? '&' : '?').'v='.filemtime($fullPath);
        }

        return $url;
    }

    public static function store(UploadedFile $file, string $directory, ?string $oldPath = null): string
    {
        if ($oldPath) {
            self::delete($oldPath);
        }

        return $file->store($directory, 'public');
    }

    public static function delete(?string $path): void
    {
        if ($path === null || $path === '') {
            return;
        }

        Storage::disk('public')->delete($path);
    }
}
