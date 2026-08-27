# Laravel Settings

Typed, cached settings for Laravel 13 Eloquent models.

The package stores one JSON settings document per model, supports dot-notation
reads and writes, broadcasts changes after the database transaction commits,
and uses Laravel's memoized cache repository to avoid repeated reads within a
request.

## Requirements

- PHP 8.5 or later
- Laravel 13

## Installation

```bash
composer require supplycart/settings
php artisan vendor:publish --tag=settings-migrations
php artisan migrate
```

Publish the optional configuration when using a custom `Setting` subclass:

```bash
php artisan vendor:publish --tag=settings-config
```

## Usage

Implement the contract and use the trait on a persisted Eloquent model:

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Supplycart\Settings\Contracts\HasSettings as HasSettingsContract;
use Supplycart\Settings\Traits\HasSettings;

final class Company extends Model implements HasSettingsContract
{
    use HasSettings;

    public static function getDefaultSettings(): array
    {
        return [
            'timezone' => 'Asia/Kuala_Lumpur',
            'notifications' => [
                'email' => true,
            ],
        ];
    }
}
```

Read settings with optional dot notation and fallback values:

```php
$company->getSetting();
$company->getSetting('timezone');
$company->getSetting('notifications.email', false);
```

Write one setting or merge several settings:

```php
$company->setSetting('timezone', 'UTC');

$company->setSetting([
    'timezone' => 'UTC',
    'notifications' => ['email' => false],
]);
```

Array writes are merged recursively so unrelated nested settings remain intact.

## Custom setting model

Extend the package model and configure it in `config/settings.php`:

```php
use App\Models\Setting;

return [
    'model' => Setting::class,
];
```

The configured class must extend `Supplycart\Settings\Models\Setting`.

## Laravel 13 behavior

- `Cache::memo()` provides request-local memoization backed by the configured
  application cache, without requiring a cache driver that supports tags.
- `#[ObservedBy]` invalidates cache entries whenever a setting is saved or
  deleted, including updates made outside `setSetting()`.
- Settings are created through `firstOrCreate()` and protected by a unique
  `(model_type, model_id)` database constraint.
- `SettingSaved` broadcasts after commit, preventing rolled-back values from
  being emitted.

## Upgrading from the previous major version

This release intentionally contains breaking changes:

- PHP 8.5 and Laravel 13 are required.
- `HasSettings::getSettingModel()` is now part of the contract. The supplied
  trait implements it automatically.
- Models must be persisted before settings can be read or written.
- Invalid custom model configuration and invalid cached values now throw clear
  exceptions.
- `Setting::toArray()` uses normal Eloquent serialization. Broadcast payloads
  continue to contain the settings values themselves.
- The settings table now requires one row per morph owner. Deduplicate existing
  rows before adding the unique `(model_type, model_id)` constraint.

## Development

```bash
composer lint:check
composer analyse
composer test
composer test:coverage
```

Pint enforces formatting, Larastan/PHPStan runs at level `max`, and CI enforces
at least 85% line coverage.
