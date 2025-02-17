<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
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

        // Check if database file exists first
        if (!file_exists($databasePath)) {
            throw new \Exception('Database file does not exist.');
        }

        // Ensure the backup directory exists with proper permissions
        if (!File::isDirectory($backupDirectory)) {
            File::makeDirectory($backupDirectory, 0755, true, true);
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
            // Close existing database connections
            DB::disconnect();

            // Replace the current database with the backup
            File::copy($backupPath, $databasePath);

            // Set proper permissions
            chmod($databasePath, 0644);

            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function uploadRestoreBackup($file): bool
    {
        try {
            $databasePath = database_path('database.sqlite');
            $timestamp = now()->format('Y-m-d-H-i-s');

            $backupPath = storage_path("app/backups/{$timestamp}_database.sqlite");

            if (File::exists($databasePath)) {
                File::copy($databasePath, $backupPath);
            }

            // Close existing database connections
            DB::disconnect();

            // Remove current database file
            if (File::exists($databasePath)) {
                File::delete($databasePath);
            }

            // Upload and move the new database file
            $uploadedFile = $file;
            $uploadedFile->move(database_path(), 'database.sqlite');

            // Set proper permissions
            chmod($databasePath, 0644);

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

    public function purgeTransaction()
    {
        DB::table('sales')->truncate();
        DB::table('inventories')->truncate();
        DB::table('purchases')->truncate();
    }
}
