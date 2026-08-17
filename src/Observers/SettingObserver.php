<?php

declare(strict_types=1);

namespace Supplycart\Settings\Observers;

use Illuminate\Support\Facades\Cache;
use Supplycart\Settings\Models\Setting;

final class SettingObserver
{
    public function saved(Setting $setting): void
    {
        $this->forget($setting);
    }

    public function deleted(Setting $setting): void
    {
        $this->forget($setting);
    }

    private function forget(Setting $setting): void
    {
        Cache::memo()->forget($setting::cacheKey($setting->model_type, $setting->model_id));
    }
}
