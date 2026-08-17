<?php

declare(strict_types=1);

namespace Supplycart\Settings\Tests\Stubs;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Supplycart\Settings\Contracts\HasSettings as SettingsContract;
use Supplycart\Settings\Traits\HasSettings;

final class Company extends Model implements SettingsContract
{
    /** @use HasFactory<CompanyFactory> */
    use HasFactory;

    use HasSettings;

    /** @return array<string, mixed> */
    #[\Override]
    public static function getDefaultSettings(): array
    {
        return [
            'timezone' => 'Asia/KualaLumpur',
            'currency' => 'MYR',
            'currency_unit' => 'RM',
        ];
    }

    protected static function newFactory(): CompanyFactory
    {
        return CompanyFactory::new();
    }
}
