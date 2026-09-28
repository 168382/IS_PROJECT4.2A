<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use RuntimeException;

class ItemImage
{
    public function fromUpload(UploadedFile $file): array
    {
        $bytes = file_get_contents($file->getRealPath());

        if ($bytes === false) {
            throw new RuntimeException('Unable to read uploaded image.');
        }

        return $this->fromBytes($bytes);
    }

    public function fromBytes(string $bytes): array
    {
        $info = getimagesizefromstring($bytes);
        $mime = $info['mime'] ?? null;
        if (! in_array($mime, ['image/jpeg', 'image/png', 'image/gif', 'image/webp'], true)) {
            throw new RuntimeException('Unsupported item image format.');
        }
        if ($info[0] > 4096 || $info[1] > 4096) {
            throw new RuntimeException('Item image dimensions exceed the 4096px limit.');
        }

        $image = imagecreatefromstring($bytes);
        if ($image === false) {
            throw new RuntimeException('Unable to decode item image.');
        }

        try {
            $small = imagecreatetruecolor(9, 8);
            imagecopyresampled($small, $image, 0, 0, 0, 0, 9, 8, imagesx($image), imagesy($image));
            $hash = '';
            $color = ['red' => 0, 'green' => 0, 'blue' => 0];
            for ($y = 0; $y < 8; $y++) {
                for ($x = 0; $x < 8; $x++) {
                    $left = imagecolorsforindex($small, imagecolorat($small, $x, $y));
                    $right = imagecolorsforindex($small, imagecolorat($small, $x + 1, $y));
                    $luma = fn (array $pixel) => 299 * $pixel['red'] + 587 * $pixel['green'] + 114 * $pixel['blue'];
                    $hash .= $luma($left) > $luma($right) ? '1' : '0';
                    foreach ($color as $channel => &$total) {
                        $total += $left[$channel];
                    }
                    unset($total);
                }
            }
            imagedestroy($small);
        } finally {
            imagedestroy($image);
        }

        $average = sprintf('%02x%02x%02x', round($color['red'] / 64), round($color['green'] / 64), round($color['blue'] / 64));

        return ['image_data' => $bytes, 'image_mime' => $mime, 'image_hash' => $hash.':'.$average];
    }

    public function similarity(?string $left, ?string $right): float
    {
        if (! $left || ! $right || ! preg_match('/^[01]{64}:[0-9a-f]{6}$/', $left)
            || ! preg_match('/^[01]{64}:[0-9a-f]{6}$/', $right)) {
            return 0;
        }

        $colorDistance = 0;
        foreach ([65, 67, 69] as $position) {
            $colorDistance += abs(hexdec(substr($left, $position, 2)) - hexdec(substr($right, $position, 2)));
        }
        $colorSimilarity = (1 - $colorDistance / 765) * 100;
        if ($colorSimilarity < 80) {
            return 0;
        }

        $different = 0;
        for ($i = 0; $i < 64; $i++) {
            $different += $left[$i] !== $right[$i] ? 1 : 0;
        }

        return 0.75 * (64 - $different) / 64 * 100 + 0.25 * $colorSimilarity;
    }
}
