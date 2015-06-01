<?php
	ob_start();
	session_start();
?>
<!doctype html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>spieldose</title>
		<meta name="description" content="music for the masses">
		<meta name="author" content="alex">
		<link rel="stylesheet" href="assets/bootstrap-3.3.4-dist/css/bootstrap.min.css">
		<style>
			h2
			{
				text-align: center;
			}
			div#container
			{
				margin: 64px auto;
				width: 30%;
			}
			input, div.alert
			{
				margin: 1em 0em;
			}
		</style>
		<!--[if lt IE 9]>
		<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
		<![endif]-->
	</head>
	<body>
		<div id="container">		
		<?php
			if (isset($_SESSION["user_id"])) {
		?>
			<form id="f_signout" method="post" action="api/user/signout.php">
				<h2 title="<?= $_SESSION["user_id"] ?>">spieldose:: user logged</h2>
				<button class="btn btn-lg btn-primary btn-block" type="submit">Sign out</button>
			</form>		
		<?php
			} else {
		?>
			<form id="f_signin" method="post" action="api/user/signin.php">
				<h2>spieldose</h2>
				<label for="email" class="sr-only">Email address</label>
				<input type="email" id="email" name="email" class="form-control" placeholder="email address" required autofocus>
				<label for="password" class="sr-only">Password</label>
				<input type="password" id="password" name="password" class="form-control" placeholder="password" required>
				<button class="btn btn-lg btn-primary btn-block" type="submit">Sign in</button>
			</form>		
		<?php				
			}
		?>
		</div>		
		<script src="assets/js/jquery-2.1.4.min.js"></script>
		<script src="assets/bootstrap-3.3.4-dist/js/bootstrap.min.js"></script>
		<script>
			
			function showAlert(msg) {
				var html = '\
					<div class="alert alert-danger alert-dismissible" role="alert">\
						<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>\
						<strong>error:</strong> ' + msg + '\
					</div>\
				';
				$("div#container").append(html);										
			}
			
			$("form#f_signin").submit(function(e) {
				e.preventDefault();
				$.ajax({
					url: $(this).attr("action"),
					method: "post", 
					data: $(this).serialize(),
					dataType : "json"
				})
				.done(function(data, textStatus, jqXHR) {
					if (data.success == true) {
						location.reload(); 
					} else {
						showAlert(data.errorMsg);
					}
				})
				.fail(function(jqXHR, textStatus, errorThrown) {
					showAlert("ajax error");
				});				
			});
			$("form#f_signout").submit(function(e) {
				e.preventDefault();
				$.ajax({
					url: $(this).attr("action"),
					method: "post", 
					data: $(this).serialize(),
					dataType : "json"
				})
				.done(function(data, textStatus, jqXHR) {
					if (data.success == true) {
						location.reload(); 
					} else {
						showAlert(data.errorMsg);
					}
				})
				.fail(function(jqXHR, textStatus, errorThrown) {
					showAlert("ajax error");
				});				
			});
		</script>
	</body>
</html>