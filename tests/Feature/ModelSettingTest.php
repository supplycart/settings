<?php

declare(strict_types=1);

namespace Supplycart\Settings\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use LogicException;
use Supplycart\Settings\Events\SettingSaved;
use Supplycart\Settings\Models\Setting;
use Supplycart\Settings\Tests\Stubs\Company;
use Supplycart\Settings\Tests\Stubs\CustomSetting;
use Supplycart\Settings\Tests\TestCase;

final class ModelSettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_model_can_have_default_settings(): void
    {
        $company = Company::factory()->create();
        $settings = $company->getSetting();

        $this->assertIsArray($settings);
        $this->assertSame(Company::getDefaultSettings(), $settings);

        $this->assertDatabaseHas('settings', [
            'model_type' => Company::class,
            'model_id' => $company->getKey(),
            'values' => json_encode($settings, JSON_THROW_ON_ERROR),
        ]);
    }

    public function test_can_get_model_setting_by_key_and_default(): void
    {
        $company = Company::factory()->create();

        $this->assertSame('Asia/KualaLumpur', $company->getSetting('timezone'));
        $this->assertSame('MYR', $company->getSetting('currency'));
        $this->assertSame('RM', $company->getSetting('currency_unit'));
        $this->assertSame('fallback', $company->getSetting('missing', 'fallback'));
    }

    public function test_can_set_model_setting_by_key(): void
    {
        $company = Company::factory()->create();

        $company->setSetting('currency', 'USD');
        $company->setSetting('currency_unit', 'USD');

        $this->assertSame('Asia/KualaLumpur', $company->getSetting('timezone'));
        $this->assertSame('USD', $company->getSetting('currency'));
        $this->assertSame('USD', $company->getSetting('currency_unit'));
    }

    public function test_can_set_nested_and_multiple_settings(): void
    {
        $company = Company::factory()->create();

        $company->setSetting('notifications.email', true);
        $company->setSetting([
            'currency' => 'SGD',
            'notifications' => ['sms' => false],
        ]);

        $this->assertTrue($company->getSetting('notifications.email'));
        $this->assertFalse($company->getSetting('notifications.sms'));
        $this->assertSame('SGD', $company->getSetting('currency'));
    }

    public function test_repeated_reads_reuse_the_same_setting_model(): void
    {
        $company = Company::factory()->create();

        $first = Setting::for($company);
        $second = Setting::for($company);

        $this->assertSame($first, $second);
        $this->assertDatabaseCount('settings', 1);
    }

    public function test_saving_a_setting_invalidates_the_memoized_cache(): void
    {
        $company = Company::factory()->create();
        $cached = Setting::for($company);

        $cached->set('currency', 'USD');
        $refreshed = Setting::for($company);

        $this->assertNotSame($cached, $refreshed);
        $this->assertSame('USD', $refreshed->get('currency'));
    }

    public function test_deleting_a_setting_invalidates_cache_and_recreates_defaults(): void
    {
        $company = Company::factory()->create();
        $setting = Setting::for($company);
        $setting->set('currency', 'USD');
        $setting->deleteOrFail();

        $recreated = Setting::for($company);

        $this->assertNotSame($setting->getKey(), $recreated->getKey());
        $this->assertSame('MYR', $recreated->get('currency'));
    }

    public function test_unsaved_models_cannot_have_settings(): void
    {
        $this->expectException(LogicException::class);

        Setting::for(new Company);
    }

    public function test_invalid_setting_model_configuration_is_rejected(): void
    {
        Config::set('settings.model', Company::class);

        $this->expectException(LogicException::class);

        (new Company)->getSettingModel();
    }

    public function test_custom_setting_models_are_created_and_returned(): void
    {
        Config::set('settings.model', CustomSetting::class);
        $company = Company::factory()->create();

        $setting = $company->setSetting('currency', 'USD');
        $reloaded = CustomSetting::for($company);

        $this->assertInstanceOf(CustomSetting::class, $setting);
        $this->assertInstanceOf(CustomSetting::class, $reloaded);
        $this->assertSame($setting->getKey(), $reloaded->getKey());
    }

    public function test_cached_values_are_type_checked(): void
    {
        $company = Company::factory()->create();
        Cache::memo()->forever($company->getCacheKey(), 'invalid');

        $this->expectException(\UnexpectedValueException::class);

        Setting::for($company);
    }

    public function test_saved_event_broadcasts_values_after_commit(): void
    {
        $company = Company::factory()->create();
        $setting = Setting::for($company);
        $event = new SettingSaved($setting);

        $this->assertTrue($event->afterCommit);
        $this->assertSame($setting->values, $event->broadcastWith());
        $this->assertSame(
            "private-settings.{$setting->model_type}.{$setting->model_id}",
            $event->broadcastOn()->name,
        );
    }
}
