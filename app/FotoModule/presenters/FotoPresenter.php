<?php

namespace FotoModule;

use Nette\Image;

/**
 * FotoPresenter
 *
 * @author Petr Poupě
 */
class FotoPresenter extends BasePresenter
{

    private $original = "foto/original";
    private $resized = "foto";

    public function actionDefault($size = NULL, $name = NULL)
    {
        $trimOriginal = trim($this->original, "/");
        $filename = $trimOriginal . "/" . $name;
        if (empty($name) || !is_file($filename)) {
            if (preg_match("~^(.*)/[^/]*$~", $name, $matches)) {
                $name = $matches[1] . "/default.png";
                if (!is_file($trimOriginal . "/" . $name)) {
                    $name = "default.png";
                }
            } else {
                $name = "default.png";
            }
            $filename = $trimOriginal . "/" . $name;
        }

        $sizeX = 0;
        $sizeY = 0;
        if (preg_match("@^(\d+)\-(\d+)$@", $size, $matches)) {
            $sizeX = $matches[1];
            $sizeY = $matches[2];
        }

        if ($sizeX > 0 && $sizeY > 0) {
            $resized = trim($this->resized, "/") . "/" . "{$sizeX}-{$sizeY}" . "/" . $name;

            if (!\CommonHelpers::file_exists($resized) || filemtime($filename) > filemtime($resized)) {
                $img = Image::fromFile($filename);
                $img->resize($sizeX, $sizeY);
                $img->save($resized);
            }

            $filename = $resized;
        }

        Image::fromFile($filename)->send(Image::PNG);
        $this->terminate(); // stop redirecting this URL (htaccess redirecting)
    }

}
