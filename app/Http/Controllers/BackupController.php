<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Services\ActivityLoggerService;
use App\Services\DatabaseBackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class BackupController extends Controller
{
    protected $backupService;
    protected $activityLog;
    private $actor;

    public function __construct(
        DatabaseBackupService $databaseBackupService,
        ActivityLoggerService $activityLoggerService
    ) {
        $this->backupService = $databaseBackupService;
        $this->activityLog = $activityLoggerService;
        $this->actor = Auth::user()->username;
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
                "database was backed up ({$filename})",
            );

            return redirect()->back()->with('success', 'Database backup completed successfully!');
        } catch (\Exception $e) {
            report($e);
            return redirect()->back()->with('error', $e->getMessage() ?? 'Failed to create a data backup');
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
            return redirect()->back()->with('error', $e->getMessage() ?? 'Failed to download database backup');
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
                "{$this->actor} deleted all backups",
            );

            return redirect()->back()->with('success', 'All database backups deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage() ?? 'Failed to delete all database backups');
        }
    }

    public function restore(Request $request)
    {
        try {
            $backupFile = $request->input('backup_file');

            // Store current session data
            $userId = Auth::id();
            $rememberToken = Auth::user()->getRememberToken();

            $this->backupService->restoreBackup($backupFile);

            // Reconnect and restore session
            DB::reconnect();

            // Update remember token in the restored database
            DB::table('users')
                ->where('id', $userId)
                ->update(['remember_token' => $rememberToken]);

            // Clear cache and regenerate session
            Artisan::call('cache:clear');
            $request->session()->regenerate();

            $this->activityLog->logDatabaseBackup(
                ActivityLog::ACTION_RESTORE,
                "{$this->actor} restored {$backupFile}",
            );

            return redirect()->back()->with('success', 'Database restored successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage() ?? 'Failed to restore database');
        }
    }

    public function upload_restore(Request $request)
    {
        try {
            $request->validate([
                'backup' => 'required|file|mimetypes:application/x-sqlite3,application/octet-stream,application/vnd.sqlite3|max:10240',
            ]);

            $backupFile = $request->file('backup');

            $filename = $backupFile->getClientOriginalName();

            // Store current session data
            $userId = Auth::id();
            $rememberToken = Auth::user()->getRememberToken();

            $this->backupService->uploadRestoreBackup($backupFile);

            // Reconnect and restore session
            DB::reconnect();

            // Update remember token in the restored database
            DB::table('users')
                ->where('id', $userId)
                ->update(['remember_token' => $rememberToken]);

            // Clear cache and regenerate session
            Artisan::call('cache:clear');
            $request->session()->regenerate();

            $this->activityLog->logDatabaseBackup(
                ActivityLog::ACTION_RESTORE,
                "{$filename} was restored from the uploaded backup",
            );

            return redirect()->back()->with('success', 'Database uploaded and restored successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage() ?? 'Failed to upload and restore database');
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

            return redirect()->back()->with('success', 'Database backup deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage() ?? 'Failed to delete database backup');
        }
    }

    public function purge_transaction(Request $request)
    {
        $request->validate([
            'password' => 'required'
        ]);

        if (Auth::user()->role_id !== 1) {
            return redirect()->back()->with('error', 'You are not authorized to perform this action.');
        }

        if (!Hash::check($request->password, auth()->user()->password)) {
            return back()->withErrors([
                'password' => 'The provided password is incorrect.'
            ]);
        }


        try {
            $this->backupService->purgeTransaction();

            $this->activityLog->logDatabaseBackup(
                ActivityLog::ACTION_PURGE,
                "{$this->actor} purged transaction",
            );

            return redirect()->back()->with('success', 'Transactions purged successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage() ?? 'Failed to purge a transaction');
        }
    }
}
