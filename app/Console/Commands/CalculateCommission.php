<?php

namespace App\Console\Commands;

use App\Http\Controllers\ProcessCommissionController;
use Illuminate\Console\Command;

class CalculateCommission extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calculate:commission
                            {file : The CSV file path/name(if uploaded in public folder) to be processed}
                            {--P|public : If file is uploaded in the public folder}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculates commission based on data provided by excel file';

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
     * @return int
     */
    public function handle(): int
    {
        $file = $this->argument('file');
        $public = $this->option('public');
        try {
            if ($public) {
                $file_url = public_path($file);
            } else {
                $file_url = $file;
            }
            $commission = new ProcessCommissionController();
            $commission->processCommission($file_url);
        } catch (\Exception $e) {
            echo "Error Occurred\n";
        }
        return 0;
    }
}
