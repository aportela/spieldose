"use strict";(globalThis["webpackChunkspieldose"]=globalThis["webpackChunkspieldose"]||[]).push([[691],{2719:(e,t,a)=>{a.d(t,{d:()=>u});var l=a(1809),s=a(8612),n=a.n(s);const i={namespace:"spieldose",storages:["local","cookie","session","memory"],storage:"local",expireDays:3650},u=(0,l.Q_)("currentPlaylist",{state:()=>({elements:[],currentIndex:-1,elementsLastChangeTimestamp:null}),getters:{hasElements:e=>e.elements&&e.elements.length>0,getElements:e=>e.elements,getElementsLastChangeTimestamp:e=>e.elementsLastChangeTimestamp,getCurrentIndex:e=>e.currentIndex,getCurrentElement:e=>e.currentIndex>=0&&e.elements.length>0?e.elements[e.currentIndex]:null,getCurrentElementURL:e=>e.currentIndex>=0&&e.elements.length>0?e.elements[e.currentIndex].url:null,allowSkipPrevious:e=>e.currentIndex>0&&e.elements.length>0,allowSkipNext:e=>e.currentIndex>=0&&e.elements.length>0&&e.currentIndex<e.elements.length-1,allowPlay:e=>!0,allowPause:e=>!0,allowResume:e=>!0,allowStop:e=>!0},actions:{load(){const e=n()(i),t=e.get("currentPlaylistElements");t&&(this.elements=t);const a=e.get("currentPlaylistElementIndex");a>=0&&(this.currentIndex=a)},saveElements(e){this.elements=e,this.currentIndex=e&&e.length>0?0:-1;const t=n()(i);t.set("currentPlaylistElements",e),t.set("currentPlaylistElementIndex",e&&e.length>0?0:-1),this.elementsLastChangeTimestamp=Date.now()},appendElements(e){this.elements=this.elements.concat(e);const t=n()(i);t.set("currentPlaylistElements",this.elements),t.set("currentPlaylistElementIndex",this.currentIndex),this.elementsLastChangeTimestamp=Date.now()},saveCurrentTrackIndex(e){this.currentIndex=e>=0?e:-1;const t=n()(i);t.set("currentPlaylistElementIndex",this.currentIndex)},skipPrevious(){this.currentIndex--},skipNext(){this.currentIndex++}}})},4958:(e,t,a)=>{a.d(t,{n:()=>i});var l=a(1809),s=a(1320);const n=(0,s.l)(),i=(0,l.Q_)("player",{state:()=>({element:null,hasPreviousUserInteractions:!1}),getters:{getElement:e=>e.element,getDuration:e=>e.element.duration},actions:{interact(){this.hasPreviousUserInteractions=!0},create(){this.element=document.createElement("audio"),this.element.id="audio_player",this.element.crossOrigin="anonymous"},setVolume(e){this.element.volume=e},setCurrentTime(e){this.element.currentTime=e},stop(){this.element.pause(),n.setStatusStopped()},load(){},play(e){e?(this.element.play(),n.setStatusPlaying()):n.isPlaying?(this.element.pause(),n.setStatusPaused()):n.isPaused?(this.element.play(),n.setStatusPlaying()):(this.element.load(),this.element.play(),n.setStatusPlaying())}}})},1320:(e,t,a)=>{a.d(t,{l:()=>s});var l=a(1809);const s=(0,l.Q_)("playerStatus",{state:()=>({status:"stopped"}),getters:{getStatus(e){return e.status},isPlaying(e){return"playing"==e.status},isStopped(e){return"stopped"==e.status},isPaused(e){return"paused"==e.status}},actions:{setStatusPlaying(){this.status="playing"},setStatusStopped(){this.status="stopped"},setStatusPaused(){this.status="paused"}}})},6729:(e,t,a)=>{a.d(t,{Z:()=>A});var l=a(9835),s=a(6970),n=a(499);const i={class:"animated-album-cover-item"},u={class:"play-album"},r={class:"album-actions"},o=(0,l._)("span",{class:"clear: both;"},null,-1),m={key:0,class:"vinyl no-cover",src:"images/vinyl.png"},c={class:"album-info"},d=["title"],p={key:1,class:"artist-name"},v={key:0},g={key:2},b={__name:"AnimatedAlbumCover",props:{title:String,artistName:String,year:Number,image:String},emits:["play","enqueue"],setup(e,{emit:t}){const a=e,b=(0,n.iH)(!1),h=(0,n.iH)(!1),y=(0,l.Fl)((()=>a.image&&!h.value?a.image:"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII="));function w(){b.value=!0}function k(){h.value=!0}function x(){t("play")}function A(){t("enqueue")}return(t,a)=>{const n=(0,l.up)("q-img"),I=(0,l.up)("q-icon"),P=(0,l.up)("router-link");return(0,l.wg)(),(0,l.iD)("div",i,[(0,l._)("div",u,[(0,l.Wm)(n,{class:(0,s.C_)(["album-thumbnail",{"album-thumbnail-animated":b.value||h.value}]),src:y.value,width:"174px",height:"174px","spinner-color":"pink",onLoad:w,onError:k},null,8,["class","src"]),(0,l._)("div",r,[(0,l.Wm)(I,{class:"cursor-pointer",name:"add_box",size:"80px",color:"pink",title:"Enqueue album",style:{left:"10px"},onClick:A}),(0,l.Wm)(I,{class:"cursor-pointer",name:"play_arrow",size:"80px",color:"pink",title:"Play album",style:{left:"80px"},onClick:x}),o]),b.value||h.value?((0,l.wg)(),(0,l.iD)("img",m)):(0,l.kq)("",!0)]),(0,l._)("div",c,[e.title?((0,l.wg)(),(0,l.iD)("p",{key:0,class:"album-name",title:e.title},(0,s.zw)(e.title),9,d)):(0,l.kq)("",!0),e.artistName?((0,l.wg)(),(0,l.iD)("p",p,[(0,l.Uk)("by "),(0,l.Wm)(P,{title:e.artistName,to:{name:"artist",params:{name:e.artistName}}},{default:(0,l.w5)((()=>[(0,l.Uk)((0,s.zw)(e.artistName),1)])),_:1},8,["title","to"]),(0,l.Uk)(),e.year?((0,l.wg)(),(0,l.iD)("span",v,"("+(0,s.zw)(e.year)+")",1)):(0,l.kq)("",!0)])):e.year?((0,l.wg)(),(0,l.iD)("p",g,"("+(0,s.zw)(e.year)+")",1)):(0,l.kq)("",!0)])])}}};var h=a(335),y=a(2857),w=a(9984),k=a.n(w);const x=b,A=x;k()(b,"components",{QImg:h.Z,QIcon:y.Z})},8691:(e,t,a)=>{a.r(t),a.d(t,{default:()=>V});var l=a(9835),s=a(6970),n=a(1957),i=a(499),u=a(1569),r=a(9302),o=a(6729),m=a(4958),c=a(2719);const d={class:"row q-gutter-xs"},p={class:"col-xl-2 col-lg-2 col-md-3 col-sm-4 col-xs-4"},v={class:"col"},g={class:"col-xl-1 col-lg-2 col-md-3 col-sm-4 col-xs-4"},b={class:"col-xl-1 col-lg-2 col-md-3 col-sm-4 col-xs-4"},h={key:0,class:"q-pa-lg flex flex-center"},y={class:"q-gutter-md row items-start"},w={__name:"BrowseAlbumsPage",setup(e){const t=(0,r.Z)(),a=(0,m.n)(),w=(0,c.d)(),k=(0,i.iH)(null),x=[{label:"Title",value:"title"},{label:"Artist",value:"albumArtistName"},{label:"Title & Artist",value:"all"}],A=(0,i.iH)(x[2]),I=[{label:"Title",value:"title"},{label:"Artist",value:"albumArtistName"},{label:"Year",value:"year"}],P=(0,i.iH)(I[0]),S=[{label:"Ascending",value:"ASC"},{label:"Descending",value:"DESC"}],_=(0,i.iH)(S[0]),f=(0,i.iH)(!1),C=(0,i.iH)(!1),E=(0,i.iH)([]),q=(0,i.iH)(0),N=(0,i.iH)(1),V=(0,i.iH)(null);function T(e){e&&(N.value=1),f.value=!1,C.value=!0;let a={title:"title"==A.value.value?k.value:null,albumArtistName:"albumArtistName"==A.value.value?k.value:null,text:"all"==A.value.value?k.value:null};u.api.album.search(a,N.value,32,P.value.value,_.value.value).then((e=>{E.value=e.data.data.items.map((e=>(e.image=e.covers.small,e))),q.value=e.data.data.pager.totalPages,k.value&&e.data.data.pager.totalResults<1&&(f.value=!0),(0,l.Y3)((()=>{V.value.$el.focus()})),C.value=!1})).catch((e=>{t.notify({type:"negative",message:"API Error: error loading albums",caption:"API Error: fatal error details: HTTP {"+e.response.status+"} ({"+e.response.statusText+"})"}),C.value=!1}))}function U(e){N.value=e,T(!1)}function Q(e){a.interact(),C.value=!0,u.api.track.search({albumMbId:e.mbId},1,0,!1,"trackNumber","ASC").then((e=>{w.saveElements(e.data.data.items.map((e=>({track:e})))),C.value=!1})).catch((e=>{C.value=!1}))}function D(e){a.interact(),C.value=!0,u.api.track.search({albumMbId:e.mbId},1,0,!1,"trackNumber","ASC").then((e=>{w.appendElements(e.data.data.items.map((e=>({track:e})))),C.value=!1})).catch((e=>{C.value=!1}))}return T(!0),(e,t)=>{const a=(0,l.up)("q-breadcrumbs-el"),i=(0,l.up)("q-breadcrumbs"),u=(0,l.up)("q-select"),r=(0,l.up)("q-icon"),m=(0,l.up)("q-input"),c=(0,l.up)("q-pagination"),w=(0,l.up)("q-card-section"),H=(0,l.up)("q-card"),W=(0,l.up)("q-page");return(0,l.wg)(),(0,l.j4)(W,null,{default:(0,l.w5)((()=>[(0,l.Wm)(H,{class:"q-pa-lg"},{default:(0,l.w5)((()=>[(0,l.Wm)(i,{class:"q-mb-lg"},{default:(0,l.w5)((()=>[(0,l.Wm)(a,{icon:"home",label:"Spieldose"}),(0,l.Wm)(a,{icon:"album",label:"Browse albums"})])),_:1}),E.value?((0,l.wg)(),(0,l.j4)(w,{key:0},{default:(0,l.w5)((()=>[(0,l._)("div",d,[(0,l._)("div",p,[(0,l.Wm)(u,{outlined:"",dense:"",modelValue:A.value,"onUpdate:modelValue":[t[0]||(t[0]=e=>A.value=e),t[1]||(t[1]=e=>T(!0))],options:x,"options-dense":"",label:"Search on",disable:C.value},{"selected-item":(0,l.w5)((e=>[(0,l.Uk)((0,s.zw)(e.opt.label),1)])),_:1},8,["modelValue","disable"])]),(0,l._)("div",v,[(0,l.Wm)(m,{modelValue:k.value,"onUpdate:modelValue":t[2]||(t[2]=e=>k.value=e),clearable:"",type:"search",outlined:"",dense:"",placeholder:"Text condition",hint:"Search albums with specified condition",loading:C.value,disable:C.value,onKeydown:t[3]||(t[3]=(0,n.D2)((0,n.iM)((e=>T(!0)),["prevent"]),["enter"])),onClear:t[4]||(t[4]=e=>{f.value=!1,T(!0)}),error:f.value,errorMessage:"No albums found with specified condition",ref_key:"searchTextRef",ref:V},{prepend:(0,l.w5)((()=>[(0,l.Wm)(r,{name:"filter_alt"})])),append:(0,l.w5)((()=>[(0,l.Wm)(r,{name:"search",class:"cursor-pointer",onClick:T})])),_:1},8,["modelValue","loading","disable","error"])]),(0,l._)("div",g,[(0,l.Wm)(u,{outlined:"",dense:"",modelValue:P.value,"onUpdate:modelValue":[t[5]||(t[5]=e=>P.value=e),t[6]||(t[6]=e=>T(!0))],options:I,"options-dense":"",label:"Sort field",disable:C.value},{"selected-item":(0,l.w5)((e=>[(0,l.Uk)((0,s.zw)(e.opt.label),1)])),_:1},8,["modelValue","disable"])]),(0,l._)("div",b,[(0,l.Wm)(u,{outlined:"",dense:"",modelValue:_.value,"onUpdate:modelValue":[t[7]||(t[7]=e=>_.value=e),t[8]||(t[8]=e=>T(!0))],options:S,"options-dense":"",label:"Sort order",disable:C.value},{"selected-item":(0,l.w5)((e=>[(0,l.Uk)((0,s.zw)(e.opt.label),1)])),_:1},8,["modelValue","disable"])])]),q.value>1?((0,l.wg)(),(0,l.iD)("div",h,[(0,l.Wm)(c,{modelValue:N.value,"onUpdate:modelValue":[t[9]||(t[9]=e=>N.value=e),U],color:"dark",max:q.value,"max-pages":5,"boundary-numbers":"","direction-links":"","boundary-links":"",disable:C.value},null,8,["modelValue","max","disable"])])):(0,l.kq)("",!0),(0,l._)("div",y,[((0,l.wg)(!0),(0,l.iD)(l.HY,null,(0,l.Ko)(E.value,(e=>((0,l.wg)(),(0,l.j4)(o.Z,{key:e.mbId||e.title,image:e.image,title:e.title,artistName:e.artist.name,year:e.year,onPlay:t=>Q(e),onEnqueue:t=>D(e)},null,8,["image","title","artistName","year","onPlay","onEnqueue"])))),128))])])),_:1})):(0,l.kq)("",!0)])),_:1})])),_:1})}}};var k=a(9885),x=a(4458),A=a(2605),I=a(8052),P=a(3190),S=a(8401),_=a(6611),f=a(2857),C=a(996),E=a(9984),q=a.n(E);const N=w,V=N;q()(w,"components",{QPage:k.Z,QCard:x.Z,QBreadcrumbs:A.Z,QBreadcrumbsEl:I.Z,QCardSection:P.Z,QSelect:S.Z,QInput:_.Z,QIcon:f.Z,QPagination:C.Z})}}]);