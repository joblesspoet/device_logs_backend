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
use Illuminate\Support\Facades\Log;

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
        $inputs['request_status'] = ModelsDeviceRequest::STATUS_PENDING;

        $count = $user->device_requests()->where(function($query) use($request){
            return $query->where('device_id', $request->input('device_id'))->where('request_status', ModelsDeviceRequest::STATUS_PENDING);
        })->count();

        if($count > 0) {
            return new JsonResponse([
                'message' => __('You had already requested the device. Please wait for approval')
                    ], JsonResponse::HTTP_FOUND);
        }

        $device_request = DB::transaction(function () use ($inputs, $user) {
            return $user->device_requests()->create($inputs);
        });

        return DeviceRequestResource::make($device_request);
    }

}
