# Clean up unneeded records

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/laravel-database-cleanup.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-database-cleanup)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/spatie/laravel-database-cleanup/master.svg?style=flat-square)](https://travis-ci.org/spatie/laravel-database-cleanup)
[![SensioLabsInsight](https://img.shields.io/sensiolabs/i/xxxxxxxxx.svg?style=flat-square)](https://insight.sensiolabs.com/projects/xxxxxxxxx)
[![Quality Score](https://img.shields.io/scrutinizer/g/spatie/laravel-database-cleanup.svg?style=flat-square)](https://scrutinizer-ci.com/g/spatie/laravel-database-cleanup)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/laravel-database-cleanup.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-database-cleanup)

This package will clean up unneeded records for your Eloquent models. 

The only thing you have to do is implement the `GetsCleanedUp`-interface.

Here's a quick example:

``` php
use Spatie\DatabaseCleanup\GetsCleanedUp;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class NewsItem extends Model implements GetsCleanedUp
{
    ...
    
     public static function cleanUp(Builder $query) : Builder
     {
        //delete up all records older than a year
        return $query->where('created_at', '<', Carbon::now()->subYear());
     }
}
```

Spatie is a webdesign agency based in Antwerp, Belgium. You'll find an overview of all our open source projects [on our website](https://spatie.be/opensource).

## Install

You can install the package via composer:
``` bash
composer require spatie/laravel-database-cleanup
```

Next up, the service provider must be registered:

```php
'providers' => [
    ...
    Spatie\DatabaseCleanup\DatabaseCleanupServiceProvider::class,

];
```
Next, you must publish the config file:

```bash
php artisan vendor:publish --provider="Spatie\DatabaseCleanup\DatabaseCleanupServiceProvider"
```

This is the content of the published config file `laravel-database-cleanup.php`.
```php
return [

    /*
     * All models that use the GetsCleanedUp interface in these directories will be cleaned.
     */
    'directories' => [
        // app_path('models'),
    ],

    /*
     * All models in this array that use the GetsCleanedUp interface will be cleaned.
     */
    'models' => [
        // App\NewsItem::class,
    ],

];
```

## Usage
All models that you want to clean up must implement the `GetsCleanedUp`-interface. In the required
`cleanUp`-method you can specify a query that selects the records that should be deleted.

Let's say you have a model called `NewsItem`, that you would like to  cleaned up. In this case your model could look like this:

``` php
use Spatie\DatabaseCleanup\GetsCleanedUp;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class NewsItem extends Model implements GetsCleanedUp
{
    ...
    
     public static function cleanUp(Builder $query) : Builder
     {
        return $query->where('created_at', '<', Carbon::now()->subYear());
     }
    
}
```

When running the console command `clean:models` all newsItems older than a year will be deleted.

This command can be scheduled in Laravel's console kernel.

```php
// app/Console/Kernel.php

protected function schedule(Schedule $schedule)
{
   $schedule->command('clean:models')->daily();
}
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

``` bash
composer test
```

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email freek@spatie.be instead of using the issue tracker.

## Credits

- [Jolita Grazyte](https://github.com/JolitaGrazyte)
- [Freek Van der Herten](https://github.com/freekmurze)
- [All Contributors](../../contributors)

## About Spatie
Spatie is a webdesign agency based in Antwerp, Belgium. You'll find an overview of all our open source projects [on our website](https://spatie.be/opensource).

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
