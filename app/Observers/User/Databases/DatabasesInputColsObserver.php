<?php

namespace App\Observers\User\Databases;

use App\Models\User\Databases\DatabasesInputCols;
use Carbon\Carbon;

class DatabasesInputColsObserver
{
    /**
     * Handle the DatabasesInputCols "created" event.
     *
     * @param  App\Models\User\Databases\DatabasesInputCols  $databases_input_cols
     * @return void
     */
    public function created(DatabasesInputCols $databases_input_cols)
    {
        $databases_input_cols->databases_input->update([
            'last_col_updated_at' => Carbon::now(),
        ]);
    }

    /**
     * Handle the DatabasesInputCols "updated" event.
     *
     * @param  App\Models\User\Databases\DatabasesInputCols  $databases_input_cols
     * @return void
     */
    public function updated(DatabasesInputCols $databases_input_cols)
    {
        $databases_input_cols->databases_input->update([
            'last_col_updated_at' => Carbon::now(),
        ]);
    }

    /**
     * Handle the DatabasesInputCols "deleted" event.
     *
     * @param  App\Models\User\Databases\DatabasesInputCols  $databases_input_cols
     * @return void
     */
    public function deleted(DatabasesInputCols $databases_input_cols)
    {
        $databases_input_cols->databases_input->update([
            'last_col_updated_at' => Carbon::now(),
        ]);
    }

    /**
     * Handle the DatabasesInputCols "restored" event.
     *
     * @param  App\Models\User\Databases\DatabasesInputCols  $databases_input_cols
     * @return void
     */
    public function restored(DatabasesInputCols $databases_input_cols)
    {
        //
    }

    /**
     * Handle the DatabasesInputCols "force deleted" event.
     *
     * @param  App\Models\User\Databases\DatabasesInputCols  $databases_input_cols
     * @return void
     */
    public function forceDeleted(DatabasesInputCols $databases_input_cols)
    {
        //
    }
}
