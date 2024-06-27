<?php

require './vendor/autoload.php';



define( "EMAIL", "noncre123@gmail.com");
define( "PASSWORD", "ywlewgcdbwiyrpvb");
define("DATABASEDNS",'mysql:host=localhost;dbname=guidance_system;charset=utf8');
define("DATABASE_USERNAME",'root');
define("DATABASE_PASSWORD", '');


$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

$pdo = new PDO(DATABASEDNS, DATABASE_USERNAME, DATABASE_PASSWORD, $options);



use Simpluity\Simpluity\BaseController;

$controller = new BaseController("testing", $pdo, true);
$data =  $controller->GET();
print_r($data);


