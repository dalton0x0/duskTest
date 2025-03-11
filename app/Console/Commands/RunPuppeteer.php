<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class RunPuppeteer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:run-puppeteer';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting script Puppeteer');

        $process = new Process(['node', base_path('puppeteer-script.js')]);
        $process->setTimeout(120);

        try {
            $process->mustRun(function ($type, $buffer) {
                echo $buffer;
            });
            $this->info('Script executed.');
        } catch (ProcessFailedException $exception) {
            $this->error('Error while running script : ' . $exception->getMessage());
        }
    }
}
