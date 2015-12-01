<?php 
/**
 * main_html
 * page_type # order: show order page
 * 				#order_html
 * 			# item: show item page
 * 				#item_html
 * 			# tender: show tender page
 * 				#tender_html
 * 
 * 
 */
$session = Controller::instance('session');
$loginUser = $session->get( 'loginUser' );
$encrypt = Controller::instance('encrypt');
$login_token = $encrypt->tokenCode('login:' . $session->get_id());
$other_token = $encrypt->tokenCode('other:' . $session->get_id());
?>
<!DOCTYPE html>
<html lang="zh">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="">
	<meta name="author" content="">
	<link rel="shortcut icon"
		href="<?php echo Router::getBaseUrl();?>asset/ico/favicon.ico">
	<title>DDMG，大大买钢</title>
	<link href="<?php echo Router::getBaseUrl();?>css/messenger.css"
		type="text/css" rel="stylesheet" />
	<link
		href="<?php echo Router::getBaseUrl();?>css/messenger-theme-flat.css"
		type="text/css" rel="stylesheet" />
	<link
		href="<?php echo Router::getBaseUrl();?>css/messenger-theme-future.css"
		type="text/css" rel="stylesheet" />
	<link href="http://libs.useso.com/js/bootstrap/3.1.1/css/bootstrap.min.css"
		rel="stylesheet">
	<link href="<?php echo Router::getBaseUrl();?>css/bootstrap-theme.min.css"
		type="text/css" rel="stylesheet" />
	<link href="<?php echo Router::getBaseUrl();?>css/bootstrap-switch-3.1.0.min.css"
		type="text/css" rel="stylesheet" />
	<link href="<?php echo Router::getBaseUrl();?>css/grumble.min.css"
		type="text/css" rel="stylesheet" />
	<link href="<?php echo Router::getBaseUrl();?>css/jquery.Jcrop.min.css"
		type="text/css" rel="stylesheet" />
	<link href="<?php echo Router::getBaseUrl();?>css/prettify.css"
		type="text/css" rel="stylesheet" />
	<!-- Custom styles for this template -->
	<link href="<?php echo Router::getBaseUrl();?>css/custom.css"
		rel="stylesheet">
	<!-- Just for debugging purposes. Don't actually copy this line! -->
	<!--[if lt IE 9]><script src="<?php echo Router::getBaseUrl();?>js/ie8-responsive-file-warning.js"></script><![endif]-->
	<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
	<!--[if lt IE 9]>
	  <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
	  <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
	<![endif]-->

	<!-- Bootstrap core JavaScript
	================================================== -->
	<!-- Placed at the end of the document so the pages load faster -->
	<!--<script src="http://libs.useso.com/js/jquery/2.1.1/jquery.min.js"></script>-->
	<script src="<?php echo Router::getBaseUrl();?>js/jquery-1.11.3.min.js"></script> <!--改用本地的-->
<!--
	<script src="<?php echo Router::getBaseUrl();?>js/jquery-2.1.4.min.js"></script> 
改用本地的-->

	<script src="<?php echo Router::getBaseUrl();?>js/messenger.min.js"></script>
	<script src="<?php echo Router::getBaseUrl();?>js/messenger-theme-future.js"></script>

	<script src="http://libs.useso.com/js/bootstrap/3.1.1/js/bootstrap.min.js"></script>
	<!-- <script src="http://libs.useso.com/js/bootstrap-switch/3.0.1/js/bootstrap-switch.min.js"></script> -->
	<script src="<?php echo Router::getBaseUrl();?>js/bootstrap-switch-3.1.0.min.js"></script>
	<script type="text/javascript"
		src="<?php echo Router::getBaseUrl();?>js/prettify.js"></script>
	<script src="<?php echo Router::getBaseUrl();?>js/prng4.js"></script>
	<script src="<?php echo Router::getBaseUrl();?>js/rng.js"></script>
	<script src="<?php echo Router::getBaseUrl();?>js/base64.js"></script>
	<script src="<?php echo Router::getBaseUrl();?>js/jsbn.js"></script>
	<script src="<?php echo Router::getBaseUrl();?>js/rsa.js"></script>
	<script src="<?php echo Router::getBaseUrl();?>js/ajaxfileupload.js"></script>
	<script src="<?php echo Router::getBaseUrl();?>js/jquery.scrollUp.js"></script>
	<script src="<?php echo Router::getBaseUrl();?>js/holder.js"></script>
	<script src="<?php echo Router::getBaseUrl();?>js/jquery.grumble.min.js"></script>
	<script src="<?php echo Router::getBaseUrl();?>js/jquery.Jcrop.min.js"></script>
	<script type="text/javascript">
		var BASE_PATH="<?php echo Router::getBaseUrl();?>";
	</script>
	<script src="<?php echo Router::getBaseUrl();?>js/custom.js"></script>
	<script src="<?php echo Router::getBaseUrl();?>js/login.js"></script>
</head>
<body>

	<input type="hidden" id="other-csrf" value="<?php echo $other_token;?>">
<div class="modal fade" id="confirm-admin-modal">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"
					aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h5 class="modal-title">提示</h5>
			</div>
			<div class="modal-body">
			     <h4 id="confirm-admin-body" class="text-center">确认操作！</h4>
			</div>
			<div class="alert alert-danger" id="confirm-admin-hint"></div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
				<button type="button" class="btn btn-primary" id="confirm-admin-btn">确定</button>
			</div>
		</div>
	</div>
