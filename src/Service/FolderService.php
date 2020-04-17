<?php

namespace Genesis\SQLExtensionWrapper\Service;

use Exception;

class FolderService
{
    public static function createPath($path)
    {
        if (!is_dir($path)) {
            if (!mkdir($path, 0777, true)) {
                throw new Exception('Unable to create path ' . $path);
            }
        }
    }

    public static function cleanPath($folder, $file)
    {
        return str_replace('//', '/', $folder . DIRECTORY_SEPARATOR . $file);
    }
}
