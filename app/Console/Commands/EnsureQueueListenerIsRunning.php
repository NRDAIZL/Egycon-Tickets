<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Queue\DatabaseQueue;
use Illuminate\Queue\Jobs\DatabaseJob;
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
        if (!$this->isQueueListenerRunningUsingPS()) {
            $this->comment('Queue listener is being started.');
            $this->startQueueListenerUsingNoHup();
            $this->info('Queue listener has been started.');
        } else {
            $this->info('Queue listener is already running.');
        }
    }

    private function isQueueListenerRunningUsingPS()
    {
        $output = [];
        exec('ps auxww | grep "queue:work" | grep -v grep 2>&1', $output);
        $this->info("Output: " . implode("\n", $output));
        return count($output) > 1;
    }

    private function isQueueListenerRunning()
    {
        /** @var DatabaseQueue $jobs  */
        $jobs = app('queue')->connection('database');
        $size = $jobs->size();
        if(!$lastSize = $this->getLastSize()){
            $this->saveQueueSize($size, time());
            return false;
        }
        $this->info("Last restart: " . (time() - $lastSize[1]) . " seconds ago");
        $this->info("Last checkup: " . (time() - $lastSize[2]) . " seconds ago");
        if($size > $lastSize[0]) {
            if(time() - $lastSize[1] >= 300 && time() - $lastSize[2] >= 5) {
                $this->error("Queue size has increased. Restarting queue listener.");
                $this->saveQueueSize($size, time());
                return false;
            }
        }
        $this->saveQueueSize($size, $lastSize[1]);
        $this->info("Current Queue Size: ".$size);
        return true;
    }

    private function getLastSize()
    {
        if (!file_exists(__DIR__ . '/queue.size')) {
            return false;
        }
        $data = file_get_contents(__DIR__ . '/queue.size');
        $data = explode(",", $data);
        return $data;
    }

    private function saveQueueSize($size, $time)
    {
        file_put_contents(__DIR__ . '/queue.size', "$size,$time,".time()); // queueSize, lastRestartTime, lastCheckupTime
    }

    private function startQueueListener()
    {
        $descriptorspec = [0 => ['pipe', 'r'], 1 => ['pipe', 'r'], 2 => ['pipe', 'r']];
        $command =  'php "' . base_path("artisan") . '" queue:work --queue=default --delay=0 --timeout=30 --sleep=2 --tries=3 > nul 2>&1 & echo $!';
        Log::info("command: ". $command);
        $proc = proc_open($command, $descriptorspec, $pipes);
        $proc_details = proc_get_status($proc);
        $pid = $proc_details['pid'];
        // $pid = exec($command, $output);
        Log::info('t', [$proc_details]);

        return $pid;
    }

    private function startQueueListenerUsingNoHup()
    {
        $output = [];
        $command =  'nohup php "' . base_path("artisan") . '" queue:work --daemon >> '. base_path('storage/logs/laravel-queue.log').' 2>&1 &';
        $command =  'nohup php "' . base_path("artisan") . '" queue:work --daemon >> '. base_path('storage/logs/laravel-queue.log').' 2>&1 &';
        Log::info("command: " . $command);
        $pid = exec($command, $output);
        Log::info('t', $output);
        return $pid;
    }
}
