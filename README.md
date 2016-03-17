# Cleaning up database programmatically

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/laravel-database-cleanup.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-database-cleanup)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/spatie/laravel-database-cleanup/master.svg?style=flat-square)](https://travis-ci.org/spatie/laravel-database-cleanup)
[![SensioLabsInsight](https://img.shields.io/sensiolabs/i/xxxxxxxxx.svg?style=flat-square)](https://insight.sensiolabs.com/projects/xxxxxxxxx)
[![Quality Score](https://img.shields.io/scrutinizer/g/spatie/laravel-database-cleanup.svg?style=flat-square)](https://scrutinizer-ci.com/g/spatie/laravel-database-cleanup)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/laravel-database-cleanup.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-database-cleanup)

This package will clean up a database programmatically through your Eloquent models, that you'll specify in the config file. 

The specified models must implement GetsCleanedUp interface and have a cleanUpModel method.

Here's an example:

``` php

use Spatie\DatabaseCleanup\GetsCleanedUp;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class NewsItem extends Model implements GetsCleanedUp
{
    ...
    
     public static function cleanUpModel(Builder $query) : Builder
     {
        return $query->where('created_at', '<', Carbon::now()->subDays(365));
     }
    
}
```

Spatie is a webdesign agency based in Antwerp, Belgium. You'll find an overview of all our open source projects [on our website](https://spatie.be/opensource).

## Install

You can install the package via composer:
``` bash
$ composer require spatie/laravel-database-cleanup
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

This is the content of the published file laravel-database-cleanup.php.
```php
return [

    /*
      * You can either specify model classes that must be cleaned up or a directory 
      * with the models that you want to get cleaned up inside,
      * or both if that makes sense.
     **/
    'models' => [
      //  App\NewsItem::class,

    ],

    'directories' => [
      //  app_path('models'),
      //  app_path('models')
    ],

];
```

## Usage
All models that you want to get cleaned up must implement GetsCleanedUp interface and have a method cleanUpModel 
in which you can specify how old the records in a database have to be to get cleaned up.

Let's say you have a model called NewsItem, that you would like to get cleaned up automatically.
 
In this case your model with a GetCleanedUp implementation could look like in this example:

``` php

use Spatie\DatabaseCleanup\GetsCleanedUp;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class NewsItem extends Model implements GetsCleanedUp
{
    ...
    
     public static function cleanUpModel(Builder $query) : Builder
     {
        return $query->where('created_at', '<', Carbon::now()->subDays(365));
     }
    
}
```
### Scheduling
If you want the cleanup command run automatically you must schedule it.

You can schedule it apart in the cron as you want.
 
```
  * * * * * php /path/to/artisan databaseCleanup:clean >> /dev/null 2>&1
```
Otherwise you may add this task in the schedule method of the App\Console\Kernel and run a cron for all scheduled tasks at once.
```
    $schedule->call('databaseCleanup:clean')->daily();
    
    * * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1
```


## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email freek@spatie.be instead of using the issue tracker.

## Credits

- [Jolita Grazyte](https://github.com/JolitaGrazyte)
- [All Contributors](../../contributors)

## About Spatie
Spatie is a webdesign agency based in Antwerp, Belgium. You'll find an overview of all our open source projects [on our website](https://spatie.be/opensource).

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
