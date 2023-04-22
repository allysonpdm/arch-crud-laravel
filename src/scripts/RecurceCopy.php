<?php

function recurse_copy(string $src, string $dest)
{
    $dir = opendir($src);
    @mkdir($dest);

    while (false !== ($file = readdir($dir))) {
        if (($file != '.') && ($file != '..')) {
            if (is_dir($src . '/' . $file)) {
                recurse_copy($src . '/' . $file, $dest . '/' . $file);
            } else {
                copy($src . '/' . $file, $dest . '/' . $file);
            }
        }
    }

    closedir($dir);
}

recurse_copy(src: __DIR__ . '/../tests', dest: __DIR__ . '/../../../../tests/ArchCrudLaravel');
recurse_copy(src: __DIR__ . '/../migrations', dest: __DIR__ . '/../../../../database/migrations');