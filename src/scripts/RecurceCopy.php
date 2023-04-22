<?php

function recurseCopy($src, $dest)
{
    if (!is_readable($src)) {
        throw new Exception("Não foi possível ler o diretório '$src'.");
    }

    $dir = opendir($src);

    if (!is_dir($dest)) {
        if (!mkdir($dest, 0755, true)) {
            throw new Exception("Não foi possível criar o diretório '$dest'.");
        }
    }

    while (($file = readdir($dir)) !== false) {
        if ($file !== '.' && $file !== '..') {
            $srcPath = $src . '/' . $file;
            $destPath = $dest . '/' . $file;

            if (is_dir($srcPath)) {
                recurseCopy($srcPath, $destPath);
            } else {
                if (!copy($srcPath, $destPath)) {
                    throw new Exception("Não foi possível copiar o arquivo '$srcPath' para '$destPath'.");
                }
            }
        }
    }

    closedir($dir);
}

try {
    recurseCopy($src, $dest);
    echo "Arquivos copiados com sucesso.\n";
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}


recurseCopy(src: __DIR__ . '/../tests', dest: __DIR__ . '/../../../../tests/ArchCrudLaravel');
recurseCopy(src: __DIR__ . '/../migrations', dest: __DIR__ . '/../../../../database/migrations');