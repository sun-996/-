<?php

//链接数据库



require('../conn.php');


$obj=(object)array();

//接收用户输入的参数
$id = trim( $_POST['id'] );
$username = trim( $_POST['username'] );
$password = md5(trim( $_POST['password'] ));
$realname = trim( $_POST['realname'] );
$avatar = trim( $_POST['avatar'] );

//查询语句(推荐双引号)
$sql = "
insert into
`admin`
(`id`,`username`,`password`,`realname`,`avatar`) 
values
('$id','$username','$password','$realname','$avatar')
";

//调试语句
// die($sql);

//执行上方的查询语句

$result=mysqli_query($conn,$sql);



//执行结果
if( $result ){
  $obj->code=20000;
  $obj->desc="插入数据成功";

}else{
  $obj->code=50000;
  $obj->desc="执行数据库查询语句失败";
}


echo json_encode( $obj );

//关闭数据库，释放资源
mysqli_close($conn);




?>