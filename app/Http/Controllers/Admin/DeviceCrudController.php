<?php

namespace App\Http\Controllers\Admin;

use App\Events\DeviceAssignedEvent;
use App\Models\Device;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Admin\Device\StoreRequest;
use App\Http\Requests\Admin\Device\UpdateRequest;
use App\Http\Controllers\Admin\Traits\CRUDUtility;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class DeviceCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class DeviceCrudController extends CrudController
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
        CRUD::setModel(\App\Models\Device::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/device');
        CRUD::setEntityNameStrings('Device', 'devices');
        $this->crud->addColumns([
            [
                'name' => 'id',
                'label' => __('#'),
                'type' => 'text',
            ],
            [
                'name' => 'device_name',
                'label' => "Name",
                'type' => 'text',

            ],
            [
                'name' => 'device_model',
                'label' => "Model",
                'type' => 'text',

            ],
            [
                'name' => 'device_version',
                'label' => "Version",
                'type' => 'text',

            ],
            [
                'name' => 'status',
                'label' => "Status",
                'type' => 'text',

            ],
            [
                'name' => 'device_picture',
                'label' => 'Picture',
                'type' => 'closure',
                'function' => function ($value) {
                    if (!empty($value->device_picture)) {
                        return '<a href="' . env("APP_URL") . "/storage" . "/" . $value->device_picture . '" target="_blank"> <img src="' . env("APP_URL") . "/storage" . "/" . $value->device_picture . '" style="width:150px;height:150px;border-radius:5px;">';
                    }
                    return "";
                },
            ]
        ]);

        $this->crud->addFields([
            [
                'name' => 'device_name',
                'label' => "Name",
                'type' => 'text',
            ],
            [
                'name' => 'device_model',
                'label' => "Model",
                'type' => 'text',
            ],
            [
                'name' => 'device_version',
                'label' => "Version",
                'type' => 'text',
            ],
            [
                'name' => 'device_picture',
                'label' => 'Picture',
                'type' => 'image',
                'upload' => true,
                'crop' => true,
                'disk' => 'public',
            ],
            [   // select_from_array
                'name' => 'status',
                'label' => "Status",
                'type' => 'enum',
            ]
        ]);

        $this->crud->setRequiredFields(StoreRequest::class, 'create');
        $this->crud->setRequiredFields(UpdateRequest::class, 'update');
    }

    /**
     * store function
     *
     * @param CreateRequest $request
     * @return void
     */
    public function store(StoreRequest $request)
    {
        $inputs = $request->only(
            'status',
            'device_name',
            'device_model',
            'device_version',
            'device_picture',
        );

        return DB::transaction(function () use ($inputs) {
            $device = Device::create($inputs);
            return $this->redirectLocation($device);
        });
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        CRUD::column('device_name');
        CRUD::column('device_model');
        CRUD::column('device_version');
        CRUD::column('device_picture');
        CRUD::column('status');
        CRUD::column('created_at');
        CRUD::column('updated_at');

        /**
         * Columns can be defined using the fluent syntax or array syntax:
         * - CRUD::column('price')->type('number');
         * - CRUD::addColumn(['name' => 'price', 'type' => 'number']);
         */
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
        $device = Device::find($id);

        $inputs = $request->only(
            'status',
            'device_name',
            'device_model',
            'device_version',
            'device_picture',
        );

        return DB::transaction(function () use ($device, $inputs) {
            $device->update($inputs);
            $status = $device->status;
            event(new DeviceAssignedEvent($device));
            return $this->redirectLocation($device);
        });
    }
}
