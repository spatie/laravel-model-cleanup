# Clean up unneeded records

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/laravel-model-cleanup.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-model-cleanup)
[![MIT Licensed](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/spatie/laravel-model-cleanup/master.svg?style=flat-square)](https://travis-ci.org/spatie/laravel-model-cleanup)
[![Quality Score](https://img.shields.io/scrutinizer/g/spatie/laravel-model-cleanup.svg?style=flat-square)](https://scrutinizer-ci.com/g/spatie/laravel-model-cleanup)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/laravel-model-cleanup.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-model-cleanup)

This package will clean up unneeded records for your Eloquent models. 

The only things you have to do is let your models implement the `GetsCleanedUp`-interface and schedule a command that performs the cleanup.

Here's a quick example of a model that implements `GetsCleanedUp`:

``` php
use Spatie\ModelCleanup\GetsCleanedUp;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class LogItem extends Model implements GetsCleanedUp
{
    ...
    
     public static function cleanUp(Builder $query) : Builder
     {
        // Delete all records older than a year
        return $query->where('created_at', '<', Carbon::now()->subYear());
     }
}
```

## Jump To

* [Installation](#installation)
* [Usage](#usage)
    * [Configure models to remove](#configure-models-to-remove)
    * [Configure models to forceRemove](#configure-models-to-forceremove)
    * [Command](#command)
* [Events](#events)
* [Changelog](CHANGELOG.md)

## Installation

You can install the package via composer:
``` bash
composer require spatie/laravel-model-cleanup
```

Next, you must publish the config file:

```bash
php artisan vendor:publish --provider="Spatie\ModelCleanup\ModelCleanupServiceProvider"
```

This is the content of the published config file `model-cleanup.php`.

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
        // App\LogItem::class,
    ],

    /*
     * Specify whether to search the configured `directories` recursively. 
     * Set to false to only search for models directly inside the specified paths.
     */
    'recursive' => true,
];
```

## Usage

### Configure models to remove

All models that you want to clean up must implement the `GetsCleanedUp`-interface. In the required
`cleanUp`-method you can specify a query that selects the records that should be deleted.

Let's say you have a model called `LogItem`, that you would like to  cleaned up. In this case your model could look like this:

``` php
use Spatie\ModelCleanup\GetsCleanedUp;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class LogItem extends Model implements GetsCleanedUp
{
    ...
    
    public static function cleanUp(Builder $query) : Builder
    {
        return $query->where('created_at', '<', Carbon::now()->subYear());
    }
    
}
```

When running the console command `clean:models` all logItems older than a year will be deleted.

### Configure models to forceRemove

All models that have the SoftDeletes trait that you want to clean up completly from the database must implement the `GetsForcedCleanedUp`-interface. In the required
`forceCleanUp`-method you can specify a query that selects the records that should be forceDeleted.

Let's say you have a model called `LogItem`, that you would like to  cleaned up. In this case your model could look like this:

``` php
use Spatie\ModelCleanup\GetsForcedCleanedUp;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

class LogItem extends Model implements GetsForcedCleanedUp
{
    use SoftDeletes;
    ...
    
    public static function forceCleanUp(Builder $query) : Builder
    {
        return $query->onlyTrashed()->where('deleted_at', '<', Carbon::now()->subDay());
    }
    
}
```

When running the console command `clean:models` all logItems that were deleted more than a year from `Carbon::now` will be deleted completly from the database.

### Command 

When running the console command `clean:models` all the items on cleanUp will be deleted.

This command can be scheduled in Laravel's console kernel.

```php
// app/Console/Kernel.php

protected function schedule(Schedule $schedule)
{
   $schedule->command('clean:models')->daily();
}
```

## Events

After the model has been cleaned `Spatie\ModelCleanup\ModelWasCleanedUp` will be fired even if there were no records deleted.

It has two public properties: `modelClass` and `numberOfDeletedRecords`.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Testing

``` bash
composer test
```

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email freek@spatie.be instead of using the issue tracker.

## Postcardware

You're free to use this package, but if it makes it to your production environment we highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using.

Our address is: Spatie, Samberstraat 69D, 2060 Antwerp, Belgium.

We publish all received postcards [on our company website](https://spatie.be/en/opensource/postcards).

## Credits

- [Jolita Grazyte](https://github.com/JolitaGrazyte)
- [Freek Van der Herten](https://github.com/freekmurze)
- [All Contributors](../../contributors)

## Support us

Spatie is a webdesign agency based in Antwerp, Belgium. You'll find an overview of all our open source projects [on our website](https://spatie.be/opensource).

Does your business depend on our contributions? Reach out and support us on [Patreon](https://www.patreon.com/spatie). 
All pledges will be dedicated to allocating workforce on maintenance and new awesome stuff.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
