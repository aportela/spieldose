"use strict";(globalThis["webpackChunkspieldose"]=globalThis["webpackChunkspieldose"]||[]).push([[805],{2719:(e,t,s)=>{s.d(t,{d:()=>i});var a=s(1809),r=s(8612),n=s.n(r),l=s(4958);(0,l.n)();const u={namespace:"spieldose",storages:["local","cookie","session","memory"],storage:"local",expireDays:3650},i=(0,a.Q_)("currentPlaylist",{state:()=>({tracks:[],currentIndex:-1}),getters:{hasTracks:e=>e.tracks&&e.tracks.length>0,getTracks:e=>e.tracks,getCurrentIndex:e=>e.currentIndex,getCurrentTrack:e=>e.currentIndex>=0&&e.tracks.length>0?e.tracks[e.currentIndex]:null,getCurrentTrackURL:e=>e.currentIndex>=0&&e.tracks.length>0?e.tracks[e.currentIndex].url:null,allowSkipPrevious:e=>e.currentIndex>0&&e.tracks.length>0,allowSkipNext:e=>e.currentIndex>=0&&e.tracks.length>0&&e.currentIndex<e.tracks.length-1,allowPlay:e=>!0,allowPause:e=>!0,allowResume:e=>!0,allowStop:e=>!0},actions:{load(){const e=n()(u),t=e.get("currentPlaylistTracks");t&&(this.tracks=t);const s=e.get("currentPlaylistTrackIndex");s>=0&&(this.currentIndex=s)},saveTracks(e){this.tracks=e,this.currentIndex=e&&e.length>0?0:-1;const t=n()(u);t.set("currentPlaylistTracks",e),t.set("currentPlaylistTrackIndex",e&&e.length>0?0:-1)},saveCurrentTrackIndex(e){this.currentIndex=e>=0?e:-1;const t=n()(u);t.set("currentPlaylistTrackIndex",this.currentIndex)},skipPrevious(){this.currentIndex--},skipNext(){this.currentIndex++}}})},4958:(e,t,s)=>{s.d(t,{n:()=>l});var a=s(1809),r=s(1320);const n=(0,r.l)(),l=(0,a.Q_)("player",{state:()=>({element:null,hasPreviousUserInteractions:!1}),getters:{getElement:e=>e.element,getDuration:e=>e.element.duration},actions:{interact(){this.hasPreviousUserInteractions=!0},create(){this.element=document.createElement("audio"),this.element.id="audio_player"},setVolume(e){this.element.volume=e},setCurrentTime(e){this.element.currentTime=e},stop(){this.element.pause(),n.setStatusStopped()},load(){},play(e){e?(this.element.play(),n.setStatusPlaying()):n.isPlaying?(this.element.pause(),n.setStatusPaused()):n.isPaused?(this.element.play(),n.setStatusPlaying()):(this.element.load(),this.element.play(),n.setStatusPlaying())}}})},1320:(e,t,s)=>{s.d(t,{l:()=>r});var a=s(1809);const r=(0,a.Q_)("playerStatus",{state:()=>({status:"stopped"}),getters:{getStatus(e){return e.status},isPlaying(e){return"playing"==e.status},isStopped(e){return"stopped"==e.status},isPaused(e){return"paused"==e.status}},actions:{setStatusPlaying(){this.status="playing"},setStatusStopped(){this.status="stopped"},setStatusPaused(){this.status="paused"}}})},7805:(e,t,s)=>{s.r(t),s.d(t,{default:()=>w});var a=s(9835),r=s(6970),n=s(499),l=s(1569),u=s(9302),i=s(2719);const c={key:0},o={key:1},d={__name:"BrowsePathsPage",setup(e){const t=(0,u.Z)(),s=(0,i.d)(),d=(0,n.iH)(!1),p=(0,n.iH)(!1),h=(0,n.iH)([]),g=(0,n.iH)(null);function k(){d.value=!1,p.value=!0,l.api.path.getTree().then((e=>{h.value=e.data.items,p.value=!1})).catch((e=>{t.notify({type:"negative",message:"API Error: error loading paths",caption:"API Error: fatal error details: HTTP {"+e.response.status+"} ({"+e.response.statusText+"})"}),p.value=!1}))}function m(e,t){var s,a,r;if(e==t.hash)return t;for(s=0;s<t.children.length;s+=1)if(a=t.children[s],r=m(e,a),!1!==r)return r;return!1}function y(e){let t=m(e,h.value[0]);return t&&t.id&&(p.value=!0,l.api.track.search(1,0,!1,{path:t.id}).then((e=>{s.saveTracks(e.data.tracks),p.value=!1})).catch((e=>{p.value=!1}))),!0}return k(),(e,t)=>{const s=(0,a.up)("q-breadcrumbs-el"),n=(0,a.up)("q-breadcrumbs"),l=(0,a.up)("q-icon"),u=(0,a.up)("q-tree"),i=(0,a.up)("q-card-section"),d=(0,a.up)("q-card"),p=(0,a.up)("q-page");return(0,a.wg)(),(0,a.j4)(p,null,{default:(0,a.w5)((()=>[(0,a.Wm)(d,{class:"q-pa-lg"},{default:(0,a.w5)((()=>[(0,a.Wm)(n,{class:"q-mb-lg"},{default:(0,a.w5)((()=>[(0,a.Wm)(s,{icon:"home",label:"Spieldose"}),(0,a.Wm)(s,{icon:"person",label:"Browse paths"})])),_:1}),h.value&&h.value.length>0?((0,a.wg)(),(0,a.j4)(i,{key:0},{default:(0,a.w5)((()=>[(0,a._)("div",null,[(0,a.Wm)(u,{nodes:h.value,selected:g.value,"onUpdate:selected":[t[0]||(t[0]=e=>g.value=e),y],"node-key":"hash","label-key":"name","children-key":"children","no-transition":""},{"default-header":(0,a.w5)((e=>[e.node.totalFiles>0?((0,a.wg)(),(0,a.iD)("div",c,[(0,a.Wm)(l,{name:"playlist_play"}),(0,a.Uk)(),(0,a.Wm)(l,{name:"playlist_add"}),(0,a.Uk)(" "+(0,r.zw)(e.node.name)+" ("+(0,r.zw)(e.node.totalFiles)+" total tracks) ",1)])):((0,a.wg)(),(0,a.iD)("span",o,(0,r.zw)(e.node.name),1))])),_:1},8,["nodes","selected"])])])),_:1})):(0,a.kq)("",!0)])),_:1})])),_:1})}}};var p=s(9885),h=s(4458),g=s(2605),k=s(8052),m=s(3190),y=s(1831),v=s(2857),P=s(9984),I=s.n(P);const x=d,w=x;I()(d,"components",{QPage:p.Z,QCard:h.Z,QBreadcrumbs:g.Z,QBreadcrumbsEl:k.Z,QCardSection:m.Z,QTree:y.Z,QIcon:v.Z})}}]);