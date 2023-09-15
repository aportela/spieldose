"use strict";(globalThis["webpackChunkspieldose"]=globalThis["webpackChunkspieldose"]||[]).push([[740],{8106:(e,t,l)=>{l.d(t,{dC:()=>o});var a=l(4958),s=l(2719),n=l(1569),r=l(8514);const i=(0,a.n)(),u=(0,s.d)(),o={setFavorite:function(e){return new Promise(((t,l)=>{n.api.track.setFavorite(e).then((l=>{r.bus.emit("setFavoriteTrack",{trackId:e,timestamp:l.data.favorited}),u.setFavoriteTrack(e,l.data.favorited),t(l)})).catch((e=>{l(e)}))}))},unSetFavorite:function(e){return new Promise(((t,l)=>{n.api.track.unSetFavorite(e).then((l=>{r.bus.emit("unSetFavoriteTrack",{trackId:e}),u.unSetFavoriteTrack(e),t(l)})).catch((e=>{l(e)}))}))},play:function(e){i.stop(),u.saveElements(Array.isArray(e)?e:[{track:e}]),i.interact(),i.play(!0)},enqueue:function(e){u.appendElements(Array.isArray(e)?e:[{track:e}]),i.interact()}}},2719:(e,t,l)=>{l.d(t,{d:()=>o});var a=l(1809),s=l(8612),n=l.n(s),r=l(4958);const i=(0,r.n)(),u={namespace:"spieldose",storages:["local","cookie","session","memory"],storage:"local",expireDays:3650},o=(0,a.Q_)("currentPlaylist",{state:()=>({elements:[],shuffleIndexes:[],currentIndex:-1,elementsLastChangeTimestamp:null}),getters:{hasElements:e=>e.elements&&e.elements.length>0,getElements:e=>e.elements,elementCount:e=>e.elements?e.elements.length:0,getElementsLastChangeTimestamp:e=>e.elementsLastChangeTimestamp,getCurrentIndex:e=>i.getShuffle?e.shuffleIndexes[e.currentIndex]:e.currentIndex,getCurrentElement:e=>e.currentIndex>=0&&e.elements.length>0?e.elements[e.currentIndex]:null,getCurrentElementURL:e=>e.currentIndex>=0&&e.elements.length>0?e.elements[e.currentIndex].url:null,allowSkipPrevious:e=>e.currentIndex>0&&e.elements.length>0,allowSkipNext:e=>e.currentIndex>=0&&e.elements.length>0&&e.currentIndex<e.elements.length-1,allowPlay:e=>!0,allowPause:e=>!0,allowResume:e=>!0,allowStop:e=>!0},actions:{load(){const e=n()(u),t=e.get("currentPlaylistElements");t&&(this.elements=t);const l=e.get("shuffleIndexes");l&&(this.shuffleIndexes=l);const a=e.get("currentPlaylistElementIndex");a>=0&&(this.currentIndex=a)},saveCurrentElements(){const e=n()(u);e.set("currentPlaylistElements",this.elements),e.set("currentPlaylistElementIndex",this.currentIndex),e.set("shuffleIndexes",this.shuffleIndexes),this.elementsLastChangeTimestamp=Date.now()},saveElements(e){this.elements=e,this.shuffleIndexes=[...Array(e.length).keys()].sort((function(){return.5-Math.random()})),this.currentIndex=e&&e.length>0?0:-1,this.saveCurrentElements()},appendElements(e){this.elements=this.elements.concat(e),this.saveCurrentElements()},saveCurrentTrackIndex(e){this.currentIndex=e>=0?e:-1;const t=n()(u);t.set("currentPlaylistElementIndex",this.currentIndex)},skipPrevious(){this.saveCurrentTrackIndex(--this.currentIndex)},skipNext(){this.saveCurrentTrackIndex(++this.currentIndex)},clear(){this.elements=[],this.currentIndex=-1;const e=n()(u);e.set("currentPlaylistElements",[]),e.set(-1),this.elementsLastChangeTimestamp=Date.now()},setFavoriteTrack(e,t){const l=this.elements.findIndex((t=>t.track&&t.track.id==e));-1!==l&&(this.elements[l].track.favorited=t,this.saveCurrentElements())},unSetFavoriteTrack(e){const t=this.elements.findIndex((t=>t.track&&t.track.id==e));-1!==t&&(this.elements[t].track.favorited=null,this.saveCurrentElements())}}})},4958:(e,t,l)=>{l.d(t,{n:()=>r});var a=l(1809),s=l(1320);const n=(0,s.l)(),r=(0,a.Q_)("player",{state:()=>({repeatMode:null,shuffle:!1,element:null,hasPreviousUserInteractions:!1}),getters:{getRepeatMode:e=>e.repeatMode,getShuffle:e=>e.shuffle,getElement:e=>e.element,getDuration:e=>e.element.duration},actions:{interact(){this.hasPreviousUserInteractions=!0},create(){this.element=document.createElement("audio"),this.element.id="audio_player",this.element.crossOrigin="anonymous"},setVolume(e){this.element.volume=e},setCurrentTime(e){this.element.currentTime=e},stop(){this.element.pause(),n.setStatusStopped()},load(){},play(e){e?(this.element.play(),n.setStatusPlaying()):n.isPlaying?(this.element.pause(),n.setStatusPaused()):n.isPaused?(this.element.play(),n.setStatusPlaying()):(this.element.load(),this.element.play(),n.setStatusPlaying())},setRepeatMode(e){this.repeatMode=e},toggleShuffle(){this.shuffle=!this.shuffle}}})},1320:(e,t,l)=>{l.d(t,{l:()=>s});var a=l(1809);const s=(0,a.Q_)("playerStatus",{state:()=>({status:"stopped"}),getters:{getStatus(e){return e.status},isPlaying(e){return"playing"==e.status},isStopped(e){return"stopped"==e.status},isPaused(e){return"paused"==e.status}},actions:{setStatusPlaying(){this.status="playing"},setStatusStopped(){this.status="stopped"},setStatusPaused(){this.status="paused"}}})},8740:(e,t,l)=>{l.r(t),l.d(t,{default:()=>Z});var a=l(9835),s=l(499),n=l(6970),r=l(1957),i=l(9302),u=l(796),o=l(6647),c=l(1569),d=l(4958),m=l(1320),p=l(2719),v=l(8106);const g=(0,a._)("div",{class:"text-h6"},"Save current playlist",-1),h={__name:"CurrentPlaylistPage",setup(e){const t=(0,i.Z)(),{t:l}=(0,o.QT)(),h=(0,d.n)(),b=(0,p.d)(),f=(0,a.Fl)((()=>b.getElementsLastChangeTimestamp)),k=(0,s.iH)(null),y=(0,s.iH)([]),w=[{name:"index",required:!0,label:"Index",align:"right",field:e=>e.index,sortable:!1},{name:"title",required:!0,label:"Title",align:"left",field:e=>e.title,sortable:!0},{name:"artist",required:!0,label:"Artist",align:"left",field:e=>e.artist.name,sortable:!0},{name:"albumArtist",required:!1,label:"Album artist",align:"left",field:e=>e.album.artist.name,sortable:!0},{name:"albumTitle",required:!1,label:"Album",align:"left",field:e=>e.album.title,sortable:!0},{name:"albumTrackIndex",required:!1,label:"Album Track nº",align:"right",field:e=>e.trackNumber,sortable:!0},{name:"year",required:!1,label:"Year",align:"right",field:e=>e.album.year,sortable:!0},{name:"actions",required:!0,label:"Actions",align:"center",favorited:e=>e.favorited}],x=(0,s.iH)(w.map((e=>e.name))),S=((0,s.iH)({descending:!1,page:1,rowsPerPage:64}),(0,a.Fl)((()=>C.isPlaying?"play_arrow":C.isPaused?"pause":C.isStopped?"stop":"play_arrow")));(0,a.YP)(f,(e=>{I.value=b.getElements,y.value=I.value.map(((e,t)=>(e.track.index=t+1,e.track))),P.value=b.getCurrentIndex}));const C=(0,m.l)(),I=(0,s.iH)([]),P=(0,s.iH)(0),U=(0,s.iH)(!1),T=(0,s.iH)(!1),E=(0,s.iH)(null),_=(0,s.iH)(!1);function q(){h.stop(),I.value=[],y.value=[],b.clear()}function W(e){const t=I.value[e];I.value.splice(e,1),I.value.splice(e-1,0,t)}function z(e){const t=I.value[e];I.value.splice(e,1),I.value.splice(e+1,0,t)}function Q(e,a){const s=a?v.dC.unSetFavorite:v.dC.setFavorite;s(e).then((e=>{})).catch((e=>{switch(e.response.status){default:t.notify({type:"negative",message:l("API Error: error when toggling favorite flag"),caption:l("API Error: fatal error details",{status:e.response.status,statusText:e.response.statusText})});break}}))}function A(e){I.value.splice(e,1),y.value=I.value.map(((e,t)=>(e.track.index=t+1,e.track))),b.saveElements(I.value)}function Z(e,t,l){"I"!=e.target.nodeName&&"BUTTON"!=e.target.nodeName&&(h.interact(),b.saveCurrentTrackIndex(l),C.isPlaying||h.play())}function F(){h.interact(),U.value=!0,P.value=0,c.api.track.search({},1,32,!0,null,null).then((e=>{I.value=e.data.data.items.map((e=>({track:e}))),y.value=I.value.map(((e,t)=>(e.track.index=t+1,e.track))),v.dC.play(I.value),U.value=!1})).catch((e=>{t.notify({type:"negative",message:l("API Error: error loading random tracks"),caption:l("API Error: fatal error details",{status:e.response.status,statusText:e.response.statusText})}),U.value=!1}))}const H=(0,a.Fl)((()=>b.getCurrentIndex));function R(){h.stop(),F()}function V(){h.interact(),b.skipPrevious()}function D(){h.interact(),h.play()}function L(){h.interact(),h.play()}function N(){h.interact(),h.play()}function M(){h.stop(),h.setCurrentTime(0)}function j(){h.interact(),b.skipNext()}function B(){E.value=null,T.value=!0}function Y(){h.interact(),U.value=!0,P.value=0,c.api.playlist.add((0,u.Z)(),E.value,I.value.filter((e=>e.track)).map((e=>e.track.id)),_.value).then((e=>{U.value=!1,T.value=!1})).catch((e=>{t.notify({type:"negative",message:l("API Error: error loading random tracks"),caption:l("API Error: fatal error details",{status:e.response.status,statusText:e.response.statusText})}),U.value=!1}))}return(0,a.YP)(H,(e=>{P.value=e,k.value.scrollTo(e,"center-force")})),I.value=b.getElements,y.value=I.value.map(((e,t)=>(e.track.index=t+1,e.track))),P.value=b.getCurrentIndex,(e,i)=>{const u=(0,a.up)("q-breadcrumbs-el"),o=(0,a.up)("q-breadcrumbs"),c=(0,a.up)("q-btn"),d=(0,a.up)("q-btn-group"),m=(0,a.up)("q-icon"),p=(0,a.up)("q-td"),v=(0,a.up)("router-link"),h=(0,a.up)("q-table"),f=(0,a.up)("q-card"),F=(0,a.up)("q-card-section"),H=(0,a.up)("q-input"),O=(0,a.up)("q-toggle"),G=(0,a.up)("q-card-actions"),K=(0,a.up)("q-dialog"),J=(0,a.Q2)("close-popup");return(0,a.wg)(),(0,a.iD)(a.HY,null,[(0,a.Wm)(f,{class:"q-pa-lg"},{default:(0,a.w5)((()=>[(0,a.Wm)(o,{class:"q-mb-lg"},{default:(0,a.w5)((()=>[(0,a.Wm)(u,{icon:"home",label:"Spieldose"}),(0,a.Wm)(u,{icon:"list_alt",label:(0,s.SU)(l)("Current playlist")},null,8,["label"])])),_:1}),(0,a.Wm)(d,{spread:"",class:"q-mb-md"},{default:(0,a.w5)((()=>[(0,a.Wm)(c,{size:"md",outline:"",color:"dark",label:(0,s.SU)(t).screen.gt.md?(0,s.SU)(l)("Clear"):"",icon:"clear",onClick:q,disable:U.value||!(I.value&&I.value.length>0)},null,8,["label","disable"]),(0,a.Wm)(c,{size:"md",outline:"",color:"dark",label:(0,s.SU)(t).screen.gt.md?(0,s.SU)(l)("Randomize"):"",icon:"bolt",onClick:R,disable:U.value},null,8,["label","disable"]),(0,a.Wm)(c,{size:"md",outline:"",color:"dark",label:(0,s.SU)(t).screen.gt.md?(0,s.SU)(l)("Previous"):"",icon:"skip_previous",onClick:V,disable:U.value||!(0,s.SU)(b).allowSkipPrevious},null,8,["label","disable"]),(0,s.SU)(C).isStopped?((0,a.wg)(),(0,a.j4)(c,{key:0,size:"md",outline:"",color:"dark",label:(0,s.SU)(t).screen.gt.md?(0,s.SU)(l)("Play"):"",icon:"play_arrow",onClick:D,disable:U.value||!(0,s.SU)(b).hasElements},null,8,["label","disable"])):(0,s.SU)(C).isPlaying?((0,a.wg)(),(0,a.j4)(c,{key:1,size:"md",outline:"",color:"dark",label:(0,s.SU)(t).screen.gt.md?(0,s.SU)(l)("Pause"):"",icon:"pause",onClick:L,disable:U.value||!(I.value&&I.value.length>0)},null,8,["label","disable"])):(0,s.SU)(C).isPaused?((0,a.wg)(),(0,a.j4)(c,{key:2,size:"md",outline:"",color:"dark",label:(0,s.SU)(t).screen.gt.md?(0,s.SU)(l)("Resume"):"",icon:"play_arrow",onClick:N,disable:U.value||!(I.value&&I.value.length>0)},null,8,["label","disable"])):(0,a.kq)("",!0),(0,a.Wm)(c,{size:"md",outline:"",color:"dark",label:(0,s.SU)(t).screen.gt.md?(0,s.SU)(l)("Stop"):"",icon:"stop",onClick:M,disable:U.value||(0,s.SU)(C).isStopped||!(I.value&&I.value.length>0)},null,8,["label","disable"]),(0,a.Wm)(c,{size:"md",outline:"",color:"dark",label:(0,s.SU)(t).screen.gt.md?(0,s.SU)(l)("Next"):"",icon:"skip_next",onClick:j,disable:U.value||!(0,s.SU)(b).allowSkipNext},null,8,["label","disable"]),(0,a.Wm)(c,{size:"md",outline:"",color:"dark",label:(0,s.SU)(t).screen.gt.md?(0,s.SU)(l)("Download"):"",icon:"save_alt",disable:U.value||!(0,s.SU)(b).getCurrentElementURL,href:(0,s.SU)(b).getCurrentElementURL},null,8,["label","disable","href"]),(0,a.Wm)(c,{size:"md",outline:"",color:"dark",label:(0,s.SU)(t).screen.gt.md?(0,s.SU)(l)("Save as"):"",icon:"save_alt",disable:U.value||!(I.value&&I.value.length>0),onClick:B},null,8,["label","disable"])])),_:1}),(0,a.Wm)(h,{ref_key:"tableRef",ref:k,class:"my-sticky-header-table",style:{height:"46.8em"},title:"Current playlist",rows:y.value,columns:w,"row-key":"id","virtual-scroll":"","rows-per-page-options":[0],"visible-columns":x.value,onRowClick:Z,"hide-bottom":!0},{"body-cell-index":(0,a.w5)((e=>[(0,a.Wm)(p,{props:e},{default:(0,a.w5)((()=>[P.value+1==e.value?((0,a.wg)(),(0,a.j4)(m,{key:0,name:S.value,color:"pink",size:"sm",class:"q-mr-sm"},null,8,["name"])):(0,a.kq)("",!0),(0,a.Uk)(" "+(0,n.zw)(e.value)+" / "+(0,n.zw)(y.value.length),1)])),_:2},1032,["props"])])),"body-cell-artist":(0,a.w5)((e=>[(0,a.Wm)(p,{props:e},{default:(0,a.w5)((()=>[e.value?((0,a.wg)(),(0,a.j4)(v,{key:0,class:(0,n.C_)({"text-white text-bold":!1}),to:{name:"artist",params:{name:e.value}}},{default:(0,a.w5)((()=>[(0,a.Wm)(m,{name:"link",class:"q-mr-sm"}),(0,a.Uk)((0,n.zw)(e.value),1)])),_:2},1032,["to"])):(0,a.kq)("",!0)])),_:2},1032,["props"])])),"body-cell-albumArtist":(0,a.w5)((e=>[(0,a.Wm)(p,{props:e},{default:(0,a.w5)((()=>[e.value?((0,a.wg)(),(0,a.j4)(v,{key:0,class:(0,n.C_)({"text-white text-bold":!1}),to:{name:"artist",params:{name:e.value}}},{default:(0,a.w5)((()=>[(0,a.Wm)(m,{name:"link",class:"q-mr-sm"}),(0,a.Uk)((0,n.zw)(e.value),1)])),_:2},1032,["to"])):(0,a.kq)("",!0)])),_:2},1032,["props"])])),"body-cell-actions":(0,a.w5)((e=>[(0,a.Wm)(p,{props:e},{default:(0,a.w5)((()=>[(0,a.Uk)((0,n.zw)(e.value)+" ",1),(0,a.Wm)(d,{outline:""},{default:(0,a.w5)((()=>[(0,a.Wm)(c,{size:"sm",color:"white","text-color":"grey-5",icon:"north",title:(0,s.SU)(l)("Up"),disable:"",onClick:W},null,8,["title"]),(0,a.Wm)(c,{size:"sm",color:"white","text-color":"grey-5",icon:"south",title:(0,s.SU)(l)("Down"),disable:"",onClick:z},null,8,["title"]),(0,a.Wm)(c,{size:"sm",color:"white","text-color":e.row.favorited?"pink":"grey-5",icon:"favorite",title:(0,s.SU)(l)("Toggle favorite"),onClick:t=>Q(e.row.id,e.row.favorited)},null,8,["text-color","title","onClick"]),(0,a.Wm)(c,{size:"sm",color:"white","text-color":"grey-5",icon:"delete",title:(0,s.SU)(l)("Remove"),onClick:(0,r.iM)((t=>A(e.row.index-1)),["stop","prevent"])},null,8,["title","onClick"])])),_:2},1024)])),_:2},1032,["props"])])),_:1},8,["rows","visible-columns"])])),_:1}),(0,a.Wm)(K,{modelValue:T.value,"onUpdate:modelValue":i[3]||(i[3]=e=>T.value=e)},{default:(0,a.w5)((()=>[(0,a.Wm)(f,{style:{"min-width":"350px"}},{default:(0,a.w5)((()=>[(0,a.Wm)(F,null,{default:(0,a.w5)((()=>[g])),_:1}),(0,a.Wm)(F,{class:"q-pt-none"},{default:(0,a.w5)((()=>[(0,a.Wm)(H,{outlined:"",dense:"",modelValue:E.value,"onUpdate:modelValue":i[0]||(i[0]=e=>E.value=e),autofocus:"",onKeyup:i[1]||(i[1]=(0,r.D2)((e=>T.value=!1),["enter"])),label:"Playlist name"},null,8,["modelValue"])])),_:1}),(0,a.Wm)(F,{class:"q-pt-none"},{default:(0,a.w5)((()=>[(0,a.Wm)(O,{label:"Public",color:"pink",modelValue:_.value,"onUpdate:modelValue":i[2]||(i[2]=e=>_.value=e)},null,8,["modelValue"])])),_:1}),(0,a.Wm)(G,{align:"right",class:""},{default:(0,a.w5)((()=>[(0,a.wy)((0,a.Wm)(c,{outline:"",label:"Cancel"},null,512),[[J]]),(0,a.Wm)(c,{outline:"",label:"Save",disable:!E.value,onClick:Y},null,8,["disable"])])),_:1})])),_:1})])),_:1},8,["modelValue"])],64)}}};var b=l(4458),f=l(2605),k=l(8052),y=l(7236),w=l(8879),x=l(9994),S=l(136),C=l(6384),I=l(7220),P=l(2857),U=l(2074),T=l(3190),E=l(6611),_=l(3175),q=l(1821),W=l(2146),z=l(9984),Q=l.n(z);const A=h,Z=A;Q()(h,"components",{QCard:b.Z,QBreadcrumbs:f.Z,QBreadcrumbsEl:k.Z,QBtnGroup:y.Z,QBtn:w.Z,QTable:x.Z,QSpace:S.Z,QSelect:C.Z,QTd:I.Z,QIcon:P.Z,QDialog:U.Z,QCardSection:T.Z,QInput:E.Z,QToggle:_.Z,QCardActions:q.Z}),Q()(h,"directives",{ClosePopup:W.Z})}}]);