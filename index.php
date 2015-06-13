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
		<link href="assets/font-awesome-4.3.0/css/font-awesome.min.css" rel="stylesheet">
		<link rel="stylesheet" href="assets/css/spieldose.css">
		<!--[if lt IE 9]>
		<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
		<![endif]-->
	</head>
	<body>
		<?php
			if (isset($_SESSION["user_id"])) {
		?>
		<div id="sidebar">
			<ul id="menu">
				<li><a href="#" class="disabled_link"><div><i class="fa fa-2x fa-home"></i>dashboard</div></a></li>
				<li><a href="#" class="disabled_link"><div><i class="fa fa-2x fa-user"></i>artists</div></a></li>
				<li><a href="#" class="disabled_link"><div><i class="fa fa-2x fa-file-audio-o"></i>albums</div></a></li>
				<li><a href="#" class="disabled_link"><div><i class="fa fa-2x fa-tags"></i>genres</div></a></li>
				<li><a href="#" class="disabled_link"><div><i class="fa fa-2x fa-cog"></i>preferences</div></a></li>
			</ul>
		</div>
		<div id="content">
			<div id="top">
				<div class="row">
					<div class="col-lg-5">
						<form method="post" action="#" id="f_search">
							<div class="input-group">
								<input type="text" class="form-control input-sm" name="q" id="q" placeholder="search...">
								<span class="input-group-btn">
									<button class="btn btn-default btn-sm" type="button">
										<span class="glyphicon glyphicon-search"></span>
									</button>
								</span>
							</div>
						</form>
					</div>
				</div>
			</div>
			<div id="dashboard" class="section">
				<h1>Discover <i class="fa fa-bar-chart"></i></h1>
				<h2>Tracks</h2>
				<div id="dashboard_tracks">
					<div class="row">
						<div class="col-md-4">
							<h3>Recently added to library</h3>
							<ul id="dashboard_recently_added_tracks">
							</ul>							
						</div>
						<div class="col-md-4">
							<h3>Most played</h3>
							<ol id="dashboard_most_played_tracks">
							</ol>
						</div>
						<div class="col-md-4">
							<h3>Random</h3>
							<ul id="dashboard_random_tracks">
							</ul>							
						</div>
					</div>
				</div>
				<h2>Albums</h2>
				<div id="dashboard_albums"></div>
				<h2>Artists</h2>
				<div id="dashboard_artists"></div>
			</div>
			<div id="artist_view" class="section hidden">
				<h1 id="artist_name"></h1>
				<img id="artist_image" src="#">
				<div id="artist_info">
					<div id="artist_bio"></div>
					<p id="artist_tags"></p>
				</div>
				<div class="clearfix"></div>
				
				<h3>Top tracks</h3>
					<ol id="artist_top_tracks">
					</ol>
				<h3>Top albums</h3>			
				<div id="artist_albums">					
				</div>		
				<div class="clearfix"></div>											
			</div>
		</div>
		<div id="player" class="hidden">
			<p id="now_playing_header">NOW PLAYING</p>			
			<img id="now_playing_image" src="http://userserve-ak.last.fm/serve/500/92690831/Deus+Ex+Machinae.jpg" />
			<div id="now_playing_info">
				<span id="now_playing_song_title" title="Soundtrack to the Rebellion">Soundtrack to the Rebellion</span>
				<span id="now_playing_song_time">6:00</span>
				<span id="now_playing_artist" title="Machinae Supremacy">Machinae Supremacy</span>
			</div>
            <audio id="audio" controls preload="none">
                <source src="http://static.machinaesupremacy.com/musicfiles/fury/02-machinae_supremacy-soundtrack_to_the_rebellion.mp3" type="audio/mpeg">
            	Your browser does not support the audio element.
            </audio>            
            <div id="player_controls">
                <i title="repeat all" class="fa fa-refresh fa-lg"></i>
                <i title="shuffle" class="fa fa-random fa-lg"></i>
                <i id="crtl_play_previous" title="previous" class="fa fa-backward fa-lg"></i>
                <i id="crtl_play_next" title="next" class="fa fa-forward fa-lg"></i>
                <i title="mark as loved song" class="fa fa-heart fa-lg"></i>
                <i title="download song" class="fa fa-save fa-lg"></i>
            </div>
          	<ul id="now_playing_list">
            	<li class="selecte2d"><a class="playlist_track"><span class="title" title="Soundtrack to the Rebellion">Soundtrack to the Rebellion</span><span class="duration">6:00</span></a></li>
          	</ul>
			<div class="clearfix"></div> 						
		</div>
		
		<?php
			} else {
		?>
		<div id="signin_container">				
			<form id="f_signin" method="post" action="api/user/signin.php">
				<h2>spieldose</h2>
				<label for="email" class="sr-only">Email address</label>
				<input type="email" id="email" name="email" class="form-control" placeholder="email address" required autofocus>
				<label for="password" class="sr-only">Password</label>
				<input type="password" id="password" name="password" class="form-control" placeholder="password" required>
				<button class="btn btn-lg btn-primary btn-block" type="submit">Sign in</button>
			</form>		
		</div>		
		<?php				
			}
		?>
		<script src="assets/js/jquery-2.1.4.min.js"></script>
		<script src="assets/bootstrap-3.3.4-dist/js/bootstrap.min.js"></script>
		<script src="assets/js/spieldose.js"></script>
	</body>
</html>