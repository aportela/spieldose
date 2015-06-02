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
	datumTokenizer: function(artists) {
		return Bloodhound.tokenizers.whitespace(name);
	},
	queryTokenizer: Bloodhound.tokenizers.whitespace,
	remote: {
		url: './api/artist/search.php?q=%QUERY&limit=3',
		wildcard: '%QUERY',
		filter: function(response) {
			return(response.artists);
		}	
	}
});

artists.initialize();

var albums = new Bloodhound({
	datumTokenizer: function(artists) {
		return Bloodhound.tokenizers.whitespace(name);
	},
	queryTokenizer: Bloodhound.tokenizers.whitespace,
	remote: {
		url: './api/album/search.php?q=%QUERY&limit=3',
		wildcard: '%QUERY',
		filter: function(response) {
			return(response.albums);
		}	
	}
});

albums.initialize();

var songs = new Bloodhound({
	datumTokenizer: function(songs) {
		return Bloodhound.tokenizers.whitespace(title);
	},
	queryTokenizer: Bloodhound.tokenizers.whitespace,
	remote: {
		url: './api/song/search.php?q=%QUERY&limit=3',
		wildcard: '%QUERY',
		filter: function(response) {
			return(response.songs);
		}	
	}
});

songs.initialize();

$('input#q').typeahead(
	{
		hint: true,
		highlight: true,
		minLength: 3,
	},
	{
  		name: 'songs',
		limit: 5,
		displayKey: function (song) {
	  		return song.title;
		},
		source: songs.ttAdapter(),
  		templates: {
			header: '<div id="song_results"><h3>SONGS</h3>',
			footer: '</div>',
	    	suggestion: function(song) {
				return('<div class="result"><span class="result_song_title">' + song.title + '</span><br /><span class="result_song_artist">' + (song.artistName ? song.artistName : '') + '</span><div class="clearfix"></div></div>');
			}
	  	}		   
	},
	{
  		name: 'artists',
		limit: 5,
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
		limit: 5,  
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
