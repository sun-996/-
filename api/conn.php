<?php

//设置跨域允许，开发时全部允许（*）
header('Access-Control-Allow-Origin:*');


//设置时区（PRC中国）
date_default_timezone_set('PRC');

//设置输出json文档，防止中文乱码
header('Content-type:application/json;charset=utf-8');
// header('Content-type:text/html;charset=utf-8');


//数据库登录信息
$db_url="localhost";
$db_name="root";
$db_pass="";
$db_dbname="vivo";
$db_port="3306";

//创建数据库连接
$conn=mysqli_connect($db_url,$db_name,$db_pass,$db_dbname,$db_port);

//判断数据库是否连接成功
if($conn==false){
  die("链接数据库失败".mysqli_connect_error());
}

//防止中文数据插入表中乱码

mysqli_query($conn,"set names utf8");


?>