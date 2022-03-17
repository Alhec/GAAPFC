<?php

namespace App\Console\Commands;

use App\Log;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DatabaseBackUp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'database:backup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Realiza un backup de la base de datos.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $filename = "backup-" . Carbon::now()->format('Y-m-d') . ".sql";

        $command = "mysqldump -u ". env('DB_USERNAME') ." -p" . env('DB_PASSWORD') ." -h" .
            env('DB_HOST') ." ". env('DB_DATABASE') . " --no-tablespaces"."  > " . storage_path() ."/app/backup/" . $filename;
        $returnVar = NULL;
        $output  = NULL;

        exec($command, $output, $returnVar);
    }
}
