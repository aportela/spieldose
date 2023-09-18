"use strict";(globalThis["webpackChunkspieldose"]=globalThis["webpackChunkspieldose"]||[]).push([[164],{8106:(e,t,a)=>{a.d(t,{dC:()=>i,oS:()=>u});var l=a(6223),s=a(2719),n=a(1569),o=a(4867);const r=(0,s.d)(),i={setFavorite:function(e){return new Promise(((t,a)=>{n.api.track.setFavorite(e).then((a=>{o.spieldoseEvents.emit.track.setFavorite(e,a.data.favorited),t(a)})).catch((e=>{a(e)}))}))},unSetFavorite:function(e){return new Promise(((t,a)=>{n.api.track.unSetFavorite(e).then((a=>{o.spieldoseEvents.emit.track.unSetFavorite(e),t(a)})).catch((e=>{a(e)}))}))},play:function(e){l.spieldosePlayer.interact(),l.spieldosePlayer.isStopped()||l.spieldosePlayer.actions.stop(),r.saveElements(Array.isArray(e)?e:[{track:e}]),l.spieldosePlayer.actions.play(!0)},enqueue:function(e){player.interact(),r.appendElements(Array.isArray(e)?e:[{track:e}])}},u={saveElements:function(e){r.saveElements(e)},appendElements:function(e){r.appendElements(e)},clear:function(){r.clear()},skipPrevious:function(){r.skipPrevious()},skipNext:function(){r.skipNext()}}},2719:(e,t,a)=>{a.d(t,{d:()=>i});var l=a(1809),s=a(8612),n=a.n(s),o=a(6223);const r={namespace:"spieldose",storages:["local","cookie","session","memory"],storage:"local",expireDays:3650},i=(0,l.Q_)("currentPlaylist",{state:()=>({elements:[],shuffleIndexes:[],currentIndex:-1,elementsLastChangeTimestamp:null}),getters:{hasElements:e=>e.elements&&e.elements.length>0,getElements:e=>e.elements,elementCount:e=>e.elements?e.elements.length:0,getElementsLastChangeTimestamp:e=>e.elementsLastChangeTimestamp,getCurrentIndex:e=>o.spieldosePlayer.getShuffle()?e.shuffleIndexes[e.currentIndex]:e.currentIndex,getCurrentElement:e=>e.currentIndex>=0&&e.elements.length>0?e.elements[e.currentIndex]:null,getCurrentElementURL:e=>e.currentIndex>=0&&e.elements.length>0?e.elements[e.currentIndex].url:null,allowSkipPrevious:e=>e.currentIndex>0&&e.elements.length>0,allowSkipNext:e=>e.currentIndex>=0&&e.elements.length>0&&e.currentIndex<e.elements.length-1,allowPlay:e=>!0,allowPause:e=>!0,allowResume:e=>!0,allowStop:e=>!0},actions:{load(){const e=n()(r),t=e.get("currentPlaylistElements");t&&(this.elements=t);const a=e.get("shuffleIndexes");a&&(this.shuffleIndexes=a);const l=e.get("currentPlaylistElementIndex");l>=0&&(this.currentIndex=l)},saveCurrentElements(){const e=n()(r);e.set("currentPlaylistElements",this.elements),e.set("currentPlaylistElementIndex",this.currentIndex),e.set("shuffleIndexes",this.shuffleIndexes),this.elementsLastChangeTimestamp=Date.now()},saveElements(e){this.elements=e,this.shuffleIndexes=[...Array(e.length).keys()].sort((function(){return.5-Math.random()})),this.currentIndex=e&&e.length>0?0:-1,this.saveCurrentElements()},appendElements(e){this.elements=this.elements.concat(e),this.saveCurrentElements()},saveCurrentTrackIndex(e){this.currentIndex=e>=0?e:-1;const t=n()(r);t.set("currentPlaylistElementIndex",this.currentIndex)},skipPrevious(){this.saveCurrentTrackIndex(--this.currentIndex)},skipNext(){this.saveCurrentTrackIndex(++this.currentIndex)},clear(){this.elements=[],this.saveCurrentElements()}}})},5164:(e,t,a)=>{a.r(t),a.d(t,{default:()=>Qe});var l=a(9835),s=a(499),n=a(6970),o=a(9302),r=a(6647),i=a(4376);const u={class:"text-h6 row"},m={class:"col text-left"},c={class:"col text-right"},p={key:1},d={__name:"DashboardBaseBlock",props:{className:{type:String},loading:{type:Boolean},icon:{type:String},title:{type:String}},emits:["refresh"],setup(e,{emit:t}){const{t:a}=(0,r.QT)();function o(){t("refresh")}return(t,r)=>{const i=(0,l.up)("q-icon"),d=(0,l.up)("q-spinner"),g=(0,l.up)("q-card-section"),y=(0,l.up)("q-skeleton"),k=(0,l.up)("q-card");return(0,l.wg)(),(0,l.j4)(k,{class:"my-card q-ma-md"},{default:(0,l.w5)((()=>[(0,l.Wm)(g,{class:"bg-grey-3 text-black"},{default:(0,l.w5)((()=>[(0,l._)("div",u,[(0,l._)("div",m,[(0,l.Wm)(i,{name:e.icon,class:"cursor-pointer q-mr-sm"},null,8,["name"]),(0,l.Uk)((0,n.zw)(e.title),1)]),(0,l._)("div",c,[e.loading?((0,l.wg)(),(0,l.j4)(d,{key:0,color:"pink",size:"sm",class:"q-ml-sm",thickness:8})):((0,l.wg)(),(0,l.j4)(i,{key:1,name:"refresh",class:"cursor-pointer",onClick:o,title:(0,s.SU)(a)("reload")},null,8,["title"]))])])])),_:1}),(0,l.Wm)(g,{class:"bg-white text-black",style:{"min-height":"17em"}},{default:(0,l.w5)((()=>[(0,l.WI)(t.$slots,"body",{},(()=>[e.loading?((0,l.wg)(),(0,l.j4)(y,{key:0,type:"text",square:"",animation:"blink",height:"198px"})):((0,l.wg)(),(0,l.iD)("div",p,[(0,l.WI)(t.$slots,"tabs"),(0,l.WI)(t.$slots,"list"),(0,l.WI)(t.$slots,"chart")]))]))])),_:3})])),_:3})}}};var g=a(4458),y=a(3190),k=a(2857),w=a(3902),b=a(7133),v=a(9984),h=a.n(v);const D=d,f=D;h()(d,"components",{QCard:g.Z,QCardSection:y.Z,QIcon:k.Z,QSpinner:w.Z,QSkeleton:b.ZP});var q=a(8106);const _={key:0,class:"is-size-6-5 dashboard_list_item"},x={key:0},S={__name:"DashboardBaseBlockListElementTrack",props:{track:{type:Object}},setup(e){const{t}=(0,r.QT)();return(a,o)=>{const r=(0,l.up)("q-icon"),i=(0,l.up)("router-link");return e.track?((0,l.wg)(),(0,l.iD)("li",_,[(0,l.WI)(a.$slots,"prepend"),(0,l.Wm)(r,{name:"play_arrow",size:"sm",title:(0,s.SU)(t)("play track"),class:"cursor-pointer q-mr-xs",onClick:o[0]||(o[0]=t=>(0,s.SU)(q.dC).play(e.track))},null,8,["title"]),(0,l.Wm)(r,{name:"add_box",size:"sm",title:(0,s.SU)(t)("enqueue track"),class:"cursor-pointer q-mr-xs",onClick:o[1]||(o[1]=t=>(0,s.SU)(q.dC).enqueue(e.track))},null,8,["title"]),(0,l._)("span",null,(0,n.zw)(e.track.title),1),e.track.artist.name?((0,l.wg)(),(0,l.iD)("span",x,[(0,l.Uk)(" / "),(0,l.Wm)(i,{to:{name:"artist",params:{name:e.track.artist.name}}},{default:(0,l.w5)((()=>[(0,l.Uk)((0,n.zw)(e.track.artist.name),1)])),_:1},8,["to"])])):(0,l.kq)("",!0),(0,l.WI)(a.$slots,"append")])):(0,l.kq)("",!0)}}},P=S,W=P;h()(S,"components",{QIcon:k.Z});const I={key:0,class:"is-size-6-5 dashboard_list_item"},U={key:0},Y={__name:"DashboardBaseBlockListElementArtist",props:{artist:{type:Object}},setup(e){return(t,a)=>{const s=(0,l.up)("q-icon"),o=(0,l.up)("router-link");return e.artist?((0,l.wg)(),(0,l.iD)("li",I,[(0,l.WI)(t.$slots,"prepend"),(0,l.Wm)(s,{name:"link",size:"sm",class:"q-mr-xs"}),e.artist.name?((0,l.wg)(),(0,l.iD)("span",U,[(0,l.Wm)(o,{to:{name:"artist",params:{name:e.artist.name}}},{default:(0,l.w5)((()=>[(0,l.Uk)((0,n.zw)(e.artist.name),1)])),_:1},8,["to"])])):(0,l.kq)("",!0),(0,l.WI)(t.$slots,"append")])):(0,l.kq)("",!0)}}},T=Y,C=T;h()(Y,"components",{QIcon:k.Z});const Z={key:0,class:"is-size-6-5 dashboard_list_item"},A={key:0},E={key:1},z={__name:"DashboardBaseBlockListElementAlbum",props:{album:{type:Object}},setup(e){return(t,a)=>{const s=(0,l.up)("q-icon"),o=(0,l.up)("router-link");return e.album?((0,l.wg)(),(0,l.iD)("li",Z,[(0,l.WI)(t.$slots,"prepend"),(0,l.Wm)(s,{name:"album",size:"sm",class:"q-mr-xs"}),(0,l._)("span",null,(0,n.zw)(e.album.title),1),e.album.year?((0,l.wg)(),(0,l.iD)("span",A," ("+(0,n.zw)(e.album.year)+") ",1)):(0,l.kq)("",!0),e.album.albumArtistName?((0,l.wg)(),(0,l.iD)("span",E,[(0,l.Uk)(" / "),(0,l.Wm)(o,{to:{name:"artist",params:{name:e.album.albumArtistName}}},{default:(0,l.w5)((()=>[(0,l.Uk)((0,n.zw)(e.album.albumArtistName),1)])),_:1},8,["to"])])):(0,l.kq)("",!0),(0,l.WI)(t.$slots,"append")])):(0,l.kq)("",!0)}}},Q=z,H=Q;h()(z,"components",{QIcon:k.Z});const N={key:0,class:"is-size-6-5 dashboard_list_item"},M={__name:"DashboardBaseBlockListElementGenre",props:{genre:{type:Object}},setup(e){return(t,a)=>{const s=(0,l.up)("q-icon");return e.genre?((0,l.wg)(),(0,l.iD)("li",N,[(0,l.WI)(t.$slots,"prepend"),(0,l.Wm)(s,{name:"tag",size:"sm"}),(0,l._)("span",null,(0,n.zw)(e.genre.name),1),(0,l.WI)(t.$slots,"append")])):(0,l.kq)("",!0)}}},B=M,j=B;h()(M,"components",{QIcon:k.Z});var L=a(1569);const F={key:0,class:"q-px-sm"},$={class:"q-ml-sm"},K={key:1,class:"q-px-sm"},V={class:"q-ml-sm"},R={key:2,class:"q-px-sm"},G={class:"q-ml-sm"},O={key:3,class:"q-px-sm"},J={class:"q-ml-sm"},X={key:4},ee={key:0,class:"text-h5 text-center q-py-sm q-mt-xl q-mt-sm"},te={key:1,class:"text-h5 text-center q-py-sm q-mt-xl q-mt-sm"},ae=5,le={__name:"DashboardBaseBlockTop",props:{icon:{type:String},entity:{type:String},globalStats:Boolean},setup(e){const t=e,a=(0,o.Z)(),{t:u}=(0,r.QT)(),m=(0,s.iH)(!1),c=(0,s.iH)([]),p=(0,s.iH)(null),d=(0,l.Fl)((()=>t.globalStats||!1));(0,l.YP)(d,(e=>{g.global=d.value,h()}));let g={fromDate:null,toDate:null,global:d.value};(0,l.YP)(p,(e=>{switch(g={fromDate:null,toDate:null,global:d.value},e){case"today":g.fromDate=i.ZP.formatDate(Date.now(),"YYYYMMDD"),g.toDate=i.ZP.formatDate(Date.now(),"YYYYMMDD");break;case"lastWeek":g.fromDate=i.ZP.formatDate(i.ZP.addToDate(Date.now(),{days:-7}),"YYYYMMDD"),g.toDate=i.ZP.formatDate(Date.now(),"YYYYMMDD");break;case"lastMonth":g.fromDate=i.ZP.formatDate(i.ZP.addToDate(Date.now(),{months:-1}),"YYYYMMDD"),g.toDate=i.ZP.formatDate(Date.now(),"YYYYMMDD");break;case"lastYear":g.fromDate=i.ZP.formatDate(i.ZP.addToDate(Date.now(),{years:-1}),"YYYYMMDD"),g.toDate=i.ZP.formatDate(Date.now(),"YYYYMMDD");break;case"always":break}h()}));const y=[{label:"Today",value:"today"},{label:"Last week",value:"lastWeek"},{label:"Last month",value:"lastMonth"},{label:"Last year",value:"lastYear"},{label:"Always",value:"always"}],k=(0,l.Fl)((()=>{switch(t.entity){case"tracks":return u("Top played tracks");case"artists":return u("Top played artists");case"albums":return u("Top played albums");case"genres":return u("Top played genres");default:return null}}));let w=null;switch(t.entity){case"tracks":w=L.api.metrics.getTracks;break;case"artists":w=L.api.metrics.getArtists;break;case"albums":w=L.api.metrics.getAlbums;break;case"genres":w=L.api.metrics.getGenres;break}const b=(0,s.iH)(Date.now()),v=(0,s.iH)(!1);function h(){v.value=!1,p.value&&(m.value=!0,w(g,"playCount",ae).then((e=>{c.value=e.data.data,b.value=Date.now(),m.value=!1})).catch((e=>{v.value=!0,m.value=!1,a.notify({type:"negative",message:"API Error: error loading metrics",caption:u("API Error: fatal error details",{status:e.response.status,statusText:e.response.statusText})})})))}return p.value="lastWeek",(t,a)=>{const o=(0,l.up)("q-tab"),r=(0,l.up)("q-tabs"),i=(0,l.up)("q-icon");return(0,l.wg)(),(0,l.j4)((0,l.LL)(f),{icon:e.icon||"format_list_numbered",title:k.value,loading:m.value,onRefresh:h},{tabs:(0,l.w5)((()=>[(0,l.Wm)(r,{modelValue:p.value,"onUpdate:modelValue":a[0]||(a[0]=e=>p.value=e),"no-caps":"",class:"text-pink-7 q-mb-md"},{default:(0,l.w5)((()=>[((0,l.wg)(),(0,l.iD)(l.HY,null,(0,l.Ko)(y,(e=>(0,l.Wm)(o,{key:e.value,name:e.value,label:(0,s.SU)(u)(e.label)},null,8,["name","label"]))),64))])),_:1},8,["modelValue"])])),list:(0,l.w5)((()=>["tracks"==e.entity?((0,l.wg)(),(0,l.iD)("ol",F,[((0,l.wg)(!0),(0,l.iD)(l.HY,null,(0,l.Ko)(c.value,((e,t,a,o)=>{const r=b.value;if(o&&o.key===e.id&&(0,l.nQ)(o,r))return o;const i=((0,l.wg)(),(0,l.j4)(W,{key:e.id,track:e.track},{append:(0,l.w5)((()=>[(0,l._)("span",$,(0,n.zw)(e.playCount)+" "+(0,n.zw)((0,s.SU)(u)(e.playCount>1?"nPlayCounts":"onePlayCount")),1)])),_:2},1032,["track"]));return i.memo=r,i}),a,1),128))])):"artists"==e.entity?((0,l.wg)(),(0,l.iD)("ol",K,[((0,l.wg)(!0),(0,l.iD)(l.HY,null,(0,l.Ko)(c.value,((e,t,a,o)=>{const r=b.value;if(o&&o.key===e.id&&(0,l.nQ)(o,r))return o;const i=((0,l.wg)(),(0,l.j4)(C,{key:e.id,artist:e},{append:(0,l.w5)((()=>[(0,l._)("span",V,(0,n.zw)(e.playCount)+" "+(0,n.zw)((0,s.SU)(u)(e.playCount>1?"nPlayCounts":"onePlayCount")),1)])),_:2},1032,["artist"]));return i.memo=r,i}),a,3),128))])):"albums"==e.entity?((0,l.wg)(),(0,l.iD)("ol",R,[((0,l.wg)(!0),(0,l.iD)(l.HY,null,(0,l.Ko)(c.value,((e,t,a,o)=>{const r=b.value;if(o&&o.key===e.id&&(0,l.nQ)(o,r))return o;const i=((0,l.wg)(),(0,l.j4)(H,{key:e.id,album:e},{append:(0,l.w5)((()=>[(0,l._)("span",G,(0,n.zw)(e.playCount)+" "+(0,n.zw)((0,s.SU)(u)(e.playCount>1?"nPlayCounts":"onePlayCount")),1)])),_:2},1032,["album"]));return i.memo=r,i}),a,5),128))])):"genres"==e.entity?((0,l.wg)(),(0,l.iD)("ol",O,[((0,l.wg)(!0),(0,l.iD)(l.HY,null,(0,l.Ko)(c.value,((e,t,a,o)=>{const r=b.value;if(o&&o.key===e.id&&(0,l.nQ)(o,r))return o;const i=((0,l.wg)(),(0,l.j4)(j,{key:e.id,genre:e},{append:(0,l.w5)((()=>[(0,l._)("span",J,(0,n.zw)(e.playCount)+" "+(0,n.zw)((0,s.SU)(u)(e.playCount>1?"nPlayCounts":"onePlayCount")),1)])),_:2},1032,["genre"]));return i.memo=r,i}),a,7),128))])):(0,l.kq)("",!0),m.value?(0,l.kq)("",!0):((0,l.wg)(),(0,l.iD)("div",X,[v.value?((0,l.wg)(),(0,l.iD)("h5",ee,[(0,l.Wm)(i,{name:"error",size:"xl"}),(0,l.Uk)(" "+(0,n.zw)((0,s.SU)(u)("Error loading data")),1)])):c.value&&c.value.length>0?(0,l.kq)("",!0):((0,l.wg)(),(0,l.iD)("h5",te,[(0,l.Wm)(i,{name:"warning",size:"xl"}),(0,l.Uk)(" "+(0,n.zw)((0,s.SU)(u)("No enought data")),1)]))]))])),_:1},40,["icon","title","loading"])}}};var se=a(7817),ne=a(7661);const oe=le,re=oe;h()(le,"components",{QTabs:se.Z,QTab:ne.Z,QIcon:k.Z});const ie={__name:"LabelTimestampAgo",props:{className:{type:String},timestamp:{type:Number}},setup(e){const t=e,{t:a}=(0,r.QT)();let o="",u=i.ZP.getDateDiff(Date.now(),t.timestamp,"years");return u<1?(u=i.ZP.getDateDiff(Date.now(),t.timestamp,"months"),u<1?(u=i.ZP.getDateDiff(Date.now(),t.timestamp,"days"),u<1?(u=i.ZP.getDateDiff(Date.now(),t.timestamp,"hours"),u<1?(u=i.ZP.getDateDiff(Date.now(),t.timestamp,"minutes"),u<1?(u=i.ZP.getDateDiff(Date.now(),t.timestamp,"seconds"),o=a(u>1?"nSecondsAgo":"oneSecondAgo",{count:u})):o=a(u>1?"nMinutesAgo":"oneMinuteAgo",{count:u})):o=a(u>1?"nHoursAgo":"oneHourAgo",{count:u})):o=a(u>1?"nDaysAgo":"oneDayAgo",{count:u})):o=a(u>1?"nMonthsAgo":"oneMonthAgo",{count:u})):o=a(u>1?"nYearsAgo":"oneYearAgo",{count:u}),(t,a)=>((0,l.wg)(),(0,l.iD)("span",{class:(0,n.C_)(e.className)},[(0,l.WI)(t.$slots,"prepend"),(0,l.Uk)((0,n.zw)((0,s.SU)(o)),1),(0,l.WI)(t.$slots,"append")],2))}},ue=ie,me=ue,ce={key:0,class:"q-px-sm"},pe={key:1,class:"q-px-sm"},de={key:2,class:"q-px-sm"},ge={key:3,class:"q-px-sm"},ye={key:4},ke={key:0,class:"text-h5 text-center q-py-sm q-mt-xl q-mt-sm"},we={key:1,class:"text-h5 text-center q-py-sm q-mt-xl q-mt-sm"},be=5,ve={__name:"DashboardBaseBlockRecently",props:{icon:{type:String},played:{type:Boolean},added:{type:Boolean},globalStats:Boolean},setup(e){const t=e,a=(0,o.Z)(),{t:i}=(0,r.QT)(),u=(0,s.iH)(!1),m=(0,s.iH)([]),c=(0,l.Fl)((()=>{let e=null;return t.played?e=i("Recently played"):t.added&&(e=i("Recently added")),e})),p=(0,s.iH)(null);(0,l.YP)(p,(()=>{y={global:g.value},b()}));const d=[{label:"Tracks",value:"tracks",function:L.api.metrics.getTracks,listItemType:""},{label:"Artists",value:"artists",function:L.api.metrics.getArtists},{label:"Albums",value:"albums",function:L.api.metrics.getAlbums},{label:"Genres",value:"genres",function:L.api.metrics.getGenres}],g=(0,l.Fl)((()=>t.globalStats||!1));(0,l.YP)(g,(e=>{t.played&&(y.global=g.value,b())}));let y={global:g.value};const k=(0,s.iH)(Date.now()),w=(0,s.iH)(!1);function b(){if(w.value=!1,p.value){m.value=[];const e=d.filter((e=>e.value==p.value))[0].function;u.value=!0;let l=null;t.played?l="recentlyPlayed":t.added&&(l="recentlyAdded"),e(y,l,be).then((e=>{m.value=e.data.data,k.value=Date.now(),u.value=!1})).catch((e=>{w.value=!0,u.value=!1,a.notify({type:"negative",message:"API Error: error loading metrics",caption:i("API Error: fatal error details",{status:e.response.status,statusText:e.response.statusText})})}))}}return p.value="tracks",(t,a)=>{const o=(0,l.up)("q-tab"),r=(0,l.up)("q-tabs"),g=(0,l.up)("q-icon");return(0,l.wg)(),(0,l.j4)((0,l.LL)(f),{icon:e.icon||"format_list_numbered",title:c.value,loading:u.value,onRefresh:b},{tabs:(0,l.w5)((()=>[(0,l.Wm)(r,{modelValue:p.value,"onUpdate:modelValue":a[0]||(a[0]=e=>p.value=e),"no-caps":"",class:"text-pink-7 q-mb-md"},{default:(0,l.w5)((()=>[((0,l.wg)(),(0,l.iD)(l.HY,null,(0,l.Ko)(d,(e=>(0,l.Wm)(o,{key:e.value,name:e.value,label:(0,s.SU)(i)(e.label)},null,8,["name","label"]))),64))])),_:1},8,["modelValue"])])),list:(0,l.w5)((()=>["tracks"==p.value?((0,l.wg)(),(0,l.iD)("ol",ce,[((0,l.wg)(!0),(0,l.iD)(l.HY,null,(0,l.Ko)(m.value,((t,a,s,n)=>{const o=k.value;if(n&&n.key===t.id&&(0,l.nQ)(n,o))return n;const r=((0,l.wg)(),(0,l.j4)(W,{key:t.id,track:t.track},(0,l.Nv)({_:2},[e.played?{name:"append",fn:(0,l.w5)((()=>[(0,l.Wm)(me,{className:"q-ml-sm",timestamp:1e3*t.lastPlayTimestamp},{prepend:(0,l.w5)((()=>[(0,l.Uk)("(")])),append:(0,l.w5)((()=>[(0,l.Uk)(")")])),_:2},1032,["timestamp"])])),key:"0"}:e.added?{name:"append",fn:(0,l.w5)((()=>[(0,l.Wm)(me,{className:"q-ml-sm",timestamp:1e3*t.addedTimestamp},{prepend:(0,l.w5)((()=>[(0,l.Uk)("(")])),append:(0,l.w5)((()=>[(0,l.Uk)(")")])),_:2},1032,["timestamp"])])),key:"1"}:void 0]),1032,["track"]));return r.memo=o,r}),a,1),128))])):"artists"==p.value?((0,l.wg)(),(0,l.iD)("ol",pe,[((0,l.wg)(!0),(0,l.iD)(l.HY,null,(0,l.Ko)(m.value,((t,a,s,n)=>{const o=k.value;if(n&&n.key===t.id&&(0,l.nQ)(n,o))return n;const r=((0,l.wg)(),(0,l.j4)(C,{key:t.id,artist:t},(0,l.Nv)({_:2},[e.played?{name:"append",fn:(0,l.w5)((()=>[(0,l.Wm)(me,{className:"q-ml-sm",timestamp:1e3*t.lastPlayTimestamp},{prepend:(0,l.w5)((()=>[(0,l.Uk)("(")])),append:(0,l.w5)((()=>[(0,l.Uk)(")")])),_:2},1032,["timestamp"])])),key:"0"}:e.added?{name:"append",fn:(0,l.w5)((()=>[(0,l.Wm)(me,{className:"q-ml-sm",timestamp:1e3*t.addedTimestamp},{prepend:(0,l.w5)((()=>[(0,l.Uk)("(")])),append:(0,l.w5)((()=>[(0,l.Uk)(")")])),_:2},1032,["timestamp"])])),key:"1"}:void 0]),1032,["artist"]));return r.memo=o,r}),a,3),128))])):"albums"==p.value?((0,l.wg)(),(0,l.iD)("ol",de,[((0,l.wg)(!0),(0,l.iD)(l.HY,null,(0,l.Ko)(m.value,((t,a,s,n)=>{const o=k.value;if(n&&n.key===t.id&&(0,l.nQ)(n,o))return n;const r=((0,l.wg)(),(0,l.j4)(H,{key:t.id,album:t},(0,l.Nv)({_:2},[e.played?{name:"append",fn:(0,l.w5)((()=>[(0,l.Wm)(me,{className:"q-ml-sm",timestamp:1e3*t.lastPlayTimestamp},{prepend:(0,l.w5)((()=>[(0,l.Uk)("(")])),append:(0,l.w5)((()=>[(0,l.Uk)(")")])),_:2},1032,["timestamp"])])),key:"0"}:e.added?{name:"append",fn:(0,l.w5)((()=>[(0,l.Wm)(me,{className:"q-ml-sm",timestamp:1e3*t.addedTimestamp},{prepend:(0,l.w5)((()=>[(0,l.Uk)("(")])),append:(0,l.w5)((()=>[(0,l.Uk)(")")])),_:2},1032,["timestamp"])])),key:"1"}:void 0]),1032,["album"]));return r.memo=o,r}),a,5),128))])):"genres"==p.value?((0,l.wg)(),(0,l.iD)("ol",ge,[((0,l.wg)(!0),(0,l.iD)(l.HY,null,(0,l.Ko)(m.value,((t,a,s,n)=>{const o=k.value;if(n&&n.key===t.name&&(0,l.nQ)(n,o))return n;const r=((0,l.wg)(),(0,l.j4)(j,{key:t.name,genre:t},(0,l.Nv)({_:2},[e.played?{name:"append",fn:(0,l.w5)((()=>[(0,l.Wm)(me,{className:"q-ml-sm",timestamp:1e3*t.lastPlayTimestamp},{prepend:(0,l.w5)((()=>[(0,l.Uk)("(")])),append:(0,l.w5)((()=>[(0,l.Uk)(")")])),_:2},1032,["timestamp"])])),key:"0"}:e.added?{name:"append",fn:(0,l.w5)((()=>[(0,l.Wm)(me,{className:"q-ml-sm",timestamp:1e3*t.addedTimestamp},{prepend:(0,l.w5)((()=>[(0,l.Uk)("(")])),append:(0,l.w5)((()=>[(0,l.Uk)(")")])),_:2},1032,["timestamp"])])),key:"1"}:void 0]),1032,["genre"]));return r.memo=o,r}),a,7),128))])):(0,l.kq)("",!0),u.value?(0,l.kq)("",!0):((0,l.wg)(),(0,l.iD)("div",ye,[w.value?((0,l.wg)(),(0,l.iD)("h5",ke,[(0,l.Wm)(g,{name:"error",size:"xl"}),(0,l.Uk)(" "+(0,n.zw)((0,s.SU)(i)("Error loading data")),1)])):m.value&&m.value.length>0?(0,l.kq)("",!0):((0,l.wg)(),(0,l.iD)("h5",we,[(0,l.Wm)(g,{name:"warning",size:"xl"}),(0,l.Uk)(" "+(0,n.zw)((0,s.SU)(i)("No enought data")),1)]))]))])),_:1},40,["icon","title","loading"])}}},he=ve,De=he;h()(ve,"components",{QTabs:se.Z,QTab:ne.Z,QIcon:k.Z});var fe=a(1957),qe=a(9844);const _e={class:"ct-chart"},xe={key:0},Se={key:0,class:"text-h5 text-center q-py-sm q-mt-xl q-mt-sm"},Pe={key:1,class:"text-h5 text-center q-py-sm q-mt-xl q-mt-sm"},We={__name:"DashboardBaseBlockChart",props:{icon:{type:String},globalStats:Boolean},setup(e){const t=e,a=(0,o.Z)(),{t:i}=(0,r.QT)(),u=(0,s.iH)(!1);let m=[];const c=(0,s.iH)(null);(0,l.YP)(c,(e=>{w()}));const p=[{label:"Hour",value:"hour"},{label:"Weekday",value:"weekday"},{label:"Month",value:"month"},{label:"Year",value:"year"}],d=(0,l.Fl)((()=>t.globalStats||!1));(0,l.YP)(d,(e=>{w()}));const g={low:0,showArea:!0,fullWidth:!0,chartPadding:{left:48,right:48}};function y(){let e=[],t=[];switch(c.value){case"hour":e=["00","01","02","03","04","05","06","07","08","09","10","11","12","13","14","15","16","17","18","19","20","21","22","23"],t=new Array(24).fill(0),m.forEach((e=>{t[new Number(e.hour)]=e.total}));break;case"weekday":e=[i("Sunday"),i("Monday"),i("Tuesday"),i("Wednesday"),i("Thursday"),i("Friday"),i("Saturday")],t=new Array(7).fill(0),m.forEach((e=>{t[new Number(e.weekday)]=e.total}));break;case"month":e=[i("January"),i("February"),i("March"),i("April"),i("May"),i("June"),i("July"),i("August"),i("September"),i("October"),i("November"),i("December")],t=new Array(12).fill(0),m.forEach((e=>{t[new Number(e.month)]=e.total}));break;case"year":e=m.map((e=>e.year)),t=m.map((e=>e.total));break}e.length>1?new qe.wW(".ct-chart",{labels:e,series:[t]},g):new qe.vz(".ct-chart",{labels:e,series:[t]},g)}const k=(0,s.iH)(!1);function w(){k.value=!1,c.value&&(u.value=!0,L.api.metrics.getDataRanges({dateRange:c.value,global:d.value}).then((e=>{m=e.data.data,u.value=!1,(0,l.Y3)((()=>{y()}))})).catch((e=>{k.value=!0,u.value=!1,a.notify({type:"negative",message:"API Error: error loading metrics",caption:i("API Error: fatal error details",{status:e.response.status,statusText:e.response.statusText})})})))}return c.value="hour",(t,a)=>{const o=(0,l.up)("q-tab"),r=(0,l.up)("q-tabs"),d=(0,l.up)("q-icon");return(0,l.wg)(),(0,l.j4)((0,l.LL)(f),{icon:e.icon||"analytics",title:(0,s.SU)(i)("Play stats"),loading:u.value,onRefresh:w},{tabs:(0,l.w5)((()=>[(0,l.Wm)(r,{modelValue:c.value,"onUpdate:modelValue":a[0]||(a[0]=e=>c.value=e),"no-caps":"",class:"text-pink-7 q-mb-md"},{default:(0,l.w5)((()=>[((0,l.wg)(),(0,l.iD)(l.HY,null,(0,l.Ko)(p,(e=>(0,l.Wm)(o,{key:e.value,name:e.value,label:(0,s.SU)(i)(e.label)},null,8,["name","label"]))),64))])),_:1},8,["modelValue"])])),chart:(0,l.w5)((()=>[(0,l.wy)((0,l._)("div",_e,null,512),[[fe.F8,(0,s.SU)(m)&&(0,s.SU)(m).length>0]]),u.value?(0,l.kq)("",!0):((0,l.wg)(),(0,l.iD)("div",xe,[k.value?((0,l.wg)(),(0,l.iD)("h5",Se,[(0,l.Wm)(d,{name:"error",size:"xl"}),(0,l.Uk)(" "+(0,n.zw)((0,s.SU)(i)("Error loading data")),1)])):(0,s.SU)(m)&&(0,s.SU)(m).length>0?(0,l.kq)("",!0):((0,l.wg)(),(0,l.iD)("h5",Pe,[(0,l.Wm)(d,{name:"warning",size:"xl"}),(0,l.Uk)(" "+(0,n.zw)((0,s.SU)(i)("No enought data")),1)]))]))])),_:1},40,["icon","title","loading"])}}},Ie=We,Ue=Ie;h()(We,"components",{QTabs:se.Z,QTab:ne.Z,QIcon:k.Z});const Ye={class:"row"},Te={class:"row"},Ce={class:"col-12"},Ze={__name:"DashboardPage",setup(e){const t=(0,o.Z)(),{t:a}=(0,r.QT)(),i=(0,s.iH)([{label:"My stats",value:"myStats",icon:"person"},{label:"Global stats",value:"globalStats",icon:"public"}]),u=(0,s.iH)(i.value[0].value),m=(0,l.Fl)((()=>t.screen.width>=2560?"col-xl-4":"col-xl-6"));return(e,t)=>{const o=(0,l.up)("q-breadcrumbs-el"),r=(0,l.up)("q-breadcrumbs"),c=(0,l.up)("q-tab"),p=(0,l.up)("q-tabs"),d=(0,l.up)("q-card");return(0,l.wg)(),(0,l.j4)(d,{class:"q-pa-lg"},{default:(0,l.w5)((()=>[(0,l.Wm)(r,{class:"q-mb-lg"},{default:(0,l.w5)((()=>[(0,l.Wm)(o,{icon:"home",label:"Spieldose"}),(0,l.Wm)(o,{icon:"analytics",label:(0,s.SU)(a)("Dashboard")},null,8,["label"])])),_:1}),(0,l.Wm)(p,{modelValue:u.value,"onUpdate:modelValue":t[0]||(t[0]=e=>u.value=e),"inline-label":"","no-caps":"",dense:"",class:"text-pink-7 q-mb-md shadow-2"},{default:(0,l.w5)((()=>[((0,l.wg)(!0),(0,l.iD)(l.HY,null,(0,l.Ko)(i.value,(e=>((0,l.wg)(),(0,l.j4)(c,{key:e.value,name:e.value,icon:e.icon,label:(0,s.SU)(a)(e.label)},null,8,["name","icon","label"])))),128))])),_:1},8,["modelValue"]),(0,l._)("div",Ye,[(0,l._)("div",{class:(0,n.C_)(["col-lg-6 col-12",m.value])},[(0,l.Wm)(re,{entity:"tracks",globalStats:"globalStats"==u.value},null,8,["globalStats"])],2),(0,l._)("div",{class:(0,n.C_)(["col-lg-6 col-12",m.value])},[(0,l.Wm)(re,{entity:"artists",globalStats:"globalStats"==u.value},null,8,["globalStats"])],2),(0,l._)("div",{class:(0,n.C_)(["col-lg-6 col-12",m.value])},[(0,l.Wm)(re,{entity:"albums",globalStats:"globalStats"==u.value},null,8,["globalStats"])],2),(0,l._)("div",{class:(0,n.C_)(["col-lg-6 col-12",m.value])},[(0,l.Wm)(re,{entity:"genres",globalStats:"globalStats"==u.value},null,8,["globalStats"])],2),(0,l._)("div",{class:(0,n.C_)(["col-lg-6 col-12",m.value])},[(0,l.Wm)(De,{played:!0,globalStats:"globalStats"==u.value},null,8,["globalStats"])],2),(0,l._)("div",{class:(0,n.C_)(["col-lg-6 col-12",m.value])},[(0,l.Wm)(De,{added:!0,globalStats:"globalStats"==u.value},null,8,["globalStats"])],2)]),(0,l._)("div",Te,[(0,l._)("div",Ce,[(0,l.Wm)(Ue,{globalStats:"globalStats"==u.value},null,8,["globalStats"])])])])),_:1})}}};var Ae=a(2605),Ee=a(8052);const ze=Ze,Qe=ze;h()(Ze,"components",{QCard:g.Z,QBreadcrumbs:Ae.Z,QBreadcrumbsEl:Ee.Z,QTabs:se.Z,QTab:ne.Z})}}]);