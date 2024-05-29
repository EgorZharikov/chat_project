<?php

namespace App\core\Session;

class Session
{

    // задаем время жизни сессионных кук



    // стартуем сессию
    public static function start()
    {
        session_start();
    }


    /**
     * Проверяем сессию на наличие в ней переменной c заданным именем
     */
    public static function has($name)
    {
        return isset($_SESSION[$name]);
    }



    /**
     * Устанавливаем сессию с именем $name и значением $value
     *
     *
     * @param $name
     * @param $value
     */
    public static function set($name, $value)
    {
        $_SESSION[$name] = $value;
    }





    /**
     * Когда мы хотим сохранить в сессии сразу много значений - используем массив
     *
     * @param $vars
     */
    public static function setArray(array $vars)
    {
        foreach ($vars as $name => $value) {
            static::set($name, $value);
        }
    }



    /**
     * Получаем значение сессий
     *
     * @param $name
     * @return mixed
     */
    public static function get($name)
    {
        if (isset($_SESSION[$name])) {
            return $_SESSION[$name];
        }
    }

    public static function flash($name)
    {
        if (isset($_SESSION[$name])) {
            $flush = $_SESSION[$name];
            unset($_SESSION[$name]);
            
            return $flush;
        }
    }



    /**
     * @param $name - Уничтожаем сессию с именем $name
     */
    public static function destroy($name)
    {
        unset($_SESSION[$name]);
    }



    /**
     * Полностью очищаем все данные пользователя
     */
    public static function destroyAll()
    {
        session_destroy();
    }



    /**
     * Устанавливаем куки  
     *
     * @param $name
     * @param $value
     */
    public static function setCookie($name, $value)
    {
        setcookie($name, $value, strtotime('+7 days'), '/');
    }



    /**
     * Получаем куки
     *
     * @param $name
     * @return mixed
     */
    public static function getCookie($name)
    {

        if (isset($_COOKIE[$name])) {
            return $_COOKIE[$name];
        }
    }

    //проверяем куки
    public static function hasCookie($name)
    {
        return isset($_COOKIE[$name]);
    }

    /**
     * @param $name Удалаяем
     */
    public static function removeCookie($name)
    {
        setcookie($name, '', time() - 3600, '/');
    }

    public static function getToken()
    {
        $token = hash('gost-crypto', random_int(0, 999999));
        $_SESSION["CSRF"] = $token;
        return $token;
    }
}
