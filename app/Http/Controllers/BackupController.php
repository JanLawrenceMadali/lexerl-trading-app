<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class BackupController extends Controller
{
    public function index()
    {
        $backups = [];
        $backupDirectory = storage_path('app/backups');

        if (file_exists($backupDirectory)) {
            $files = scandir($backupDirectory);
            foreach ($files as $file) {
                if ($file != '.' && $file != '..') {
                    $backups[] = $file;
                }
            }
        }

        return inertia('Settings/BackupDatabases/Index', [
            'backups' => $backups
        ]);
    }

    public function manualBackup()
    {
        sleep(1);

        $databasePath = database_path('database.sqlite');
        $backupDirectory = storage_path('app/backups');
        $backupFileName = now()->format('Y-m-d_H-i-s') . '_database.sqlite';
        $backupPath = $backupDirectory . '/' . $backupFileName;

        try {
            // Ensure the backup directory exists
            Storage::makeDirectory('backups');

            // Copy the database file to the backup location
            if (!file_exists($databasePath)) {
                return redirect()->back()->with('error', 'Database file does not exist.');
            }

            copy($databasePath, $backupPath);

            $this->logs('manual', $backupFileName);

            return redirect()->back()->with('success', 'Database backup created successfully.');
        } catch (\Exception $e) {
            report($e);
            return redirect()->back()->with('error', 'Failed to create a backup: ' . $e->getMessage());
        }
    }

    public function download(Request $request)
    {
        $filename = $request->input('file');
        $backupPath = storage_path('app/backups/' . $filename);

        if (!file_exists($backupPath)) {
            return redirect()->back()->with('error', 'Backup file not found.');
        }

        // Create a ZIP file
        $zipFileName = pathinfo($filename, PATHINFO_FILENAME) . '.zip';
        $zipPath = storage_path('app/backups/' . $zipFileName);

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
            return redirect()->back()->with('error', 'Failed to create ZIP file. Check permissions.');
        }

        $zip->addFile($backupPath, basename($backupPath));
        $zip->close();

        $this->logs('download', $zipFileName);

        // Return the ZIP file for download
        return response()->download($zipPath)->deleteFileAfterSend(true);
    }

    public function cleanAll()
    {
        $backupDirectory = storage_path('app/backups');

        if (!file_exists($backupDirectory)) {
            return redirect()->back()->with('error', 'Backup directory does not exist.');
        }

        // Get all files in the backup directory
        $files = File::files($backupDirectory);

        if (empty($files)) {
            return redirect()->back()->with('info', 'No backups to delete.');
        }

        // Loop through and delete each file
        foreach ($files as $file) {
            File::delete($file);
        }

        $this->logs('deleted');

        return redirect()->back()->with('success', 'All backups successfully deleted.');
    }

    public function restore(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|max:' . env('UPLOAD_MAX_SIZE', 10240), // 10MB max size (configurable)
        ]);

        $uploadedFile = $request->file('backup_file');
        $databasePath = database_path('database.sqlite');

        // Declare the variable outside the try block
        $backupCurrentDatabase = null;

        try {
            // Backup the current database (optional)
            $timestamp = now()->format('Y_m_d_His');
            $backupCurrentDatabase = storage_path("app/backups/backup_$timestamp.sqlite");

            if (file_exists($databasePath)) {
                File::copy($databasePath, $backupCurrentDatabase);
            }

            // Move the uploaded file and replace the current database
            $uploadedFile->move(database_path(), 'database.sqlite');

            $this->logs('restore', $uploadedFile->getClientOriginalName());

            return redirect()->back()->with('success', 'Database successfully restored from the uploaded backup.');
        } catch (\Exception $e) {
            // Rollback to previous database in case of failure
            if ($backupCurrentDatabase && file_exists($backupCurrentDatabase)) {
                File::copy($backupCurrentDatabase, $databasePath);
            }
            return redirect()->back()->with('error', 'Failed to restore database: ' . $e->getMessage());
        }
    }

    private function logs(string $action, string $description = null)
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'description' => Auth::user()->username . ' ' . $action . ' a backup ' . $description
        ]);
    }
}
