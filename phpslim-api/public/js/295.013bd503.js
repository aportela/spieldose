"use strict";(globalThis["webpackChunkspieldose"]=globalThis["webpackChunkspieldose"]||[]).push([[295],{2719:(e,l,t)=>{t.d(l,{d:()=>r});var s=t(1809),a=t(8612),n=t.n(a);const i={namespace:"spieldose",storages:["local","cookie","session","memory"],storage:"local",expireDays:3650},r=(0,s.Q_)("currentPlaylist",{state:()=>({elements:[],currentIndex:-1,elementsLastChangeTimestamp:null}),getters:{hasElements:e=>e.elements&&e.elements.length>0,getElements:e=>e.elements,getElementsLastChangeTimestamp:e=>e.elementsLastChangeTimestamp,getCurrentIndex:e=>e.currentIndex,getCurrentElement:e=>e.currentIndex>=0&&e.elements.length>0?e.elements[e.currentIndex]:null,getCurrentElementURL:e=>e.currentIndex>=0&&e.elements.length>0?e.elements[e.currentIndex].url:null,allowSkipPrevious:e=>e.currentIndex>0&&e.elements.length>0,allowSkipNext:e=>e.currentIndex>=0&&e.elements.length>0&&e.currentIndex<e.elements.length-1,allowPlay:e=>!0,allowPause:e=>!0,allowResume:e=>!0,allowStop:e=>!0},actions:{load(){const e=n()(i),l=e.get("currentPlaylistElements");l&&(this.elements=l);const t=e.get("currentPlaylistElementIndex");t>=0&&(this.currentIndex=t)},saveElements(e){this.elements=e,this.currentIndex=e&&e.length>0?0:-1;const l=n()(i);l.set("currentPlaylistElements",e),l.set("currentPlaylistElementIndex",e&&e.length>0?0:-1),this.elementsLastChangeTimestamp=Date.now()},appendElements(e){this.elements=this.elements.concat(e);const l=n()(i);l.set("currentPlaylistElements",this.elements),l.set("currentPlaylistElementIndex",this.currentIndex),this.elementsLastChangeTimestamp=Date.now()},saveCurrentTrackIndex(e){this.currentIndex=e>=0?e:-1;const l=n()(i);l.set("currentPlaylistElementIndex",this.currentIndex)},skipPrevious(){this.currentIndex--},skipNext(){this.currentIndex++}}})},4958:(e,l,t)=>{t.d(l,{n:()=>i});var s=t(1809),a=t(1320);const n=(0,a.l)(),i=(0,s.Q_)("player",{state:()=>({element:null,hasPreviousUserInteractions:!1}),getters:{getElement:e=>e.element,getDuration:e=>e.element.duration},actions:{interact(){this.hasPreviousUserInteractions=!0},create(){this.element=document.createElement("audio"),this.element.id="audio_player",this.element.crossOrigin="anonymous"},setVolume(e){this.element.volume=e},setCurrentTime(e){this.element.currentTime=e},stop(){this.element.pause(),n.setStatusStopped()},load(){},play(e){e?(this.element.play(),n.setStatusPlaying()):n.isPlaying?(this.element.pause(),n.setStatusPaused()):n.isPaused?(this.element.play(),n.setStatusPlaying()):(this.element.load(),this.element.play(),n.setStatusPlaying())}}})},1320:(e,l,t)=>{t.d(l,{l:()=>a});var s=t(1809);const a=(0,s.Q_)("playerStatus",{state:()=>({status:"stopped"}),getters:{getStatus(e){return e.status},isPlaying(e){return"playing"==e.status},isStopped(e){return"stopped"==e.status},isPaused(e){return"paused"==e.status}},actions:{setStatusPlaying(){this.status="playing"},setStatusStopped(){this.status="stopped"},setStatusPaused(){this.status="paused"}}})},1295:(e,l,t)=>{t.r(l),t.d(l,{default:()=>re});var s=t(9835),a=t(1957),n=t(499),i=t(1569),r=t(9302),o=t(6970);const c={class:"row"},u={class:"col-4"},m=["src"],d={class:"col-4"},p=["src"],v={class:"col-4"},g=["src"],y={class:"row"},h={class:"col-4"},_=["src"],w={class:"col-4"},k=["src"],x={class:"col-4"},f=["src"],b={class:"row"},P={class:"col-4"},C=["src"],I={class:"col-4"},q=["src"],W={class:"col-4"},S=["src"],Q=["src"],E="images/vinyl.png",Z={__name:"BrowsePlaylistItem",props:{playlist:Object,mode:String},emits:["play","delete"],setup(e,{emit:l}){const t=e,n=(0,s.Fl)((()=>"mosaic"==t.mode)),i=(0,s.Fl)((()=>"vinylCollection"==t.mode));function r(){l("play",t.playlist.id)}function Z(){l("delete",t.playlist.id)}function T(){const e="ABCDEF0123456789";let l="#";while(l.length<7)l+=e.charAt(Math.floor(16*Math.random()+1));return l}return(l,t)=>{const D=(0,s.up)("q-card-section"),V=(0,s.up)("q-separator"),H=(0,s.up)("q-avatar"),B=(0,s.up)("q-btn"),j=(0,s.up)("q-btn-group"),A=(0,s.up)("q-card");return(0,s.wg)(),(0,s.j4)(A,{class:"col-xl-2 col-lg-3 col-md-12 col-sm-12 col-xs-12 shadow-box shadow-10 q-mt-lg",bordered:""},{default:(0,s.w5)((()=>[(0,s.Wm)(D,null,{default:(0,s.w5)((()=>[(0,s.Uk)((0,o.zw)(e.playlist.name)+" ("+(0,o.zw)(e.playlist.trackCount)+" track/s) ",1)])),_:1}),(0,s.Wm)(V),n.value?((0,s.wg)(),(0,s.j4)(D,{key:0},{default:(0,s.w5)((()=>[(0,s._)("div",c,[(0,s._)("div",u,[(0,s._)("img",{class:"mosaic_cover_element",src:e.playlist.covers[0]||E},null,8,m)]),(0,s._)("div",d,[(0,s._)("img",{class:"mosaic_cover_element",src:e.playlist.covers[1]||E},null,8,p)]),(0,s._)("div",v,[(0,s._)("img",{class:"mosaic_cover_element",src:e.playlist.covers[2]||E},null,8,g)])]),(0,s._)("div",y,[(0,s._)("div",h,[(0,s._)("img",{class:"mosaic_cover_element",src:e.playlist.covers[3]||E},null,8,_)]),(0,s._)("div",w,[(0,s._)("img",{class:"mosaic_cover_element",src:e.playlist.covers[4]||E},null,8,k)]),(0,s._)("div",x,[(0,s._)("img",{class:"mosaic_cover_element",src:e.playlist.covers[5]||E},null,8,f)])]),(0,s._)("div",b,[(0,s._)("div",P,[(0,s._)("img",{class:"mosaic_cover_element",src:e.playlist.covers[6]||E},null,8,C)]),(0,s._)("div",I,[(0,s._)("img",{class:"mosaic_cover_element",src:e.playlist.covers[7]||E},null,8,q)]),(0,s._)("div",W,[(0,s._)("img",{class:"mosaic_cover_element",src:e.playlist.covers[8]||E},null,8,S)])])])),_:1})):i.value?((0,s.wg)(),(0,s.j4)(D,{key:1,style:{height:"140px"}},{default:(0,s.w5)((()=>[((0,s.wg)(),(0,s.iD)(s.HY,null,(0,s.Ko)(9,(l=>(0,s.Wm)(H,{key:l,size:"100px",class:"overlapping",style:(0,o.j5)(`left: ${25*l}px`)},{default:(0,s.w5)((()=>[e.playlist.covers[l]?((0,s.wg)(),(0,s.iD)("img",{key:0,src:e.playlist.covers[l],class:(0,o.C_)("mosaic_cover_element rotate-"+45*(l-1))},null,10,Q)):((0,s.wg)(),(0,s.iD)("div",{key:1,class:"no_cover",style:(0,o.j5)("background: "+T())},null,4))])),_:2},1032,["style"]))),64))])),_:1})):(0,s.kq)("",!0),(0,s.Wm)(V),(0,s.Wm)(D,null,{default:(0,s.w5)((()=>[(0,s.Wm)(j,{spread:"",outline:""},{default:(0,s.w5)((()=>[(0,s.Wm)(B,{label:"play",stack:"",icon:"play_arrow",onClick:(0,a.iM)(r,["prevent"])},null,8,["onClick"]),(0,s.Wm)(B,{label:"edit",stack:"",icon:"edit",disabled:""}),(0,s.Wm)(B,{label:"delete",stack:"",icon:"delete",onClick:(0,a.iM)(Z,["prevent"])},null,8,["onClick"])])),_:1})])),_:1})])),_:1})}}};var T=t(4458),D=t(3190),V=t(926),H=t(1357),B=t(7236),j=t(8879),A=t(9984),U=t.n(A);const L=Z,M=L;U()(Z,"components",{QCard:T.Z,QCardSection:D.Z,QSeparator:V.Z,QAvatar:H.Z,QBtnGroup:B.Z,QBtn:j.Z});var z=t(4958),F=t(2719);const K={key:0,class:"q-pa-lg flex flex-center"},N={class:"q-gutter-md row items-start"},R=(0,s._)("span",{class:"q-ml-sm"},"Delete playlist",-1),Y=(0,s._)("p",null,"Are you sure ?",-1),O={__name:"BrowsePlaylistsPage",setup(e){const l=(0,r.Z)(),t=(0,n.iH)(null),o=(0,n.iH)(!1),c=(0,n.iH)(!1),u=(0,n.iH)([]),m=(0,n.iH)(0),d=(0,n.iH)(1),p=(0,n.iH)("mosaic"),v=(0,z.n)(),g=(0,F.d)(),y=(0,n.iH)(!1),h=(0,n.iH)(null);function _(e){e&&(d.value=1),o.value=!1,c.value=!0,i.api.playlist.search(d.value,32,{name:t.value}).then((e=>{u.value=e.data.data.items,m.value=e.data.data.pager.totalPages,t.value&&e.data.data.pager.totalResults<1&&(o.value=!0),c.value=!1})).catch((e=>{l.notify({type:"negative",message:"API Error: error loading playlists",caption:"API Error: fatal error details: HTTP {"+e.response.status+"} ({"+e.response.statusText+"})"}),c.value=!1}))}function w(e){d.value=e,_(!1)}function k(e){v.interact(),c.value=!0,i.api.track.search({playlistId:e},1,0,!1,"playListTrackIndex","ASC").then((e=>{g.saveElements(e.data.data.items.map((e=>({track:e})))),c.value=!1})).catch((e=>{c.value=!1}))}function x(e){y.value=!0,h.value=e}function f(){c.value=!0,i.api.playlist.delete(h.value).then((e=>{c.value=!1,y.value=!1,h.value=null,l.notify({type:"positive",message:"Playlist deleted"}),c.value=!1,_(!0)})).catch((e=>{c.value=!1}))}return _(!0),(e,l)=>{const n=(0,s.up)("q-breadcrumbs-el"),i=(0,s.up)("q-breadcrumbs"),r=(0,s.up)("q-icon"),v=(0,s.up)("q-input"),g=(0,s.up)("q-btn-toggle"),h=(0,s.up)("q-pagination"),b=(0,s.up)("q-avatar"),P=(0,s.up)("q-card-section"),C=(0,s.up)("q-btn"),I=(0,s.up)("q-card-actions"),q=(0,s.up)("q-card"),W=(0,s.up)("q-dialog"),S=(0,s.up)("q-page"),Q=(0,s.Q2)("close-popup");return(0,s.wg)(),(0,s.j4)(S,null,{default:(0,s.w5)((()=>[(0,s.Wm)(q,{class:"q-pa-lg"},{default:(0,s.w5)((()=>[(0,s.Wm)(i,{class:"q-mb-lg"},{default:(0,s.w5)((()=>[(0,s.Wm)(n,{icon:"home",label:"Spieldose"}),(0,s.Wm)(n,{icon:"list",label:"Browse playlists"})])),_:1}),u.value?((0,s.wg)(),(0,s.j4)(P,{key:0},{default:(0,s.w5)((()=>[(0,s.Wm)(v,{modelValue:t.value,"onUpdate:modelValue":l[0]||(l[0]=e=>t.value=e),clearable:"",type:"search",outlined:"",dense:"",placeholder:"Text condition",hint:"Search playlists with name",loading:c.value,disable:c.value,onKeydown:l[1]||(l[1]=(0,a.D2)((0,a.iM)((e=>_(!0)),["prevent"]),["enter"])),onClear:l[2]||(l[2]=e=>{o.value=!1,_(!0)}),error:o.value,errorMessage:"No playlists found with specified condition"},{prepend:(0,s.w5)((()=>[(0,s.Wm)(r,{name:"filter_alt"})])),append:(0,s.w5)((()=>[(0,s.Wm)(r,{name:"search",class:"cursor-pointer",onClick:_})])),_:1},8,["modelValue","loading","disable","error"]),(0,s.Wm)(g,{class:"q-my-md",push:"","toggle-color":"pink",modelValue:p.value,"onUpdate:modelValue":l[3]||(l[3]=e=>p.value=e),options:[{label:"style: mosaic",value:"mosaic"},{label:"style: vinyl collection",value:"vinylCollection"}]},null,8,["modelValue"]),m.value>1?((0,s.wg)(),(0,s.iD)("div",K,[(0,s.Wm)(h,{modelValue:d.value,"onUpdate:modelValue":[l[4]||(l[4]=e=>d.value=e),w],color:"dark",max:m.value,"max-pages":5,"boundary-numbers":"","direction-links":"","boundary-links":"",disable:c.value},null,8,["modelValue","max","disable"])])):(0,s.kq)("",!0),(0,s._)("div",N,[((0,s.wg)(!0),(0,s.iD)(s.HY,null,(0,s.Ko)(u.value,(e=>((0,s.wg)(),(0,s.j4)(M,{key:e.id,playlist:e,mode:p.value,onPlay:l=>k(e.id),onDelete:l=>x(e.id)},null,8,["playlist","mode","onPlay","onDelete"])))),128))]),(0,s.Wm)(W,{modelValue:y.value,"onUpdate:modelValue":l[5]||(l[5]=e=>y.value=e),persistent:""},{default:(0,s.w5)((()=>[(0,s.Wm)(q,null,{default:(0,s.w5)((()=>[(0,s.Wm)(P,{class:"row items-center"},{default:(0,s.w5)((()=>[(0,s.Wm)(b,{icon:"info"}),R])),_:1}),(0,s.Wm)(P,null,{default:(0,s.w5)((()=>[Y])),_:1}),(0,s.Wm)(I,{align:"right"},{default:(0,s.w5)((()=>[(0,s.wy)((0,s.Wm)(C,{flat:"",label:"Cancel",color:"primary"},null,512),[[Q]]),(0,s.Wm)(C,{flat:"",label:"YES",color:"primary",onClick:f})])),_:1})])),_:1})])),_:1},8,["modelValue"])])),_:1})):(0,s.kq)("",!0)])),_:1})])),_:1})}}};var G=t(9885),$=t(2605),J=t(8052),X=t(6611),ee=t(2857),le=t(1389),te=t(996),se=t(2074),ae=t(1821),ne=t(2146);const ie=O,re=ie;U()(O,"components",{QPage:G.Z,QCard:T.Z,QBreadcrumbs:$.Z,QBreadcrumbsEl:J.Z,QCardSection:D.Z,QInput:X.Z,QIcon:ee.Z,QBtnToggle:le.Z,QPagination:te.Z,QDialog:se.Z,QAvatar:H.Z,QCardActions:ae.Z,QBtn:j.Z}),U()(O,"directives",{ClosePopup:ne.Z})}}]);