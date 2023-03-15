<?php

namespace LanciWeb\LaravelMakeCrud\Commands;

use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Doctrine\Inflector\InflectorFactory;

class MakeCrudCommand extends Command
{

    /**
     * The original string passed as an argument from the user. 
     * It can contains a namespace
     *
     * @var string
     */
    private string $model_class;

    /**
     * The name of the model, stripped from any namespace.
     *
     * @var string
     */
    private string $model_name;


    /**
     * The plural form of the model and its namespace.
     *
     * @var string
     */
    private string $plural_form;


    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:crud {model : The model to be created}
    {--a|all : Adds Migration, Seeder, Resource Controllers, Factory, Policy and FormRequest and views}
    {--api : Adds an Api Resource Controller}
    {--c|controller : Adds the Controller}
    {--m|migration : Adds the Migration}
    {--s|seeder : Adds the Controller}
    {--p|policy : Adds the Policy}
    {--f|factory : Adds the Factory}
    {--R|requests : Adds the Form Requests}
    {--b|views : Adds the Views}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates Model, Migration, Seeder, Resource Controllers and views for a given resource.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {

        $this->setModelData();


        $needs_controller = $this->hasNoOptions() || $this->option('all')  || $this->option('controller') || $this->option('api');
        $needs_views = $this->hasNoOptions() || (!$this->option('api') && ($this->option('all') || $this->option('views')));
        $needs_routes = $this->hasNoOptions() || $this->option('all') || $this->option('api');


        // # Model 
        $this->generateModel();

        // # Controller
        if ($needs_controller) $this->generateController();

        // # Routes
        if ($needs_routes) $this->registerWebRoutes();

        // # Views
        if ($needs_views) $this->generateViews();
    }

    /**
     * Ensures the argument provided by the user is correctly formatted
     */
    private function setModelData()
    {

        $model_class = $this->argument('model');
        $model_class = Str::replace('.', '/', $model_class);
        $elements  = explode('/', $model_class);

        $elements = Arr::map($elements, fn ($e) => ucfirst($e));
        $model_class = Arr::join($elements, '/');


        $this->model_class = $model_class;
        $this->model_name = array_pop($elements);

        $this->setPluralForm();
    }

    public function setPluralForm()
    {
        $inflector = InflectorFactory::create()->build();

        $this->plural_form =  $inflector->pluralize($this->model_class);
    }


    /**
     * Checks whether the user did not provide any option
     * 
     * @return bool
     */
    private function hasNoOptions(): bool
    {
        foreach ($this->options() as $o => $v) if ($v) return false;
        return true;
    }


    /**
     * Generates the model and optional classes exept controllers
     * 
     * @return void
     */
    private function generateModel(): void
    {


        $options =  [
            'name' => $this->model_name,
            '--migration' => $this->option('all') || $this->hasNoOptions() || $this->option('migration'),
            '-s' => $this->option('all') || $this->hasNoOptions() || $this->option('seeder'),
            '-f' => $this->option('all') || $this->option('factory'),
            '--policy' => $this->option('all') || $this->option('policy'),
            '--requests' => $this->option('all') || $this->option('requests')
        ];

        $this->call('make:model', $options);
    }

    /**
     *  Generates the controller
     */
    private function generateController(): void
    {

        $controller_name = $this->model_class . 'Controller';

        if ($this->option('api')) $controller_name = 'Api/' . $controller_name;

        $options = [
            'name' => $controller_name,
            '--model' => $this->model_name,
            '--resource' => true,
            '--api' => $this->option('api')
        ];

        $this->call('make:controller', $options);
    }

    /**
     * Generate the blade files for the crud options.
     * This is executed if the 'all' or 'views' option is provided.
     * It is also executed if no option is provided.
     * 
     * @return void
     */
    private function generateViews(): void
    {
        $view_path = Str::lower(Str::replace('/', '.', $this->plural_form));
        $this->call('make:view', ['path' => $view_path, '-c' => true]);
    }

    /**
     * Registers the routes on web.php routes
     * This is executed if the 'all' or 'resource' option is provided.
     * It is also executed if no option is provided.
     * 
     * @return void
     */
    private function registerWebRoutes(): void
    {
        // Checks whether is api routes
        $is_api = $this->option('api');

        // Prepares the name of the resource (i.e: 'admin.posts')
        $resource_name = Str::lower($this->plural_form);

        // Prepares the name of the controller class with namespace

        $namespace = 'App\Http\Controllers\\';
        if ($is_api) $namespace .= 'Api\\';
        $full_class = $namespace . Str::replace('/', '\\', $this->model_class);
        $controller_class = $full_class . 'Controller::class';

        // Prepares the parameters for the Route::resource method
        $params = "'$resource_name', $controller_class";


        // Checks whether the mdel provided by the user has a subfolder (i.e.: 'Admin/Post')
        $has_prefix = Str::contains($this->model_class, '/');

        // extract the name of the folder if present
        if ($has_prefix) {
            $elements = explode('/', $this->model_class);
            $folder_name = Str::lower($elements[0]);

            // adds the prefix to the routes names
            if (!$is_api) $params .= ", ['as' => '$folder_name']";
        }

        // Prepare the path of the web routes file
        $routes_file = $is_api ? 'api' : 'web';
        $route_method = $is_api ? 'apiResource' : 'resource';

        //  Writes on the web routes file
        File::append(base_path("routes/$routes_file.php"), "\nRoute::$route_method($params);");

        // Outputs result
        $this->info("Routes registered successfully");
        $this->newLine(2);
    }
}
