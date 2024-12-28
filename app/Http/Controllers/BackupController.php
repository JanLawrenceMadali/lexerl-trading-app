<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Services\ActivityLoggerService;
use App\Services\DatabaseBackupService;
use Illuminate\Http\Request;

class BackupController extends Controller
{
    protected $backupService;
    protected $activityLog;

    public function __construct(
        DatabaseBackupService $databaseBackupService,
        ActivityLoggerService $activityLoggerService
    ) {
        $this->backupService = $databaseBackupService;
        $this->activityLog = $activityLoggerService;
    }

    public function index()
    {
        $backups = $this->backupService->getAllBackups();

        return inertia('Settings/BackupDatabases/Index', [
            'backups' => $backups
        ]);
    }

    public function manualBackup()
    {
        try {
            $filename = now()->format('Y-m-d_H-i-s') . '_database.sqlite';

            $this->backupService->createManualBackup($filename);

            $this->activityLog->logDatabaseBackup(
                ActivityLog::ACTION_BACKUP,
                "{$filename} was backup manually",
            );

            return redirect()->back()->with('success', 'Database backup manually created successfully.');
        } catch (\Exception $e) {
            report($e);
            return redirect()->back()->with('error', $e->getMessage() ?? 'Failed to create a backup');
        }
    }

    public function download(Request $request)
    {
        try {
            $filename = $request->input('file');

            $zipPath = $this->backupService->createBackupZip($filename);

            $this->activityLog->logDatabaseBackup(
                ActivityLog::ACTION_DOWNLOAD,
                "{$filename} was downloaded",
            );

            return response()->download($zipPath)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage() ?? 'Failed to download backup');
        }
    }

    public function cleanAll()
    {
        try {
            $deletedCount = $this->backupService->deleteAllBackups();

            if ($deletedCount === 0) {
                return redirect()->back()->with('info', 'No backups to delete.');
            }

            $this->activityLog->logDatabaseBackup(
                ActivityLog::ACTION_DELETED,
                "All backups were deleted",
            );

            return redirect()->back()->with('success', 'All backups successfully deleted.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage() ?? 'Failed to delete backups');
        }
    }

    public function restore(Request $request)
    {
        try {
            $backupFile = $request->input('backup_file');

            $this->backupService->restoreBackup($backupFile);

            $this->activityLog->logDatabaseBackup(
                ActivityLog::ACTION_RESTORE,
                "{$backupFile} was restored",
            );

            return redirect()->back()->with('success', 'Database successfully restored from the uploaded backup.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to restore database: ' . $e->getMessage());
        }
    }

    public function destroy(Request $request)
    {
        try {
            $filename = $request->input('file');

            $this->backupService->deleteBackup($filename);

            $this->activityLog->logDatabaseBackup(
                ActivityLog::ACTION_DELETED,
                "{$filename} was deleted",
            );

            return redirect()->back()->with('success', 'Backup successfully deleted.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage() ?? 'Failed to delete backup');
        }
    }
}
