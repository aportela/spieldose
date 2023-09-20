"use strict";(globalThis["webpackChunkspieldose"]=globalThis["webpackChunkspieldose"]||[]).push([[64],{8106:(t,a,e)=>{e.d(a,{dC:()=>r,oS:()=>u,up:()=>o});var s=e(5090),i=e(1569),l=e(4867);const n=(0,s.H)(),r={setFavorite:function(t){return new Promise(((a,e)=>{i.api.track.setFavorite(t).then((e=>{l.spieldoseEvents.emit.track.setFavorite(t,e.data.favorited),a(e)})).catch((t=>{e(t)}))}))},unSetFavorite:function(t){return new Promise(((a,e)=>{i.api.track.unSetFavorite(t).then((e=>{l.spieldoseEvents.emit.track.unSetFavorite(t),a(e)})).catch((t=>{e(t)}))}))},increasePlayCount:function(t){return new Promise(((a,e)=>{i.api.track.increasePlayCount(t).then((e=>{l.spieldoseEvents.emit.track.increasePlayCount(t),a(e)})).catch((t=>{e(t)}))}))},play:function(t){n.isStopped||n.stop(),n.sendElementsToCurrentPlaylist(Array.isArray(t)?t:[{track:t}])},enqueue:function(t){n.interact(),n.appendElementsToCurrentPlaylist(Array.isArray(t)?t:[{track:t}])}},o={play:function(t){i.api.track.search({albumMbId:t.mbId},1,0,!1,"trackNumber","ASC").then((t=>{n.sendElementsToCurrentPlaylist(t.data.data.items.map((t=>({track:t}))))})).catch((t=>{}))},enqueue:function(t){i.api.track.search({albumMbId:album.mbId},1,0,!1,"trackNumber","ASC").then((t=>{n.interact(),n.appendElementsToCurrentPlaylist(t.data.data.items.map((t=>({track:t}))))})).catch((t=>{}))}},u={loadPlaylist:function(t){return new Promise(((a,e)=>{n.interact(),i.api.playlist.get(t).then((t=>{n.setPlaylistAsCurrent(t.data.playlist),a()})).catch((t=>{e(t)}))}))},saveElements:function(t){n.sendElementsToCurrentPlaylist(Array.isArray(t)?t:[{track:t}])},appendElements:function(t){n.appendElementsToCurrentPlaylist(Array.isArray(t)?t:[{track:t}])},setRadioStation:function(t){n.interact(),n.setCurrentRadioStation(t)}}},5090:(t,a,e)=>{e.d(a,{H:()=>o});var s=e(1809),i=e(8612),l=e.n(i);const n=Array.from(window.location.host).reduce(((t,a)=>0|31*t+a.charCodeAt(0)),0),r={namespace:"spieldose#"+n,storages:["local","cookie","session","memory"],storage:"local",expireDays:3650},o=(0,s.Q_)("spieldose",{state:()=>({data:{audio:null,audioMotionAnalyzerSource:null,player:{userInteracted:!1,volume:.1,status:"stopped",muted:!1,repeatMode:"none",shuffle:!1,sideBarTopArt:{mode:"animation"},sidebarAudioMotionAnalyzer:{visible:!0,mode:7}},currentPlaylistIndex:0,playlists:[{id:null,name:null,owner:{id:null,name:null},public:!1,lastChangeTimestamp:null,currentElementIndex:-1,elements:[],shuffleIndexes:[],currentRadioStation:null}]}}),getters:{getAudioInstance:t=>t.data.audio,hasPreviousUserInteractions:t=>t.data.player.userInteracted,getAudioMotionAnalyzerSource:t=>t.data.audioMotionAnalyzerSource,isSidebarAudioMotionAnalyzerVisible:t=>t.data.player.sidebarAudioMotionAnalyzer.visible,getSidebarAudioMotionAnalyzerMode:t=>t.data.player.sidebarAudioMotionAnalyzer.mode,hasSidebarTopArtAnimationMode:t=>"animation"==t.data.player.sideBarTopArt.mode,getPlayerStatus:t=>t.data.player.status,isMuted:t=>t.data.player.muted,isPlaying:t=>"playing"==t.data.player.status,isStopped:t=>"stopped"==t.data.player.status,isPaused:t=>"paused"==t.data.player.status,getVolume:t=>t.data.player.volume,getDuration:t=>t.data.audio?t.data.audio.duration:0,getRepeatMode:t=>t.data.player.repeatMode,getShuffle:t=>t.data.player.shuffle,currentPlaylistElementCount:t=>t.data.playlists[0].elements?t.data.playlists[0].elements.length:0,hasCurrentPlaylistElements(t){return this.currentPlaylistElementCount>0},getCurrentPlaylist:t=>t.data.playlists[0],getCurrentPlaylistIndex:t=>t.data.playlists[0].currentElementIndex,getShuffleCurrentPlaylistIndex:t=>t.data.playlists[0].shuffleIndexes[t.data.playlists[0].currentElementIndex],getCurrentPlaylistLastChangedTimestamp:t=>t.data.playlists[0].lastChangeTimestamp,isCurrentPlaylistElementATrack(t){return!this.hasCurrentPlaylistARadioStation&&this.hasCurrentPlaylistElements&&t.data.playlists[0].elements[t.data.playlists[0].currentElementIndex].track},hasCurrentPlaylistARadioStation:t=>null!=t.data.playlists[0].currentRadioStation,getCurrentPlaylistElement(t){return this.hasCurrentPlaylistARadioStation?{radioStation:t.data.playlists[0].currentRadioStation}:this.hasCurrentPlaylistElements&&t.data.playlists[0].currentElementIndex>=0?this.getShuffle?t.data.playlists[0].elements[t.data.playlists[0].shuffleIndexes[t.data.playlists[0].currentElementIndex]]:t.data.playlists[0].elements[t.data.playlists[0].currentElementIndex]:null},getCurrentPlaylistElementURL(t){return this.hasCurrentPlaylistARadioStation?t.data.playlists[0].currentRadioStation.directStream:this.hasCurrentPlaylistElements&&t.data.playlists[0].currentElementIndex>=0?this.getShuffle?t.data.playlists[0].elements[t.data.playlists[0].shuffleIndexes[t.data.playlists[0].currentElementIndex]].track.url:t.data.playlists[0].elements[t.data.playlists[0].currentElementIndex].track.url:null},getCurrentPlaylistElementNormalImage(t){return this.hasCurrentPlaylistARadioStation?t.data.playlists[0].currentRadioStation.images&&t.data.playlists[0].currentRadioStation.images.normal?t.data.playlists[0].currentRadioStation.images.normal:null:this.hasCurrentPlaylistElements&&t.data.playlists[0].currentElementIndex>=0&&t.data.playlists[0].elements[t.data.playlists[0].currentElementIndex].track&&t.data.playlists[0].elements[t.data.playlists[0].currentElementIndex].track.covers&&t.data.playlists[0].elements[t.data.playlists[0].currentElementIndex].track.covers.normal?this.getShuffle?t.data.playlists[0].elements[t.data.playlists[0].shuffleIndexes[t.data.playlists[0].currentElementIndex]].track.covers.normal:t.data.playlists[0].elements[t.data.playlists[0].currentElementIndex].track.covers.normal:null},getCurrentPlaylistElementSmallImage(t){return this.hasCurrentPlaylistARadioStation?t.data.playlists[0].currentRadioStation.images&&t.data.playlists[0].currentRadioStation.images.small?t.data.playlists[0].currentRadioStation.images.small:null:this.hasCurrentPlaylistElements&&t.data.playlists[0].currentElementIndex>=0&&t.data.playlists[0].elements[t.data.playlists[0].currentElementIndex].track&&t.data.playlists[0].elements[t.data.playlists[0].currentElementIndex].track.covers&&t.data.playlists[0].elements[t.data.playlists[0].currentElementIndex].track.covers.small?this.getShuffle?t.data.playlists[0].elements[t.data.playlists[0].shuffleIndexes[t.data.playlists[0].currentElementIndex]].track.covers.small:t.data.playlists[0].elements[t.data.playlists[0].currentElementIndex].track.covers.small:null},allowSkipPrevious:t=>t.data.playlists[0].currentElementIndex>0,allowSkipNext:t=>t.data.playlists[0].currentElementIndex<t.data.playlists[0].elements.length-1},actions:{create:function(t){null==this.data.audio?(this.data.audio=void 0!==t?new Audio(t):new Audio,this.data.audio.autoplay=!1,this.data.audio.crossOrigin="anonymous"):(this.data.audio.src=null,this.data.audio.crossOrigin="anonymous"),this.restorePlayerSettings(this.hasPreviousUserInteractions),this.restoreCurrentPlaylist()},setAudioSource(t){void 0!==t&&t&&(this.data.audio&&(this.data.audio.src=t),this.hasPreviousUserInteractions&&!this.isPlaying&&this.play(!0))},setAudioMotionAnalyzerSource:function(t){this.data.audioMotionAnalyzerSource=t},toggleSidebarAudioMotionAnalyzer:function(){this.data.player.sidebarAudioMotionAnalyzer.visible=!this.data.player.sidebarAudioMotionAnalyzer.visible,this.savePlayerSettings()},setSidebarAudioMotionAnalyzerMode:function(t){this.data.player.sidebarAudioMotionAnalyzer.mode=t,this.savePlayerSettings()},toggleSidebarTopArtAnimationMode:function(){"animation"==this.data.player.sideBarTopArt.mode?this.data.player.sideBarTopArt.mode="normal":this.data.player.sideBarTopArt.mode="animation",this.savePlayerSettings()},interact:function(){this.data.player.userInteracted=!0},savePlayerSettings:function(){const t=l()(r);t.set("playerSettings",this.data.player)},restorePlayerSettings:function(t){const a=l()(r),e=a.get("playerSettings");e&&(this.data.player=e,this.data.player.status="stopped",this.data.player.userInteracted=void 0!==t&&1==t,this.data.audio&&(this.data.audio.volume=this.data.player.volume,this.data.audio.muted=this.data.player.muted))},setVolume:function(t){t>=0&&t<=1&&(this.data.player.volume=t,this.data.audio&&(this.data.audio.volume=t),this.savePlayerSettings())},toggleMute:function(){this.data.player.muted=!this.data.player.muted,this.data.audio&&(this.data.audio.muted=this.data.player.muted),this.savePlayerSettings()},setCurrentTime:function(t){this.data.audio&&(this.data.audio.currentTime=t)},play:function(t){this.hasPreviousUserInteractions?t?(this.data.audio&&this.data.audio.play(),this.data.player.status="playing"):this.isPlaying?(this.data.audio&&this.data.audio.pause(),this.data.player.status="paused"):(this.isPaused,this.data.audio&&this.data.audio.play(),this.data.player.status="playing"):console.error("play error: no previous user interactions")},pause:function(){this.isPlaying&&(this.data.audio&&this.data.audio.pause(),this.data.player.status="paused")},resume:function(){this.isPaused&&(this.data.audio&&this.data.audio.play(),this.data.player.status="playing")},stop:function(){this.isStopped||(this.data.audio&&(this.data.audio.pause(),this.data.audio.currentTime=0),this.data.player.status="stopped")},toggleRepeatMode:function(){switch(this.data.player.repeatMode){case"none":this.data.player.repeatMode="track";break;case"track":this.data.player.repeatMode="playlist";break;case"playlist":this.data.player.repeatMode="none";break}this.savePlayerSettings()},toggleShuffeMode:function(){this.data.player.shuffle=!this.data.player.shuffle,this.savePlayerSettings()},clearCurrentPlaylist:function(t){this.stop(),this.data.playlists[0]={id:null,name:null,owner:{id:null,name:null},public:!1,lastChangeTimestamp:Date.now(),currentIndex:-1,elements:[],shuffleIndexes:[],currentRadioStation:null},this.data.currentPlaylistIndex=0,t||this.saveCurrentPlaylist()},setPlaylistAsCurrent:function(t){this.stop(),this.data.playlists[0]={id:t.id,name:t.name,owner:t.owner,public:t.public||!1,lastChangeTimestamp:Date.now(),currentElementIndex:t.tracks.length>0?0:-1,elements:t.tracks?t.tracks.map((t=>({track:t}))):[],shuffleIndexes:[...Array(t.tracks?t.tracks.length:0).keys()].sort((function(){return.5-Math.random()})),currentRadioStation:null},this.data.currentPlaylistIndex=0,this.saveCurrentPlaylist(),this.hasCurrentPlaylistElements&&(this.setAudioSource(this.getCurrentPlaylistElementURL),this.play(!0))},setCurrentRadioStation:function(t){this.data.playlists[0].currentRadioStation=t,this.saveCurrentPlaylist(),this.setAudioSource(this.getCurrentPlaylistElementURL),this.play(!0)},sendElementsToCurrentPlaylist:function(t){this.interact(),this.stop();const a=t&&Array.isArray(t)&&t.length>0;a&&(this.data.playlists[0]={id:null,name:null,owner:{id:null,name:null},public:!1,lastChangeTimestamp:Date.now(),currentElementIndex:a?0:-1,elements:a?t:[],shuffleIndexes:a?[...Array(t.length).keys()].sort((function(){return.5-Math.random()})):[],currentRadioStation:null},this.data.currentPlaylistIndex=0,this.saveCurrentPlaylist(),this.setAudioSource(this.getCurrentPlaylistElementURL),this.play(!0))},appendElementsToCurrentPlaylist:function(t){this.interact();const a=this.data.playlists[0].elements.length>0,e=t&&Array.isArray(t)&&t.length>0;e&&(this.data.playlists[0].elements=this.data.playlists[0].elements.concat(t),this.data.playlists[0].shuffleIndexes=[...Array(this.data.playlists[0].elements.length).keys()].sort((function(){return.5-Math.random()})),this.data.playlists[0].currentRadioStation=null,a||(this.data.playlists[0].currentElementIndex=e?0:-1),this.data.currentPlaylistIndex=0,this.data.playlists[0].lastChangeTimestamp=Date.now(),this.saveCurrentPlaylist(),this.isPlaying||(this.setAudioSource(this.getCurrentPlaylistElementURL),this.play(!0)))},restoreCurrentPlaylist:function(){this.clearCurrentPlaylist(!0);const t=l()(r),a=t.get("currentPlaylist");a&&(this.data.playlists[0]=a,this.setAudioSource(this.getCurrentPlaylistElementURL))},saveCurrentPlaylist:function(){const t=l()(r);t.set("currentPlaylist",this.data.playlists[0])},skipPrevious:function(){this.allowSkipPrevious&&(this.interact(),this.data.playlists[0].currentRadioStation&&(this.data.playlists[0].currentRadioStation=null),this.data.playlists[0].currentElementIndex--,this.data.playlists[0].lastChangeTimestamp=Date.now(),this.saveCurrentPlaylist(),this.setAudioSource(this.getCurrentPlaylistElementURL),this.play(!0))},skipNext:function(){this.allowSkipNext&&(this.interact(),this.data.playlists[0].currentRadioStation&&(this.data.playlists[0].currentRadioStation=null),this.data.playlists[0].currentElementIndex++,this.data.playlists[0].lastChangeTimestamp=Date.now(),this.saveCurrentPlaylist(),this.setAudioSource(this.getCurrentPlaylistElementURL),this.play(!0))},skipToIndex:function(t){t>=0&&t<this.data.playlists[0].elements.length&&(this.interact(),this.data.playlists[0].currentRadioStation&&(this.data.playlists[0].currentRadioStation=null),this.data.playlists[0].currentElementIndex=t,this.data.playlists[0].lastChangeTimestamp=Date.now(),this.saveCurrentPlaylist(),this.setAudioSource(this.getCurrentPlaylistElementURL),this.play(!0))}}})},5226:(t,a,e)=>{e.d(a,{Z:()=>v});var s=e(9835),i=e(6970),l=e(499);const n={class:"animated-album-cover-item"},r={class:"play-album"},o={class:"album-actions"},u=(0,s._)("span",{class:"clear: both;"},null,-1),d={key:0,class:"vinyl no-cover",src:"images/vinyl-medium.png"},y={class:"album-info"},p=["title"],c={key:1,class:"artist-name"},m={key:0},h={key:2},g={__name:"AnimatedAlbumCover",props:{title:String,artistName:String,year:Number,image:String},emits:["play","enqueue"],setup(t,{emit:a}){const e=t,g=(0,l.iH)(!1),A=(0,l.iH)(!1),f=(0,s.Fl)((()=>e.image&&!A.value?e.image:"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII="));function P(){g.value=!0}function S(){A.value=!0}function C(){a("play")}function v(){a("enqueue")}return(a,e)=>{const l=(0,s.up)("q-img"),k=(0,s.up)("q-icon"),E=(0,s.up)("router-link");return(0,s.wg)(),(0,s.iD)("div",n,[(0,s._)("div",r,[(0,s.Wm)(l,{class:(0,i.C_)(["album-thumbnail",{"album-thumbnail-animated":g.value||A.value}]),src:f.value,width:"174px",height:"174px","spinner-color":"pink",onLoad:P,onError:S},null,8,["class","src"]),(0,s._)("div",o,[(0,s.Wm)(k,{class:"cursor-pointer",name:"add_box",size:"80px",color:"pink",title:"Enqueue album",style:{left:"10px"},onClick:v}),(0,s.Wm)(k,{class:"cursor-pointer",name:"play_arrow",size:"80px",color:"pink",title:"Play album",style:{left:"80px"},onClick:C}),u]),g.value||A.value?((0,s.wg)(),(0,s.iD)("img",d)):(0,s.kq)("",!0)]),(0,s._)("div",y,[t.title?((0,s.wg)(),(0,s.iD)("p",{key:0,class:"album-name",title:t.title},(0,i.zw)(t.title),9,p)):(0,s.kq)("",!0),t.artistName?((0,s.wg)(),(0,s.iD)("p",c,[(0,s.Uk)("by "),(0,s.Wm)(E,{title:t.artistName,to:{name:"artist",params:{name:t.artistName}}},{default:(0,s.w5)((()=>[(0,s.Uk)((0,i.zw)(t.artistName),1)])),_:1},8,["title","to"]),(0,s.Uk)(),t.year?((0,s.wg)(),(0,s.iD)("span",m,"("+(0,i.zw)(t.year)+")",1)):(0,s.kq)("",!0)])):t.year?((0,s.wg)(),(0,s.iD)("p",h,"("+(0,i.zw)(t.year)+")",1)):(0,s.kq)("",!0)])])}}};var A=e(335),f=e(2857),P=e(9984),S=e.n(P);const C=g,v=C;S()(g,"components",{QImg:A.Z,QIcon:f.Z})},8442:(t,a,e)=>{e.d(a,{Z:()=>u});var s=e(9835),i=e(6970),l=e(4376),n=e(6647);const r={__name:"LabelTimestampAgo",props:{className:{type:String},timestamp:{type:Number}},setup(t){const a=t,{t:e}=(0,n.QT)(),r=(0,s.Fl)((()=>{let t="",s=l.ZP.getDateDiff(Date.now(),a.timestamp,"years");return s<1?(s=l.ZP.getDateDiff(Date.now(),a.timestamp,"months"),s<1?(s=l.ZP.getDateDiff(Date.now(),a.timestamp,"days"),s<1?(s=l.ZP.getDateDiff(Date.now(),a.timestamp,"hours"),s<1?(s=l.ZP.getDateDiff(Date.now(),a.timestamp,"minutes"),s<1?(s=l.ZP.getDateDiff(Date.now(),a.timestamp,"seconds"),t=e(s>1?"nSecondsAgo":"oneSecondAgo",{count:s})):t=e(s>1?"nMinutesAgo":"oneMinuteAgo",{count:s})):t=e(s>1?"nHoursAgo":"oneHourAgo",{count:s})):t=e(s>1?"nDaysAgo":"oneDayAgo",{count:s})):t=e(s>1?"nMonthsAgo":"oneMonthAgo",{count:s})):t=e(s>1?"nYearsAgo":"oneYearAgo",{count:s}),t}));return(a,e)=>((0,s.wg)(),(0,s.iD)("span",{class:(0,i.C_)(t.className)},[(0,s.WI)(a.$slots,"prepend"),(0,s.Uk)((0,i.zw)(r.value),1),(0,s.WI)(a.$slots,"append")],2))}},o=r,u=o}}]);