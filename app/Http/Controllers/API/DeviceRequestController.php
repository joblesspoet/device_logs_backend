<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\Devices\DeviceRequest;
use App\Http\Resources\API\DeviceRequestResource;
use App\Models\DeviceRequest as ModelsDeviceRequest;
use App\Traits\GetAuthUserTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DeviceRequestController extends Controller
{
    use GetAuthUserTrait;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $user =  $this->getAuthUser();
        $my_requests = $user->device_requests();
        return DeviceRequestResource::collection(
            $my_requests->orderBy('id', 'desc')->paginate(5)
        );
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(DeviceRequest $request)
    {
        $user =  $this->getAuthUser();
        $inputs = $request->only(['device_id', 'request_detail']);
        $inputs['user_id'] = $user->id;

        return DB::transaction(function () use ($inputs) {
            $d_request = ModelsDeviceRequest::create($inputs);
            return DeviceRequestResource::make($d_request);
        });
    }

}
