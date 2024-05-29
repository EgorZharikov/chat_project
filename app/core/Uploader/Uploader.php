<?php

namespace App\core\Uploader;

class Uploader
{
    public static function get_uploaded_img($file_name)
    {       
            $fileName = $_FILES['files']['name'][0];
            $errors = '';
            if ($_FILES['files']['size'][0] > UPLOAD_MAX_SIZE) {
                $errors = 'Недопустимый размер файла ' . $fileName . '<br>';
            }

            if (!in_array($_FILES['files']['type'][0], ALLOWED_TYPES)) {
                $errors = 'Недопустимый формат файла ' . $fileName . '<br>';
            }

            $type = strstr($fileName, ".");
            $name = $file_name . $type;
            $savePath = UPLOAD_DIR . $name;
            $tmpName = $_FILES['files']['tmp_name'][0];

            if (!move_uploaded_file($tmpName, $savePath)) {
                $errors = 'Ошибка загрузки файла ' . '#' . $_FILES['files']['error'][0] . '<br>';
            }

            return [
                'errors' => $errors,
                'file_name' => $name
            ];
    }
}
