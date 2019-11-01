<?php
//载入json web token 验证类
include 'JWT.php';

//令牌信息
define('iss', 'vivo-admin.com'); //签发者 域名
define('secret', '1gHuiop975cdashyex9Ud23ldsvm2Xq'); //密钥
define('leeway', 60); //60秒余地

?>