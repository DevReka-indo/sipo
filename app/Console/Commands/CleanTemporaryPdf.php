<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CleanTemporaryPdf extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clean-temporary-pdf';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up temporary PDF files older than 1 day';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $path = storage_path('app/public/pdf');
        $files = File::files($path);
        $deleted = 0;
        foreach ($files as $file) {
            File::delete($file->getRealPath());
            $deleted++;
        }
        $this->info("Deleted {$deleted} temporary PDF files.");
    }
}
