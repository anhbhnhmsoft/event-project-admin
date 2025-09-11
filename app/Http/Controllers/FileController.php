<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    const CACHE_TIME = 60 * 60 * 48; // 2 ngày

    // Hàm chung để xử lý ảnh và video
    protected function serveFile($file_path, $type)
    {
        // Ngăn người dùng nhập path kiểu ../../
        $file_path = ltrim($file_path, '/');

        if (!Storage::disk('public')->exists($file_path)) {
            abort(404, 'File not found');
        }
        // Lấy path và mime type từ cache (hoặc tính toán)
        $fileInfo = Cache::remember("file_info:$file_path", self::CACHE_TIME, function () use ($file_path) {
            $path = Storage::disk('public')->path($file_path);
            $mime = mime_content_type($path);
            return [
                'path' => $path,
                'mime' => $mime,
            ];
        });

        // Kiểm tra loại tệp
        if (strpos($fileInfo['mime'], $type) === false) {
            abort(415, 'Unsupported Media Type');
        }

        // Trả về tệp với Content-Type tương ứng
        return response()->file($fileInfo['path'], [
            'Content-Type' => $fileInfo['mime'],
            'Cache-Control' => 'public, max-age=' . self::CACHE_TIME . ', immutable',
        ]);
    }

    // Phương thức xử lý hình ảnh
    public function image($file_path)
    {
        return $this->serveFile($file_path, 'image');
    }

    // Phương thức xử lý video
    public function video($file_path)
    {
        return $this->serveFile($file_path, 'video');
    }
}
