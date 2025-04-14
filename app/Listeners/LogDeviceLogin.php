<?php

namespace App\Listeners;
use App\Models\DeviceLogins;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Auth\Events\Login;
use Jenssegers\Agent\Agent;

class LogDeviceLogin
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Login $event)
    {
        $agent = new Agent();
        $device = $agent->device();
        $platform = $agent->platform();
        $browser = $agent->browser();
        $ip = request()->ip();

        DeviceLogins::create([
            'ten_dang_nhap' => $event->user->ten_dang_nhap,
            'device' => $device,
            'ip_address' => $ip,
            'platform' => $platform,
            'browser' => $browser,
        ]);
    }
}
