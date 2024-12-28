<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class DatabaseBackupService
{
    public function getAllBackups(): array
    {
        $backups = [];
        $backupDirectory = storage_path('app/backups');

        if (file_exists($backupDirectory)) {
            $files = scandir($backupDirectory, SCANDIR_SORT_DESCENDING);
            foreach ($files as $file) {
                if ($file != '.' && $file != '..') {
                    $backups[] = $file;
                }
            }
        }

        return $backups;
    }

    public function createManualBackup(string $filename): bool
    {
        $databasePath = database_path('database.sqlite');
        $backupDirectory = storage_path('app/backups');
        $backupFileName = $filename;
        $backupPath = $backupDirectory . '/' . $backupFileName;

        // Ensure the backup directory exists
        Storage::makeDirectory('backups');

        // Check if database file exists
        if (!file_exists($databasePath)) {
            throw new \Exception('Database file does not exist.');
        }

        // Copy the database file
        if (!copy($databasePath, $backupPath)) {
            throw new \Exception('Failed to copy database file.');
        }

        return true;
    }

    public function createBackupZip(string $filename): string
    {
        $backupPath = storage_path('app/backups/' . $filename);

        if (!file_exists($backupPath)) {
            throw new \Exception('Backup file not found.');
        }

        // Create a ZIP file
        $zipFileName = pathinfo($filename, PATHINFO_FILENAME) . '.zip';
        $zipPath = storage_path('app/backups/' . $zipFileName);

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
            throw new \Exception('Failed to create ZIP file. Check permissions.');
        }

        $zip->addFile($backupPath, basename($backupPath));
        $zip->close();

        return $zipPath;
    }

    public function restoreBackup($backupFileName): bool
    {
        $databasePath = database_path('database.sqlite');
        $backupPath = storage_path('app/backups/' . $backupFileName);

        if (!file_exists($backupPath)) {
            throw new \Exception('Backup file not found.');
        }

        try {
            // Replace the current database with the backup
            File::copy($backupPath, $databasePath);
            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function deleteBackup($backupFileName): bool
    {
        $backupPath = storage_path('app/backups/' . $backupFileName);

        if (file_exists($backupPath)) {
            return File::delete($backupPath);
        }

        return false;
    }

    public function deleteAllBackups(): int
    {
        $backupDirectory = storage_path('app/backups');

        if (!file_exists($backupDirectory)) {
            return 0;
        }

        // Get all files in the backup directory
        $files = File::files($backupDirectory);

        if (empty($files)) {
            return 0;
        }

        // Delete each file
        $deletedCount = 0;
        foreach ($files as $file) {
            if (File::delete($file)) {
                $deletedCount++;
            }
        }

        return $deletedCount;
    }
}
