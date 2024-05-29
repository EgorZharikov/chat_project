<?php
namespace App\core\Redirect;

class Redirect
{
    public static function redirect($route)
    {
        header("Location:$route");
        exit;
    }
}