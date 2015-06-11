var spieldose = {
	tracksCache: [],
	currentTrack: null,
	playTrack: function(id) {
		spieldose.currentTrack = null;
		for(var i = 0, found = false; i < spieldose.tracksCache.length && ! found; i++) {
			console.log(spieldose.tracksCache[i].id);
			if (spieldose.tracksCache[i].id == id) {
				spieldose.currentTrack = spieldose.tracksCache[i];
				found = true;
			}
		}
		console.log(found);
		if (found) {
			$("span#now_playing_song_title").text(spieldose.currentTrack.title);
			$("span#now_playing_song_title").attr("title", spieldose.currentTrack.title)
			$("span#now_playing_song_time").text(spieldose.currentTrack.playtimeString);
			$("span#now_playing_artist").text(spieldose.currentTrack.artist.name);
			$("span#now_playing_artist").attr("title", spieldose.currentTrack.artist.name);
			var audio = $("audio#audio");
			audio[0].pause();
			$("audio#audio source").attr("src", "api/track/get.php?id=" + id);
			audio[0].load();
			audio[0].play();	
		}
	},
	tag: {
		// get tag value || empty string (avoid "null" values)
		getStr: function(field) {
			return(field ? field : "");
		}	
	},
	artist: {
		// not artist image credits: https://www.iconfinder.com/icons/339940/band_festival_music_rock_stage_icon#size=128
		defaultImage: "https://cdn2.iconfinder.com/data/icons/app-types-in-grey/128/app_type_festival_512px_GREY.png",
		// check for (lastfm) large artist image existence
		hasImages(images) {
			return(images && images.length > 1);	
		},
		// get (lastfm) large artist image || not artist image
		getImage(images) {						
			return(images && images.length > 1 ? images[2].url : spieldose.artist.defaultImage);
		},		
	},
	album: {
		// vinyl image credits: jordygreen - http://jordygreen.deviantart.com/art/Vinyl-Disc-Icon-Updated-57968239
		defaultCover: "http://fc08.deviantart.net/fs17/f/2007/170/9/8/Vinyl_Disc_Icon_Updated_by_jordygreen.png",
		// check for (lastfm) large album image existence
		hasImages(images) {
			return(images && images.length > 1);	
		},
		// get (lastfm) large album image || default vinyl cover
		getImage(images) {						
			return(images && images.length > 1 ? images[2].url : spieldose.album.defaultCover);
		},		
		enqueue: function(album) {
			if (album.tracks.length > 0)
			{				
				$("img#now_playing_image").attr("src", spieldose.album.getImage(album.image));
				var html = "";
				for (var i = 0; i < album.tracks.length; i++) {
					html += spieldose.html.listItem.playlistTrack(album.tracks[i]);
					spieldose.tracksCache.push(album.tracks[i]);
				}
				$("ul#now_playing_list").html(html);
				// auto play first track
				$("ul#now_playing_list li:first a").click();
			}
		}
	},
	dashboard: {
		// refresh & redraw dashboard section
		refresh: function() {
			$("div.section").addClass("hidden");
			// get random albums
			$.ajax({
				url: "api/album/search.php?limit=16&offset=0&sort=rnd",
				method: "get" 
			})
			.done(function(data, textStatus, jqXHR) {
				if (data.success == true) {
					spieldose.html.dashboard.putAlbums(data.albums);					
					// get random artists
					$.ajax({
						url: "api/artist/search.php?limit=16&offset=0&sort=rnd",
						method: "get" 
					})
					.done(function(data, textStatus, jqXHR) {
						if (data.success == true) {
							spieldose.html.dashboard.putArtists(data.artists);
							// get most played tracks 							
							$.ajax({
								url: "api/track/search.php?limit=5&offset=0&sort=rnd",
								method: "get" 
							})
							.done(function(data, textStatus, jqXHR) {
								if (data.success == true) {
									spieldose.html.dashboard.putMostPlayedTracks(data.tracks);
									spieldose.html.dashboard.putRecentlyAddedTracks(data.tracks);
									$("div#dashboard").removeClass("hidden");	
								} else {
									// TODO
								}								
							})
							.fail(function(jqXHR, textStatus, errorThrown) {
								// TODO
							});																									
						} else {
							// TODO
						}
					})
					.fail(function(jqXHR, textStatus, errorThrown) {
						// TODO
					});				
					
				} else {
					// TODO
				}
			})
			.fail(function(jqXHR, textStatus, errorThrown) {
				// TODO
			});					
		}
	},
	html: {
		alert(msg) {
			var html = '\
				<div class="alert alert-danger alert-dismissible" role="alert">\
					<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>\
					<strong>error:</strong> ' + msg + '\
				</div>\
			';
			return(html);			
		},
		listItem: {
			trackList: function(data) {
				var trackTitle = spieldose.tag.getStr(data.title);
				var artistName = spieldose.tag.getStr(data.artist.name);
				var artistId = spieldose.tag.getStr(data.artist.id);
				if (artistName.length < 1) {
					artistName = "Unknown";
				}
				// <a  href="api/artist/get.php?id=' + data.id + '" data-id="' + data.id + '">\
				var html = '<li><a class="play_track disabled_link" data-id="' + data.id + '" href="api/track/get.php?id=' + data.id + '" title="' + trackTitle + '">' + trackTitle + '</a><p><a class="view_artist" data-id="' + artistId + '" href="api/artist/get.php?id=' + artistId + '">' + artistName + '</a></p></li>';
				return(html);	
			},
			album: function(data) {
				var artistName = spieldose.tag.getStr(data.artist.name);
				var artistId = spieldose.tag.getStr(data.artist.id);
				var albumName = spieldose.tag.getStr(data.name);
				var html = '\
						<div class="album_item">\
							<a class="play_album" href="api/album/get.php?id=' + data.id + '" data-id="' + data.id + '">\
							' + (spieldose.album.hasImages(data.image) ? '<img class="album_cover" src="' + spieldose.album.getImage(data.image) + '" />"' : "") + '\
								<i class="fa fa-play fa-4x"></i>\
								<img class="vynil no_cover" src="' + spieldose.album.defaultCover + '" />\
							</a>\
							<div class="album_info">\
								<p class="artist_name" title="' + artistName  + '"><a class="view_artist" data-id="' + artistId + '" href="api/artist/get.php?id=' + artistId + '">' + artistName + '</a></p>\
								<p class="album_name" title="' + albumName + '">' + albumName + '</p>\
							</div>\
						</div>\
				';
				return(html);
			},
			artist: function(data) {
				var artistName = spieldose.tag.getStr(data.name);
				var html = '\
						<div class="artist_item">\
							<a class="view_artist" href="api/artist/get.php?id=' + data.id + '" data-id="' + data.id + '">\
								<img src="' + spieldose.artist.getImage(data.image) + '" />\
								<i class="fa fa-search fa-4x"></i>\
							</a>\
							<div class="artist_info">\
								<p class="artist_name" title="' + artistName + '">' + artistName + '</p>\
							</div>\
						</div>\
				';
				return(html);				
			},
			playlistTrack: function(data) {
				var trackTitle = spieldose.tag.getStr(data.title);
				var playTimeString = spieldose.tag.getStr(data.playtimeString);
				var html = '\
					<li>\
						<a data-id="' + data.id + '" class="playlist_track" href="api/track/get.php?id=' + data.id + '">\
						<span title="' + trackTitle + '" class="title">' + trackTitle + '</span><span class="duration">' + playTimeString + '</span>\
						</a>\
					</li>\
					';
				return(html);
			}
		},
		dashboard: {
			putAlbums: function(albums) {
				var html = "";
				for (var i = 0; i < albums.length; i++) {
					html += spieldose.html.listItem.album(albums[i]);
				}
				html += '\
					<div class="clearfix"></div>\
				';
				$("div#dashboard_albums").html(html);
			},
			putArtists: function(artists) {
				var html = "";
				for (var i = 0; i < artists.length; i++) {
					html += spieldose.html.listItem.artist(artists[i]);
				}
				html += '\
					<div class="clearfix"></div>\
				';
				$("div#dashboard_artists").html(html);
			},
			putMostPlayedTracks: function(tracks) {
				var html = "";
				for (var i = 0; i < tracks.length; i++) {
					html += spieldose.html.listItem.trackList(tracks[i]);
				}
				$("ol#dashboard_most_played_tracks").html(html);				
			},
			putRecentlyAddedTracks: function(tracks) {
				var html = "";
				for (var i = 0; i < tracks.length; i++) {
					html += spieldose.html.listItem.trackList(tracks[i]);
				}
				$("ul#dashboard_recently_added_tracks").html(html);
			}
		}
	}
};

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
			$("div#signin_container").append(spieldose.html.alert(data.errorMsg))
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		$("div#signin_container").append(spieldose.html.alert("ajax error"))
	});				
});

