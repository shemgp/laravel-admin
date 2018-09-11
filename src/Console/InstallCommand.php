<?php

namespace Encore\Admin\Console;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Console\Command;

class InstallCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'admin:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the admin package';

    /**
     * Install directory.
     *
     * @var string
     */
    protected $directory = '';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->initDatabase();

        $this->initAdminDirectory();
    }

    /**
     * Create tables and seed it.
     *
     * @return void
     */
    public function initDatabase()
    {
        $this->call('migrate');

        if (Administrator::count() == 0) {
            $this->call('db:seed', ['--class' => \Encore\Admin\Auth\Database\AdminTablesSeeder::class]);
        }
    }

    /**
     * Initialize the admAin directory.
     *
     * @return void
     */
    protected function initAdminDirectory()
    {
        $this->directory = config('admin.directory');

        if (is_dir($this->directory)) {
            $this->line("<error>{$this->directory} directory already exists !</error> ");

            return;
        }

        $this->makeDir('/');
        $this->line('<info>Admin directory was created:</info> '.str_replace(base_path(), '', $this->directory));

        $this->makeDir('Controllers');

        $this->createHomeController();
        $this->createExampleController();

        $this->createBootstrapFile();
        $this->createRoutesFile();

        $this->addInfyomGenerator();
    }

    /**
     * Create HomeController.
     *
     * @return void
     */
    public function createHomeController()
    {
        $homeController = $this->directory.'/Controllers/HomeController.php';
        $contents = $this->getStub('HomeController');

        $this->laravel['files']->put(
            $homeController,
            str_replace('DummyNamespace', config('admin.route.namespace'), $contents)
        );
        $this->line('<info>HomeController file was created:</info> '.str_replace(base_path(), '', $homeController));
    }

    /**
     * Create HomeController.
     *
     * @return void
     */
    public function createExampleController()
    {
        $exampleController = $this->directory.'/Controllers/ExampleController.php';
        $contents = $this->getStub('ExampleController');

        $this->laravel['files']->put(
            $exampleController,
            str_replace('DummyNamespace', config('admin.route.namespace'), $contents)
        );
        $this->line('<info>ExampleController file was created:</info> '.str_replace(base_path(), '', $exampleController));
    }

    /**
     * Create routes file.
     *
     * @return void
     */
    protected function createBootstrapFile()
    {
        $file = $this->directory.'/bootstrap.php';

        $contents = $this->getStub('bootstrap');
        $this->laravel['files']->put($file, $contents);
        $this->line('<info>Bootstrap file was created:</info> '.str_replace(base_path(), '', $file));
    }

    /**
     * Create routes file.
     *
     * @return void
     */
    protected function createRoutesFile()
    {
        $file = $this->directory.'/routes.php';

        $contents = $this->getStub('routes');
        $this->laravel['files']->put($file, str_replace('DummyNamespace', config('admin.route.namespace'), $contents));
        $this->line('<info>Routes file was created:</info> '.str_replace(base_path(), '', $file));
    }

    /**
     * Get stub contents.
     *
     * @param $name
     *
     * @return string
     */
    protected function getStub($name)
    {
        return $this->laravel['files']->get(__DIR__."/stubs/$name.stub");
    }

    /**
     * Make new directory.
     *
     * @param string $path
     */
    protected function makeDir($path = '')
    {
        $this->laravel['files']->makeDirectory("{$this->directory}/$path", 0755, true, true);
    }

    /**
     * Add Infyom generators in composer file
     */
    protected function addInfyomGenerator()
    {
        $file = base_path('composer.json');
        $composer_json = json_decode(file_get_contents($file), true);

        $composer_json['require']['infyomlabs/laravel-generator'] = "dev-5.5-datagrid-bootform-patches";
        $composer_json['require']['infyomlabs/adminlte-templates'] = "dev-5.5-datagrid-bootform-patches";

        $composer_json['repositories']['laravel-generator'] = [
                "type" => "vcs",
                "url" => "https://github.com/shemgp/laravel-generator.git"
            ];
        $composer_json['repositories']['adminlte-templates'] = [
                "type" => "vcs",
                "url" => "https://github.com/shemgp/adminlte-templates.git"
            ];
        $composer_json['repositories']['datagrid'] = [
                "type" => "vcs",
                "url" => "https://github.com/shemgp/datagrid"
            ];

        $composer_json["minimum-stability"] = "dev";

        $this->laravel['files']->put($file, json_encode($composer_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $this->line('<info>Infyom Generator added to composer.json:</info> '.str_replace(base_path(), '', $file));
        $this->line('<info>Please run</info> composer update <info>to install Infyom Generator.</info>');
    }
}
