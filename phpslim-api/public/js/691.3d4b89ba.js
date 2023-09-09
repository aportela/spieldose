"use strict";(globalThis["webpackChunkspieldose"]=globalThis["webpackChunkspieldose"]||[]).push([[691],{2719:(e,t,a)=>{a.d(t,{d:()=>r});var l=a(1809),s=a(8612),n=a.n(s);const i={namespace:"spieldose",storages:["local","cookie","session","memory"],storage:"local",expireDays:3650},r=(0,l.Q_)("currentPlaylist",{state:()=>({elements:[],currentIndex:-1,elementsLastChangeTimestamp:null}),getters:{hasElements:e=>e.elements&&e.elements.length>0,getElements:e=>e.elements,getElementsLastChangeTimestamp:e=>e.elementsLastChangeTimestamp,getCurrentIndex:e=>e.currentIndex,getCurrentElement:e=>e.currentIndex>=0&&e.elements.length>0?e.elements[e.currentIndex]:null,getCurrentElementURL:e=>e.currentIndex>=0&&e.elements.length>0?e.elements[e.currentIndex].url:null,allowSkipPrevious:e=>e.currentIndex>0&&e.elements.length>0,allowSkipNext:e=>e.currentIndex>=0&&e.elements.length>0&&e.currentIndex<e.elements.length-1,allowPlay:e=>!0,allowPause:e=>!0,allowResume:e=>!0,allowStop:e=>!0},actions:{load(){const e=n()(i),t=e.get("currentPlaylistElements");t&&(this.elements=t);const a=e.get("currentPlaylistElementIndex");a>=0&&(this.currentIndex=a)},saveElements(e){this.elements=e,this.currentIndex=e&&e.length>0?0:-1;const t=n()(i);t.set("currentPlaylistElements",e),t.set("currentPlaylistElementIndex",e&&e.length>0?0:-1),this.elementsLastChangeTimestamp=Date.now()},saveCurrentTrackIndex(e){this.currentIndex=e>=0?e:-1;const t=n()(i);t.set("currentPlaylistElementIndex",this.currentIndex)},skipPrevious(){this.currentIndex--},skipNext(){this.currentIndex++}}})},4958:(e,t,a)=>{a.d(t,{n:()=>i});var l=a(1809),s=a(1320);const n=(0,s.l)(),i=(0,l.Q_)("player",{state:()=>({element:null,hasPreviousUserInteractions:!1}),getters:{getElement:e=>e.element,getDuration:e=>e.element.duration},actions:{interact(){this.hasPreviousUserInteractions=!0},create(){this.element=document.createElement("audio"),this.element.id="audio_player",this.element.crossOrigin="anonymous"},setVolume(e){this.element.volume=e},setCurrentTime(e){this.element.currentTime=e},stop(){this.element.pause(),n.setStatusStopped()},load(){},play(e){e?(this.element.play(),n.setStatusPlaying()):n.isPlaying?(this.element.pause(),n.setStatusPaused()):n.isPaused?(this.element.play(),n.setStatusPlaying()):(this.element.load(),this.element.play(),n.setStatusPlaying())}}})},1320:(e,t,a)=>{a.d(t,{l:()=>s});var l=a(1809);const s=(0,l.Q_)("playerStatus",{state:()=>({status:"stopped"}),getters:{getStatus(e){return e.status},isPlaying(e){return"playing"==e.status},isStopped(e){return"stopped"==e.status},isPaused(e){return"paused"==e.status}},actions:{setStatusPlaying(){this.status="playing"},setStatusStopped(){this.status="stopped"},setStatusPaused(){this.status="paused"}}})},6729:(e,t,a)=>{a.d(t,{Z:()=>w});var l=a(9835),s=a(6970),n=a(499);const i={class:"animated-album-cover-item"},r={key:0,class:"vinyl no-cover",src:"images/vinyl.png"},u={class:"album-info"},o=["title"],m={key:1,class:"artist-name"},c={key:0},d={key:2},p={__name:"AnimatedAlbumCover",props:{title:String,artistName:String,year:Number,image:String},emits:["play"],setup(e,{emit:t}){const a=e,p=(0,n.iH)(!1),g=(0,n.iH)(!1),v=(0,l.Fl)((()=>a.image&&!g.value?a.image:"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII="));function h(){p.value=!0}function b(){g.value=!0}function y(){t("play")}return(t,a)=>{const n=(0,l.up)("q-img"),w=(0,l.up)("q-icon"),k=(0,l.up)("router-link");return(0,l.wg)(),(0,l.iD)("div",i,[(0,l._)("div",{class:"play-album",title:"Play album",onClick:y},[(0,l.Wm)(n,{class:(0,s.C_)(["album-thumbnail",{"album-thumbnail-animated":p.value||g.value}]),src:v.value,width:"174px",height:"174px","spinner-color":"pink",onLoad:h,onError:b},null,8,["class","src"]),(0,l.Wm)(w,{name:"play_arrow",class:"browse_album_play_icon",size:"128px",color:"white"}),p.value||g.value?((0,l.wg)(),(0,l.iD)("img",r)):(0,l.kq)("",!0)]),(0,l._)("div",u,[e.title?((0,l.wg)(),(0,l.iD)("p",{key:0,class:"album-name",title:e.title},(0,s.zw)(e.title),9,o)):(0,l.kq)("",!0),e.artistName?((0,l.wg)(),(0,l.iD)("p",m,[(0,l.Uk)("by "),(0,l.Wm)(k,{title:e.artistName,to:{name:"artist",params:{name:e.artistName}}},{default:(0,l.w5)((()=>[(0,l.Uk)((0,s.zw)(e.artistName),1)])),_:1},8,["title","to"]),(0,l.Uk)(),e.year?((0,l.wg)(),(0,l.iD)("span",c,"("+(0,s.zw)(e.year)+")",1)):(0,l.kq)("",!0)])):e.year?((0,l.wg)(),(0,l.iD)("p",d,"("+(0,s.zw)(e.year)+")",1)):(0,l.kq)("",!0)])])}}};var g=a(335),v=a(2857),h=a(9984),b=a.n(h);const y=p,w=y;b()(p,"components",{QImg:g.Z,QIcon:v.Z})},8691:(e,t,a)=>{a.r(t),a.d(t,{default:()=>Q});var l=a(9835),s=a(1957),n=a(6970),i=a(499),r=a(1569),u=a(9302),o=a(6729),m=a(4958),c=a(2719);const d={class:"row q-gutter-xs"},p={class:"col"},g={class:"col-xl-1 col-lg-2 col-md-3 col-sm-4 col-xs-4"},v={class:"col-xl-1 col-lg-2 col-md-3 col-sm-4 col-xs-4"},h={key:0,class:"q-pa-lg flex flex-center"},b={class:"q-gutter-md row items-start"},y={__name:"BrowseAlbumsPage",setup(e){const t=(0,u.Z)(),a=(0,m.n)(),y=(0,c.d)(),w=(0,i.iH)(null),k=[{label:"Title",value:"title"},{label:"Artist",value:"albumArtistName"}],A=(0,i.iH)(k[0]),x=[{label:"Ascending",value:"ASC"},{label:"Descending",value:"DESC"}],I=(0,i.iH)(x[0]),P=(0,i.iH)(!1),S=(0,i.iH)(!1),_=(0,i.iH)([]),C=(0,i.iH)(0),f=(0,i.iH)(1),q=(0,i.iH)(null);function E(e){e&&(f.value=1),P.value=!1,S.value=!0,r.api.album.search(w.value,f.value,32,A.value.value,I.value.value).then((e=>{_.value=e.data.data.items.map((e=>(e.image=e.covers.small,e))),C.value=e.data.data.pager.totalPages,w.value&&e.data.data.pager.totalResults<1&&(P.value=!0),(0,l.Y3)((()=>{q.value.$el.focus()})),S.value=!1})).catch((e=>{t.notify({type:"negative",message:"API Error: error loading albums",caption:"API Error: fatal error details: HTTP {"+e.response.status+"} ({"+e.response.statusText+"})"}),S.value=!1}))}function Q(e){f.value=e,E(!1)}function U(e){a.interact(),S.value=!0,r.api.track.search({albumMbId:e.mbId},1,0,!1,"trackNumber","ASC").then((e=>{y.saveElements(e.data.data.items.map((e=>({track:e})))),S.value=!1})).catch((e=>{S.value=!1}))}return E(!0),(e,t)=>{const a=(0,l.up)("q-breadcrumbs-el"),i=(0,l.up)("q-breadcrumbs"),r=(0,l.up)("q-icon"),u=(0,l.up)("q-input"),m=(0,l.up)("q-select"),c=(0,l.up)("q-pagination"),y=(0,l.up)("q-card-section"),V=(0,l.up)("q-card"),N=(0,l.up)("q-page");return(0,l.wg)(),(0,l.j4)(N,null,{default:(0,l.w5)((()=>[(0,l.Wm)(V,{class:"q-pa-lg"},{default:(0,l.w5)((()=>[(0,l.Wm)(i,{class:"q-mb-lg"},{default:(0,l.w5)((()=>[(0,l.Wm)(a,{icon:"home",label:"Spieldose"}),(0,l.Wm)(a,{icon:"album",label:"Browse albums"})])),_:1}),_.value?((0,l.wg)(),(0,l.j4)(y,{key:0},{default:(0,l.w5)((()=>[(0,l._)("div",d,[(0,l._)("div",p,[(0,l.Wm)(u,{modelValue:w.value,"onUpdate:modelValue":t[0]||(t[0]=e=>w.value=e),clearable:"",type:"search",outlined:"",dense:"",placeholder:"Text condition",hint:"Search albums with title",loading:S.value,disable:S.value,onKeydown:t[1]||(t[1]=(0,s.D2)((0,s.iM)((e=>E(!0)),["prevent"]),["enter"])),onClear:t[2]||(t[2]=e=>{P.value=!1,E(!0)}),error:P.value,errorMessage:"No albums found with specified condition",ref_key:"albumTitleRef",ref:q},{prepend:(0,l.w5)((()=>[(0,l.Wm)(r,{name:"filter_alt"})])),append:(0,l.w5)((()=>[(0,l.Wm)(r,{name:"search",class:"cursor-pointer",onClick:E})])),_:1},8,["modelValue","loading","disable","error"])]),(0,l._)("div",g,[(0,l.Wm)(m,{outlined:"",dense:"",modelValue:A.value,"onUpdate:modelValue":[t[3]||(t[3]=e=>A.value=e),t[4]||(t[4]=e=>E(!0))],options:k,"options-dense":"",label:"Sort field",disable:S.value},{"selected-item":(0,l.w5)((e=>[(0,l.Uk)((0,n.zw)(e.opt.label),1)])),_:1},8,["modelValue","disable"])]),(0,l._)("div",v,[(0,l.Wm)(m,{outlined:"",dense:"",modelValue:I.value,"onUpdate:modelValue":[t[5]||(t[5]=e=>I.value=e),t[6]||(t[6]=e=>E(!0))],options:x,"options-dense":"",label:"Sort order",disable:S.value},{"selected-item":(0,l.w5)((e=>[(0,l.Uk)((0,n.zw)(e.opt.label),1)])),_:1},8,["modelValue","disable"])])]),C.value>1?((0,l.wg)(),(0,l.iD)("div",h,[(0,l.Wm)(c,{modelValue:f.value,"onUpdate:modelValue":[t[7]||(t[7]=e=>f.value=e),Q],color:"dark",max:C.value,"max-pages":5,"boundary-numbers":"","direction-links":"","boundary-links":"",disable:S.value},null,8,["modelValue","max","disable"])])):(0,l.kq)("",!0),(0,l._)("div",b,[((0,l.wg)(!0),(0,l.iD)(l.HY,null,(0,l.Ko)(_.value,(e=>((0,l.wg)(),(0,l.j4)(o.Z,{key:e.mbId||e.title,image:e.image,title:e.title,artistName:e.artist.name,year:e.year,onPlay:t=>U(e)},null,8,["image","title","artistName","year","onPlay"])))),128))])])),_:1})):(0,l.kq)("",!0)])),_:1})])),_:1})}}};var w=a(9885),k=a(4458),A=a(2605),x=a(8052),I=a(3190),P=a(6611),S=a(2857),_=a(8401),C=a(996),f=a(9984),q=a.n(f);const E=y,Q=E;q()(y,"components",{QPage:w.Z,QCard:k.Z,QBreadcrumbs:A.Z,QBreadcrumbsEl:x.Z,QCardSection:I.Z,QInput:P.Z,QIcon:S.Z,QSelect:_.Z,QPagination:C.Z})}}]);