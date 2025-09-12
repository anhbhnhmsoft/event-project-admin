<?php

namespace App\Services;

use App\Models\Config;
use App\Utils\Constants\ConfigName;
use Illuminate\Support\Facades\DB;

class ConfigService
{
    public function getAllConfig()
    {
        return Config::all();
    }

    public function getConfigByKeys(array $keys)
    {
        return Config::query()->whereIn('config_key', $keys)->pluck('config_value', 'config_key');
    }

    public function getConfig(ConfigName $key)
    {
        return Config::query()->where('config_key', $key->value)->first();
    }

    public function updateConfigs(array $form): bool
    {
        try {
            DB::beginTransaction();
            foreach ($form as $key => $value) {
                $config = Config::query()->where('config_key', $key)->first();
                if ($config) {
                    $config->update(['config_value' => $value]);
                }
            }
            DB::commit();
            return true;
        } catch (\Exception $exception) {
            DB::rollBack();
            return false;
        }
    }

    public function getConfigValue(string $configKey, $default = null)
    {
        $config = Config::query()->where('config_key', $configKey)->first();
        return $config ? $config->config_value : $default;
    }
}
