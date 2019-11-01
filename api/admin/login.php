<?php

//链接数据库


require('../conn.php');

require('../jwt/init.php');


//初始化一个空对象
$obj =(object)array();

//接收用户的参数
$username = trim( $_POST['username'] );
$password = md5(trim( $_POST['password'] ));

$sql="
select `id` from `admin`
where `username`='$username' and `password`='$password' 
limit 1;
";

// die($sql);

$result=mysqli_query($conn,$sql);

if($result){

  $total=mysqli_num_rows($result);

  if($total>0){
    $row=mysqli_fetch_all($result);
    $obj->code=20000;
    $obj->desc="登录成功";

    //生成token（令牌）信息

    //当前时间（1970年到现在的总秒数）
    $nowtime = time();

    //token信息
    $token = array(
      'iss' => 'sun.com', //签发者 域名
      'aud' => $username, //面向的用户 登录的用户账号
      'iat' => $nowtime, //签发时间
      'exp' => $nowtime + 640800 //过期时间1分钟
    );

    //输出token给前端保存到本地
    $obj -> token = JWT::encode( $token , 'sun' );

  }else{

    $obj->code=50001;
    $obj->desc='用户名或密码错误！';

  }

}else{

  $obj->code=50000;
  $obj->desc='执行数据库语句失败';
  
}

echo json_encode($obj);


//关闭数据库
mysqli_close($conn);
