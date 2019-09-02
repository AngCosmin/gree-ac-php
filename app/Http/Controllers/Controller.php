<?php

namespace App\Http\Controllers;

use App\DeviceFinder;
use App\Device;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function updateStatus(Request $request)
    {
    public function index() {
        $finder = new DeviceFinder();
        $device = new Device();
        $data = $finder->scan();
        $device->cid = $data->cid;
        $device->mid = $data->cid;
        $device->mac = $data->mac;
        $device->name = $data->name;
        $device->pair();

        $device->off();
        // $device->setTemperature(22);
        // $device->setFanSpeed(3);
        // $device->setSwing(1);

        return "Done";
    }
}
