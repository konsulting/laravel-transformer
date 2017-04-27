# # Transformer
*A simple way to transform data, with helpers for transforming data in [Laravel](https://laravel.com) applications.*

## Installation
* Install Transformer using composer: `composer require konsulting/laravel-transformer`

### Transformer in a Laravel application
* Transformer uses `Illuminate\Support\Collection` and `Illuminate\Support\Arr`, and requires a couple of extensions to these. 
The extensions are available in the `konsulting/laravel-extend-collections` package.
* You'll need both the `CollectionsServiceProvider` from that package, and `TransformerServiceProvider` in your `config/app.php`.

```php
'providers' => [
    // Other service providers...
    
    Konsulting\Laravel\CollectionsServiceProvider::class,
    Konsulting\Laravel\Transformer\TransformerServiceProvider::class,
],	
```

* Optionally, add the Transformer Facade to `config/app.php`

```php
'aliases' => [
    // Other aliases...

    'Transformer' => Konsulting\Laravel\Transformer\TransformerFacade::class,
],
```

* Optionally publish the config file, and adjust the rule packs you want to use. 
`php artisan vendor:publish --provider=Konsulting\\Laravel\\Transformer\\TransformerServiceProvider --tag=config`

### Transformer outside a Laravel application
* Transformer uses `Illuminate\Support\Collection` and `Illuminate\Support\Arr`. Outside a Laravel application, 
it will use `tighten/collect` (an extraction of Collection && Arr from Laravel's Illuminate\Support) to get these dependencies.
* Transform also requires a couple of extensions to these. The extensions are available in the 
`konsulting/laravel-extend-collections` package. You'll need to register the extensions manually. 
* You will need to build up your Transformer manually for use in your application.

```php
    // Basic example
    
    use Konsulting\Laravel\Transformer\Transformer;
    use Konsulting\Laravel\Transformer\RulePacks\CoreRulePack;
    use Konsulting\Laravel\Transformer\RulePacks\CarbonRulePack;
    
    require __DIR__ . '/../vendor/autoload.php';
    
    // Extend Illuminate\Support\Arr and Illuminate\Support\Collection
    \Konsulting\Laravel\load_collection_extensions();
    
    // Build up Transformer
    $transformer = new Transformer([CoreRulePack::class, CarbonRulePack::class]);
    
    // Transformer now available to use, see Usage
```

## Usage
Transformer uses `RulePacks` to provide transformation functionality. RulePacks can be added to the Transformer during construction, or after with the `addRulePack` or `addRulePacks` methods.

A set of rules can be passed in during construction (useful when applying the same rules to different sets of data) or rules can be passed in at the point when performing transformation.

To transform data, the `transform` method is used. It accepts an array (or collection) of data to transform, and optionally rules to apply.

* Rules are presented in a similar manner to the [Laravel Validator](https://laravel.com/docs/5.4/validation). They provide functionality to handle nested data, and follow the same string format.
* Arrays of rules are indexed by a field expression and provide a `|` (pipe) delimited list of rules to apply. 
* Rules may be provided a set of parameters in CSV format. Field expressions may use `*` as a wildcard to match elements at that depth and `**` as a special case to match everything. 
* Rule sequences are built up in the order they are provided.

```php
    // using the $transformer built up earlier
    
    $rules = [
        '*' => 'drop_if_empty',
        'name' => 'trim',
        'contacts.*.name' => 'trim|uppercase'
    ];
    
    $data = [
        0 => '',
        'name' => '   Keoghan Litchfield   ',
        'contacts' => [
            ['name' => 'Robin'],
            ['name' => 'Roger'],
            ['name' => ''],
        ],
    ];
    
    $result = $transformer->transform($data, $rules);
    
    //    Outputs [
    //        'name' => 'Keoghan Litchfield',
    //        'contacts' => [
    //            ['name' => 'ROBIN'],
    //            ['name' => 'ROGER'],
    //        ],
    //    ];
```
### Transform helper
There is also a helper class `Transform`, which facilitates the easy transformation of a single value by one or more rules. `Transform` receives an instance of `Transformer` via its constructor, which provides the transformation logic and determines which rules are available.
Using the instance of `Transformer` built up previously:

```php
use Konsulting\Laravel\Transformer\Transform;

$transform = new Transform($transformer);
```

Rules may be called as methods on the `Transform` object, with the value to be transformed passed in as the first argument and any rule parameters as subsequent arguments. 

```php
$transform->trim(' Some string to be trimmed   ');  // Outputs 'Some string to be trimmed'

$transform->regexReplace('testing', 'e', 'oa');     // Outputs 'toasting'
```

Alternatively, rules may be passed via the `withRule()` and `withRules()` methods (for singular and multiple rules respectively).
Rule parameters are passed either as separate arguments, or as an array. 

```php
// Single rule
$transform->withRule('  test  ', 'trim');                           // Outputs 'test'

// Single rule with parameters passed as separate arguments
$transform->withRule('test', 'regex_replace', 'e', 'oa');           // Outputs 'toast'

// Singe rule with parameters passed as an array
$transform->withRule('test', 'regex_replace', ['e', 'oa']);         // Outputs 'toast' as well

// Multiple rules passed as a sequential array
$transform->withRules('  test  ', ['trim', 'uppercase']);           // Outputs 'TEST'

// Multiple rules and parameters passed as an assocative array: [$rule => [$param1, $param2], $rule2 => []...]
$transform->withRules('--test--', [                                 // Outputs 'TOAST'
    'trim'          => ['-'],
    'regex_replace' => ['e', 'oa'],
    'uppercase'     => [],
]);
```
#### Fluent API
Rules may also be called fluently: the input value is set with the `input()` method, and the result is obtained with `get()`.
Any number of rule methods may be chained between these.

```php
$transform->input(' hello ')
    ->trim()
    ->regexReplace('hello', 'world')
    ->uppercase()
    ->get();
    
// Outputs 'WORLD'
```
When the fluent API is used, the value is not passed as an argument to the rule methods (as it has already been set via `input()`).
As such, all arguments passed to rule methods are treated as rule parameters.

`withRule()` and `withRules()` may be used to fluently declare rules with or without parameters:
```php
$transform->input($input)
    ->withRule('trim')
    ->uppercase()
    ->get();
    
$transform->input($input)
    ->lowercase()
    ->withRules(['trim', 'uppercase'])
    ->get();
```

### Available Rules
We provide a couple of rule packs for use, it is easy to extend the rules available by creating your own Rule Pack. Rule Packs are loaded in the declared order, methods in later packs will override packs loaded earlier.

Parameter names are denoted by `<param>` and optional parameters by `[<param>]`.

#### Core Rule Pack

##### Cleaning up
* `null_if_empty`
* `null_if_empty_string`
* `return_null_if_empty` – equivalent to `null_if_empty|bail_if_null`.
* `return_null_if_empty_string` – equivalent to `null_if_empty_string|bail_if_null`.
* `bail_if_null`
* `drop_if_null`
* `drop_if_empty`
* `drop_if_empty_string`
* `trim:[<character(s)_to_trim>]` – performs default PHP trim() if no characters supplied.

##### Casting
* `string` - convert to string, an array is transformed to a CSV or returns ‘’ for items that cannot be represented as a string.
* `boolean` 
* `array` 
* `collection` - convert to `Illuminate\Support\Collection`
* `json` 
* `float` 
* `integer`
* `date_time:[timezone]`
* `date_time_immutable:[timezone]`

##### String manipulation
* `uppercase`
* `lowercase`

#####  Regex and string replace
* `replace:<search_string>,<replace_string>`
* `regex_replace:<search_regex>,<replace_string>`
* `numeric`
* `alpha`
* `alpha_dash`
* `alpha_num`
* `alpha_num_dash`

#### Carbon Rule Pack
* `carbon`
* `date_format` - parameter for the format required.

#### Related Fields Rule Pack
* `null_with:<key>`
* `drop_with:<key>`
* `bail_with:<key>`
* `null_without:<key>`
* `drop without:<key>`
* `bail_without:<key>`

#### Number Rule Pack
* `clamp:<min>,<max>` - constrain a number between two values

_**Note:** key is a dot notation key for another field in the dataset_

### Laravel Helpers
We use Laravel frequently, so have a couple of extras added here.

#### Facade
Use the facade to gain easy access to the Transformer wherever you are.
```php
\Transformer::transform($data, $rules);
```

#### Request Macro `transform`
The Service Provider adds the transform Macro to the `Illuminate\Http\Request` class. This makes it simple to invoke the transformation on a request at any point. The method passes the request object back to allow chaining.

~Using the Request::transform($rules) Macro~
```php
// Example Controller

namespace App\Http\Controllers;

use App\ContactRequest;
use Illuminate\Http\Request;

class ContactRequestsController
{
    // ...

    public function store(Request $request)
    {
        $request->transform([
            'name' => 'trim|uppercase',
            'message' => 'trim',
        ]);

        $this->validate($request, [ 
            'name' => 'required',
        ]);

        return ContactRequest::create(
            $request->only('name', 'message')
        );
    }
}
```

#### TransformingRequest Trait
This trait makes the form request transform the data before validation occurs (which is upon it being resolved by the container).

Rules for transformation are provided in the `transformRules` method.

~Using the TransformingRequest Trait~
```php
// Form Request

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Konsulting\Laravel\Transformer\TransformableRequest;

class ContactFormRequest extends FormRequest
{
    use TransformableRequest;

	  // ... other methods including rules()
	
	  public function transformRules()
    {
        return [
            'name' => 'trim|uppercase',
            'message' => 'trim',
        ];
    }
```

```php
// Controller

namespace App\Http\Controllers;

use App\ContactRequest;
use App\Http\Requests\ContactFormRequests;

class ContactRequestsController
{
    // ...

    public function store(ContactFormRequest $request)
    {
        return ContactRequest::create(
            $request->only('name', 'message')
        );
    }
}
```

### Middleware
The `TransformRequest` middleware applies transformations to requests according to configured rules. These rules are specified in the `middleware_rules` key of the config file as detailed in [Usage](#usage).

To register the middleware for use in your project, add the following line to your project's `App/Http/Kernel.php`:

```php
'transform_data' => \Konsulting\Laravel\Transformer\Middleware\TransformRequest::class
```

The default middleware rules state that every field should be trimmed of whitespace and nulled if empty:

```php
'middleware_rules' => [
    '**' => 'trim|return_null_if_empty',
]
```

Rules need not be applied to all fields; specific fields may be targeted within the middleware if required:

```php
'middleware_rules' => [
    'postcode'  => 'uppercase',
    'email'     => 'lowercase',
]
```

With the above configuration, the postcode and email fields of every request sent through the middleware will be affected, but all other fields will be left unchanged.

Multiple transformer middlewares may be useful in a project: to achieve this, copy `laravel-transformer/src/Middleware/TransformRequest.php` to your project's `App/Http/Middleware` directory, and rename/edit as necessary. Each new middleware will have to be registered in the kernel.

## Contributing
Contributions are welcome and will be fully credited. We will accept contributions by Pull Request. 

Please:

* Use the PSR-2 Coding Standard.
* Add tests, if you’re not sure how, please ask.
* Document changes in behaviour, including readme.md.

## Testing
We use [PHPUnit](https://phpunit.de) and the excellent [orchestral/testbench](https://github.com/orchestral/testbench).

Run tests using PHPUnit: `vendor/bin/phpunit`
