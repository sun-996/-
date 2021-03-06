<?php
//链接数据库
require('../conn.php');

//在数组的基础上初始化一个空对象
$obj = (object) array();

//接收用户输入的参数
$id = trim( $_POST['id'] );
$name = trim( $_POST['name'] );
$icon = trim( $_POST['icon'] );
$iconed = trim( $_POST['iconed'] );
$path = trim( $_POST['path'] );
$orderid = (int) $_POST['orderid'];


//查询语句（推荐双引号）
$sql = "
update `footnav` set
`name` = '$name',
`icon` = '$icon',
`iconed` = '$iconed',
`path` = '$path',
`orderid` = $orderid
where `id` = '$id'
";

//输出调试专用
// die($sql);

//执行上面的查询语句
$result = mysqli_query( $conn, $sql );

//判断执行结果
if( $result ){
  $obj -> code = 20000;
  $obj -> desc = '执行数据库查询语句成功！';
}else{
  $obj -> code = 50000;
  $obj -> desc = '执行数据库查询语句失败！';
}

//将php对象转换为json字符串数据
echo json_encode( $obj );

//关闭数据库连接，释放资源
mysqli_close( $conn );
?>