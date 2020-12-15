<?php /** @noinspection ALL */

/** @noinspection PhpIllegalStringOffsetInspection */

namespace Cloudteam\Core\Console\Commands;

use File;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Class CrudViewCommand
 *
 * php artisan crud:view brands --fields=name#string;type#select#options=1_New**2_NotNew --view-path=Business --validations=name#required
 *
 * @package Cloudteam\Core\Console\Commands
 */
class CrudViewCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crud:view
                            {name : Tên của table trong database.}
                            {--fields= : Tên các column để hiện trong view.}
                            {--view-path= : The name of the view path.}
                            {--pk=id : The name of the primary key.}
                            {--validations= : Khai báo field validation trong controller.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create views for the Crud.';

    /**
     * View Directory Path.
     *
     * @var string
     */
    protected $viewDirectoryPath;

    /**
     *  Form field types collection.
     *
     * @var array
     */
    protected $typeLookup = [
        'string'   => 'text',
        'char'     => 'text',
        'varchar'  => 'text',
        'text'     => 'textarea',
        'password' => 'password',
        'email'    => 'email',
        'number'   => 'text',
        'date'     => 'date',
        'datetime' => 'datetime-local',
        'time'     => 'time',
        'boolean'  => 'radio',
        'select'   => 'select',
        'file'     => 'file',
    ];

    /**
     * Variables that can be used in stubs
     *
     * @var array
     */
    protected $vars = [
        'formFields',
        'formFieldsHtml',
        'varName',
        'crudName',
        'crudNameCap',
        'crudNameSingular',
        'primaryKey',
        'modelName',
        'title',
        'route',
        'modelNameCap',
        'viewName',
        'routePrefix',
        'routePrefixCap',
        'routeGroup',
        'formHeadingHtml',
        'formSearchHtml',
        'viewTemplateDir',
        'formBodyHtmlForShowView',
        'userViewPath',
    ];

    /**
     * Form's fields.
     *
     * @var array
     */
    protected $formFields = [];

    /**
     * Html of Form's fields.
     *
     * @var string
     */
    protected $formFieldsHtml = '';

    /**
     * Number of columns to show from the table. Others are hidden.
     *
     * @var integer
     */
    protected $defaultColumnsToShow = 3;

    /**
     * Variable name with first letter in lowercase
     *
     * @var string
     */
    protected $varName = '';

    /**
     * Name of the Crud.
     *
     * @var string
     */
    protected $crudName = '';

    /**
     * Crud Name in capital form.
     *
     * @var string
     */
    protected $crudNameCap = '';

    /**
     * Crud Name in singular form.
     *
     * @var string
     */
    protected $crudNameSingular = '';

    /**
     * Primary key of the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Name of the Model.
     *
     * @var string
     */
    protected $modelName = '';

    /**
     * Name of the Model with first letter in capital
     *
     * @var string
     */
    protected $modelNameCap = '';

    /**
     * Name of the View Dir.
     *
     * @var string
     */
    protected $viewName = '';

    /**
     * Name or prefix of the Route Group.
     *
     * @var string
     */
    protected $routeGroup = '';

    /**
     * Html of the form heading.
     *
     * @var string
     */
    protected $formHeadingHtml = '';

    /**
     * Html of the form body.
     *
     * @var string
     */
    protected $formSearchHtml = '';

    /**
     * Html of view to show.
     *
     * @var string
     */
    protected $formBodyHtmlForShowView = '';

    /**
     * User defined values
     *
     * @var array
     */
    protected $customData = [];

    /**
     * Template directory where views are generated
     *
     * @var string
     */
    protected $viewTemplateDir = '';

    /**
     * Delimiter used for replacing values
     *
     * @var array
     */
    protected $delimiter;

    /**
     * Delimiter used for replacing values
     *
     * @var array
     */
    protected $title;

    /**
     * Delimiter used for replacing values
     *
     * @var array
     */
    protected $route;

    protected $userViewPath;

    /**
     * Create a new command instance.
     *
     */
    public function __construct()
    {
        parent::__construct();

        if (config('basecore.view_columns_number')) {
            $this->defaultColumnsToShow = config('basecore.view_columns_number');
        }

        $this->delimiter = config('basecore.custom_delimiter') ?: ['%%', '%%'];
    }

    /**
     * Execute the console command.
     *
     * @return void
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function handle()
    {
        $formHelper              = 'html';
        $this->viewDirectoryPath = \dirname(__DIR__) . '/stubs/views/' . $formHelper . '/';

        $this->route       = $this->argument('name');
        $this->crudName    = Str::snake($this->argument('name'));
        $this->varName     = lcfirst($this->argument('name'));
        $this->crudNameCap = ucwords($this->crudName);

        $this->modelName    = $this->crudNameSingular = Str::singular((variablize($this->argument('name'))));
        $this->modelNameCap = Str::singular((Str::studly($this->route)));

        $this->title      = camel2words(Str::singular((Str::studly($this->argument('name')))));
        $this->primaryKey = $this->option('pk');
        $this->viewName   = $this->route;

        $viewDirectory = config('view.paths')[0] . '/modules/';
        $path          = $viewDirectory . $this->viewName . '/';
        if ($this->option('view-path')) {
            $this->userViewPath = lcfirst($this->option('view-path'));
            $path               = $viewDirectory . $this->userViewPath . '/' . $this->viewName . '/';
        }

        $this->viewTemplateDir = isset($this->userViewPath)
            ? $this->userViewPath . '/' . $this->viewName
            : $this->viewName;

        if ( ! File::isDirectory($path)) {
            File::makeDirectory($path, 0755, true);
        }

        $fields = $this->option('fields');
        //Xóa dấu ; cuối cùng để tránh bị lỗi phần tử rỗng
        $fields      = rtrim($fields, ';');
        $fieldsArray = explode(';', $fields);

        $this->formFields = [];

        $validations = $this->option('validations');

        if ($fields) {
            $idx = 0;

            foreach ($fieldsArray as $item) {
                $itemArray = explode('#', $item);

                $this->formFields[$idx]['name']     = trim($itemArray[0]);
                $this->formFields[$idx]['type']     = trim($itemArray[1]);
                $this->formFields[$idx]['required'] = preg_match('/' . $itemArray[0] . '/', $validations) ? true : false;
                $this->formFields[$idx]['options']  = '';

                if ($this->formFields[$idx]['type'] == 'select' && isset($itemArray[2])) {
                    $options      = trim($itemArray[2]);
                    $options      = str_replace('options=', '', $options);
                    $optionValues = "<option></option>\n";
                    if (Str::startsWith($options, 'enum')) {
                        //options=enum**className
                        $options        = explode('**', $options);
                        $enumClassName  = ucfirst(collect($options)->last());
                        $enumAttribute  = Str::snake(Str::plural($enumClassName));
                        $enumVariable   = lcfirst($enumClassName);
                        $modelEnumField = $this->crudNameSingular . '->' . $this->formFields[$idx]['name'];

                        $optionValues .= '@foreach($' . $this->crudNameSingular . '->' . $enumAttribute . ' as $key => $' . $enumVariable . ')' . "\n";
                        $optionValues .= '<option value="{{ $key }}" {{ $' . $modelEnumField . ' == $key ? \' selected\' : \'\' }}>{{ $' . $enumVariable . ' }}</option>' . "\n";
                        $optionValues .= '@endforeach' . "\n";

                        //tạo file class enum

                        if ( ! class_exists("App\Enums\{$enumClassName}")) {
                            $this->call('make:enum', [
                                'name' => $enumClassName,
                            ]);
                        }

                    } elseif (\strpos($options, '**', true) !== false) {
                        //options=1_value1**2_value2
                        $options = explode('**', $options);
                        foreach ($options as $option) {
                            $values = explode('_', $option);

                            [$value, $valueText] = $values;
                            $valueText    = __(ucwords(camel2words($valueText)));
                            $optionValues .= "<option value='" . $value . "'>$valueText</option>\n";
                        }
                    } else {
                        $foreignKeyText = $this->crudNameSingular . "->$options" . '_id';
                        $optionValues   .= '@if($' . $this->crudNameSingular . '->exists)' . "\n";
                        $optionValues   .= '<option value="{{ $' . $foreignKeyText . ' }}" selected>{{ $' . $this->crudNameSingular . '->' . $options . '->name }}</option>' . "\n";
                        $optionValues   .= '@endif' . "\n";
                    }

                    $this->formFields[$idx]['options'] = $optionValues;
                }

                $idx++;
            }
        }

        foreach ($this->formFields as $item) {
            $this->formFieldsHtml .= $this->createField($item);
        }

        foreach ($this->formFields as $value) {
            $field = $value['name'];

            $label                         = '{{ $' . $this->crudNameSingular . '->label(\'' . $field . '\') }}';
            $value                         = '{{ $' . $this->crudNameSingular . '->' . $field . ' }}';
            $this->formHeadingHtml         .= '<th>' . $label . '</th>';
            $this->formSearchHtml          .= '<div class="col-sm-12 col-md-6 col-lg-4 col-xl-3 form-group">
                                                    <label for="txt_' . $field . '">' . $label . '</label>
                                                    <input class="form-control" name="' . $field . '" id="txt_' . $field . '">
                                               </div>' . "\n";
            $this->formBodyHtmlForShowView .= '<tr>
                                                <th class="w-25"> ' . $label . ' </th>
                                                <td> ' . $value . ' </td>
                                              </tr>';
        }

        if ($this->userViewPath) {
            $this->userViewPath = $this->userViewPath . '.' . $this->route;
        } else {
            $this->userViewPath = $this->route;
        }

        $this->templateStubs($path);

        $this->info('View created successfully.');
    }

    /**
     * Default template configuration if not provided
     *
     * @return array
     */
    private function defaultTemplating()
    {
        return [
            'index'   => ['crudName', 'formHeadingHtml', 'route', 'userViewPath', 'viewTemplateDir', 'crudNameSingular', 'modelNameCap'],
            '_search' => ['formSearchHtml', 'crudName'],
            'create'  => ['crudName', 'modelNameCap', 'crudNameSingular', 'route', 'userViewPath', 'viewTemplateDir'],
            'edit'    => ['crudName', 'modelNameCap', 'crudNameSingular', 'title', 'route', 'userViewPath', 'viewTemplateDir'],
            '_form'   => ['formFieldsHtml', 'route', 'crudName', 'modelNameCap', 'crudNameSingular'],
            'show'    => ['formBodyHtmlForShowView', 'crudNameSingular', 'route', 'modelNameCap'],
        ];
    }

    /**
     * Generate files from stub
     *
     * @param $path
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function templateStubs($path)
    {
        $dynamicViewTemplate = $this->defaultTemplating();

        foreach ($dynamicViewTemplate as $name => $vars) {
            $file    = $this->viewDirectoryPath . $name . '.blade.stub';
            $newFile = $path . $name . '.blade.php';
            if (File::copy($file, $newFile)) {
                $this->templateVars($newFile, $vars);

                continue;
            }

            echo "failed to copy $file...\n";
        }
    }

    /**
     * Update specified values between delimiter with real values
     *
     * @param $file
     * @param $vars
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function templateVars($file, $vars)
    {
        [$start, $end] = $this->delimiter;

        foreach ($vars as $var) {
            $replace = $start . $var . $end;
            if (\in_array($var, $this->vars)) {
                File::put($file, str_replace($replace, $this->$var, File::get($file)));
            }
        }
    }

    /**
     * Form field wrapper.
     *
     * @param string $item
     * @param string $field
     *
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function wrapField($item, $field, $prefix = 'txt_')
    {
        $formGroup = File::get($this->viewDirectoryPath . 'form-fields/wrap-field.blade.stub');

        $labelText = sprintf("$%s->label('%s')", $this->crudNameSingular, str_replace('_id', '', $item['name']));

        return sprintf($formGroup, $item['name'], $prefix, $labelText, $field);
    }

    /**
     * Form field generator.
     *
     * @param array $item
     *
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function createField($item)
    {
        $type = str_replace("'", '', $item['type']);
        switch ($this->typeLookup[$type]) {
            case 'datetime-local':
            case 'date':
            case 'time':
                return $this->createDateTimeField($item);
            case 'radio':
                return $this->createRadioField($item);
            case 'textarea':
                return $this->createTextareaField($item);
            case 'select':
                return $this->createSelectField($item);
            default: // text, number, email, phone, mobile-phone
                return $this->createFormField($item);
        }
    }

    /**
     * Create a specific field using the form helper.
     *
     * @param array $item
     *
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function createFormField($item)
    {
        [$start, $end] = $this->delimiter;

        $required = $item['required'] ? 'data-fv-not-empty="true"' : '';

        $customClass = ' text-alphanum';

        if ($item['type'] === 'number') {
            $customClass = ' text-numeric';
        } elseif ($item['type'] === 'phone') {
            $customClass = ' text-phone';
        } elseif ($item['type'] === 'mobile-phone') {
            $customClass = ' text-mobile-phone';
        }

        $type   = str_replace("'", '', $item['type']);
        $markup = File::get($this->viewDirectoryPath . 'form-fields/form-field.blade.stub');
        $markup = str_replace(
            [$start . 'required' . $end, $start . 'fieldType' . $end, $start . 'itemName' . $end, $start . 'crudNameSingular' . $end, $start . 'customClass' . $end],
            [$required, $this->typeLookup[$type], $item['name'], $this->crudNameSingular, $customClass],
            $markup);

        return $this->wrapField(
            $item,
            $markup
        );
    }

    /**
     * Create a generic input field using the form helper.
     *
     * @param array $item
     *
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function createDateTimeField($item)
    {
        [$start, $end] = $this->delimiter;

        $required    = $item['required'] ? 'required' : '';
        $customClass = '';

        if ($item['type'] === 'date') {
            $customClass = 'text-datepicker';
        } elseif ($item['type'] === 'datetime') {
            $customClass = 'text-datetimepicker';
        } elseif ($item['type'] === 'time') {
            $customClass = 'text-timepicker';
        }

        $markup = File::get($this->viewDirectoryPath . 'form-fields/input-field.blade.stub');
        $markup = str_replace(
            [$start . 'required' . $end, $start . 'fieldType' . $end, $start . 'itemName' . $end, $start . 'crudNameSingular' . $end, $start . 'customClass' . $end],
            [$required, $this->typeLookup[$item['type']], $item['name'], $this->crudNameSingular, $customClass],
            $markup);

        return $this->wrapField(
            $item,
            $markup
        );
    }

    /**
     * Create a yes/no radio button group using the form helper.
     *
     * @param array $item
     *
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function createRadioField($item)
    {
        [$start, $end] = $this->delimiter;

        $markup = File::get($this->viewDirectoryPath . 'form-fields/radio-field.blade.stub');
        $markup = str_replace($start . 'crudNameSingular' . $end, $this->crudNameSingular, $markup);

        return $this->wrapField($item, sprintf($markup, $item['name']));
    }

    /**
     * Create a textarea field using the form helper.
     *
     * @param array $item
     *
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function createTextareaField($item)
    {
        [$start, $end] = $this->delimiter;

        $required = $item['required'] ? 'required' : '';

        $markup = File::get($this->viewDirectoryPath . 'form-fields/textarea-field.blade.stub');
        $markup = str_replace([$start . 'required' . $end, $start . 'fieldType' . $end, $start . 'itemName' . $end, $start . 'crudNameSingular' . $end], [$required, $this->typeLookup[$item['type']], $item['name'], $this->crudNameSingular],
            $markup);

        return $this->wrapField(
            $item,
            $markup,
            'textarea_'
        );
    }

    /**
     * Create a select field using the form helper.
     *
     * @param array $item
     *
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function createSelectField($item)
    {
        [$start, $end] = $this->delimiter;

        $required = $item['required'] ? 'required' : '';

        $markup = File::get($this->viewDirectoryPath . 'form-fields/select-field.blade.stub');
        $markup = str_replace([$start . 'required' . $end, $start . 'options' . $end, $start . 'itemName' . $end, $start . 'crudNameSingular' . $end], [$required, $item['options'], $item['name'], $this->crudNameSingular], $markup);

        return $this->wrapField(
            $item,
            $markup,
            'select_'
        );
    }
}