// don't follow events of disabled links  
$("body").on("click", "a.disabled_link", function(e) {
	e.preventDefault();
	e.stopPropagation();
});

// play album event click
$("body").on("click", "a.play_album", function(e) {
	e.preventDefault();
	$("div#content").addClass("content_with_player");
	$("div#player").removeClass("hidden");
	$.ajax({
		url: $(this).attr("href"),
		method: "get" 
	})
	.done(function(data, textStatus, jqXHR) {
		if (data.success == true) {
			spieldose.album.enqueue(data.album);
		} else {
			// TODO
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		// TODO
	});					
});

// view artist event click
$("body").on("click", "a.view_artist", function(e) {
	e.preventDefault();
	var id = $(this).data("id");
	if (id) {
		$.ajax({
			url: $(this).attr("href"),
			method: "get" 
		})
		.done(function(data, textStatus, jqXHR) {
			if (data.success == true) {
				// hide all sections
				$("div.section").addClass("hidden");
				$("h1#artist_name").text(data.artist.name);
				$("div#artist_bio").html(data.artist.bio);
				$("img#artist_image").attr("src", spieldose.artist.getImage(data.artist.image));						
				var htmlTags = "";
				if (data.artist.tags.length > 0) {
					for (var i = 0; i < data.artist.tags.length; i++) {
						htmlTags += '<a class="btn btn-default btn-sm" href="#" role="button">' + data.artist.tags[i] + '</a>';					
					}			
				}
				$("p#artist_tags").html(htmlTags);
				// show artist section
				$("div#artist_view").removeClass("hidden");
			} else {
				// TODO
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			// TODO
		});							
	}
});

 
$("body").on("click", "a.playlist_track", function(e) {
	e.preventDefault();
	var id = $(this).data("id");
	if (id) {
		spieldose.playTrack(id);
		$(this).closest("ul").find("a").removeClass("selected");
		$(this).addClass("selected");
	}
}); 

spieldose.dashboard.refresh();