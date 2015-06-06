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

// browse by artist link
$("a#browse_by_artist").click(function(e) {
	e.preventDefault();
	$.ajax({
		url: $(this).attr("href"),
		method: "post", 
		data: $(this).serialize()
	})
	.done(function(data, textStatus, jqXHR) {
		if (data.success == true) {
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

/*
var artists = new Bloodhound({
	datumTokenizer: Bloodhound.tokenizers.whitespace,
	queryTokenizer: Bloodhound.tokenizers.whitespace,
	prefetch: "./api/artist/search.php",
	remote: {
		cache: false,
		url: './api/artist/search.php?q=%QUERY&limit=3',
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
		url: './api/album/search.php?q=%QUERY&limit=3',
		wildcard: '%QUERY',
		filter: function(response) {
			return(response.albums);
		}	
	}
});

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

$('input#q').typeahead(
	{
		hint: false,
		highlight: false,
		minLength: 3,
	},
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

*/
/* TYPEAHEAD */

// start play song
function play(id) {
	$("audio#audio source").attr("src", "api/song/get.php?id=" + id);
	var audio = $("audio#audio");
	audio[0].pause();
    audio[0].load();//suspends and restores all audio element
    audio[0].play();	
}

// enqueue song into playlist
function enQueueSong(id, title, artist) {
	var html = '<li data-id="' + id + '"><span class="idx"></span>' + title + '<span class="duration">0:00</span></li>';
	$("ul#now_playing_list").append(html);	
}

// enqueue into playlist & play song 
function playSong(id, title, artist) {
	$("div#now_playing_song_info h3 span.title").text(title);
	$("div#now_playing_song_info h4.artist").text(artist);	
	$("ul#now_playing_list").html("");
	enQueueSong(id, title, artist);
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
	enQueueSong($(this).data("id"), $(this).next().text(), $(this).next().next().next().text());
});

// enqueue into playlist search song result
$("body").on("click", "div.result i.play_album", function(e) {
	e.preventDefault();
	var id = $(this).data("id");
	$.ajax({
		url: "api/album/get.php?id=" + id,
		method: "get"
	})
	.done(function(data, textStatus, jqXHR) {
		console.log(data.songs.length);
		if (data.success == true) {
			
			$("ul#now_playing_list").html("");
			for (var i = 0; i < data.songs.length; i++) {
				enQueueSong(data.songs[i].id, data.songs[i].title, data.songs[i].artistName);
			} 
			if (data.songs.length > 0)
			{
				$("ul#now_playing_list li:first").addClass("selected");
				play(data.songs[0].id);
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

*/

/*
$.ajax({
	url: "api/artist/get.php?id=",
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
