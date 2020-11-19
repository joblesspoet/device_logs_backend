<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class DeviceLogCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class DeviceLogCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     * 
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\DeviceLog::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/devicelog');
        CRUD::setEntityNameStrings('Device Log', 'Devices Logs');

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
                'name' => 'log_detail',
                'label' => 'Log Detail',
                'type' => 'text',
            ],
            [
                'name' => 'created_at',
                'label' => 'Created At',
                'type' => 'text',
            ],
        ]);
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        // CRUD::column('id');
        // CRUD::column('user_id');
        // CRUD::column('device_id');
        // CRUD::column('log_detail');
        // CRUD::column('created_at');
        // CRUD::column('updated_at');
    }
}
