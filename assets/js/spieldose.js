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
				return('<div class="result"><span data-id="' + song.id + '" class="result_song_title">' + song.title + '</span><br /><span class="result_song_artist">' + (song.artistName ? song.artistName : '') + '</span><div class="clearfix"></div></div>');
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
				return('<div class="result"><img src="http://userserve-ak.last.fm/serve/34s/92459761.jpg" /><span class="result_album">' + album.name + '</span><br /><span class="result_artist_album">' + (album.artistName ? album.artistName : '') + '</span><div class="clearfix"></div></div>');
			}
	  	}		   
	}	
);

/* TYPEAHEAD */

function playSong(id, title, artist) {
	$("div#now_playing_song_info h3 span.title").text(title);
	$("div#now_playing_song_info h4.artist").text(artist);
	var html = '<li class="selected"><span class="idx">1</span>' + title + '<span class="duration">5:39</span></li>';
	$("audio#audio source").attr("src", "api/song/get.php?id=" + id);
	var audio = $("audio#audio");
	audio[0].pause();
    audio[0].load();//suspends and restores all audio element
    audio[0].play();
	$("ul#now_playing_list").html(html);	
}

// play search song result
$("body").on("click", "span.result_song_title", function(e) {
	e.preventDefault();
	playSong($(this).data("id"), $(this).text(), $(this).next().next().text());
});


// play song from table random row
$("body").on("click", "table tbody tr", function(e) {
	e.preventDefault();
	playSong($(this).data("id"), $(this).find("td:eq(0)").text(), $(this).find("td:eq(1)").text());
});

// load random songs at start & fill table
$.ajax({
	url: "api/song/search.php?limit=32&order=rnd",
	method: "post" 
})
.done(function(data, textStatus, jqXHR) {
	if (data.success == true) {
		var html = "";
		for (var i = 0; i < data.songs.length; i++) {
			html += '<tr data-id="' + data.songs[i].id + '">\
				<td>' + data.songs[i].title + '</td>\
				<td>' + data.songs[i].artistName + '</td>\
				<td>' + data.songs[i].albumName + '</td>\
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
