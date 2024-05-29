<?php
namespace App\core\Controller;

use App\core\View\View;

class Controller implements IController
{
    protected $view;

    public function __construct()
    {
        $this->view = New View;
    }

    public function index() {
        
    }

}