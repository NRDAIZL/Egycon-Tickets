<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class EnsureQueueListenerIsRunning extends Command
{
    protected $signature = 'queue:checkup';

    protected $description = 'Ensure that the queue listener is running.';

    public function __construct()
    {
        parent::__construct();
    }
    public function handle()
    {
        if (!$this->isQueueListenerRunning()) {
            $this->comment('Queue listener is being started.');
            $pid = $this->startQueueListener();
            $this->saveQueueListenerPID($pid);
        }

        $this->comment('Queue listener is running.');
    }

    private function getProcessCommand($os, $pid){
        $command = "ps -p {$pid}";
        switch ($os) {
            case "linux":
                $this->info("Server is running on linux OS");
                $this->info("ps will be used");
                break;
            case "windows nt":
                $command = "tasklist /fi \"pid eq {$pid}\"";
                $this->info("Server is running on Windows OS");
                $this->info("Command tasklist will be used");
                break;
            default:
                $os = "linux";
                $this->error("Unable to detect OS");
                $this->info("ps will be used");
                break;
        }
        return $command;
    }

    private function isQueueListenerRunning()
    {
        if (!$pid = $this->getLastQueueListenerPID()) {
            return false;
        }
        $os = strtolower(php_uname('s'));
        $command = $this->getProcessCommand(strtolower(php_uname('s')), $pid);
        $process = exec($command);
        Log::info("process: ". $process);
        if($os == "windows nt"){
            $processIsQueueListener = !str_contains($process, 'No tasks are running'); 
        }else{
            $processIsQueueListener = !empty($process);
        }
        return $processIsQueueListener;
    }

    private function getLastQueueListenerPID()
    {
        if (!file_exists(__DIR__ . '/queue.pid')) {
            return false;
        }

        return file_get_contents(__DIR__ . '/queue.pid');
    }

    private function saveQueueListenerPID($pid)
    {
        file_put_contents(__DIR__ . '/queue.pid', $pid);
    }

    private function startQueueListener()
    {
        //$command = 'php-cli ' . base_path() . '/artisan queue:listen --timeout=60 --sleep=5 --tries=3 > /dev/null & echo $!'; // 5.1
        //$command = 'php-cli ' . base_path() . '/artisan queue:work --timeout=60 --sleep=5 --tries=3 > /dev/null & echo $!'; // 5.6 - see comments

        //handle memory issues
        $descriptorspec = [0 => ['pipe', 'r'], 1 => ['pipe', 'r'], 2 => ['pipe', 'r']];

        $command =  'php "' . base_path() . '\\artisan" queue:work --queue=default --delay=0 --timeout=30 --sleep=5 --tries=3 > nul 2>&1 & echo $!';
        Log::info("command: ". $command);
        $proc = proc_open($command, $descriptorspec, $pipes);
        $proc_details = proc_get_status($proc);
        $pid = $proc_details['pid'];
        // $pid = exec($command, $output);
        Log::info('t', [$proc_details]);

        return $pid;
    }
}
