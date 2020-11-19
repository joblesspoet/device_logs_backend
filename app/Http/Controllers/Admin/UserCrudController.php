<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Traits\CRUDUtility;
use App\Http\Requests\Admin\User\StoreRequest;
use App\Http\Requests\Admin\User\UpdateRequest;
use App\Http\Requests\UserRequest;
use App\Models\User;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Class UserCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class UserCrudController extends CrudController
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
        CRUD::setModel(\App\Models\User::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/user');
        CRUD::setEntityNameStrings('User', 'Users');
        $this->crud->addColumns([
            [
                'name' => 'id',
                'label' => __('#'),
                'type' => 'text',
            ],
            [
                'name' => 'name',
                'label' => "Name",
                'type' => 'text',

            ],
            [
                'name' => 'email',
                'label' => "Email",
                'type' => 'text',
            ]
        ]);

        $this->crud->addFields([
            [
                'name' => 'name',
                'label' => "Name",
                'type' => 'text',
            ],
            [
                'name' => 'email',
                'label' => 'Email Address',
                'type' => 'email',
            ],
            [
                'name' => 'password',
                'label' => 'Password',
                'type' => 'password',
            ],
            [
                'name' => 'password_confirmation',
                'label' => 'Password confirmation',
                'type' => 'password',
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
        // CRUD::column('name');
        // CRUD::column('email');
        // CRUD::column('created_at');
        // CRUD::column('updated_at');

        /**
         * Columns can be defined using the fluent syntax or array syntax:
         * - CRUD::column('price')->type('number');
         * - CRUD::addColumn(['name' => 'price', 'type' => 'number']); 
         */
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
            [
                'name',
                'email',
                'password'
            ]
        );

        return DB::transaction(function () use ($inputs) {
            $user = User::create($inputs);
            return $this->redirectLocation($user);
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
        $user = User::find($id);

        $inputs = $request->only(
            [
                'name',
                'email',
                'password'
            ]
        );

        if (is_null($inputs['password'])) {
            unset($inputs['password']);
        }

        return DB::transaction(function () use ($user, $inputs) {
            $user->update($inputs);
            return $this->redirectLocation($user);
        });
    }
}
