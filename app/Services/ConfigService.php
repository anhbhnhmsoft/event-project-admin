<?php

namespace App\Services;

use App\Models\Config;
use App\Models\Organizer;
use App\Utils\Constants\ConfigName;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ConfigService
{
    public function getAllConfigByOrganizerId($organizerId)
    {
        return Config::query()->where('organizer_id', $organizerId)->get();
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
            Log::info('Update Config: ' . $exception->getMessage());
            DB::rollBack();
            return false;
        }
    }

    public function getConfigValue(string $configKey, $organizerId, $default = null)
    {
        $config = Config::query()->where('organizer_id', $organizerId)->where('config_key', $configKey)->first();
        return $config ? $config->config_value : $default;
    }

    public function getOrganizerInfo($organizerId)
    {
        return Organizer::findOrFail($organizerId);
    }


    public function updateConfigsByOrganizerId($organizerId, array $configValues): bool
    {
        try {
            DB::beginTransaction();

            foreach ($configValues as $key => $value) {
                Config::where('organizer_id', $organizerId)
                    ->where('config_key', $key)
                    ->update([
                        'config_value' => $value
                    ]);
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating configs by organizer: ' . $e->getMessage());
            return false;
        }
    }

    public function updateOrganizer($organizerId, array $data): bool
    {
        try {
            $organizer = Organizer::findOrFail($organizerId);
            $organizer->update($data);

            return true;
        } catch (\Exception $e) {
            Log::error('Error updating organizer: ' . $e->getMessage());
            return false;
        }
    }
}
