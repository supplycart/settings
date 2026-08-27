<?php

declare(strict_types=1);

namespace Supplycart\Settings\Tests\Stubs;

use Supplycart\Settings\Models\Setting;

final class CustomSetting extends Setting
{
    protected $table = 'settings';
}
