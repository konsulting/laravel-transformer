# Transformer

*A simple way to transform data, with helpers for transforming data in [Laravel](https://laravel.com) requests.*

## Installation

* Install Transformer using composer: `composer require konsulting/tranformer 
	
## Example usage

```php
$input = [
    'my-date-1' => '2015-04-04',
    'my-date-2' => '2015-03-04',
    'my-date-3' => '2015-03-04',
    'my-date-4' => '2015-03-04',
    'my-date-5' => [
        'year'   => '2015',
        'month'  => '04',
        'day'    => '05',
        'hour'   => '22',
        'minute' => '43',
        'second' => '01',
    ],
];


$rules = [
    'my-date-1' => 'format:d-m-Y',
    'my-date-2' => 'to_persist_format',
    'my-date-3' => 'to_display_format',
    'my-date-4' => 'to_carbon',
    'my-date-5' => 'combine|to_carbon',
];
     
        
$output = Transformer::transform($input, $rules);
```

## Contributing

Contributions are welcome and will be fully credited. We will accept contributions by Pull Request. 

Please:

* Use the PSR-2 Coding Standard
* Add tests, if you’re not sure how, please ask.
* Document changes in behaviour, including readme.md.

## Testing
We use [PHPUnit](https://phpunit.de) and the excellent [orchestral/testbench](https://github.com/orchestral/testbench) 

Run tests using PHPUnit: `vendor/bin/phpunit`
