<?php

namespace App\core\Auth;

use App\core\Session\Session;
use App\core\Db\Db;
use App\models\AccountModel;
use App\core\Validator\Validator;
use App\core\Request\Request;
use App\core\Redirect\Redirect;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Auth
{
    public static function check_user()
    {
        $accountModel = new AccountModel;
        $request =  Request::createFromGlobals();
        if (Session::has('id')) {
            return [
                'login' => Session::get('login'),
                'auth' => Session::get('auth'),
                'id' => Session::get('id'),
                'email' => Session::get('email')
            ];
        } else if (Session::hasCookie('id') and Session::hasCookie('hash')) {

            $userip = ip2long($request->server['REMOTE_ADDR']);
            $id = Session::getCookie('id');
            $params = ['id' => $id];
            $userdata = $accountModel->get_user_data($params);

            if (($userdata[0]['hash'] !== $request->cookie['hash']) or ($userdata[0]['id'] !== intval($request->cookie['id']))
                or (($userdata[0]['ip'] !== $userip)  and ($userdata[0]['ip'] !== "0"))
            ) {
                Session::removeCookie("login");
                Session::removeCookie("id");
                Session::removeCookie("hash");
                Session::removeCookie("email");
            } else {
                Session::setArray([
                    'login' => $userdata[0]['login'],
                    'id' => $userdata[0]['id'],
                    'auth' => true,
                    'email' => $userdata[0]['email']
                ]);
                return [
                    'login' => $userdata[0]['login'],
                    'auth' => true,
                    'id' => $userdata[0]['id'],
                    'email' => $userdata[0]['email']
                ];
            }
        }
    }

    public static function signup()
    {
        $request =  Request::createFromGlobals();
        if (isset($request->post['signup'])) {
            $password = $request->post['password'];
            $confirm = $request->post['confirm'];
            $email = $request->post['email'];
            $fail = Validator::validate_email($email);
            $fail .= Validator::validate_password($password, $confirm);
            $fail .= Validator::check_username_exist($email);

            if (empty($fail)) {
                $password = password_hash($password, PASSWORD_DEFAULT);
                $activation_code = mt_rand(11111111, 99999999);
                Session::has('auth') ? Session::destroyAll() : false;
                Session::setArray([
                    'passwordTmp' => $password,
                    'emailTmp' => $email,
                    'codeTmp' => $activation_code
                ]);
                self::send_mail($email, $activation_code);

                Redirect::redirect('/account/activation');
            } else {
                Session::set('errors', $fail);
                Redirect::redirect('/account/signup');
            }
        }
    }

    public static function signin()
    {
        $request =  Request::createFromGlobals();
        $sessionToken = Session::get('CSRF');
        $postToken = $request->post['token'] ?? null;

        if (isset($request->post['signin'])) {

            if ($sessionToken == $postToken) {
                $email = Validator::test_input($request->post['email']);
                $password = Validator::test_input($request->post['password']);
                $accountModel = new AccountModel;
                $params = ['email' => $email];
                $data = $accountModel->get_signin_data($params);
                if ($data and password_verify($password, $data[0]['password'])) {
                    $id = $data[0]['id'];
                    Session::setArray([
                        'auth' => true,
                        'login' => $data[0]['login'],
                        'id' => $id,
                        'email' => $email
                    ]);

                    if (isset($request->post['save_user'])) {
                        $hash = md5(static::generateCode(10));
                        $ip = ip2long($request->server['REMOTE_ADDR']);
                        $accountModel = new AccountModel;
                        $params = ['hash' => $hash, 'ip' => $ip, 'id' => $id];
                        $accountModel->update_user_hash($params);
                        Session::setCookie('id', $id);
                        Session::setCookie('hash', $hash);
                        Session::setCookie('login', $data[0]['login']);
                        Session::setCookie('email', $email);
                    }
                    Redirect::redirect('/home');
                } else {
                    $fail = 'Неверный пароль или имя пользователя';
                    Session::set('errors', $fail);
                    Redirect::redirect('/account/signin');
                }
            }
        }
    }

    public static function generateCode($length = 6)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHI JKLMNOPRQSTUVWXYZ0123456789";
        $code = "";
        $clen = strlen($chars) - 1;
        while (strlen($code) < $length) {
            $code .= $chars[mt_rand(0, $clen)];
        }
        return $code;
    }

    public static function signout(): void
    {
        $request =  Request::createFromGlobals();
        if (isset($request->post['signout'])) {
            Session::removeCookie("login");
            Session::removeCookie("id");
            Session::removeCookie("hash");
            Session::removeCookie("email");
            Session::destroyAll();
            Redirect::redirect('/account/signin');
        }
    }

    public static function check_auth()
    {
        if (Session::has('auth')) {
            return true;
        } else {
            Redirect::redirect('/account/signin');
        }
    }

    public static function signup_accept()
    {
        if (isset($_POST['activation'])) {

            if (
                isset($_POST['code']) && isset($_SESSION['codeTmp']) &&
                intval($_POST['code']) === intval($_SESSION['codeTmp'])
            ) {
                $password = Session::get('passwordTmp');
                $email = Session::get('emailTmp');
                $login = Session::get('emailTmp');
                $params = ['email' => $email, 'password' => $password, 'login' => $login, 'avatar' => 'default_avatar.png'];
                $accountModel = new AccountModel;
                $id = $accountModel->insert_user_data($params);
                Session::destroy('passwordTmp');
                Session::destroy('emailTmp');
                Session::destroy('codeTmp');
                Session::setArray([
                    'auth' => true,
                    'id' => $id,
                    'login' => $login,
                    'email' => $email
                ]);
                Redirect::redirect('/home');
            } else {
                Session::set('errors', 'Код подтверждения не верный!');
            }
        }
    }
    public static function send_mail($to, $activation_code)
    {
        $subject = "Подтверждение электронной почты";
        $body = '<h4>Activatiov code: ' . $activation_code . '</h4>';
        $mail = new PHPMailer(true);
        try {
            //Server settings
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host       = 'smtp.yandex.ru';                     //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            $mail->Username   = 'phpdevsender@yandex.ru';                     //SMTP username
            $mail->Password   = 'okdaerabsuuhtfan';                               //SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
            $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            //Recipients
            $mail->setFrom('phpdevsender@yandex.ru');
            $mail->addAddress($to);               //Name is optional

            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = $subject;
            $mail->Body    = $body;

            $mail->send();
            echo 'Message has been sent';
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }
}
