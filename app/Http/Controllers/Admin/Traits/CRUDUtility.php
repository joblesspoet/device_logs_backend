<?php

namespace App\Http\Controllers\Admin\Traits;

use Illuminate\Database\Eloquent\Model;

trait CRUDUtility
{
    /**
     * @param \Model
     * @param \method
     * @return \CRUD\url
     */
    protected function redirectLocation(Model $model, string $method = 'store')
    {
        $message = 'backpack::crud.insert_success';

        if($method == 'update'){
            $message = 'backpack::crud.update_success';
        }

        // show a success message
        \Alert::success(trans($message))->flash();

        // save the redirect choice for next time
        $this->crud->setSaveAction();

        return $this->crud->performSaveAction($model->getKey());
    }
    
}
