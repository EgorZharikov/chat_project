<?php

namespace App\core\Validator;
use App\core\Db\Db;
class Validator {


static function validate_username($field)
{
    if ($field == "") return "Не введено имя пользователя.<br>";
    else if (strlen($field) < 5 or strlen($field) > 30)
        return "Логин должен быть не меньше 5-х символов и не больше 30.<br>";
    else if (preg_match("/[^a-zA-Z0-9_-]/", $field))
        return "В имени пользователя разрешены только  a-z, A-Z, 0-9, - и _<br>";
    return "";
}

static function check_username_exist($field)
{
    $mail = self::test_input($field);
    $db = new Db();
    $sql = "SELECT email FROM users WHERE email = '$mail'";
    $check =  $db->column($sql);
    if ($check) {
        return 'Пользователь уже зарегистрирован';
    } else return '';
}
static function validate_password($password, $confirm)
{
    if ($password == "") return "Не введен пароль.<br>";
    else if (strlen($password) < 8)
        return "В пароле должно быть не менее 8 символов.<br>";
    else if ($password !== $confirm)
        return "Пароли не совпадают.<br>";
    else if (
        !preg_match("/[a-z]/", $password) ||
        !preg_match("/[A-Z]/", $password) ||
        !preg_match("/[0-9]/", $password)
    )
        return "Пароль требует 1 сивола из каждого набора a-z, A-Z и 0-9<br>";
    return "";
}

static function test_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

static function validate_email($field)
    {
        if ($field == "") return "Не введен адрес электронной почты.<br>";
        else if (
            !((strpos($field, ".") > 0) &&
                (strpos($field, "@") > 0)) ||
            preg_match("/[^a-zA-Z0-9.@_-]/", $field)
        )
            return "Электронный адрес имеет неверный формат.<br>";
        return "";
    }

}