<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Response;

/**
 * UploadController
 * Handles file uploads with automatic crop & compress
 */
class UploadController extends Controller
{
    /** Max dimensions per type */
    private const SIZES = [
        'news'       => ['w' => 1200, 'h' => 630],   // 1.91:1 social share
        'activities' => ['w' => 1200, 'h' => 630],
        'profiles'   => ['w' => 400,  'h' => 400],
        'logos'      => ['w' => 800,  'h' => 800],
        'general'    => ['w' => 1200, 'h' => 800],
    ];

    /** WebP quality */
    private const QUALITY = 82;

    /**
     * POST  ?controller=upload&action=image
     * Optional body params (JSON or form fields):
     *   cropX, cropY, cropWidth, cropHeight  — client-side crop coordinates
     */
    public function image(): void
    {
        $this->requirePost();

        if (empty($_FILES['file'])) {
            Response::error('ไม่พบไฟล์ที่อัปโหลด');
        }

        $file = $_FILES['file'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            Response::error('เกิดข้อผิดพลาดในการอัปโหลดไฟล์ (code: ' . $file['error'] . ')');
        }

        // Validate type
        $allowed = defined('ALLOWED_IMAGE_TYPES')
            ? ALLOWED_IMAGE_TYPES
            : ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($file['tmp_name']);

        if (!in_array($mime, $allowed)) {
            Response::error('ประเภทไฟล์ไม่ถูกต้อง อนุญาตเฉพาะ JPEG, PNG, GIF, WEBP');
        }

        // Validate size (max 10 MB — will be compressed later)
        $maxSize = 10 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            Response::error('ไฟล์ใหญ่เกินไป (สูงสุด 10 MB)');
        }

        // Determine sub-directory
        $subDir  = preg_replace('/[^a-z0-9_-]/i', '', $this->query('type') ?: 'general');
        $baseDir = defined('UPLOAD_DIR') ? UPLOAD_DIR : __DIR__ . '/../../uploads/';
        $dir     = rtrim($baseDir, '/') . '/' . $subDir;

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // ─── Process Image: Crop → Resize → Compress → WebP ───
        $src = $this->loadImage($file['tmp_name'], $mime);
        if (!$src) {
            Response::error('ไม่สามารถอ่านไฟล์รูปภาพได้');
        }

        $origW = imagesx($src);
        $origH = imagesy($src);

        // Client-side crop coordinates (from Cropper.js)
        $cropX = isset($_POST['cropX']) ? (int)$_POST['cropX'] : null;
        $cropY = isset($_POST['cropY']) ? (int)$_POST['cropY'] : null;
        $cropW = isset($_POST['cropWidth']) ? (int)$_POST['cropWidth'] : null;
        $cropH = isset($_POST['cropHeight']) ? (int)$_POST['cropHeight'] : null;

        // Apply crop if provided
        if ($cropW && $cropH && $cropW > 0 && $cropH > 0) {
            $cropX = max(0, min($cropX, $origW));
            $cropY = max(0, min($cropY, $origH));
            $cropW = min($cropW, $origW - $cropX);
            $cropH = min($cropH, $origH - $cropY);

            $cropped = imagecreatetruecolor($cropW, $cropH);
            imagealphablending($cropped, false);
            imagesavealpha($cropped, true);
            imagecopy($cropped, $src, 0, 0, $cropX, $cropY, $cropW, $cropH);
            imagedestroy($src);
            $src = $cropped;
            $origW = $cropW;
            $origH = $cropH;
        }

        // Resize if larger than max dimensions
        $limits = self::SIZES[$subDir] ?? self::SIZES['general'];
        $maxW = $limits['w'];
        $maxH = $limits['h'];

        if ($origW > $maxW || $origH > $maxH) {
            $ratio  = min($maxW / $origW, $maxH / $origH);
            $newW   = (int)round($origW * $ratio);
            $newH   = (int)round($origH * $ratio);

            $resized = imagecreatetruecolor($newW, $newH);
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            imagecopyresampled($resized, $src, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
            imagedestroy($src);
            $src = $resized;
        }

        // Save as WebP
        $filename = date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.webp';
        $target   = $dir . '/' . $filename;

        if (!imagewebp($src, $target, self::QUALITY)) {
            imagedestroy($src);
            Response::error('ไม่สามารถบันทึกไฟล์ได้');
        }

        $finalW = imagesx($src);
        $finalH = imagesy($src);
        imagedestroy($src);

        $finalSize = filesize($target);

        // Build public URL — use path relative to project root
        $url = 'uploads/' . $subDir . '/' . $filename;

        Response::success([
            'url'      => $url,
            'filename' => $filename,
            'size'     => $finalSize,
            'width'    => $finalW,
            'height'   => $finalH,
            'mime'     => 'image/webp',
        ], 'อัปโหลดสำเร็จ');
    }

