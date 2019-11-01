<?php
//在数组的基础上初始化一个空对象
$obj = (object) array();

//获取token（令牌）信息，并且解密token
include('../jwt/init.php');

//接收用户传来的token字符串
$token = trim( $_POST['token'] );

try {

  JWT::$leeway = 640800;
  JWT::decode($token, 'sun', ['HS256']);
  $obj -> code = 20000;
  $obj -> desc = 'token是符合要求的';

}catch(Exception $e) {

  $obj -> code = 50000;
  $obj -> desc = $e->getMessage();

}

//将php对象转换为json字符串数据
echo json_encode( $obj );

?>