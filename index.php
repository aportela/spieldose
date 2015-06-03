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
		
		<!-- left sidebar (navigation menu) -->
		<!-- credit: ishwarkatwe (http://bootsnipp.com/snippets/featured/responsive-navigation-menu) -->
		
        <div class="nav-side-menu">
            <div class="brand">spieldose</div>
            <i class="fa fa-bars fa-2x toggle-btn" data-toggle="collapse" data-target="#menu-content"></i>      
                <div class="menu-list">      
                    <ul id="menu-content" class="menu-content collapse out">
                        <li class="active"><a href="#"><i class="fa fa-home fa-lg"></i> Dashboard</a></li>
                        <li data-toggle="collapse" data-target="#browse_library" class="collapsed">
                          <a href="#"><i class="fa fa-database fa-lg"></i> Browse library<span class="arrow"></span></a>
                        </li>
                        <ul class="sub-menu collapse" id="browse_library">
                            <li><a id="browse_by_artist" href="api/artist/search.php">by artist</a></li>
                            <li><a href="#">by genre</a></li>
                            <li><a href="#">by year</a></li>
                            <li><a id="browse_by_album" href="api/album/search.php"href="#">by album</a></li>
                            <li><a href="#">by path</a></li>
                            <li><a href="#">by random</a></li>
                        </ul>
                        <li data-toggle="collapse" data-target="#lists" class="collapsed">
                          <a href="#"><i class="fa fa-server fa-lg"></i> Lists <span class="arrow"></span></a>
                        </li>  
                        <ul class="sub-menu collapse" id="lists">
                          <li>most played</li>
                          <li>less played</li>
                          <li>recent played</li>
                          <li>loved songs</li>
                          <li>new songs </li>
                          <li>saved playlists</li>
                        </ul>
                        <li data-toggle="collapse" data-target="#streams" class="collapsed">
                          <a href="#"><i class="fa fa-globe fa-lg"></i> Streams <span class="arrow"></span></a>
                        </li>  
                        <ul class="sub-menu collapse" id="streams">
                            <li>80's Alt & New Wave</li>
                            <li>Blues Rock</li>
                            <li>Smooth Jazz</li>
                            <li>Top Hits</li>
                            <li>Classic Rock</li>
                            <li>90's Hits</li>
                            <li>Mostly Classical</li>
                            <li>Chillout</li>
                            <li>Dance Hits</li>
                            <li>Oldies</li>                            
                        </ul>
                        <li>
                          <a href="#"><i class="fa fa-sellsy fa-lg"></i> Podcasts</a>
                        </li>  
                        <li data-toggle="collapse" data-target="#profile" class="collapsed">
                          <a href="#"><i class="fa fa-user fa-lg"></i> Profile <span class="arrow"></span></a>
                        </li>  
                        <ul class="sub-menu collapse" id="profile">
                          <li>update</li>
                          <li><a id="signout" href="api/user/signout.php">signout</a></li>
                        </ul>
                    </ul>                
             </div>
        </div>		
        
        <div id="container">

            <form id="f_search" class="form-horizontal">
                <div class="form-group">
                    <div class="col-sm-12">
                        <input id="q" name="q" class="typeahead form-control" type="text" placeholder="search...">
                    </div>
                </div>
            </form>
                     
         <table class="table table-condensed table-striped">
             <thead>
                 <tr>
                     <td>Track</td>
                     <td>Artist</td>
                     <td>Album</td>
                 </tr>
             </thead>
             <tbody>                 
             </tbody>
         </table>
          <!--
          <div class="album_container">
            <img src="http://userserve-ak.last.fm/serve/174s/74670442.png" alt="album cover">                    
            <div class="album_info">                        
              <p class="album_name">The Number of the Beast</p>                        
              <p class="album_artist">Iron Maiden</p>                    
            </div>                
          </div>
          <div class="album_container">
            <img src="http://userserve-ak.last.fm/serve/500/98944295/198XAD+cover.jpg" alt="album cover">                    
            <div class="album_info">                        
              <p class="album_name">198XAD</p>                        
              <p class="album_artist">Mega Drive</p>                    
            </div>                
          </div>
          <div class="album_container">
            <img src="http://userserve-ak.last.fm/serve/500/82594365/MTV+Unplugged+in+New+York+PNG.png" alt="album cover">                    
            <div class="album_info">                        
              <p class="album_name">MTV Unplugged in New York </p>                        
              <p class="album_artist">Nirvana</p>                    
            </div>                
          </div>
          <div class="album_container">
            <img src="http://userserve-ak.last.fm/serve/500/39581157/London+Calling+the+clash.png" alt="album cover">                    
            <div class="album_info">                        
              <p class="album_name">London Calling</p>                        
              <p class="album_artist">The Clash</p>                    
            </div>                
          </div>
          <div class="album_container">
            <img src="http://userserve-ak.last.fm/serve/500/65269442/My+Generation++The+Very+Best+of+The+Who+The+Very+Best+Of+The+Who.png" alt="album cover">                    
            <div class="album_info">                        
              <p class="album_name">My Generation - The Very Best of The Who</p>                        
              <p class="album_artist">The Who</p>                    
            </div>                
          </div>
          <div class="album_container">
            <img src="http://userserve-ak.last.fm/serve/_/88047615/The+Joshua+Tree.png" alt="album cover">                    
            <div class="album_info">                        
              <p class="album_name">The Joshua Tree</p>                        
              <p class="album_artist">U2</p>                    
            </div>                
          </div>
          <div class="album_container">
            <img src="http://userserve-ak.last.fm/serve/500/97977057/Thriller.png" alt="album cover">                    
            <div class="album_info">                        
              <p class="album_name">Thriller</p>                        
              <p class="album_artist">Michael Jackson</p>                    
            </div>                
          </div>
          <div class="album_container">
            <img src="http://userserve-ak.last.fm/serve/_/51552793/Appetite+for+Destruction.png" alt="album cover">                    
            <div class="album_info">                        
              <p class="album_name">Appetite for Destruction</p>                        
              <p class="album_artist">Guns N' Roses</p>                    
            </div>                
          </div>
          <div class="album_container">
            <img src="http://userserve-ak.last.fm/serve/500/57909181/Out+of+Time.png" alt="album cover">                    
            <div class="album_info">                        
              <p class="album_name">Out of Time</p>                        
              <p class="album_artist">R.E.M.</p>                    
            </div>                
          </div>
          <div class="album_container">
            <img src="http://userserve-ak.last.fm/serve/500/94157253/Oxygne.png" alt="album cover">                    
            <div class="album_info">                        
              <p class="album_name">Oxygène</p>                        
              <p class="album_artist">Jean Michel Jarre</p>                    
            </div>                
          </div>
          <div class="album_container">
            <img src="http://userserve-ak.last.fm/serve/_/52675611/Hay+Alguien+Ah.jpg" alt="album cover">                    
            <div class="album_info">                        
              <p class="album_name">¿Hay Alguien Ahí?</p>                        
              <p class="album_artist">Los Suaves</p>                    
            </div>                
          </div>
          <div class="album_container">
            <img src="http://userserve-ak.last.fm/serve/500/92459761/Random+Access+Memories.png" alt="album cover">                    
            <div class="album_info">                        
              <p class="album_name">Random Access Memories</p>                        
              <p class="album_artist">Daft Punk</p>                    
            </div>                
          </div>
          <div class="album_container">
            <img src="http://userserve-ak.last.fm/serve/500/102110675/Alchemy++Dire+Straits+Live++1++2+Alchemy++1984++Vinyl.png" alt="album cover">                    
            <div class="album_info">                        
              <p class="album_name">Alchemy - Dire Straits Live</p>                        
              <p class="album_artist">Dire Straits</p>                    
            </div>                
          </div>
          <div class="album_container">
            <img src="http://userserve-ak.last.fm/serve/500/92633831/Greatest+Hits+II.png" alt="album cover">                    
            <div class="album_info">                        
              <p class="album_name">Greatest Hits II</p>                        
              <p class="album_artist">Queen</p>                    
            </div>                
          </div>
          <div class="album_container">
            <img src="http://userserve-ak.last.fm/serve/500/75977790/Mutter.png" alt="album cover">                    
            <div class="album_info">                        
              <p class="album_name">Mutter</p>                        
              <p class="album_artist">Rammstein</p>                    
            </div>                
          </div>
          <div class="album_container">
            <img src="http://userserve-ak.last.fm/serve/_/78014436/A+Kiss+Before+You+Go+Live+in+Hamburg+katzenjammer.jpg" alt="album cover">                    
            <div class="album_info">                        
              <p class="album_name">A Kiss Before You Go: Live in Hamburg </p>                        
              <p class="album_artist">Katzenjammer</p>                    
            </div>                
          </div>
          <div class="album_container">
            <img src="http://userserve-ak.last.fm/serve/_/102541753/Jagged+Little+Pill.png" alt="album cover">                    
            <div class="album_info">                        
              <p class="album_name">Jagged Little Pill</p>                        
              <p class="album_artist">Alanis Morissette</p>                    
            </div>                
          </div>
          <div class="album_container">
            <img src="http://userserve-ak.last.fm/serve/500/56688071/Rage+Against+the+Machine+Rage.png" alt="album cover">                    
            <div class="album_info">                        
              <p class="album_name">Rage Against the Machine</p>                        
              <p class="album_artist">Rage Against the Machine</p>                    
            </div>                
          </div>
          <div class="album_container">
            <img src="http://userserve-ak.last.fm/serve/_/36082847/Entre+Ciment+Et+Belle+toile+keny_arkana__entre_ciment_et_b.jpg" alt="album cover">                    
            <div class="album_info">                        
              <p class="album_name">Entre Ciment Et Belle Étoile</p>                        
              <p class="album_artist">Keny Arkana</p>                    
            </div>                
          </div>
          <div class="album_container">
            <img src="http://userserve-ak.last.fm/serve/500/85296157/1984++Cover.png" alt="album cover">                    
            <div class="album_info">                        
              <p class="album_name">1984</p>                        
              <p class="album_artist">Van Halen</p>                    
            </div>                
          </div>
          <div class="album_container">
            <img src="http://userserve-ak.last.fm/serve/500/97736065/Destroyer+KISS.png" alt="album cover">                    
            <div class="album_info">                        
              <p class="album_name">Destroyer</p>                        
              <p class="album_artist">Kiss</p>                    
            </div>                
          </div>
          <div class="album_container">
            <img src="http://userserve-ak.last.fm/serve/500/91072511/TERROR+404+T404.png" alt="album cover">                    
            <div class="album_info">                        
              <p class="album_name">TERROR 404</p>                        
              <p class="album_artist">Perturbator</p>                    
            </div>                
          </div>
          <div class="album_container">
            <img src="http://userserve-ak.last.fm/serve/_/40852589/The+Galician+Connection+PORTADA.jpg" alt="album cover">                    
            <div class="album_info">                        
              <p class="album_name">The Galician Connection</p>                        
              <p class="album_artist">Cristina Pato</p>                    
            </div>                
          </div>
          <div class="clearfix"></div>
              
          -->
        </div>
        
        <div id="sidebar_player">
          <div id="now_playing_cover">
            <h2>NOW PLAYING</h2>
            <div id="now_playing_song_info">
              <h3><span class="title">Soundtrack to The Rebellion</span><span class="duration">0:00 / 6:00</span></h3>
              <h4 class="artist">Machinae Supremacy</h4>                    
            </div>          
          </div>
          <ul id="now_playing_list">
            <li><span class="idx">1</span>Player One<span class="duration">5:39</span></li>
            <li><span class="idx">2</span>Super Steve<span class="duration">5:37</span></li>
            <li class="selected"><span class="idx">3</span>Soundtrack to the Rebellion<span class="duration">6:00</span></li>
          </ul>
          <audio id="audio" controls preload="none">
            <!--
            <source src="horse.ogg" type="audio/ogg">
            -->
            <source src="http://static.machinaesupremacy.com/musicfiles/fury/02-machinae_supremacy-soundtrack_to_the_rebellion.mp3" type="audio/mpeg">
            Your browser does not support the audio element.
          </audio>
          <div id="playlist_controls">
              <i title="repeat all" class="fa fa-refresh fa-lg"></i>
              <i title="shuffle" class="fa fa-random fa-lg"></i>
              <i id="crtl_play_previous" title="previous" class="fa fa-backward fa-lg"></i>
              <i id="crtl_play_next" title="next" class="fa fa-forward fa-lg"></i>
              <i title="save playlist" class="fa fa-save fa-lg"></i>
              <i title="mark as loved song" class="fa fa-heart fa-lg"></i>
              <i title="download song" class="fa fa-download fa-lg"></i>              
          </div>                  
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
    <script src="assets/js/typeahead.bundle.min.js"></script>      
		<script src="assets/js/spieldose.js"></script>
	</body>
</html>