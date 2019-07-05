<?php

namespace CoffeeCode\Cropper;

/**
 * Class CoffeeCode Cropper
 *
 * @author Robson V. Leite <https://github.com/robsonvleite>
 * @package CoffeeCode\Cropper
 */
class Cropper
{
    /** @var string */
    private $cachePath;

    /** @var array */
    private $cacheSize;

    /** @var string */
    private $imagePath;

    /** @var string */
    private $imageName;

    /** @var string */
    private $imageMime;

    /** @var string */
    private $imageInfo;

    /**
     * Allow jpg and png to thumb and cache generate
     *
     * @var array allowed media types
     */
    private static $allowedExt = ['image/jpeg', "image/png"];

    /**
     * Cropper constructor.
     *
     * @param string $cachePath
     * @param int $jpgQuality
     * @param int $pngCompressor
     * @throws \Exception
     */
    public function __construct(string $cachePath, int $jpgQuality = 75, int $pngCompressor = 5)
    {
        $this->cachePath = $cachePath;
        $this->cacheSize = [$jpgQuality, $pngCompressor];

        if (!file_exists($this->cachePath) || !is_dir($this->cachePath)) {
            if (!mkdir($this->cachePath, 0755)) {
                throw new \Exception("Could not create cache folder");
            }
        }
    }

    /**
     * Make an thumb image
     *
     * @param string $imagePath
     * @param int $width
     * @param int|null $height
     * @return null|string
     */
    public function make(string $imagePath, int $width, int $height = null): ?string
    {
        if (!file_exists($imagePath)) {
            return "Image not found";
        }

        $this->imagePath = $imagePath;
        $this->imageMime = mime_content_type($this->imagePath);
        $this->imageInfo = pathinfo($this->imagePath);
        $this->imageName = hash("crc32", $this->imageInfo['basename']) . date("Ymd") . hash("crc32",
                "{$width}{$height}") . ($this->imageMime == "image/jpeg" ? ".jpg" : ".png");

        if (!in_array($this->imageMime, self::$allowedExt)) {
            return "Not a valid JPG or PNG image";
        }

        if (file_exists("{$this->cachePath}/{$this->imageName}") && is_file("{$this->cachePath}/{$this->imageName}")) {
            return "{$this->cachePath}/{$this->imageName}";
        }

        return $this->imageCache($width, $height);
    }

    /**
     * Clear cache
     *
     * @param string|null $imageName
     * @example $t->flush("images/image.jpg"); clear image name and variations size
     * @example $t->flush(); clear all image cache folder
     */
    public function flush(string $imageName = null): void
    {
        $scan = scandir($this->cachePath);
        $name = ($imageName ? hash("crc32", pathinfo($imageName)['basename']) : null);

        foreach ($scan as $file) {
            $file = "{$this->cachePath}/{$file}";
            if ($imageName && strpos($file, $name)) {
                $this->imageDestroy($file);
            } elseif (!$imageName) {
                $this->imageDestroy($file);
            }
        }
    }

    /**
     * @param int $width
     * @param int|null $height
     * @return null|string
     */
    private function imageCache(int $width, int $height = null): ?string
    {
        list($src_w, $src_h) = getimagesize($this->imagePath);
        $height = ($height ?? ($width * $src_h) / $src_w);

        $src_x = 0;
        $src_y = 0;

        $cmp_x = $src_w / $width;
        $cmp_y = $src_h / $height;

        if ($cmp_x > $cmp_y) {
            $src_w = round($src_w / $cmp_x * $cmp_y);
            $src_x = round(($src_w - ($src_w / $cmp_x * $cmp_y))); //2
        } elseif ($cmp_y > $cmp_x) {
            $src_h = round($src_h / $cmp_y * $cmp_x);
            $src_y = round(($src_h - ($src_h / $cmp_y * $cmp_x))); //2
        }

        $src_x = (int)$src_x;
        $src_h = (int)$src_h;
        $src_y = (int)$src_y;
        $src_y = (int)$src_y;

        if ($this->imageMime == "image/jpeg") {
            return $this->fromJpg($width, $height, $src_x, $src_y, $src_w, $src_h);
        }

        if ($this->imageMime == "image/png") {
            return $this->fromPng($width, $height, $src_x, $src_y, $src_w, $src_h);
        }

        return null;
    }

    /**
     * @param string $imagePatch
     */
    private function imageDestroy(string $imagePatch): void
    {
        if (file_exists($imagePatch) && is_file($imagePatch)) {
            unlink($imagePatch);
        }
    }

    /**
     * @param int $width
     * @param int $height
     * @param int $src_x
     * @param int $src_y
     * @param int $src_w
     * @param int $src_h
     * @return string
     */
    private function fromJpg(int $width, int $height, int $src_x, int $src_y, int $src_w, int $src_h): string
    {
        $thumb = imagecreatetruecolor($width, $height);
        $source = imagecreatefromjpeg($this->imagePath);
        imagecopyresized($thumb, $source, 0, 0, $src_x, $src_y, $width, $height, $src_w, $src_h);
        imagejpeg($thumb, "{$this->cachePath}/{$this->imageName}", $this->cacheSize[0]);

        imagedestroy($thumb);
        imagedestroy($source);

        return "{$this->cachePath}/{$this->imageName}";
    }

    /**
     * @param int $width
     * @param int $height
     * @param int $src_x
     * @param int $src_y
     * @param int $src_w
     * @param int $src_h
     * @return string
     */
    private function fromPng(int $width, int $height, int $src_x, int $src_y, int $src_w, int $src_h): string
    {
        $thumb = imagecreatetruecolor($width, $height);
        $source = imagecreatefrompng($this->imagePath);

        imagealphablending($thumb, false);
        imagesavealpha($thumb, true);
        imagecopyresized($thumb, $source, 0, 0, $src_x, $src_y, $width, $height, $src_w, $src_h);
        imagepng($thumb, "{$this->cachePath}/{$this->imageName}", $this->cacheSize[1]);

        imagedestroy($thumb);
        imagedestroy($source);

        return "{$this->cachePath}/{$this->imageName}";
    }
}