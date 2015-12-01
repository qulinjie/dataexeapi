
<?php 
/*
 * $code
 * $message
 * $jump
 * $jump_url
 * $jump_url_name
 */
?>

<!DOCTYPE html>
<html lang="zh">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="<?php echo Router::getBaseUrl();?>asset/ico/favicon.ico">

    <title>xplusplus.cn错误页面</title>

    <!-- Bootstrap core CSS -->
    <link href="<?php echo Router::getBaseUrl();?>css/bootstrap.min.css" rel="stylesheet">
    <style type="text/css">
body {
  padding-top: 20px;
  padding-bottom: 20px;
}


.footer {
  padding-right: 15px;
  padding-left: 15px;
}

.footer {
  padding-top: 19px;
  color: #777;
  border-top: 1px solid #e5e5e5;
}

@media (min-width: 768px) {
  .container {
    max-width: 730px;
  }
}
.container-narrow > hr {
  margin: 30px 0;
}

.jumbotron {
  text-align: center;
  border-bottom: 1px solid #e5e5e5;
}
.jumbotron .btn {
  padding: 14px 24px;
  font-size: 21px;
}

@media screen and (min-width: 768px) {
  .footer {
    padding-right: 0;
    padding-left: 0;
  }
  
  .jumbotron {
    border-bottom: 0;
  }
}
	</style>
    <!-- Just for debugging purposes. Don't actually copy these 2 lines! -->
    <!--[if lt IE 9]><script src="<?php echo Router::getBaseUrl();?>js/ie8-responsive-file-warning.js"></script><![endif]-->
    <script src="<?php echo Router::getBaseUrl();?>js/ie-emulation-modes-warning.js"></script>

    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <script src="<?php echo Router::getBaseUrl();?>js/ie10-viewport-bug-workaround.js"></script>

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>

  <body>
    <div class="container">

      <div class="jumbotron">
        <h1>Xplusplus.cn &nbsp;&nbsp; <?php echo $code;?></h1>
        <BR>
        <p class="lead"><?php echo $message;?></p>
        <p><a class="btn btn-lg btn-success" href="<?php echo Router::getBaseUrl()?>index.php/index" role="button">回xplusplus.cn首页</a></p>
        <?php if($jump){?>
        <p>自动跳转到<?php echo $jump_url_name;?>中...</p>
        <?php }?>
      </div>

      <div class="footer">
        <a href="<?php echo Router::getBaseUrl()?>index.php/index">xplusplus.cn</a>
      </div>

    </div>
    <?php if($jump) {?>
    <script type="text/javascript">
    	setTimeout(function(){
    		window.location.href='<?php echo $jump_url;?>';
    	}, 3000);
    </script>
    <?php }?>
  </body>
</html>
