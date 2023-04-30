<?php

namespace ArchCrudLaravel\App\Tests\Traits;

use FilesystemIterator;

trait RemoveMigrations
{
    protected function removeMigrations()
    {
        $libraryDirectory = __DIR__.'/../../../../../../database/migrations';
        $projectDirectory = database_path('migrations');
        $libraryDirectoryIterator = new FilesystemIterator($libraryDirectory);

        $libraryDirectoryFiles = [];
        foreach ($libraryDirectoryIterator as $fileInfo) {
            if ($fileInfo->isFile()) {
                $libraryDirectoryFiles[] = $fileInfo->getFilename();
            }
        }

        $projectDirectoryIterator = new FilesystemIterator($projectDirectory);
        foreach ($projectDirectoryIterator as $fileInfo) {
            if ($fileInfo->isFile() && in_array($fileInfo->getFilename(), $libraryDirectoryFiles)) {
                unlink($projectDirectory . '/' . $fileInfo->getFilename());
            }
        }
    }
}
