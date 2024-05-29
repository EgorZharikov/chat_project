<?php
namespace App\models;
use App\core\Db\Db;
use App\core\Validator\Validator;
use App\core\Session\Session;
use App\core\Uploader\Uploader;
use App\core\Redirect\Redirect;
use App\core\Auth\Auth;

class AccountModel
{
    public function get_user_data($params) 
    {
        $db = new Db;
        $sql = "SELECT hash, ip, id, login, email FROM users WHERE id = :id";
        $userdata = $db->row($sql, $params);
        return $userdata;
    }

    public function insert_user_data($params)
    {
        $db = new Db();
        $sql = "INSERT INTO users (email, password, login, avatar) VALUES (:email, :password, :login, :avatar)";
        $id = $db->insert($sql, $params);
        return $id;
    }

    public function get_signin_data($params)
    {
        $db = new Db();
        $sql = "SELECT id, password, login FROM users WHERE email = :email";
        $data = $db->row($sql, $params);
        return $data;
    }

    public function update_user_hash($params)
    {
        $db = new Db();
        $sql = "UPDATE users SET hash = :hash, ip = :ip WHERE id = :id";
        $db->query($sql, $params);
    }

    public static function get_profile_data($id) {
        $db = new Db();
        $params = ['id' => $id];
        $sql = "SELECT u.id, u.login, u.email, u.avatar, u.ip, u.last_seen, us.sound, us.email_privacy
                FROM users as u
                JOIN user_settings AS us ON us.id_user  = u.id
                WHERE u.id = :id";
        $data = $db->row($sql, $params);
        return $data;
    }

    public static function check_profile_settings() {
        if(isset($_POST['rename'])) {
            $db = new Db();
            $login = Validator::test_input($_POST['login']);
            $params = ['login' => $login];
            $sql = "SELECT id FROM users WHERE login = :login OR email = :login";
            $result =  $db->column($sql, $params);
            if(!$result) {
                $params = ['id' => $_SESSION['id'], 'login' => $login];
                $sql = "UPDATE users SET login = :login WHERE id = :id";
                $db->query($sql, $params);
                $_SESSION['login'] = $login;
            }
        }

        if(isset($_POST['setting'])) {
            $sound = isset($_POST['sound_on']) ? 1 : 0;
            $email = isset($_POST['email_hide']) ? 1 : 0;
            $id = $_SESSION['id'];
            $params = ['id_user' => $id, 'sound' => $sound, 'email_privacy' => $email];
            $db = new Db();
            $sql = "UPDATE user_settings SET sound = :sound, email_privacy = :email_privacy WHERE id_user = :id_user";
            $db->query($sql, $params);
        }

        if(isset($_POST['upload_avatar']) && !empty($_FILES)) {
            $id = $_SESSION['id'];
            $file_name = 'user_' . $id;    
            $uploaded_img =  Uploader::get_uploaded_img($file_name);
            if(empty($uploaded_img['errors'])) {
                $db = new Db();
                $params = ['id' => $id, 'avatar' => $uploaded_img['file_name']];
                $sql = "UPDATE users SET avatar = :avatar WHERE id = :id";
                $db->query($sql, $params);
            } else {
                $_SESSION['errors'] = $uploaded_img['errors'];
            }

            Redirect::redirect('/account/profile');
        }

        if (isset($_POST['signout'])) {
            Auth::signout();
        }
    }
}
