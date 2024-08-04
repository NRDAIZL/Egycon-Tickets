<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Queue\DatabaseQueue;
use Illuminate\Queue\Jobs\DatabaseJob;
use Illuminate\Support\Facades\Log;

class ClearLogs extends Command
{
    protected $signature = 'log:cleanup';

    protected $description = 'Clear old logs.';

    protected $lines_threshold;

    protected $lines_to_keep;

    public function __construct()
    {
        parent::__construct();

        $this->lines_threshold = env('MAX_LOG_LINES_THRESHOLD', 2000);
        $this->lines_to_keep = env('MAX_LOG_LINES_TO_KEEP', 10);
    }
    public function handle()
    {
        $this->info('Cleaning up logs...');
        $this->comment('Found ' . count($this->getFiles()) . ' log files.');
        $this->clearOldLogs();
        $this->info('Logs have been cleaned up.');
    }

    private function getFiles()
    {
        $logFiles = glob(storage_path('logs/*.log'));
        return $logFiles;
    }


    private function clearOldLogs()
    {
        $logFiles = glob(storage_path('logs/*.log'));
        foreach ($logFiles as $logFile) {
            $this->info("Clearing old log file: $logFile");
            $lines = file($logFile);
            $this->comment('Found ' . count($lines) . ' lines in ' . $logFile);
            if (count($lines) < $this->lines_threshold) {
                $this->info('Log file is below threshold. Skipping.');
                continue;
            }
            $fileName = $this->copyFileToArchive($logFile);
            $linesToKeep = array_slice($lines, -$this->lines_to_keep);
            $linesToKeep = implode("", $linesToKeep);
            file_put_contents($logFile, "*Old logs have been archived in $fileName\n");
            file_put_contents($logFile, $linesToKeep, FILE_APPEND);
        }
    }


    private function copyFileToArchive($file)
    {
        $archiveDir = storage_path('logs/archive');
        if (!is_dir($archiveDir)) {
            mkdir($archiveDir);
        }
        $date = date('Y-m-d');
        $newFile = $archiveDir . '/' . basename($file, '.log') . '-' . $date . '.log';
        copy($file, $newFile);
        $this->info("Copied $file to $newFile");
        return $newFile;
    }



}
