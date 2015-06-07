// put alerts on signin / signout errors
function showSignInAlert(msg) {
	var html = '\
		<div class="alert alert-danger alert-dismissible" role="alert">\
			<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>\
			<strong>error:</strong> ' + msg + '\
		</div>\
	';
	$("div#signin_container").append(html);										
}

// signin form submit event
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
			showSignInAlert(data.errorMsg);
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		showSignInAlert("ajax error");
	});				
});

function browseArtists(artists) {
	var html = "";
	for (var i = 0; i < artists.length; i++) {
		var image = artists[i].image.length > 2 ?  artists[i].image[2].url : "#";
		html += '\
          <div class="artist_container">\
            <img src="' + image + '" alt="artist image">\
			<div class="artist_info">\
            	<p class="artist_name" title="' + artists[i].name + '">' + artists[i].name + '</p>\
			</div>\
          </div>\
		';
	}
	html += '\
		<div class="clearfix"></div>\
	';	
	$("div#artists").html(html);
}

var offset = 0;

// browse by artist link
$("a#browse_by_artist").click(function(e) {
	e.preventDefault();
	$.ajax({
		url: $(this).attr("href") + "?limit=64&offset=" + offset,
		method: "post", 
		data: $(this).serialize()
	})
	.done(function(data, textStatus, jqXHR) {
		if (data.success == true) {
			/*
			if (data.artists.length > 0) {
				browseArtists(data.artists);
				offset += data.artists.length;			
			}
			*/
		} else {
			// TODO
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		// TODO
	});					
});

// browse by album link
$("a#browse_by_album").click(function(e) {
	e.preventDefault();
	$.ajax({
		url: $(this).attr("href"),
		method: "post", 
		data: $(this).serialize()
	})
	.done(function(data, textStatus, jqXHR) {
		if (data.success == true) {
		} else {
			//showSignInAlert(data.errorMsg);
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		//showSignInAlert("ajax error");
	});					
});

// signout link
$("a#signout").click(function(e) {
	e.preventDefault();
	$.ajax({
		url: $(this).attr("href"),
		method: "post", 
		data: $(this).serialize(),
		dataType : "json"
	})
	.done(function(data, textStatus, jqXHR) {
		if (data.success == true) {
			location.reload(); 
		} else {
			// TODO
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		// TODO
	});				
});


/* TYPEAHEAD */

var artists = new Bloodhound({
	datumTokenizer: Bloodhound.tokenizers.whitespace,
	queryTokenizer: Bloodhound.tokenizers.whitespace,
	
	//prefetch: "./api/artist/search.php",
	remote: {
		cache: false,
		url: './api/artist/search.php?q=%QUERY&limit=6&offset=0',
		wildcard: '%QUERY',
		filter: function(response) {
			return(response.artists);
		}	
	}
});

var albums = new Bloodhound({
	datumTokenizer: Bloodhound.tokenizers.whitespace,
	queryTokenizer: Bloodhound.tokenizers.whitespace,
	prefetch: './api/album/search.php',
	remote: {
		cache: false,
		url: './api/album/search.php?q=%QUERY&limit=6&offset=0',
		wildcard: '%QUERY',
		filter: function(response) {
			return(response.albums);
		}	
	}
});

/*
var songs = new Bloodhound({
	datumTokenizer: Bloodhound.tokenizers.whitespace,
	queryTokenizer: Bloodhound.tokenizers.whitespace,
	prefetch: './api/song/search.php',
	remote: {
		cache: false,
		url: './api/song/search.php?q=%QUERY&limit=3',
		wildcard: '%QUERY',
		filter: function(response) {
			return(response.songs);
		}	
	}
});

*/
$('input#q').typeahead(
	{
		hint: false,
		highlight: false,
		minLength: 3,
	},
	/*
	{
  		name: 'songs',
		displayKey: function (song) {
	  		return song.title;
		},
		source: songs.ttAdapter(),
  		templates: {
			header: '<div id="song_results"><h3>SONGS</h3>',
			footer: '</div>',
	    	suggestion: function(song) {
				return('<div class="result"><i data-id="' + song.id + '" title="play this song" class="play_song fa fa-play-circle fa-2x"></i> <i data-id="' + song.id + '" title="enqueue this song" class="enqueue_song fa fa-plus fa-2x"></i><span class="result_song_title">' + song.title + '</span><br /><span class="result_song_artist">' + (song.artistName ? song.artistName : '') + '</span><div class="clearfix"></div></div>');
			}
	  	}		   
	},
	*/
	{
  		name: 'artists',
		displayKey: function (artist) {
	  		return artist.name;
		},
		source: artists.ttAdapter(),
  		templates: {
			header: '<div id="artist_results"><h3>ARTISTS</h3>',
			footer: '</div>',
	    	suggestion: function(artist) {
				return('<div class="result"><img src="http://userserve-ak.last.fm/serve/34s/33130469.jpg" /><span class="result_artist">' + artist.name + '</span><div class="clearfix"></div></div>');
			}
	  	}		   
	},
	{
  		name: 'albums',
		displayKey: function (album) {
	  		return album.name;
		},
		source: albums.ttAdapter(),
  		templates: {
			header: '<div id="album_results"><h3>ALBUMS</h3>',
			footer: '</div>',
	    	suggestion: function(album) {
				return('<div class="result"><img src="http://userserve-ak.last.fm/serve/34s/92459761.jpg" /><i data-id="' + album.id + '" title="play this album" class="play_album fa fa-play-circle fa-2x"></i><span class="result_album">' + album.name + '</span><br /><span class="result_artist_album">' + (album.artistName ? album.artistName : '') + '</span><div class="clearfix"></div></div>');
			}
	  	}		   
	}
);


/* TYPEAHEAD */

var playlistSongs = [];

// start play song
function play(id) {
	$("audio#audio source").attr("src", "api/track/get.php?id=" + id);
	var audio = $("audio#audio");
	audio[0].pause();
    audio[0].load();//suspends and restores all audio element
    audio[0].play();	
	for (var i = 0, found = false; i < playlistSongs.length && ! found; i++) {
		if (playlistSongs[i].id == id) {
			$("div#now_playing_song_info span.title").text(playlistSongs[i].title);
			$("div#now_playing_song_info span.duration").text("0:00 / " + playlistSongs[i].playtimeString);
			$("div#now_playing_song_info h4.artist").text(playlistSongs[i].artist.name);
		}
	}
}

// enqueue song into playlist
function enQueueSong(id, title, artist, playtime, cover) {
	var html = '<li data-id="' + id + '" data-image="' + cover + '"><span class="idx"></span>' + title + '<span class="duration">' + playtime + '</span></li>';
	$("ul#now_playing_list").append(html);	
}

// enqueue into playlist & play song 
function playSong(id, title, artist) {
	$("div#now_playing_song_info h3 span.title").text(title);
	$("div#now_playing_song_info h4.artist").text(artist);	
	$("ul#now_playing_list").html("");
	enQueueSong(id, title, artist, null, null);
	$("ul#now_playing_list li").addClass("selected");
	play(id);
}

// play next playlist song
function playlistNext() {		
	var next = $("ul#now_playing_list li.selected").next();
	if (next.length == 1) {
		var id = $(next).data("id");
		if (id) {
			var actual = $("ul#now_playing_list li.selected");
			var cover = $("ul#now_playing_list li.selected").data("image");
			$("div#now_playing_cover").css("background-image", cover);
			$(actual).removeClass("selected");
			$(next).addClass("selected");
			play(id);			
		} 
	}	
}

// play previous playlist song
function playlistPrev() {		
	var prev = $("ul#now_playing_list li.selected").prev();
	if (prev.length == 1) {
		var id = $(prev).data("id");
		if (id) {
			var actual = $("ul#now_playing_list li.selected");
			var cover = $("ul#now_playing_list li.selected").data("image");
			$("div#now_playing_cover").css("background-image", cover);			
			$(actual).removeClass("selected");
			$(prev).addClass("selected");
			play(id);							
		}
	}
}

$("body").on("click", "i#crtl_play_previous", function(e) {
	playlistPrev();
});

$("body").on("click", "i#crtl_play_next", function(e) {
	playlistNext();
});

// play search song result
$("body").on("click", "div.result i.play_song", function(e) {
	e.preventDefault();	
	playSong($(this).data("id"), $(this).next().next().text(), $(this).next().next().next().next().text());
});

// enqueue into playlist search song result
$("body").on("click", "div.result i.enqueue_song", function(e) {
	e.preventDefault();
	enQueueSong($(this).data("id"), $(this).next().text(), $(this).next().next().next().text(), null, null);
});

// enqueue into playlist search song result
$("body").on("click", "div.result i.play_album, div.album_container", function(e) {
	e.preventDefault();
	var id = $(this).data("id");
	$.ajax({
		url: "api/album/get.php?id=" + id,
		method: "get"
	})
	.done(function(data, textStatus, jqXHR) {
		if (data.success == true) {
			$("ul#now_playing_list").html("");
			playlistSongs = data.album.tracks;
			for (var i = 0; i < data.album.tracks.length; i++) {
				enQueueSong(data.album.tracks[i].id, data.album.tracks[i].title, data.album.tracks[i].artist.name, data.album.tracks[i].playtimeString, data.album.image.length > 2 ? data.album.image[2].url: null);
			} 
			if (data.album.tracks.length > 0)
			{
				$("ul#now_playing_list li:first").addClass("selected");
				var cover = $("ul#now_playing_list li.selected").data("image");
				if (! cover) {
					cover = "http://fc08.deviantart.net/fs17/f/2007/170/9/8/Vinyl_Disc_Icon_Updated_by_jordygreen.png";
				}
				$("div#now_playing_cover").css("background", 'rgba(0, 0, 0, 0) url("' + cover + '") no-repeat scroll 0 0 / 300px 300px');				
				play(data.album.tracks[0].id);
			}						
		} else {
			// TODO
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		// TODO
	});					
});


// play song from table random row
$("body").on("click", "table tbody tr", function(e) {
	e.preventDefault();
	playSong($(this).data("id"), $(this).find("td:eq(0)").text(), $(this).find("td:eq(1)").text());
});

/*
// load random songs at start & fill table
$.ajax({
	url: "api/artist/get.php?id=",
	method: "get" 
})
.done(function(data, textStatus, jqXHR) {
	if (data.success == true) {
		var html = "";
		for (var i = 0; i < data.songs.length; i++) {
			html += '<tr data-id="' + data.songs[i].id + '">\
				<td>' + data.songs[i].title + '</td>\
				<td>' + data.songs[i].artistName + ' - ' + data.songs[i].albumName + '</td>\
			</tr>';
		}
		$("table tbody").html(html);
	} else {
		// TODO
	}
})
.fail(function(jqXHR, textStatus, errorThrown) {
	// TODO
});					



$.ajax({
	url: "api/artist/search.php?limit=51221",
	method: "get" 
})
.done(function(data, textStatus, jqXHR) {
	if (data.success == true) {
		$("h1#album_title").text(data.album.name);
		$("h2#album_artist").text(data.album.artist.name);
		$("h3#album_year").text(data.album.year);
		if (data.album.image.length > 3) {
			$("img#album_cover").attr("src", data.album.image[3].url);
		}
		$("div#album_description").text(data.album.about);
		$("div#album_view").removeClass("hidden");
	} else {
		// TODO
	}
})
.fail(function(jqXHR, textStatus, errorThrown) {
	// TODO
});					

*/


	function putRandomArtists(artists, callback) {
		var html ="";
		for (var i = 0; i < artists.length; i++) {
		var image = artists[i].image.length > 2 ?  artists[i].image[2].url : "http://fc08.deviantart.net/fs17/f/2007/170/9/8/Vinyl_Disc_Icon_Updated_by_jordygreen.png";
		html += '\
          <div class="artist_container">\
            <img src="' + image + '" alt="artist image">\
			<div class="artist_info">\
            	<p class="artist_name" title="' + artists[i].name + '">' + artists[i].name + '</p>\
			</div>\
          </div>\
		';			
		}
		html += '\
			<div class="clearfix"></div>\
		';
		$("div#artists_scroll").css("width", (artists.length * 240) + "px");
		$("div#artists_scroll").html(html);	
		callback();
	}
	
	function putRandomAlbums(albums, callback) {
		var html = "";
		for (var i = 0; i < albums.length; i++) {
		var image = albums[i].image.length > 2 ?  albums[i].image[2].url : "http://fc08.deviantart.net/fs17/f/2007/170/9/8/Vinyl_Disc_Icon_Updated_by_jordygreen.png";
		html += '\
          <div class="album_container" data-id="' + albums[i].id + '">\
            <img src="' + image + '" alt="album cover">\
            <div class="album_info">\
              <p class="album_name">' + albums[i].name + '</p>\
              <p class="album_artist" title="' + (albums[i].artist.name && albums[i].artist.name.length > 0 ? albums[i].artist.name : "") + '">' + (albums[i].artist.name && albums[i].artist.name.length > 0 ? albums[i].artist.name : "") + '</p>\
            </div>\
          </div>\
		';			
		}
		html += '\
			<div class="clearfix"></div>\
		';
		$("div#albums_scroll").css("width", (albums.length * 240) + "px");
		$("div#albums_scroll").html(html);
		callback();
	}

	$.ajax({
		url: "api/artist/search.php?limit=16&offset=0&sort=rnd",
		method: "get" 
	})
	.done(function(data, textStatus, jqXHR) {
		if (data.success == true) {
			putRandomArtists(data.artists, function() {

				$.ajax({
					url: "api/album/search.php?limit=16&offset=0&sort=rnd",
					method: "get" 
				})
				.done(function(data, textStatus, jqXHR) {
					if (data.success == true) {
						putRandomAlbums(data.albums, function() {
							    $(function()
							    {
							    	$('.scroll-pane').jScrollPane();
							    });
							
						});
					} else {
						// TODO
					}
				})
				.fail(function(jqXHR, textStatus, errorThrown) {
					// TODO
				});					
				
			});
		} else {
			// TODO
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		// TODO
	});					