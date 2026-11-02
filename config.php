<?php
session_set_cookie_params(['httponly'=>true,'samesite'=>'Strict']);session_start();
function db(){static $p;return $p??=new PDO('mysql:host=localhost;dbname=tgbtl_ott;charset=utf8mb4','root','',[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false]);}
function out($d,$c=200){http_response_code($c);header('Content-Type: application/json');echo json_encode($d);exit;}
function need($roles=['super','admin','support']){if(empty($_SESSION['u'])||!in_array($_SESSION['u']['role'],$roles))out(['error'=>'Không có quyền'],403);}
function csrf(){if($_SERVER['REQUEST_METHOD']==='POST'&&!hash_equals($_SESSION['csrf']??'',$_SERVER['HTTP_X_CSRF']??''))out(['error'=>'CSRF'],419);}
function logit($a,$d=''){db()->prepare('INSERT INTO logs(admin,action,detail)VALUES(?,?,?)')->execute([$_SESSION['u']['username']??'-',$a,$d]);}
