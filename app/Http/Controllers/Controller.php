<?php

namespace App\Http\Controllers;

/**
 * Base Controller
 * 
 * Laravel 11 no longer includes AuthorizesRequests and ValidatesRequests traits by default.
 * If any child controllers use $this->authorize() or $this->validate(), you can add
 * the traits back here or to those specific controllers.
 */
abstract class Controller
{
    //
}
