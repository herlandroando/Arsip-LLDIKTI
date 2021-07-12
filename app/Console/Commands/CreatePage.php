<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CreatePage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:view.js {view : Name of view that want to make}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make template page for vue with inertia support.';

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
    public function handle()
    {
        $path_name = $this->argument('view');
        if (Storage::disk("js_view")->exists("$path_name.vue")) {
            $this->error('Error : File existed!');
            return 0;
        }
        $template = "";
        $is_error = false;
        $i = 0;
        $template = file_get_contents(base_path("resources/template/main.stub"));
        Storage::disk("js_view")->put("$path_name.vue", $template);
        $this->info('Success : The command was successful!');
        return 0;
    }
}
