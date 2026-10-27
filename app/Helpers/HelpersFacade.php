<?php
// app/Helpers/HelpersFacade.php
namespace App\Helpers;

use Illuminate\Support\Facades\Facade;

class HelpersFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'helpers';
    }
}
