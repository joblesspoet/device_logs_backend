<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\Devices\DeviceRequest;
use App\Http\Resources\API\DeviceRequestResource;
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
            $my_requests->orderBy('id', 'desc')->paginate(100)
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

        DB::transaction(function () use ($inputs) {
            ModelsDeviceRequest::create($inputs);
            return JsonResponse::HTTP_OK;
        });
    }

}
