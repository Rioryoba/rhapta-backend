<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Http\Requests\StoreSettingRequest;
use App\Http\Requests\UpdateSettingRequest;
use App\Http\Resources\SettingResource;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all();
        return SettingResource::collection($settings);
    }

    public function show(Setting $setting)
    {
        return new SettingResource($setting);
    }

    public function store(StoreSettingRequest $request)
    {
        $this->authorize('create', Setting::class);
        $service = app(\App\Services\SettingService::class);
        $setting = $service->createSetting($request->validated());
        return new SettingResource($setting);
    }

    public function update(UpdateSettingRequest $request, Setting $setting)
    {
        $this->authorize('update', $setting);
        $service = app(\App\Services\SettingService::class);
        $setting = $service->updateSetting($setting, $request->validated());
        return new SettingResource($setting);
    }
    // ...existing code...
}
