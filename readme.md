# Transformer

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
    require __DIR__ . '/../vendor/autoload.php';
    
    // Extend Illuminate\Support\Arr and Illuminate\Support\Collection
    require __DIR__ . '/../vendor/konsulting/laravel-extend-collections/src/arr_macros.php';
    require __DIR__ . '/../vendor/konsulting/laravel-extend-collections/src/collection_macros.php';
    
    // Build up Transformer
    $transformer = new \Konsulting\Laravel\Transformer\Transformer;
    $transformer->addRuleSets([
        \Konsulting\Laravel\Transformer\RulePacks\CoreRulePack::class,
        \Konsulting\Laravel\Transformer\RulePacks\CarbonRulePack::class,
    ]);
    
    // Transformer now available to use, see Usage
```

## Usage

## Contributing

Contributions are welcome and will be fully credited. We will accept contributions by Pull Request. 

Please:

* Use the PSR-2 Coding Standard
* Add tests, if you’re not sure how, please ask.
* Document changes in behaviour, including readme.md.

## Testing
We use [PHPUnit](https://phpunit.de)

Run tests using PHPUnit: `vendor/bin/phpunit`
