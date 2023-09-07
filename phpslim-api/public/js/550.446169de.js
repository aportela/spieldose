"use strict";(globalThis["webpackChunkspieldose"]=globalThis["webpackChunkspieldose"]||[]).push([[550],{2719:(e,t,a)=>{a.d(t,{d:()=>o});var s=a(1809),l=a(8612),n=a.n(l);const r={namespace:"spieldose",storages:["local","cookie","session","memory"],storage:"local",expireDays:3650},o=(0,s.Q_)("currentPlaylist",{state:()=>({elements:[],currentIndex:-1}),getters:{hasElements:e=>e.elements&&e.elements.length>0,getElements:e=>e.elements,getCurrentIndex:e=>e.currentIndex,getCurrentElement:e=>e.currentIndex>=0&&e.elements.length>0?e.elements[e.currentIndex]:null,getCurrentElementURL:e=>e.currentIndex>=0&&e.elements.length>0?e.elements[e.currentIndex].url:null,allowSkipPrevious:e=>e.currentIndex>0&&e.elements.length>0,allowSkipNext:e=>e.currentIndex>=0&&e.elements.length>0&&e.currentIndex<e.elements.length-1,allowPlay:e=>!0,allowPause:e=>!0,allowResume:e=>!0,allowStop:e=>!0},actions:{load(){const e=n()(r),t=e.get("currentPlaylistElements");t&&(this.elements=t);const a=e.get("currentPlaylistElementIndex");a>=0&&(this.currentIndex=a)},saveElements(e){this.elements=e,this.currentIndex=e&&e.length>0?0:-1;const t=n()(r);t.set("currentPlaylistElements",e),t.set("currentPlaylistElementIndex",e&&e.length>0?0:-1)},saveCurrentTrackIndex(e){this.currentIndex=e>=0?e:-1;const t=n()(r);t.set("currentPlaylistElementIndex",this.currentIndex)},skipPrevious(){this.currentIndex--},skipNext(){this.currentIndex++}}})},4958:(e,t,a)=>{a.d(t,{n:()=>r});var s=a(1809),l=a(1320);const n=(0,l.l)(),r=(0,s.Q_)("player",{state:()=>({element:null,hasPreviousUserInteractions:!1}),getters:{getElement:e=>e.element,getDuration:e=>e.element.duration},actions:{interact(){this.hasPreviousUserInteractions=!0},create(){this.element=document.createElement("audio"),this.element.id="audio_player"},setVolume(e){this.element.volume=e},setCurrentTime(e){this.element.currentTime=e},stop(){this.element.pause(),n.setStatusStopped()},load(){},play(e){if(e){const e=this.element.play();void 0!==e&&e.then((()=>{})).catch((e=>{this.element.playback.pause(),this.element.currentTime=0})),n.setStatusPlaying()}else n.isPlaying?(this.element.pause(),n.setStatusPaused()):n.isPaused?(this.element.play(),n.setStatusPlaying()):(this.element.load(),this.element.play(),n.setStatusPlaying())}}})},1320:(e,t,a)=>{a.d(t,{l:()=>l});var s=a(1809);const l=(0,s.Q_)("playerStatus",{state:()=>({status:"stopped"}),getters:{getStatus(e){return e.status},isPlaying(e){return"playing"==e.status},isStopped(e){return"stopped"==e.status},isPaused(e){return"paused"==e.status}},actions:{setStatusPlaying(){this.status="playing"},setStatusStopped(){this.status="stopped"},setStatusPaused(){this.status="paused"}}})},5550:(e,t,a)=>{a.r(t),a.d(t,{default:()=>Qe});var s=a(9835),l=a(499),n=a(7712),r=a(6970),o=a(4376),i=a(9302);const u={class:"text-h6 row"},m={class:"col text-left"},c={class:"col text-right"},p={key:1},d={__name:"DashboardBaseBlock",props:{className:{type:String},loading:{type:Boolean},icon:{type:String},title:{type:String}},emits:["refresh"],setup(e,{emit:t}){const{t:a}=(0,n.QT)();function o(){t("refresh")}return(t,n)=>{const i=(0,s.up)("q-icon"),d=(0,s.up)("q-spinner"),y=(0,s.up)("q-card-section"),g=(0,s.up)("q-skeleton"),k=(0,s.up)("q-card");return(0,s.wg)(),(0,s.j4)(k,{class:"my-card q-ma-md"},{default:(0,s.w5)((()=>[(0,s.Wm)(y,{class:"bg-grey-3 text-black"},{default:(0,s.w5)((()=>[(0,s._)("div",u,[(0,s._)("div",m,[(0,s.Wm)(i,{name:e.icon,class:"cursor-pointer q-mr-sm"},null,8,["name"]),(0,s.Uk)((0,r.zw)(e.title),1)]),(0,s._)("div",c,[e.loading?((0,s.wg)(),(0,s.j4)(d,{key:0,color:"pink",size:"sm",class:"q-ml-sm",thickness:8})):((0,s.wg)(),(0,s.j4)(i,{key:1,name:"refresh",class:"cursor-pointer",onClick:o,title:(0,l.SU)(a)("reload")},null,8,["title"]))])])])),_:1}),(0,s.Wm)(y,{class:"bg-white text-black"},{default:(0,s.w5)((()=>[(0,s.WI)(t.$slots,"body",{},(()=>[e.loading?((0,s.wg)(),(0,s.j4)(g,{key:0,type:"text",square:"",animation:"blink",height:"198px"})):((0,s.wg)(),(0,s.iD)("div",p,[(0,s.WI)(t.$slots,"tabs"),(0,s.WI)(t.$slots,"list"),(0,s.WI)(t.$slots,"chart")]))]))])),_:3})])),_:3})}}};var y=a(4458),g=a(3190),k=a(2857),w=a(3902),b=a(7133),v=a(9984),h=a.n(v);const D=d,_=D;h()(d,"components",{QCard:y.Z,QCardSection:g.Z,QIcon:k.Z,QSpinner:w.Z,QSkeleton:b.ZP});var f=a(4958),P=a(2719);const q={key:0,class:"is-size-6-5 dashboard_list_item"},x={key:0},W={__name:"DashboardBaseBlockListElementTrack",props:{track:{type:Object}},setup(e){const{t}=(0,n.QT)(),a=(0,f.n)(),o=(0,P.d)();function i(e){a.stop(),o.saveElements([{track:e}]),a.interact(),a.play(!1)}return(a,n)=>{const o=(0,s.up)("q-icon"),u=(0,s.up)("router-link");return e.track?((0,s.wg)(),(0,s.iD)("li",q,[(0,s.WI)(a.$slots,"prepend"),(0,s.Wm)(o,{name:"play_arrow",size:"sm",title:(0,l.SU)(t)("play track"),class:"cursor-pointer q-mr-xs",onClick:n[0]||(n[0]=t=>i(e.track))},null,8,["title"]),(0,s._)("span",null,(0,r.zw)(e.track.title),1),e.track.artist.name?((0,s.wg)(),(0,s.iD)("span",x,[(0,s.Uk)(" / "),(0,s.Wm)(u,{to:{name:"artist",params:{name:e.track.artist.name}}},{default:(0,s.w5)((()=>[(0,s.Uk)((0,r.zw)(e.track.artist.name),1)])),_:1},8,["to"])])):(0,s.kq)("",!0),(0,s.WI)(a.$slots,"append")])):(0,s.kq)("",!0)}}},Y=W,I=Y;h()(W,"components",{QIcon:k.Z});const T={key:0,class:"is-size-6-5 dashboard_list_item"},S={key:0},U={__name:"DashboardBaseBlockListElementArtist",props:{artist:{type:Object}},setup(e){return(t,a)=>{const l=(0,s.up)("q-icon"),n=(0,s.up)("router-link");return e.artist?((0,s.wg)(),(0,s.iD)("li",T,[(0,s.WI)(t.$slots,"prepend"),(0,s.Wm)(l,{name:"link",size:"sm",class:"q-mr-xs"}),e.artist.name?((0,s.wg)(),(0,s.iD)("span",S,[(0,s.Wm)(n,{to:{name:"artist",params:{name:e.artist.name}}},{default:(0,s.w5)((()=>[(0,s.Uk)((0,r.zw)(e.artist.name),1)])),_:1},8,["to"])])):(0,s.kq)("",!0),(0,s.WI)(t.$slots,"append")])):(0,s.kq)("",!0)}}},Z=U,A=Z;h()(U,"components",{QIcon:k.Z});const C={key:0,class:"is-size-6-5 dashboard_list_item"},Q={key:0},z={key:1},M={__name:"DashboardBaseBlockListElementAlbum",props:{album:{type:Object}},setup(e){return(t,a)=>{const l=(0,s.up)("q-icon"),n=(0,s.up)("router-link");return e.album?((0,s.wg)(),(0,s.iD)("li",C,[(0,s.WI)(t.$slots,"prepend"),(0,s.Wm)(l,{name:"album",size:"sm",class:"q-mr-xs"}),(0,s._)("span",null,(0,r.zw)(e.album.title),1),e.album.year?((0,s.wg)(),(0,s.iD)("span",Q," ("+(0,r.zw)(e.album.year)+") ",1)):(0,s.kq)("",!0),e.album.albumArtistName?((0,s.wg)(),(0,s.iD)("span",z,[(0,s.Uk)(" / "),(0,s.Wm)(n,{to:{name:"artist",params:{name:e.album.albumArtistName}}},{default:(0,s.w5)((()=>[(0,s.Uk)((0,r.zw)(e.album.albumArtistName),1)])),_:1},8,["to"])])):(0,s.kq)("",!0),(0,s.WI)(t.$slots,"append")])):(0,s.kq)("",!0)}}},E=M,N=E;h()(M,"components",{QIcon:k.Z});const H={key:0,class:"is-size-6-5 dashboard_list_item"},B={__name:"DashboardBaseBlockListElementGenre",props:{genre:{type:Object}},setup(e){return(t,a)=>{const l=(0,s.up)("q-icon");return e.genre?((0,s.wg)(),(0,s.iD)("li",H,[(0,s.WI)(t.$slots,"prepend"),(0,s.Wm)(l,{name:"tag",size:"sm"}),(0,s._)("span",null,(0,r.zw)(e.genre.name),1),(0,s.WI)(t.$slots,"append")])):(0,s.kq)("",!0)}}},j=B,L=j;h()(B,"components",{QIcon:k.Z});const $={__name:"LabelTimestampAgo",props:{className:{type:String},timestamp:{type:Number}},setup(e){const t=e,{t:a}=(0,n.QT)();let i="",u=o.ZP.getDateDiff(Date.now(),t.timestamp,"years");return u<1?(u=o.ZP.getDateDiff(Date.now(),t.timestamp,"months"),u<1?(u=o.ZP.getDateDiff(Date.now(),t.timestamp,"days"),u<1?(u=o.ZP.getDateDiff(Date.now(),t.timestamp,"hours"),u<1?(u=o.ZP.getDateDiff(Date.now(),t.timestamp,"minutes"),u<1?(u=o.ZP.getDateDiff(Date.now(),t.timestamp,"seconds"),i=a(u>1?"nSecondsAgo":"oneSecondAgo",{count:u})):i=a(u>1?"nMinutesAgo":"oneMinuteAgo",{count:u})):i=a(u>1?"nHoursAgo":"oneHourAgo",{count:u})):i=a(u>1?"nDaysAgo":"oneDayAgo",{count:u})):i=a(u>1?"nMonthsAgo":"oneMonthAgo",{count:u})):i=a(u>1?"nYearsAgo":"oneYearAgo",{count:u}),(t,a)=>((0,s.wg)(),(0,s.iD)("span",{class:(0,r.C_)(e.className)},[(0,s.WI)(t.$slots,"prepend"),(0,s.Uk)((0,r.zw)((0,l.SU)(i)),1),(0,s.WI)(t.$slots,"append")],2))}},K=$,V=K;var R=a(1569);const O={key:0,class:"q-px-sm"},F={class:"q-ml-sm"},G={key:1,class:"q-px-sm"},J={class:"q-ml-sm"},X={key:2,class:"q-px-sm"},ee={class:"q-ml-sm"},te={key:3,class:"q-px-sm"},ae={class:"q-ml-sm"},se={key:4,class:"text-h5 text-center"},le=5,ne={__name:"DashboardBaseBlockTop",props:{icon:{type:String},entity:{type:String}},setup(e){const t=e,a=(0,i.Z)(),{t:u}=(0,n.QT)(),m=(0,l.iH)(!1),c=(0,l.iH)([]),p=(0,l.iH)(null);let d={fromDate:null,toDate:null};(0,s.YP)(p,(e=>{switch(d={fromDate:null,toDate:null},e){case"today":d.fromDate=o.ZP.formatDate(Date.now(),"YYYYMMDD"),d.toDate=o.ZP.formatDate(Date.now(),"YYYYMMDD");break;case"lastWeek":d.fromDate=o.ZP.formatDate(o.ZP.addToDate(Date.now(),{days:-7}),"YYYYMMDD"),d.toDate=o.ZP.formatDate(Date.now(),"YYYYMMDD");break;case"lastMonth":d.fromDate=o.ZP.formatDate(o.ZP.addToDate(Date.now(),{months:-1}),"YYYYMMDD"),d.toDate=o.ZP.formatDate(Date.now(),"YYYYMMDD");break;case"lastYear":d.fromDate=o.ZP.formatDate(o.ZP.addToDate(Date.now(),{years:-1}),"YYYYMMDD"),d.toDate=o.ZP.formatDate(Date.now(),"YYYYMMDD");break;case"always":break}w()}));const y=[{label:"Today",value:"today"},{label:"Last week",value:"lastWeek"},{label:"Last month",value:"lastMonth"},{label:"Last year",value:"lastYear"},{label:"Always",value:"always"}],g=(0,s.Fl)((()=>{switch(t.entity){case"tracks":return u("Top played tracks");case"artists":return u("Top played artists");case"albums":return u("Top played albums");case"genres":return u("Top played genres");default:return null}}));let k=null;switch(t.entity){case"tracks":k=R.api.metrics.getTracks;break;case"artists":k=R.api.metrics.getArtists;break;case"albums":k=R.api.metrics.getAlbums;break;case"genres":k=R.api.metrics.getGenres;break}function w(){p.value&&(m.value=!0,k(d,"playCount",le).then((e=>{c.value=e.data.data,m.value=!1})).catch((e=>{m.value=!1,a.notify({type:"negative",message:"API Error: error loading metrics",caption:u("API Error: fatal error details",{status:e.response.status,statusText:e.response.statusText})})})))}return p.value="always",(t,a)=>{const n=(0,s.up)("q-tab"),o=(0,s.up)("q-tabs"),i=(0,s.up)("q-icon");return(0,s.wg)(),(0,s.j4)((0,s.LL)(_),{icon:e.icon||"format_list_numbered",title:g.value,loading:m.value,onRefresh:w},{tabs:(0,s.w5)((()=>[(0,s.Wm)(o,{modelValue:p.value,"onUpdate:modelValue":a[0]||(a[0]=e=>p.value=e),"no-caps":"",class:"text-pink-7 q-mb-md"},{default:(0,s.w5)((()=>[((0,s.wg)(),(0,s.iD)(s.HY,null,(0,s.Ko)(y,(e=>(0,s.Wm)(n,{key:e.value,name:e.value,label:(0,l.SU)(u)(e.label)},null,8,["name","label"]))),64))])),_:1},8,["modelValue"])])),list:(0,s.w5)((()=>["tracks"==e.entity?((0,s.wg)(),(0,s.iD)("ol",O,[((0,s.wg)(!0),(0,s.iD)(s.HY,null,(0,s.Ko)(c.value,(e=>((0,s.wg)(),(0,s.j4)(I,{key:e.id,track:e.track},{append:(0,s.w5)((()=>[(0,s._)("span",F,(0,r.zw)(e.playCount)+" "+(0,r.zw)((0,l.SU)(u)(e.playCount>1?"nPlayCounts":"onePlayCount")),1)])),_:2},1032,["track"])))),128))])):"artists"==e.entity?((0,s.wg)(),(0,s.iD)("ol",G,[((0,s.wg)(!0),(0,s.iD)(s.HY,null,(0,s.Ko)(c.value,(e=>((0,s.wg)(),(0,s.j4)(A,{key:e.id,artist:e},{append:(0,s.w5)((()=>[(0,s._)("span",J,(0,r.zw)(e.playCount)+" "+(0,r.zw)((0,l.SU)(u)(e.playCount>1?"nPlayCounts":"onePlayCount")),1)])),_:2},1032,["artist"])))),128))])):"albums"==e.entity?((0,s.wg)(),(0,s.iD)("ol",X,[((0,s.wg)(!0),(0,s.iD)(s.HY,null,(0,s.Ko)(c.value,(e=>((0,s.wg)(),(0,s.j4)(N,{key:e.id,album:e},{append:(0,s.w5)((()=>[(0,s._)("span",ee,(0,r.zw)(e.playCount)+" "+(0,r.zw)((0,l.SU)(u)(e.playCount>1?"nPlayCounts":"onePlayCount")),1)])),_:2},1032,["album"])))),128))])):"genres"==e.entity?((0,s.wg)(),(0,s.iD)("ol",te,[((0,s.wg)(!0),(0,s.iD)(s.HY,null,(0,s.Ko)(c.value,(e=>((0,s.wg)(),(0,s.j4)(L,{key:e.id,genre:e},{append:(0,s.w5)((()=>[(0,s._)("span",ae,(0,r.zw)(e.playCount)+" "+(0,r.zw)((0,l.SU)(u)(e.playCount>1?"nPlayCounts":"onePlayCount")),1)])),_:2},1032,["genre"])))),128))])):(0,s.kq)("",!0),m.value||c.value&&c.value.length>0?(0,s.kq)("",!0):((0,s.wg)(),(0,s.iD)("h5",se,[(0,s.Wm)(i,{name:"warning",size:"xl"}),(0,s.Uk)(" No enought data")]))])),_:1},40,["icon","title","loading"])}}};var re=a(7817),oe=a(7661);const ie=ne,ue=ie;h()(ne,"components",{QTabs:re.Z,QTab:oe.Z,QIcon:k.Z});const me={key:0,class:"q-px-sm"},ce={key:1,class:"q-px-sm"},pe={key:2,class:"q-px-sm"},de={key:3,class:"q-px-sm"},ye={key:4,class:"text-h5 text-center"},ge=5,ke={__name:"DashboardBaseBlockRecently",props:{icon:{type:String},played:{type:Boolean},added:{type:Boolean}},setup(e){const t=e,a=(0,i.Z)(),{t:r}=(0,n.QT)(),o=(0,l.iH)(!1),u=(0,l.iH)([]),m=(0,s.Fl)((()=>{let e=null;return t.played?e=r("Recently played"):t.added&&(e=r("Recently added")),e})),c=(0,l.iH)(null);(0,s.YP)(c,(()=>{y()}));const p=[{label:"Tracks",value:"tracks",function:R.api.metrics.getTracks,listItemType:""},{label:"Artists",value:"artists",function:R.api.metrics.getArtists},{label:"Albums",value:"albums",function:R.api.metrics.getAlbums},{label:"Genres",value:"genres",function:R.api.metrics.getGenres}];let d={entity:"tracks"};function y(){if(c.value){u.value=[];const e=p.filter((e=>e.value==c.value))[0].function;o.value=!0;let s=null;t.played?s="recentlyPlayed":t.added&&(s="recentlyAdded"),e(d,s,ge).then((e=>{u.value=e.data.data,o.value=!1})).catch((e=>{o.value=!1,a.notify({type:"negative",message:"API Error: error loading metrics",caption:r("API Error: fatal error details",{status:e.response.status,statusText:e.response.statusText})})}))}}return c.value="tracks",(t,a)=>{const n=(0,s.up)("q-tab"),i=(0,s.up)("q-tabs"),d=(0,s.up)("q-icon");return(0,s.wg)(),(0,s.j4)((0,s.LL)(_),{icon:e.icon||"format_list_numbered",title:m.value,loading:o.value,onRefresh:y},{tabs:(0,s.w5)((()=>[(0,s.Wm)(i,{modelValue:c.value,"onUpdate:modelValue":a[0]||(a[0]=e=>c.value=e),"no-caps":"",class:"text-pink-7 q-mb-md"},{default:(0,s.w5)((()=>[((0,s.wg)(),(0,s.iD)(s.HY,null,(0,s.Ko)(p,(e=>(0,s.Wm)(n,{key:e.value,name:e.value,label:(0,l.SU)(r)(e.label)},null,8,["name","label"]))),64))])),_:1},8,["modelValue"])])),list:(0,s.w5)((()=>["tracks"==c.value?((0,s.wg)(),(0,s.iD)("ol",me,[((0,s.wg)(!0),(0,s.iD)(s.HY,null,(0,s.Ko)(u.value,(t=>((0,s.wg)(),(0,s.j4)(I,{key:t.id,track:t.track},(0,s.Nv)({_:2},[e.played?{name:"append",fn:(0,s.w5)((()=>[(0,s.Wm)(V,{className:"q-ml-sm",timestamp:1e3*t.lastPlayTimestamp},{prepend:(0,s.w5)((()=>[(0,s.Uk)("(")])),append:(0,s.w5)((()=>[(0,s.Uk)(")")])),_:2},1032,["timestamp"])])),key:"0"}:e.added?{name:"append",fn:(0,s.w5)((()=>[(0,s.Wm)(V,{className:"q-ml-sm",timestamp:1e3*t.addedTimestamp},{prepend:(0,s.w5)((()=>[(0,s.Uk)("(")])),append:(0,s.w5)((()=>[(0,s.Uk)(")")])),_:2},1032,["timestamp"])])),key:"1"}:void 0]),1032,["track"])))),128))])):"artists"==c.value?((0,s.wg)(),(0,s.iD)("ol",ce,[((0,s.wg)(!0),(0,s.iD)(s.HY,null,(0,s.Ko)(u.value,(t=>((0,s.wg)(),(0,s.j4)(A,{key:t.id,artist:t},(0,s.Nv)({_:2},[e.played?{name:"append",fn:(0,s.w5)((()=>[(0,s.Wm)(V,{className:"q-ml-sm",timestamp:1e3*t.lastPlayTimestamp},{prepend:(0,s.w5)((()=>[(0,s.Uk)("(")])),append:(0,s.w5)((()=>[(0,s.Uk)(")")])),_:2},1032,["timestamp"])])),key:"0"}:e.added?{name:"append",fn:(0,s.w5)((()=>[(0,s.Wm)(V,{className:"q-ml-sm",timestamp:1e3*t.addedTimestamp},{prepend:(0,s.w5)((()=>[(0,s.Uk)("(")])),append:(0,s.w5)((()=>[(0,s.Uk)(")")])),_:2},1032,["timestamp"])])),key:"1"}:void 0]),1032,["artist"])))),128))])):"albums"==c.value?((0,s.wg)(),(0,s.iD)("ol",pe,[((0,s.wg)(!0),(0,s.iD)(s.HY,null,(0,s.Ko)(u.value,(t=>((0,s.wg)(),(0,s.j4)(N,{key:t.id,album:t},(0,s.Nv)({_:2},[e.played?{name:"append",fn:(0,s.w5)((()=>[(0,s.Wm)(V,{className:"q-ml-sm",timestamp:1e3*t.lastPlayTimestamp},{prepend:(0,s.w5)((()=>[(0,s.Uk)("(")])),append:(0,s.w5)((()=>[(0,s.Uk)(")")])),_:2},1032,["timestamp"])])),key:"0"}:e.added?{name:"append",fn:(0,s.w5)((()=>[(0,s.Wm)(V,{className:"q-ml-sm",timestamp:1e3*t.addedTimestamp},{prepend:(0,s.w5)((()=>[(0,s.Uk)("(")])),append:(0,s.w5)((()=>[(0,s.Uk)(")")])),_:2},1032,["timestamp"])])),key:"1"}:void 0]),1032,["album"])))),128))])):"genres"==c.value?((0,s.wg)(),(0,s.iD)("ol",de,[((0,s.wg)(!0),(0,s.iD)(s.HY,null,(0,s.Ko)(u.value,(t=>((0,s.wg)(),(0,s.j4)(L,{key:t.name,genre:t},(0,s.Nv)({_:2},[e.played?{name:"append",fn:(0,s.w5)((()=>[(0,s.Wm)(V,{className:"q-ml-sm",timestamp:1e3*t.lastPlayTimestamp},{prepend:(0,s.w5)((()=>[(0,s.Uk)("(")])),append:(0,s.w5)((()=>[(0,s.Uk)(")")])),_:2},1032,["timestamp"])])),key:"0"}:e.added?{name:"append",fn:(0,s.w5)((()=>[(0,s.Wm)(V,{className:"q-ml-sm",timestamp:1e3*t.addedTimestamp},{prepend:(0,s.w5)((()=>[(0,s.Uk)("(")])),append:(0,s.w5)((()=>[(0,s.Uk)(")")])),_:2},1032,["timestamp"])])),key:"1"}:void 0]),1032,["genre"])))),128))])):(0,s.kq)("",!0),o.value||u.value&&u.value.length>0?(0,s.kq)("",!0):((0,s.wg)(),(0,s.iD)("h5",ye,[(0,s.Wm)(d,{name:"warning",size:"xl"}),(0,s.Uk)(" No enought data")]))])),_:1},40,["icon","title","loading"])}}},we=ke,be=we;h()(ke,"components",{QTabs:re.Z,QTab:oe.Z,QIcon:k.Z});var ve=a(9844);const he=(0,s._)("div",{class:"ct-chart"},null,-1),De={__name:"DashboardBaseBlockChart",props:{icon:{type:String}},setup(e){const t=(0,i.Z)(),{t:a}=(0,n.QT)(),r=(0,l.iH)(!1),o=(0,l.iH)([]),u=(0,l.iH)(null);(0,s.YP)(u,(e=>{d()}));const m=[{label:"Hour",value:"hour"},{label:"Weekday",value:"weekday"},{label:"Month",value:"month"},{label:"Year",value:"year"}],c={low:0,showArea:!0,fullWidth:!0,chartPadding:{left:48,right:48}};function p(){let e=[];const t=o.value.map((e=>e.total));switch(u.value){case"hour":e=o.value.map((e=>e.hour));break;case"weekday":e=[a("Sunday"),a("Monday"),a("Tuesday"),a("Wednesday"),a("Thursday"),a("Friday"),a("Saturday")];break;case"month":e=[a("January"),a("February"),a("March"),a("April"),a("May"),a("June"),a("July"),a("August"),a("September"),a("October"),a("November"),a("December")];break;case"year":e=o.value.map((e=>e.year));break}e.length>1?new ve.wW(".ct-chart",{labels:e,series:[t]},c):new ve.vz(".ct-chart",{labels:e,series:[t]},c)}function d(){u.value&&(r.value=!0,R.api.metrics.getDataRanges(u.value).then((e=>{o.value=e.data.data,r.value=!1,(0,s.Y3)((()=>{p()}))})).catch((e=>{r.value=!1,t.notify({type:"negative",message:"API Error: error loading metrics",caption:a("API Error: fatal error details",{status:e.response.status,statusText:e.response.statusText})})})))}return u.value="hour",(t,n)=>{const o=(0,s.up)("q-tab"),i=(0,s.up)("q-tabs");return(0,s.wg)(),(0,s.j4)((0,s.LL)(_),{icon:e.icon||"analytics",title:(0,l.SU)(a)("Play stats"),loading:r.value,onRefresh:d},{tabs:(0,s.w5)((()=>[(0,s.Wm)(i,{modelValue:u.value,"onUpdate:modelValue":n[0]||(n[0]=e=>u.value=e),"no-caps":"",class:"text-pink-7 q-mb-md"},{default:(0,s.w5)((()=>[((0,s.wg)(),(0,s.iD)(s.HY,null,(0,s.Ko)(m,(e=>(0,s.Wm)(o,{key:e.value,name:e.value,label:(0,l.SU)(a)(e.label)},null,8,["name","label"]))),64))])),_:1},8,["modelValue"])])),chart:(0,s.w5)((()=>[he])),_:1},40,["icon","title","loading"])}}},_e=De,fe=_e;h()(De,"components",{QTabs:re.Z,QTab:oe.Z});const Pe={class:"row"},qe={class:"col-xl-4 col-lg-6 col-12"},xe={class:"col-xl-4 col-lg-6 col-12"},We={class:"col-xl-4 col-lg-6 col-12"},Ye={class:"col-xl-4 col-lg-6 col-12"},Ie={class:"col-xl-4 col-lg-6 col-12"},Te={class:"col-xl-4 col-lg-6 col-12"},Se={__name:"DashboardPage",setup(e){const{t}=(0,n.QT)();return(e,a)=>{const n=(0,s.up)("q-breadcrumbs-el"),r=(0,s.up)("q-breadcrumbs"),o=(0,s.up)("q-card"),i=(0,s.up)("q-page");return(0,s.wg)(),(0,s.j4)(i,null,{default:(0,s.w5)((()=>[(0,s.Wm)(o,{class:"q-pa-lg"},{default:(0,s.w5)((()=>[(0,s.Wm)(r,{class:"q-mb-lg"},{default:(0,s.w5)((()=>[(0,s.Wm)(n,{icon:"home",label:"Spieldose"}),(0,s.Wm)(n,{icon:"analytics",label:(0,l.SU)(t)("Dashboard")},null,8,["label"])])),_:1}),(0,s._)("div",Pe,[(0,s._)("div",qe,[(0,s.Wm)(ue,{entity:"tracks"})]),(0,s._)("div",xe,[(0,s.Wm)(ue,{entity:"artists"})]),(0,s._)("div",We,[(0,s.Wm)(ue,{entity:"albums"})]),(0,s._)("div",Ye,[(0,s.Wm)(ue,{entity:"genres"})]),(0,s._)("div",Ie,[(0,s.Wm)(be,{played:!0})]),(0,s._)("div",Te,[(0,s.Wm)(be,{added:!0})])]),(0,s.Wm)(fe)])),_:1})])),_:1})}}};var Ue=a(9885),Ze=a(2605),Ae=a(8052);const Ce=Se,Qe=Ce;h()(Se,"components",{QPage:Ue.Z,QCard:y.Z,QBreadcrumbs:Ze.Z,QBreadcrumbsEl:Ae.Z})}}]);