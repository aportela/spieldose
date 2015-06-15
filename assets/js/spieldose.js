
var markupTemplates = {
	alert: '\
		<div class="alert alert-danger alert-dismissible" role="alert">\
			<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>\
			<strong>error:</strong> {{msg}}\
		</div>\
	',
	playListTracks: '\
		{{tracks}}\
		<li>\
			<a data-id="{{id}}" class="playlist_track" href="api/track/get.php?id={{id}}">\
			<span title="{{title|blank>No title}}" class="title">{{title|blank>No title}}</span><span class="duration">{{playtimeString}}</span>\
			</a>\
		</li>\
		{{/tracks}}\
		',
	listTracks: '\
		{{tracks}}\
		<li>\
			<a class="play_track disabled_link" data-id="{{id}}" href="api/track/get.php?id={{id}}" title="{{title|blank>}}">{{title|blank>}}</a>\
			<p>\
				<a class="view_artist" data-id="{{artist.id}}" href="api/artist/get.php?id={{artist.id}}" title="{{artist.name|blank>}}">{{artist.name|blank>}}</a>\
			</p>\
		</li>\
		{{/tracks}}\
	',
	// vinyl image credits: jordygreen - http://jordygreen.deviantart.com/art/Vinyl-Disc-Icon-Updated-57968239
	albums: '\
		{{albums}}\
		<div class="album_item">\
			<a class="play_album" href="api/album/get.php?id={{id}}" data-id="{{id}}">\
				{{if image|notempty}}<img class="album_cover" src="{{image.2.url}}" />{{/if}}\
				<i class="fa fa-play fa-4x"></i>\
				<img class="vynil no_cover" src="http://fc08.deviantart.net/fs17/f/2007/170/9/8/Vinyl_Disc_Icon_Updated_by_jordygreen.png" />\
			</a>\
			<div class="album_info">\
				<p class="artist_name" title="{{artist.name|blank>}}"><a class="view_artist" data-id="{{artist.id}}" href="api/artist/get.php?id={{artist.id}}">{{artist.name|blank>}}</a></p>\
				<p class="album_name" title="{{name|blank>}}">{{name|blank>}}</p>\
			</div>\
		</div>\
		{{/albums}}\
		{{if albums|more>1}}<div class="clearfix"></div>\{{/if}}\
	',
	// not artist image credits: https://www.iconfinder.com/icons/339940/band_festival_music_rock_stage_icon#size=128
	artists: '\
		{{artists}}\
		<div class="artist_item">\
			<a class="view_artist" href="api/artist/get.php?id={{id}}" data-id="{{id}}">\
				<img class="album_cover" src="{{if image|notempty}}{{image.2.url}}{{else}}https://cdn2.iconfinder.com/data/icons/app-types-in-grey/128/app_type_festival_512px_GREY.png{{/if}}" />\
				<i class="fa fa-search fa-4x"></i>\
			</a>\
			<div class="artist_info">\
				<p class="artist_name" title="{{name|blank>}}">{{name|blank>}}</p>\
			</div>\
		</div>\
		{{/artists}}\
		{{if artists|more>1}}<div class="clearfix"></div>\{{/if}}\
	'
};