    /**
     * Load image from file, handling different MIME types
     */
    private function loadImage(string $path, string $mime)
    {
        switch ($mime) {
            case 'image/jpeg': return imagecreatefromjpeg($path);
            case 'image/png':  return imagecreatefrompng($path);
            case 'image/gif':  return imagecreatefromgif($path);
            case 'image/webp': return imagecreatefromwebp($path);
            default: return null;
        }
    }

    /**
     * POST  ?controller=upload&action=logo
     * Upload logo as PNG (transparency support)
     * Params: file, cropX, cropY, cropWidth, cropHeight
     */
    public function logo(): void
    {
        $this->requirePost();

        if (empty($_FILES['file'])) {
            Response::error('ไม่พบไฟล์ที่อัปโหลด');
        }

        $file = $_FILES['file'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            Response::error('เกิดข้อผิดพลาดในการอัปโหลดไฟล์');
        }

        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($file['tmp_name']);
        if (!in_array($mime, $allowed)) {
            Response::error('ประเภทไฟล์ไม่ถูกต้อง อนุญาตเฉพาะ JPEG, PNG, GIF, WEBP');
        }

        if ($file['size'] > 10 * 1024 * 1024) {
            Response::error('ไฟล์ใหญ่เกินไป (สูงสุด 10 MB)');
        }

        $baseDir = defined('UPLOAD_DIR') ? UPLOAD_DIR : __DIR__ . '/../../uploads/';
        $dir = rtrim($baseDir, '/') . '/logos';
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $src = $this->loadImage($file['tmp_name'], $mime);
        if (!$src) Response::error('ไม่สามารถอ่านไฟล์รูปภาพได้');

        $origW = imagesx($src);
        $origH = imagesy($src);

        // Crop
        $cropX = isset($_POST['cropX']) ? (int)$_POST['cropX'] : null;
        $cropY = isset($_POST['cropY']) ? (int)$_POST['cropY'] : null;
        $cropW = isset($_POST['cropWidth']) ? (int)$_POST['cropWidth'] : null;
        $cropH = isset($_POST['cropHeight']) ? (int)$_POST['cropHeight'] : null;

        if ($cropW && $cropH && $cropW > 0 && $cropH > 0) {
            $cropX = max(0, min($cropX, $origW));
            $cropY = max(0, min($cropY, $origH));
            $cropW = min($cropW, $origW - $cropX);
            $cropH = min($cropH, $origH - $cropY);
            $cropped = imagecreatetruecolor($cropW, $cropH);
            imagealphablending($cropped, false);
            imagesavealpha($cropped, true);
            $transparent = imagecolorallocatealpha($cropped, 0, 0, 0, 127);
            imagefill($cropped, 0, 0, $transparent);
            imagecopy($cropped, $src, 0, 0, $cropX, $cropY, $cropW, $cropH);
            imagedestroy($src);
            $src = $cropped;
            $origW = $cropW;
            $origH = $cropH;
        }

        // Resize if needed
        $maxW = 800; $maxH = 800;
        if ($origW > $maxW || $origH > $maxH) {
            $ratio = min($maxW / $origW, $maxH / $origH);
            $newW = (int)round($origW * $ratio);
            $newH = (int)round($origH * $ratio);
            $resized = imagecreatetruecolor($newW, $newH);
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
            imagefill($resized, 0, 0, $transparent);
            imagecopyresampled($resized, $src, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
            imagedestroy($src);
            $src = $resized;
        }

        // Save as PNG
        $filename = 'logo_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.png';
        $target = $dir . '/' . $filename;

        imagealphablending($src, false);
        imagesavealpha($src, true);
        if (!imagepng($src, $target, 6)) {
            imagedestroy($src);
            Response::error('ไม่สามารถบันทึกไฟล์ได้');
        }

        $finalW = imagesx($src);
        $finalH = imagesy($src);
        imagedestroy($src);

        $url = 'uploads/logos/' . $filename;

        Response::success([
            'url'      => $url,
            'filename' => $filename,
            'size'     => filesize($target),
            'width'    => $finalW,
            'height'   => $finalH,
            'mime'     => 'image/png',
        ], 'อัปโหลดโลโก้สำเร็จ');
    }
}
