<?php

namespace App\Http\Controllers\Admin;

use App\Models\Device;
use App\Models\DeviceRequest;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Admin\Traits\CRUDUtility;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use App\Http\Requests\Admin\DeviceRequest\StoreRequest;
use App\Http\Requests\Admin\DeviceRequest\UpdateRequest;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

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
            // [
            //     'name' => 'log_detail',
            //     'label' => 'Log Detail',
            //     'type' => 'text',
            // ],
            [
                'name' => 'status',
                'label' => "Status",
                'type' => 'select_from_array',
                'options' => array_combine(Device::STATUS, Device::STATUS),
                'allows_null' => false,
            ]
        ]);

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
        CRUD::column('user_id');
        CRUD::column('device_id');
        CRUD::column('request_detail');
        CRUD::column('created_at');
        CRUD::column('updated_at');
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
        $status =  $request->only('status');

        return DB::transaction(function () use ($inputs, $status) {
            $request = DeviceRequest::create($inputs);
            $request->device->update($status);
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
        ]);
        $status =  $request->only('status');

        return DB::transaction(function () use ($deviceRequest, $inputs, $status) {
            $deviceRequest->update($inputs);
            $deviceRequest->device->update($status);
            return $this->redirectLocation($deviceRequest);
        });
    }
}
