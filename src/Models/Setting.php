<?php

declare(strict_types=1);

namespace Supplycart\Settings\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use LogicException;
use Supplycart\Settings\Contracts\HasSettings;
use Supplycart\Settings\Events\SettingSaved;
use Supplycart\Settings\Observers\SettingObserver;
use UnexpectedValueException;

/**
 * @property int $id
 * @property string $model_type
 * @property int|string $model_id
 * @property array<string, mixed> $values
 * @property-read Model $model
 */
#[ObservedBy(SettingObserver::class)]
class Setting extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'values',
    ];

    /** @var array<string, class-string> */
    protected $dispatchesEvents = [
        'saved' => SettingSaved::class,
    ];

    /** @return MorphTo<Model, $this> */
    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    public static function for(HasSettings $model): static
    {
        if (! $model instanceof Model || ! $model->exists) {
            throw new LogicException('Settings are only available for persisted Eloquent models.');
        }

        $cache = Cache::memo();
        $cacheKey = $model->getCacheKey();
        $setting = $cache->get($cacheKey);

        if ($setting !== null && ! $setting instanceof static) {
            $cache->forget($cacheKey);

            throw new UnexpectedValueException('The cached settings value is not a Setting model.');
        }

        if ($setting instanceof static) {
            return $setting;
        }

        $setting = $model->morphOne($model->getSettingModel(), 'model')->firstOrCreate([], [
            'values' => $model::getDefaultSettings(),
        ]);

        if (! $setting instanceof static) {
            throw new UnexpectedValueException('The configured settings model does not match the called Setting class.');
        }

        $cache->forever($cacheKey, $setting);

        return $setting;
    }

    public static function cacheKey(string $modelType, int|string $modelId): string
    {
        return "settings:{$modelType}:{$modelId}";
    }

    public function get(?string $key = null, mixed $default = null): mixed
    {
        return $key === null ? $this->values : data_get($this->values, $key, $default);
    }

    /** @param array<string, mixed>|string $key */
    public function set(array|string $key, mixed $value = null): static
    {
        $values = $this->values;

        if (is_array($key)) {
            $values = array_replace_recursive($values, $key);
        } else {
            Arr::set($values, $key, $value);
        }

        $this->forceFill(['values' => $values])->saveOrFail();

        return $this;
    }

    /** @return array<string, string> */
    #[\Override]
    protected function casts(): array
    {
        return [
            'values' => 'array',
        ];
    }
}
