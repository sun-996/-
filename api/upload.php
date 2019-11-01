<?php
//在数组的基础上初始化一个空对象
$obj = (object) array();

//允许上传文件扩展名
$str_ext = 'jpg,png,gif';
$allow_ext = explode(',', $str_ext);

//允许上传文件大小
$m = 2; //M
$allow_size = $m * 1024*1024;

//设置时区（PRC中国）
date_default_timezone_set('PRC');

//设置输出html文档，防止中文乱码
header('Content-type: text/html; charset=utf-8');

//接收客户端提交的上传表单
$upfile = $_FILES['upfile'];

//错误处理
if( $upfile['error'] > 0 ){
  $obj -> code = 50001;
  $obj -> desc = '上传过程中发送错误！';
}else{

  //临时文件
  $tmpfile = $upfile['tmp_name'];

  //原始文件名
  $filename = $upfile['name'];

  //文件扩展名
  $arr = explode('.', $filename );
  $ext = end( $arr );

  //新命名文件名
  $newfilename = date('YmdHis');

  //保存路径
  $filepath = 'uploads/'.$newfilename.'.'.$ext;

  //文件大小
  $filesize = $upfile['size'];

  //检测是否超过允许大小
  if( $filesize > $allow_size ){
    $obj -> code = 50002;
    $obj -> desc = '您上传的文件过大！文件大小不能超过'.$m.'M';
  }else if( in_array( $ext, $allow_ext ) == false ){
    $obj -> code = 50003;
    $obj -> desc = '您上传的类型不符合要求！只能上传'. $str_ext;
  }else{
    //将临时文件保存到你要上传的目录
    $result = move_uploaded_file(  $tmpfile, $filepath );
    if( $result ){
      $obj -> code = 20000;
      $obj -> desc = '上传成功！';
      $obj -> filepath = 'http://localhost:8080/vivo_api/'.$filepath;
    }else{
      $obj -> code = 50004;
      $obj -> desc = '上传失败！';
    }
  }
}

//将php对象转换为json字符串数据
echo json_encode( $obj );

?>