<?php
define('ROOT', dirname(__DIR__,2) . DIRECTORY_SEPARATOR);
define('APP', ROOT . 'app' . DIRECTORY_SEPARATOR);
define('CONFIG', APP . 'config' . DIRECTORY_SEPARATOR);
define('CONTROLLERS', APP . 'controllers' . DIRECTORY_SEPARATOR);
define('CORE', APP . 'core' . DIRECTORY_SEPARATOR);
define('MODELS', APP . 'models' . DIRECTORY_SEPARATOR);
define('VIEWS', APP . 'views' . DIRECTORY_SEPARATOR);
define('PUBL', APP . 'public' . DIRECTORY_SEPARATOR);
define('LAYOUT', VIEWS . 'layout' . DIRECTORY_SEPARATOR);
define('DATABASE', APP . 'database' . DIRECTORY_SEPARATOR);
define('LOGS', APP . 'logs' . DIRECTORY_SEPARATOR);
//upload img settings
define('UPLOAD_MAX_SIZE',  5000000);
define('ALLOWED_TYPES', ['image/jpeg', 'image/png']);
define('UPLOAD_DIR_NAME', 'img');
define('UPLOAD_DIR', PUBL . UPLOAD_DIR_NAME . DIRECTORY_SEPARATOR);