</div>
<?php if ( $page_type=='login' ) { ?>

		<link href="<?php echo Router::getBaseUrl();?>css/signin.css" type="text/css" rel="stylesheet" />
		<?=$login_html ?>

<?php } else { ?>

<nav class="navbar navbar-inverse navbar-fixed-top">
	  <div class="container-fluid">
		<div class="navbar-header">
		  <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
			<span class="sr-only">Toggle navigation</span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
		  </button>
		  <a class="navbar-brand" href="#">大大买钢-卖家管理中心</a>
		</div>
		<div id="navbar" class="navbar-collapse collapse">
		  <ul class="nav navbar-nav navbar-right">
			<li><a href="javascript:;" id="">欢迎: <?= $loginUser['name'] ?>( <?= $loginUser['tel'] ?> )</a></li>
			<li><a href="#" id="seller-chg-pwd-btn">修改密码</a></li>
			<li><a href="#" id="amdin-loginOut-btn">退出</a></li>
		  </ul>
		  <!-- <form class="navbar-form navbar-right">
			<input type="text" class="form-control" placeholder="Search...">
		  </form>
		   -->
		</div>
	  </div>
	</nav>

	<div class="container-fluid">
	  <div class="row">
		<div class="col-sm-3 col-md-2 sidebar">
		  <ul class="nav nav-sidebar">
			<li <?php if(doit::$controller == 'Order'){?> class="active" <?php } ?>>
				<a href="<?php echo Router::getBaseUrl();?>order">订单管理</a></li>
			<li <?php if(doit::$controller == 'Item'){?> class="active" <?php } ?>>
				<a href="<?php echo Router::getBaseUrl();?>item">商品管理</a></li>
			 <!--<li <?php if(doit::$controller == 'Tender' || doit::$controller == 'Casttender'){?> class="active" <?php } ?>>
				<a href="<?php echo Router::getBaseUrl();?>tender">投标管理</a></li>-->
			<li><a href="#"></a></li>
		  </ul>

<!--
		  <ul class="nav nav-sidebar">
			<li><a href="">Nav item</a></li>
			<li><a href="">Nav item again</a></li>
			<li><a href="">One more nav</a></li>
			<li><a href="">Another nav item</a></li>
			<li><a href="">More navigation</a></li>
		  </ul>

		  <ul class="nav nav-sidebar">
			<li><a href="">Nav item again</a></li>
			<li><a href="">One more nav</a></li>
			<li><a href="">Another nav item</a></li>
		  </ul>
-->

		</div>

		<div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
		  <?php if($page_type == 'orderList'){?>
				<?php echo $orderList_html;?>
		  <?php } else if($page_type == 'orderDetail'){ ?>
				<?php echo $orderDetail_html;?>
		  <?php } else if($page_type == 'orderAudit'){ ?>
				<?php echo $orderAudit_html;?>
		  <?php } else if($page_type == 'orderAdd'){ ?>
				<?php echo $orderAdd_html;?>
		  <?php } else if($page_type == 'orderEdit'){ ?>
				<?php echo $orderEdit_html;?>
		  <?php } else if($page_type == 'unlinePay'){ ?>
				<?php echo $unlinePay_html;?>


		  <?php } else if( $page_type == 'erpItemList' ){?>
				<?php echo $erpItemList_html; ?>


		  <?php } else if( $page_type == 'item' ){?>
				<?php echo $item_html; ?>
		  <?php } else if( $page_type == 'itemList' ){?>
				<?php echo $itemList_html; ?>
		  <?php } else if( $page_type == 'itemAdd' ){?>
				<?php echo $itemAdd_html ?>
		  <?php } else if( $page_type == 'importRes' ){?>
				<?php echo $importResult_html ?>
		  <?php } else if( $page_type == 'itemEdit' ){?>
				<?php echo $itemEdit_html ?>
		  <?php } else if( $page_type == 'tenderList' ){?>
				<?php echo $tenderList_html; ?>
		  <?php }?>
		</div>


	  </div>
	</div>

	<div id="footer">
		<div class="container-fluid">
			<div class="row">
				<div class="col-sm-12 col-md-12" id="realfooter">
					<!--
					<p class="text-center">page footer</p>
					-->
				</div>
			</div>
		</div>
	</div>

<?php }?>

<div class="modal fade" id="upd-seller-pwd-modal">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"
					aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title" id="info_title" >修改密码</h4>
			</div>
			<div class="modal-body">
				<form class="form-horizontal">
				 <div class="form-group">
				    <label for="seller-old-pwd" class="col-sm-2 control-label">旧密码</label>
				    <div class="col-sm-8">
				      <input type="password" class="form-control" id="seller-old-pwd" placeholder="旧密码">
				    </div>
				  </div>
				  <div class="form-group">
				    <label for="seller-new-pwd" class="col-sm-2 control-label">新密码</label>
				    <div class="col-sm-8">
				      <input type="password" class="form-control" id="seller-new-pwd" placeholder="新密码">
				    </div>
				  </div>
				  <div class="form-group">
				    <label for="seller-new-pwd2" class="col-sm-2 control-label">确认密码</label>
				    <div class="col-sm-8">
				      <input type="password" class="form-control" id="seller-new-pwd2" placeholder="确认密码">
				    </div>
				  </div>
				  <div class="alert alert-danger" id="upd-seller-hint"></div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
				<button type="button" class="btn btn-primary" id="btn-seller-upd-pwd">确定</button>
			</div>
		</div>
		<!-- /.modal-content -->
	</div>
	<!-- /.modal-dialog -->
</div>
<!-- /.modal -->

</body>
</html>