var spieldose = {
	tracksCache: [],
	currentTrack: null,
	playTrack: function(id) {
		spieldose.currentTrack = null;
		for(var i = 0, found = false; i < spieldose.tracksCache.length && ! found; i++) {
			if (spieldose.tracksCache[i].id == id) {
				spieldose.currentTrack = spieldose.tracksCache[i];
				found = true;
			}
		}
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
		defaultImage: "https://cdn2.iconfinder.com/data/icons/app-types-in-grey/128/app_type_festival_512px_GREY.png",
		// check for (lastfm) large artist image existence
		hasImages(images) {
			return(images && images.length > 1);	
		},
		// get (lastfm) large artist image || not artist image
		getImage(images) {						
			return(images && images.length > 1 ? images[2].url : spieldose.artist.defaultImage);
		},		
		search: function(count, order, callback) {
			// get random artists
			$.ajax({
				url: "api/artist/search.php?limit=" + count + "&offset=0&sort=" + order,
				method: "get" 
			})
			.done(function(data, textStatus, jqXHR) {
				if (data.success == true) {
					callback(null, data.artists);
				} else {
					callback(data.error.msg, null);
				}
			})
			.fail(function(jqXHR, textStatus, errorThrown) {
				callback(errorThrown, null);
			});							
		}
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
				for (var i = 0; i < album.tracks.length; i++) {
					spieldose.tracksCache.push(album.tracks[i]);
				}
				$("ul#now_playing_list").html(Mark.up(markupTemplates.playListTracks, { tracks: album.tracks }));
				// auto play first track
				$("ul#now_playing_list li:first a").click();
			}
		}
	},
	track: {
		search: function(count, order, callback) {
			var url = null;
			switch(order) {
				case "rnd":
					url = "api/track/search.php?limit=" + count + "&offset=0&sort=rnd";
				break;
				case "top":
					url = "api/track/search.php?limit=" + count + "&offset=0&sort=top";
				break;
				case "recent":
					url = "api/track/search.php?limit=" + count + "&offset=0&sort=recent";
				break;
				default:
					url = "api/track/search.php?limit=" + count + "&offset=0";
				break;
			}
			// get recently added to library tracks
			$.ajax({
				url: url,
				method: "get" 
			})
			.done(function(data, textStatus, jqXHR) {
				if (data.success == true) {
					callback(null, data.tracks);
				} else {
					callback(data.error.msg, null);
				}
			})
			.fail(function(jqXHR, textStatus, errorThrown) {				
				callback(errorThrown, null);
			});																												
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
							spieldose.track.search(5, "recent", function(err, tracks) {
								if (err == null) {
									spieldose.html.dashboard.putRecentlyAddedTracks(tracks);
									spieldose.track.search(5, "top", function(err, tracks) {
										if (err == null) {
											spieldose.html.dashboard.putMostPlayedTracks(tracks);
											spieldose.track.search(5, "rnd", function(err, tracks) {
												if (err == null) {
													spieldose.html.dashboard.putRandomTracks(tracks);
													$("div#dashboard").removeClass("hidden");
												} else {
													//TODO
												}
											});											
										} else {
											//TODO
										}
									});									
								} else {
									//TODO
								}
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
		dashboard: {
			putAlbums: function(albums) {
				$("div#dashboard_albums").html(Mark.up(markupTemplates.albums, { albums: albums }));
			},
			putArtists: function(artists) {
				$("div#dashboard_artists").html(Mark.up(markupTemplates.artists, { artists: artists }));
			},
			putRecentlyAddedTracks: function(tracks) {
				$("ul#dashboard_recently_added_tracks").html(Mark.up(markupTemplates.listTracks, { tracks: tracks }));
			},
			putMostPlayedTracks: function(tracks) {
				$("ol#dashboard_most_played_tracks").html(Mark.up(markupTemplates.listTracks, { tracks: tracks }));				
			},
			putRandomTracks: function(tracks) {
				$("ul#dashboard_random_tracks").html(Mark.up(markupTemplates.listTracks, { tracks: tracks }));				
			}
		}
	}
};

// signin form submit event
$("form#f_signin").submit(function(e) {
	e.preventDefault();
	$("div.alert").remove();
	$.ajax({
		url: $(this).attr("action"),
		method: "post", 
		data: $(this).serialize(),
		dataType : "json"
	})
	.done(function(data, textStatus, jqXHR) {
		if (data.success == true) {
			window.location = "#/dashboard";
			location.reload(); 
		} else {			
			$("div#signin_container").append(Mark.up(markupTemplates.alert, { msg: data.errorMsg }));
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		$("div#signin_container").append(Mark.up(markupTemplates.alert, { msg: "ajax error" }));
	});				
});

// don't follow events of disabled links  
$("body").on("click", "a.disabled_link", function(e) {
	e.preventDefault();
	e.stopPropagation();
});

// dashboard link
$("a#menu_link_dashboard").click(function(e) {
	spieldose.dashboard.refresh();	
});

// browse artists link
$("a#menu_link_browse_artists").click(function(e) {
	$("div.section").addClass("hidden");
	spieldose.artist.search(32, null, function(err, artists) {
		$("div#artist_list_container").html(Mark.up(markupTemplates.artists, { artists: artists }));
		$("div#artist_list").removeClass("hidden");	
	});
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
				$("div#artist_albums").html(Mark.up(markupTemplates.albums, { albums: data.artist.albums }));
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