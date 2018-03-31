<?php
header("Content-type: text/html; charset=utf-8");
header("Cache-Control: no-cache, must-revalidate");


/* Объявление констант */
define('ROOT_PATH', __DIR__);
define('TEMPLATE_PATH', ROOT_PATH.'/templates');
define('PRODUCTS_IMG_PATH', ROOT_PATH.'/products_img');





/* Обработка запроса */
$url = $_SERVER['REQUEST_URI'];
preg_match('/^([^?]+)(\?.*?)?(#.*)?$/', $url, $matches);
$request = (isset($matches[1])) ? $matches[1] : '';
$request = str_replace('/', '', $request);

if ($request == '')
    $request = 'canvas';



/* Подключение контроллера */
$controller_path = 'controllers/'.$request.'.php';
if (file_exists($controller_path)){
    include($controller_path);
}else{
    include('controllers/404.php');
}




