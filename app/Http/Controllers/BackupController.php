<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
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
            if (!file_exists($backupDirectory)) {
                mkdir($backupDirectory, 0755, true);
            }

            // Copy the database file to the backup location
            copy($databasePath, $backupPath);

            $this->logs('Manual Backup Database');
        } catch (\Exception $e) {
            report($e);
        }
    }

    public function download(Request $request)
    {
        $filename = $request->input('file');
        $backupPath = storage_path('app/backups/' . $filename);

        if (!file_exists($backupPath)) {
            abort(404, 'File not found.');
        }

        // Create a ZIP file
        $zipFileName = pathinfo($filename, PATHINFO_FILENAME) . '.zip';
        $zipPath = storage_path('app/backups/' . $zipFileName);

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            $zip->addFile($backupPath, basename($backupPath));
            $zip->close();
        } else {
            abort(500, 'Could not create ZIP file.');
        }

        $this->logs('Download Backup Database');
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

        $this->logs('Clean All Backup Database');

        return redirect()->back()->with('success', 'All backups successfully deleted.');
    }

    public function restore(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|max:10240', // 10MB max size
        ]);

        $uploadedFile = $request->file('backup_file');
        $databasePath = database_path('database.sqlite');

        try {
            // Backup the current database (optional)
            $timestamp = now()->format('Y_m_d_His');
            $backupCurrentDatabase = storage_path("app/backups/backup_$timestamp.sqlite");
            if (file_exists($databasePath)) {
                File::copy($databasePath, $backupCurrentDatabase);
            }

            // Move the uploaded file and replace the current database
            $uploadedFile->move(database_path(), 'database.sqlite');

            $this->logs('Restore Backup Database');
            return redirect()->back()->with('success', 'Database successfully restored from the uploaded backup.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to restore database: ' . $e->getMessage());
        }
    }

    private function logs(string $action)
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'description' => $action . ' by ' . Auth::user()->username,
        ]);
    }
}
