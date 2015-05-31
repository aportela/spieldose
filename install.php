<?php
	ob_start();
	define("BASE_PATH", sprintf("%s%s", __DIR__, DIRECTORY_SEPARATOR));
	define("CONFIG_PATH", sprintf("%s%s%s%s", BASE_PATH, "data" , DIRECTORY_SEPARATOR , "configuration.php"));
	define("HTACCESS_DATA_PATH", sprintf("%s%s%s%s", BASE_PATH, "data" , DIRECTORY_SEPARATOR , ".htaccess"));
	define("DB_PATH", sprintf("%s%s%s%s", BASE_PATH, "data" , DIRECTORY_SEPARATOR, "spieldose.sqlite3"));	

	/*
		create sqlite database & tables
	*/
	function create_database($email, $password)
	{
		$error = null;
		try {
			$file_db = new PDO(sprintf("sqlite:%s", DB_PATH));
			$file_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$file_db->exec('
				CREATE TABLE IF NOT EXISTS `USER` (
					`id`	TEXT NOT NULL UNIQUE,
					`email`	TEXT NOT NULL UNIQUE,
					`password_hash`	TEXT NOT NULL,
					PRIMARY KEY(id)
				);			
			');
			$sql = ' INSERT INTO `USER` (`id`, `email`, `password_hash`) VALUES (:id, :email, :password_hash) ';
			$stmt = $file_db->prepare($sql); 
			$params = array(
				":id" => sha1($email),
				":email" => $email,
				":password_hash" => password_hash($password, PASSWORD_DEFAULT)
			);
			$stmt->execute($params);							
			$file_db->exec('
				CREATE TABLE IF NOT EXISTS `SONG` (
					`id`	TEXT NOT NULL UNIQUE,
					`mb_id`	TEXT,
					`path`	TEXT NOT NULL UNIQUE,
					`title`	TEXT,
					`artist_id`	TEXT,
					`album_id`	TEXT,
					`year`	INTEGER,
					`playtime_seconds`	INTEGER,
					`playtime_string` TEXT,
					`filesize`	INTEGER NOT NULL DEFAULT 0,				
					PRIMARY KEY(id)
				)
			');
			$file_db->exec('	
				CREATE TABLE IF NOT EXISTS `ALBUM` (
					`id`	TEXT NOT NULL UNIQUE,
					`mb_id`	TEXT,
					`year`	INTEGER,
					`name`	TEXT NOT NULL,
					`artist_id`	TEXT NOT NULL,
					`cover`	TEXT,
					PRIMARY KEY(id)
				);
			');		
			
			$file_db->exec('
				CREATE TABLE `ARTIST` (
					`id`	TEXT NOT NULL UNIQUE,
					`mb_id`	TEXT,
					`name`	TEXT NOT NULL UNIQUE,
					`genres`	TEXT,
					PRIMARY KEY(id)
				);		
			');
			$file_db = null;
		}
		catch(PDOException $e) {
			$error = $e->getMessage(); 
		}
		return($error);		
	}
	
	/*
		create data path (store sqlite database & configuration file)
	*/
	function create_required_data($music_path) {
		$error = null;
		$data_path = dirname(CONFIG_PATH);
		if (! file_exists($data_path)) {
			if (! mkdir ($data_path)) {
				$error = sprintf("error creating directory %s", $data_path);
			}			
		}		
		$configuration = sprintf('
<?php
	define("DB_PATH", "%s");
	define("MUSIC_PATH", "%s");
?>
'
		, DB_PATH, $music_path);
		if (! file_put_contents(CONFIG_PATH , $configuration)) {										
			$error = sprintf("error creating configuration file (%s)", CONFIG_PATH); 
		}
		$htaccess = sprintf('
Order Allow, Deny
Deny from All
'
		);
		if (! file_put_contents(HTACCESS_DATA_PATH , $htaccess)) {										
			$error = sprintf("error creating htaccess file (%s)", HTACCESS_DATA_PATH); 
		}
		
		return($error);
	}

	if (
		isset($_POST["music_path"]) && isset($_POST["email"]) && filter_var($_POST["email"], FILTER_VALIDATE_EMAIL) && isset($_POST["password"]))
	{
				
		header("Content-Type: application/json; charset=utf-8");
		$error = false;
		$json_response = array();
		
		if (is_dir($_POST["music_path"])) {		
			$error_message = create_required_data($_POST["music_path"]);
			if ($error_message == null) {
				$error_message = create_database($_POST["email"], $_POST["password"]);
			}
		} else {
			$error_message = sprintf("music path (%s) not found", $_POST["music_path"]);
		}		
		if ($error_message == null) {
			$json_response["installed"] = true;
			$json_response["installerPath"] = __FILE__;
		} else {
			$json_response["installed"] = false;
			$json_response["errorMessage"] = $error_message;
		}
		echo json_encode($json_response);
		ob_flush();
		exit;
	}
	$errors = false;
?>
<!doctype html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>spieldose - installation</title>
		<meta name="description" content="music for the masses">
		<meta name="author" content="alex">
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
		<!--[if lt IE 9]>
		<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
		<![endif]-->
		<style>
			div#main
			{
				width: 60%;
				margin: 64px auto;
			}
		</style>
	</head>
	<body>
		<div id="main">
			<div class="panel panel-default">
  				<div class="panel-heading">spieldose:: checking requisites...</div>
  				<div class="panel-body">					
		<?php
			if (!extension_loaded('sqlite3')) {
				$errors = true;
		?>				
					<div role="alert" class="alert alert-danger alert-dismissible fade in">
						<h4 id="oh-snap!-you-got-an-error!">PHP module not found<a href="#oh-snap!-you-got-an-error!" class="anchorjs-link"><span class="anchorjs-icon"></span></a></h4>
						<p>sqlite3 extension required, please install & configure it before continue.</p>
					</div>		
		<?php			
			} else {
				?>
					<div role="alert" class="alert alert-success alert-dismissible fade in">
						<strong>checking sqlite3 module: </strong> found!.
					</div>				
				<?php
			}
		?>
		<?php			
			if ( ! is_writable(BASE_PATH)) {	
				$errors = true;			
		?>				
					<div role="alert" class="alert alert-danger alert-dismissible fade in">
						<h4 id="oh-snap!-you-got-an-error!">spieldose path is not writable<a href="#oh-snap!-you-got-an-error!" class="anchorjs-link"><span class="anchorjs-icon"></span></a></h4>
						<p>please check www server write permissions for directory (<?= BASE_PATH ?>) before continue.</p>
					</div>		
		<?php			
			} else {
				?>
					<div role="alert" class="alert alert-success alert-dismissible fade in">
						<strong>checking base path (<?= BASE_PATH ?>) write permissions: </strong> correct!.
					</div>				
				<?php
			}
			if ( file_exists(DB_PATH)) {	
				$errors = true;			
		?>				
					<div role="alert" class="alert alert-danger alert-dismissible fade in">
						<h4 id="oh-snap!-you-got-an-error!">spieldose database exists<a href="#oh-snap!-you-got-an-error!" class="anchorjs-link"><span class="anchorjs-icon"></span></a></h4>
						<p>old database file (<?= DB_PATH ?>) found, delete before continue</p>
					</div>		
		<?php			
			}
			if (! $errors) {
				?>
				<form id="f_install" method="post" action="install.php" class="form-horizontal">
					<div class="form-group">
						<label class="col-sm-2 control-label" for="music_path">Music path: </label>
						<div class="col-sm-10">
							<input type="text" class="form-control" required id="music_path" name="music_path" placeholder="type your music path" />
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label" for="email">Email: </label>
						<div class="col-sm-10">
							<input type="email" class="form-control" required id="email" name="email" placeholder="type your email (account username)" />
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label" for="password">Password: </label>
						<div class="col-sm-10">
							<input type="password" class="form-control" required id="password" name="password" placeholder="type your password (account password)" />
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-offset-2 col-sm-10">
						<button type="submit" class="btn btn-primary">INSTALL</button>
						</div>
					</div>					
				</form>
				<?php
			}
		?>
				</div>
			</div>
		</div>
		<script src="assets/js/jquery-2.1.4.min.js"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
		<script>
			$("form#f_install").submit(function(e) {
				e.preventDefault();
				$.ajax({
					url: $(this).attr("action"),
					method: "post", 
					data: $(this).serialize()
				})
				.done(function(data, textStatus, jqXHR) {
					var html = '';
					if (data.installed == true) {
						html = '\
							<div role="alert" class="alert alert-success fade in">\
								<strong>installation successful:</strong> you can safely delete this file (' + data.installerPath + ')\
							</div>\
						';
					} else {
						html = '\
							<div role="alert" class="alert alert-danger fade in">\
								<strong>install error:</strong> ' + data.errorMessage + '\
							</div>\
						';
					}
					$("div.panel-body").append(html);						
				})
				.fail(function(jqXHR, textStatus, errorThrown) {
					var html = '\
						<div role="alert" class="alert alert-danger fade in">\
							<strong>install error (ajax):</strong> ' + errorThrown + '\
						</div>\
					';
					$("div.panel-body").append(html);						
				});
			});
		</script>
	</body>	
</html>
<?php
	ob_flush();
?>
