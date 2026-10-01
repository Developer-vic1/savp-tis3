<?php

namespace App\Support;

final class PrivateFilePath
{
    public static function valid(?string $path, string $directory): bool
    {
        return is_string($path) && str_starts_with($path, trim($directory, '/').'/')
            && ! preg_match('/[\\\\\x00-\x1F\x7F]/', $path)
            && ! in_array('..', explode('/', $path), true)
            && ! in_array('.', explode('/', $path), true);
    }
}
