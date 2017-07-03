<?php
require __DIR__ . DIRECTORY_SEPARATOR . 'defines.php';
require __DIR__ . DIRECTORY_SEPARATOR . 'accounts.php';

error_reporting(E_ALL);
date_default_timezone_set(FDEF_DEFAULT_TIMEZONE);
ini_set('default_charset', FDEF_DEFAULT_CHARSET);

spl_autoload_register(function($classname) {
    $basedir = FDEF_PATH_SRC . DIRECTORY_SEPARATOR;
    
    // ex) ProjectName\\TeamName ...
    $prefix = '';
    
    // ex) .class.php
    $suffix = '.php';
    
    $filepath = $basedir . $prefix . str_replace(FDEF_NS_SEPARATOR, DIRECTORY_SEPARATOR, $classname) . $suffix;
    
    if (file_exists($filepath) === true) {
        require $filepath;
    }
});