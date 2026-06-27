<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class DatabaseBackup extends Command
{
    protected $signature = 'db:backup {--keep=7 : Number of daily backups to keep}';
    protected $description = 'Backup the MySQL database to a SQL file';

    public function handle(): int
    {
        $dbName = config('database.connections.mysql.database');
        $dbUser = config('database.connections.mysql.username');
        $dbPass = config('database.connections.mysql.password');
        $dbHost = config('database.connections.mysql.host');
        $dbPort = config('database.connections.mysql.port');

        $date = now()->format('Y-m-d_H-i-s');
        $filename = "backup_{$dbName}_{$date}.sql";
        $path = storage_path("app/backups/{$filename}");

        $backupDir = storage_path('app/backups');
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $command = sprintf(
            'mysqldump --host=%s --port=%s --user=%s --password=%s --single-transaction --routines --triggers %s > %s',
            escapeshellarg($dbHost),
            escapeshellarg($dbPort),
            escapeshellarg($dbUser),
            escapeshellarg($dbPass),
            escapeshellarg($dbName),
            escapeshellarg($path)
        );

        $this->info("Dumping database '{$dbName}'...");
        $output = null;
        $returnCode = 0;
        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            $this->error("Backup failed (exit code {$returnCode})");
            return Command::FAILURE;
        }

        $size = filesize($path);
        $this->info("Backup created: {$filename} (" . round($size / 1024 / 1024, 2) . " MB)");

        // Rotate old backups
        $keep = (int) $this->option('keep');
        $files = glob(storage_path('app/backups/backup_*.sql'));
        usort($files, fn($a, $b) => filemtime($a) - filemtime($b));

        $toDelete = array_slice($files, 0, max(0, count($files) - $keep));
        foreach ($toDelete as $file) {
            unlink($file);
            $this->line("Deleted old backup: " . basename($file));
        }

        return Command::SUCCESS;
    }
}
