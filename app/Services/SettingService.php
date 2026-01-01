<?php

namespace App\Services;

use App\Models\Setting;

class SettingService
{
    public function createSetting(array $data): Setting
    {
        return Setting::create($data);
    }

    public function updateSetting(Setting $setting, array $data): Setting
    {
        $setting->update($data);
        return $setting;
    }
}
