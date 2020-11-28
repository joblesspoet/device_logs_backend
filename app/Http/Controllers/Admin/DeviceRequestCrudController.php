<?php

namespace App\Http\Controllers\Admin;

use App\Events\CollectDeviceEvent;
use App\Events\DeviceAssignedEvent;
use App\Models\Device;
use App\Models\DeviceRequest;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Admin\Traits\CRUDUtility;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use App\Http\Requests\Admin\DeviceRequest\StoreRequest;
use App\Http\Requests\Admin\DeviceRequest\UpdateRequest;
use App\Models\DeviceLog;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Event;
use Illuminate\Http\Request;
use Log;
use Prologue\Alerts\Facades\Alert;

/**
 * Class DeviceRequestCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class DeviceRequestCrudController extends CrudController
{
    use CRUDUtility;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation {
        store as traitStore;
    }
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation {
        update as traitUpdate;
        edit as traitEdit;
    }
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\DeviceRequest::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/devicerequest');
        CRUD::setEntityNameStrings('Device Request', 'Device Requests');

        $this->crud->addColumns([
            [
                'name' => 'id',
                'label' => __('#'),
                'type' => 'text',
            ],
            [
                'name' => 'user_id',
                'label' => "Name",
                'type' => 'closure',
                'function' => function ($model) {
                    return $model->user->name;
                }

            ],
            [
                'name' => 'device_id',
                'label' => "Device name",
                'type' => 'closure',
                'function' => function ($model) {
                    return $model->device->device_name;
                }

            ],

            [
                'name' => 'request_detail',
                'label' => 'Request Detail',
                'type' => 'text',
            ],
            [
                'name' => 'request_status',
                'label' => 'Request Status',
                'type' => 'text',
                'function' => function ($model) {
                    return str_replace("_"," ",$model);
                }
            ],
            [
                'name' => 'created_at',
                'label' => 'Created At',
                'type' => 'text',
            ],
        ]);
        $this->crud->addFields([
            [
                'label'     => "User Name",
                'type'      => 'select',
                'name'      => 'user_id',
                'entity'    => 'user',
                'model'     => "App\Models\User",
                'attribute' => 'name',
            ],
            [
                'label'     => "Device Name",
                'type'      => 'select',
                'name'      => 'device_id',
                'entity'    => 'device',
                'model'     => "App\Models\Device",
                'attribute' => 'device_name',
            ],
            [
                'name' => 'request_detail',
                'label' => 'Request Detail',
                'type' => 'text',
                'attribute' => 'readonly'
            ],
            [
                'name' => 'log_detail',
                'label' => 'Log Detail',
                'type' => 'text',
            ],
            [
                'name' => 'request_status',
                'label' => "Request Status",
                'type' => 'select_from_array',
                'options' => DeviceRequest::REQUEST_STATUS,
                'allows_null' => false,
            ]
        ]);

        $this->crud->addButtonFromModelFunction('line', 'deliver_device', 'deliverDevice', 'beginning');
        $this->crud->addButtonFromModelFunction('line', 'please_collect', 'pleaseCollect', 'beginning');
        $this->crud->addButtonFromModelFunction('line', 'receive_device', 'receiveDevice', 'beginning');

        $this->crud->setRequiredFields(StoreRequest::class, 'create');
        $this->crud->setRequiredFields(UpdateRequest::class, 'update');
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        // CRUD::column('user_id');
        // CRUD::column('device_id');
        // CRUD::column('request_detail');
        // CRUD::column('created_at');
        // CRUD::column('updated_at');
    }

    /**
     * store function
     *
     * @param CreateRequest $request
     * @return void
     */
    public function store(StoreRequest $request)
    {
        $inputs = $request->only([
            'user_id',
            'device_id',
            'request_detail',
        ]);
        $log_detail = $request->only([
            'log_detail',
            'device_id',
            'user_id'
        ]);

        $status =  $request->only('status');

        return DB::transaction(function () use ($inputs, $status, $log_detail) {
            $request = DeviceRequest::create($inputs);
            $request->device->update($status);
            DeviceLog::create($log_detail);
            return $this->redirectLocation($request);
        });
    }

    /**
     * update function
     *
     * @param UpdateRequest $request
     * @param int $id
     * @return void
     */
    public function update(UpdateRequest $request, $id)
    {
        $deviceRequest = DeviceRequest::find($id);

        $inputs = $request->only([
            'user_id',
            'device_id',
            'request_detail',
            'request_status'
        ]);

        $log_detail = $request->only([
            'log_detail',
            'device_id',
            'user_id'
        ]);
        $status =  $request->input('request_status');

        return DB::transaction(function () use ($deviceRequest, $inputs, $status, $log_detail) {
            $deviceRequest->update($inputs);
            if($status === 'APPROVED'){
                $deviceRequest->device->update(['status' => Device::INUSE]);
                DeviceLog::create($log_detail);
                event(new DeviceAssignedEvent($deviceRequest->device));
            }
            return $this->redirectLocation($deviceRequest);
        });
    }


    /**
     * Please collect action
     */
    public function pleaseCollect(DeviceRequest $deviceRequest){
        $deviceRequest->update(['request_status' => DeviceRequest::STATUS_PLASE_COLLECT]);
        event(new CollectDeviceEvent($deviceRequest));
        Alert::success(trans("Request Status has been changed."))->flash();
        return redirect('/admin/devicerequest');
    }

    /**
     * Deliver Device action
     */
    public function deliverDevice(DeviceRequest $deviceRequest) {

        $deviceRequest->update(['request_status' => DeviceRequest::STATUS_APPROVED]);
        $deviceRequest->device->update(['status' => Device::INUSE]);
        $log_detail = [
            'log_detail' => "Device has been delivered to {$deviceRequest->user->name}",
            'device_id' => $deviceRequest->device_id,
            'user_id' => $deviceRequest->user_id
        ];
        DeviceLog::create($log_detail);
        event(new DeviceAssignedEvent($deviceRequest->device));
        Alert::success(trans("Device has been assigned."))->flash();
        return redirect('/admin/devicerequest');
    }

    /**
     * Receive device
     */
    public function receiveDevice(DeviceRequest $deviceRequest) {

        $deviceRequest->device->update(['status' => Device::AVAILABLE]);
        $log_detail = [
            'log_detail' => "Device received from {$deviceRequest->user->name}",
            'device_id' => $deviceRequest->device_id,
            'user_id' => $deviceRequest->user_id
        ];
        DeviceLog::create($log_detail);
        event(new DeviceAssignedEvent($deviceRequest->device));
        Alert::success(trans("Device has been received."))->flash();
        return redirect('/admin/devicerequest');
    }
}
