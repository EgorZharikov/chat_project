<?php
namespace App\controllers;

use App\core\Controller\Controller;
use App\models\HomeModel;
use App\core\Auth\Auth;

class HomeController extends Controller {
    public function index() {
        HomeModel::check_user_actions();
        $this->view->render('home' . DIRECTORY_SEPARATOR . 'home.php');
    }

    public function ajax()
    {
        $this->view->render('home' . DIRECTORY_SEPARATOR . 'ajax.php');
    }
}