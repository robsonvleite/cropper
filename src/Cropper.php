<?php

namespace CoffeeCode\Cropper;

use Exception;
use WebPConvert\Convert\Exceptions\ConversionFailedException;
use WebPConvert\WebPConvert;

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

    /** @var string */
    private $imagePath;

    /** @var string */
    private $imageName;

    /** @var string */
    private $imageMime;

    /** @var int */
    private $quality;

    /** @var int */
    private $compressor;

    /**@var bool */
    private $webP;

    /**
     * Allow jpg and png to thumb and cache generate
     * @var array allowed media types
     */
    private static $allowedExt = ['image/jpeg', "image/png"];

    /**
     * Cropper constructor.
     * compressor must be 1-9
     * quality must be 1-100
     *
     * @param string $cachePath
     * @param int $quality
     * @param int $compressor
     * @param bool $webP
     * @throws Exception
     */
    public function __construct(string $cachePath, int $quality = 75, int $compressor = 5, bool $webP = false)
    {
        $this->cachePath = $cachePath;
        $this->quality = $quality;
        $this->compressor = $compressor;
        $this->webP = $webP;

        if (!file_exists($this->cachePath) || !is_dir($this->cachePath)) {
            if (!mkdir($this->cachePath, 0755, true)) {
                throw new Exception("Could not create cache folder");
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
        $this->imageName = $this->name($this->imagePath, $width, $height);
        $this->imageMime = mime_content_type($this->imagePath);

        if (!in_array($this->imageMime, self::$allowedExt)) {
            return "Not a valid JPG or PNG image";
        }

        return $this->image($width, $height);
    }

    /**
     * @param int $width
     * @param int|null $height
     * @return string|null
     */
    private function image(int $width, int $height = null): ?string
    {
        $imageWebP = "{$this->cachePath}/{$this->imageName}.webp";
        $imageExt = "{$this->cachePath}/{$this->imageName}." . pathinfo($this->imagePath)['extension'];

        if ($this->webP && file_exists($imageWebP) && is_file($imageWebP)) {
            return $imageWebP;
        }

        if (file_exists($imageExt) && is_file($imageExt)) {
            return $imageExt;
        }

        return $this->imageCache($width, $height);
    }

    /**
     * @param string $name
     * @param int $width
     * @param int $height
     * @return string
     */
    protected function name(string $name, int $width = null, int $height = null): string
    {
        $filterName = filter_var(mb_strtolower(pathinfo($name)["filename"]), FILTER_SANITIZE_STRIPPED);
        $formats = 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜüÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿRr"!@#$%&*()_-+={[}]/?;:.,\\\'<>°ºª';
        $replace = 'aaaaaaaceeeeiiiidnoooooouuuuuybsaaaaaaaceeeeiiiidnoooooouuuyybyRr                                 ';
        $trimName = trim(strtr(utf8_decode($filterName), utf8_decode($formats), $replace));
        $name = str_replace(["-----", "----", "---", "--"], "-", str_replace(" ", "-", $trimName));

        $hash = $this->hash($this->imagePath);
        $widthName = ($width ? "-{$width}" : "");
        $heightName = ($height ? "x{$height}" : "");

        return "{$name}{$widthName}{$heightName}-{$hash}";
    }

    /**
     * @param string $path
     * @return string
     */
    protected function hash(string $path): string
    {
        return hash("crc32", pathinfo($path)['basename']);
    }

    /**
     * Clear cache
     *
     * @param string|null $imagePath
     * @example $t->flush("images/image.jpg"); clear image name and variations size
     * @example $t->flush(); clear all image cache folder
     */
    public function flush(string $imagePath = null): void
    {
        foreach (scandir($this->cachePath) as $file) {
            $file = "{$this->cachePath}/{$file}";
            if ($imagePath && strpos($file, $this->hash($imagePath))) {
                $this->imageDestroy($file);
            } elseif (!$imagePath) {
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

        imagecopyresampled($thumb, $source, 0, 0, $src_x, $src_y, $width, $height, $src_w, $src_h);
        imagejpeg($thumb, "{$this->cachePath}/{$this->imageName}.jpg", $this->quality);

        imagedestroy($thumb);
        imagedestroy($source);

        if ($this->webP) {
            return $this->toWebP("{$this->cachePath}/{$this->imageName}.jpg");
        }

        return "{$this->cachePath}/{$this->imageName}.jpg";
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
        imagecopyresampled($thumb, $source, 0, 0, $src_x, $src_y, $width, $height, $src_w, $src_h);
        imagepng($thumb, "{$this->cachePath}/{$this->imageName}.png", $this->compressor);

        imagedestroy($thumb);
        imagedestroy($source);

        if ($this->webP) {
            return $this->toWebP("{$this->cachePath}/{$this->imageName}.png");
        }

        return "{$this->cachePath}/{$this->imageName}.png";
    }

    /**
     * @param string $image
     * @param bool $unlinkImage
     * @return string
     */
    public function toWebP(string $image, $unlinkImage = true): string
    {
        try {
            $webPConverted = pathinfo($image)["dirname"] . "/" . pathinfo($image)["filename"] . ".webp";
            WebPConvert::convert($image, $webPConverted, ["default-quality" => $this->quality]);

            if ($unlinkImage) {
                unlink($image);
            }

            return $webPConverted;
        } catch (ConversionFailedException $exception) {
            return $image;
        }
    }
}
