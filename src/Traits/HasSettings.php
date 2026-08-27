<?php

declare(strict_types=1);

namespace Supplycart\Settings\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use LogicException;
use Supplycart\Settings\Contracts\HasSettings as HasSettingsContract;
use Supplycart\Settings\Models\Setting;

/**
 * @phpstan-require-extends Model
 *
 * @phpstan-require-implements HasSettingsContract
 */
trait HasSettings
{
    /** @return MorphOne<Setting, $this> */
    public function settings(): MorphOne
    {
        return $this->morphOne($this->getSettingModel(), 'model');
    }

    public function getSetting(?string $key = null, mixed $default = null): mixed
    {
        $settingModel = $this->getSettingModel();

        return $settingModel::for($this)->get($key, $default);
    }

    /** @param array<string, mixed>|string $key */
    public function setSetting(array|string $key, mixed $value = null): Setting
    {
        $settingModel = $this->getSettingModel();

        return $settingModel::for($this)->set($key, $value);
    }

    public function getCacheKey(): string
    {
        $key = $this->getKey();

        if (! is_int($key) && ! is_string($key)) {
            throw new LogicException('Settings are only available for persisted models.');
        }

        $settingModel = $this->getSettingModel();

        return $settingModel::cacheKey($this->getMorphClass(), $key);
    }

    /** @return class-string<Setting> */
    public function getSettingModel(): string
    {
        $model = config('settings.model', Setting::class);

        if (! is_string($model) || ! is_a($model, Setting::class, true)) {
            throw new LogicException('The settings.model configuration value must extend '.Setting::class.'.');
        }

        return $model;
    }
}
