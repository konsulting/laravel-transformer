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
    Konsulting\Laravel\TransformerServiceProvider::class,
],	
```

* Optionally, add the Transformer Facade to `config/app.php`

```php
'aliases' => [
    // Other aliases...

    'Butler' => Konsulting\Laravel\TransformerFacade::class,
],
```

* Optionally publish the config file, and adjust the rule packs you want to use. 
`php artisan vendor:publish --provider=\\Konsulting\\Laravel\\TransformerServiceProvider --tag=config`

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

* Rules are presented in a similar manner to the Laravel Validator. They provide functionality to handle nested data, and follow the same string format.
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
    
    $resultIs = [
        'name' => 'Keoghan Litchfield',
        'contacts' => [
            ['name' => 'ROBIN'],
            ['name' => 'ROGER'],
        ],
    ];
```

### Available Rules
We provide a couple of rule packs for use, it is easy to extend the rules available by creating your own Rule Pack. Rule Packs are loaded in the declared order, methods in later packs will override packs loaded earlier.

#### Core Rule Pack

##### Cleaning up
`null_if_empty`
`return_null_if_empty`
`bail_if_null`
`drop_if_null`
`drop_if_empty`
`trim` - optional parameter of char to trim.

##### Casting
`string` - convert to string, an array is transformed to a CSV or returns ‘’ for items that cannot be represented as a string
`boolean` 
`array` 
`collection` - convert to `Illuminate\Support\Collection`
`json` 
`float` 
`integer`
`date_time` - optional timezone parameter
`date_time_immutable` - optional timezone parameter

##### String manipulation
`uppercase`
`lowercase`

#####  Regex
`regex_replace`
`numeric`
`alpha`
`alpha_dash`
`alpha_num`
`alpha_num_dash`

#### Carbon Rule Pack
`carbon`
`date_format` - parameter for the format required

### Laravel Helpers
We use Laravel frequently, so have a couple of extras added here.

#### Facade
Use the facade to gain easy access to the Transformer wherever you are.
```php
\Transformer::transform($data, $rules);
```

#### TransformsData Trait
This trait can be used to set up easy access to the Transformer in and object, and provides a simple place to store the rules.
Simply `use \Konsulting\Laravel\Transformer\TransformsData` , place the rules in an override of the `transformRules` method, and then you can `transform` data when needed.

#### TransformableRequest Trait
This trait is intended to sit as an extension to an Illuminate Form Request. `use Konsulting\Laravel\Transformer\TransformableRequest` and after overriding the `transformRules` method to provide the rule array, you can access transformed request data through the `transformed()` method. It also provides the transformed data to the validation logic in the form request.
Unconventionally, this trait also uses the `TransformsData` trait to reuse the code.


```php

```

## Contributing

Contributions are welcome and will be fully credited. We will accept contributions by Pull Request. 

Please:

* Use the PSR-2 Coding Standard
* Add tests, if you’re not sure how, please ask.
* Document changes in behaviour, including readme.md.

## Testing
We use [PHPUnit](https://phpunit.de)

Run tests using PHPUnit: `vendor/bin/phpunit`
