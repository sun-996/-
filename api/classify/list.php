<?php

//链接数据库


require('../conn.php');


//初始化一个空对象
$obj =(object)array();

//接收用户的参数

$sql="
select * from `classify` order by `orderid` asc ,`id` desc;
";

// die($sql);

$result=mysqli_query($conn,$sql);

if($result){

  $total=mysqli_num_rows($result);
  //没有数据也要给一个空数组
  $obj->record=array();

  if($total>0){

    $obj->code=20000;
    $obj->desc="查询到 $total 条数据";


    while($row=mysqli_fetch_assoc($result)){

      array_push($obj->record,$row);
    }

  }else{

    $obj->code=50001;
    $obj->desc='抱歉，没有任何数据！';

  }

}else{

  $obj->code=50000;
  $obj->desc='执行数据库语句失败';
  
}

echo json_encode($obj);


//关闭数据库
mysqli_close($conn);
?>
