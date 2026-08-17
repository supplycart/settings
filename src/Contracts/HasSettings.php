<?php

declare(strict_types=1);

namespace Supplycart\Settings\Contracts;

use Supplycart\Settings\Models\Setting;

interface HasSettings
{
    public function getSetting(?string $key = null, mixed $default = null): mixed;

    /** @param array<string, mixed>|string $key */
    public function setSetting(array|string $key, mixed $value = null): Setting;

    /** @return array<string, mixed> */
    public static function getDefaultSettings(): array;

    public function getCacheKey(): string;

    /** @return class-string<Setting> */
    public function getSettingModel(): string;
}
