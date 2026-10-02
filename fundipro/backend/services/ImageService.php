<?php
/**
 * Image Service - FundiPro
 * Mobile-optimized image upload and compression
 * Handles slow networks in Kenya
 */
class ImageService {
    const MAX_WIDTH  = 1200;
    const MAX_HEIGHT = 1200;
    const QUALITY    = 80;
    const MAX_SIZE   = 5 * 1024 * 1024; // 5MB

    public function upload($file, $type = 'portfolio') {
        if ($file['size'] > self::MAX_SIZE) {
            throw new Exception('Image too large. Maximum size is 5MB.');
        }

        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($file['type'], $allowed)) {
            throw new Exception('Only JPG, PNG and WebP images are allowed.');
        }

        $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid("fp_{$type}_") . '.' . strtolower($ext);
        $path     = "uploads/$type/$filename";

        // Compress image
        $compressed = $this->compress($file['tmp_name'], $file['type']);
        file_put_contents($path, $compressed);

        return [
            'url'      => 'https://fundipro.co.ke/' . $path,
            'filename' => $filename,
            'size'     => strlen($compressed),
        ];
    }

    private function compress($source, $mime) {
        [$width, $height] = getimagesize($source);

        // Resize if too large
        if ($width > self::MAX_WIDTH || $height > self::MAX_HEIGHT) {
            $ratio  = min(self::MAX_WIDTH / $width, self::MAX_HEIGHT / $height);
            $width  = (int)($width * $ratio);
            $height = (int)($height * $ratio);
        }

        $canvas = imagecreatetruecolor($width, $height);
        $src = match($mime) {
            'image/png'  => imagecreatefrompng($source),
            'image/webp' => imagecreatefromwebp($source),
            default      => imagecreatefromjpeg($source),
        };

        imagecopyresampled($canvas, $src, 0, 0, 0, 0, $width, $height,
            imagesx($src), imagesy($src));

        ob_start();
        imagejpeg($canvas, null, self::QUALITY);
        $data = ob_get_clean();
        imagedestroy($canvas);
        imagedestroy($src);
        return $data;
    }
}
