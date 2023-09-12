"use strict";(globalThis["webpackChunkspieldose"]=globalThis["webpackChunkspieldose"]||[]).push([[633],{8106:(e,t,l)=>{l.d(t,{dC:()=>u});var a=l(4958),n=l(2719),s=l(1569);const r=(0,a.n)(),i=(0,n.d)(),u={setFavorite:function(e){return new Promise(((t,l)=>{s.api.track.setFavorite(e).then((l=>{i.setFavoriteTrack(e,l.data.favorited),t(l)})).catch((e=>{l(e)}))}))},unSetFavorite:function(e){return new Promise(((t,l)=>{s.api.track.unSetFavorite(e).then((l=>{i.unSetFavoriteTrack(e),t(l)})).catch((e=>{l(e)}))}))},play:function(e){r.stop(),i.saveElements(Array.isArray(e)?e:[{track:e}]),r.interact(),r.play(!0)},enqueue:function(e){i.appendElements(Array.isArray(e)?e:[{track:e}]),r.interact()}}},2719:(e,t,l)=>{l.d(t,{d:()=>i});var a=l(1809),n=l(8612),s=l.n(n);const r={namespace:"spieldose",storages:["local","cookie","session","memory"],storage:"local",expireDays:3650},i=(0,a.Q_)("currentPlaylist",{state:()=>({elements:[],currentIndex:-1,elementsLastChangeTimestamp:null}),getters:{hasElements:e=>e.elements&&e.elements.length>0,getElements:e=>e.elements,getElementsLastChangeTimestamp:e=>e.elementsLastChangeTimestamp,getCurrentIndex:e=>e.currentIndex,getCurrentElement:e=>e.currentIndex>=0&&e.elements.length>0?e.elements[e.currentIndex]:null,getCurrentElementURL:e=>e.currentIndex>=0&&e.elements.length>0?e.elements[e.currentIndex].url:null,allowSkipPrevious:e=>e.currentIndex>0&&e.elements.length>0,allowSkipNext:e=>e.currentIndex>=0&&e.elements.length>0&&e.currentIndex<e.elements.length-1,allowPlay:e=>!0,allowPause:e=>!0,allowResume:e=>!0,allowStop:e=>!0},actions:{load(){const e=s()(r),t=e.get("currentPlaylistElements");t&&(this.elements=t);const l=e.get("currentPlaylistElementIndex");l>=0&&(this.currentIndex=l)},saveCurrentElements(){const e=s()(r);e.set("currentPlaylistElements",this.elements),e.set("currentPlaylistElementIndex",this.currentIndex),this.elementsLastChangeTimestamp=Date.now()},saveElements(e){this.elements=e,this.currentIndex=e&&e.length>0?0:-1,this.saveCurrentElements()},appendElements(e){this.elements=this.elements.concat(e),this.saveCurrentElements()},saveCurrentTrackIndex(e){this.currentIndex=e>=0?e:-1;const t=s()(r);t.set("currentPlaylistElementIndex",this.currentIndex)},skipPrevious(){this.currentIndex--},skipNext(){this.currentIndex++},clear(){this.elements=[],this.currentIndex=-1;const e=s()(r);e.set("currentPlaylistElements",[]),e.set(-1),this.elementsLastChangeTimestamp=Date.now()},setFavoriteTrack(e,t){const l=this.elements.findIndex((t=>t.track&&t.track.id==e));-1!==l&&(this.elements[l].track.favorited=t,this.saveCurrentElements())},unSetFavoriteTrack(e){const t=this.elements.findIndex((t=>t.track&&t.track.id==e));-1!==t&&(this.elements[t].track.favorited=null,this.saveCurrentElements())}}})},4958:(e,t,l)=>{l.d(t,{n:()=>r});var a=l(1809),n=l(1320);const s=(0,n.l)(),r=(0,a.Q_)("player",{state:()=>({element:null,hasPreviousUserInteractions:!1}),getters:{getElement:e=>e.element,getDuration:e=>e.element.duration},actions:{interact(){this.hasPreviousUserInteractions=!0},create(){this.element=document.createElement("audio"),this.element.id="audio_player",this.element.crossOrigin="anonymous"},setVolume(e){this.element.volume=e},setCurrentTime(e){this.element.currentTime=e},stop(){this.element.pause(),s.setStatusStopped()},load(){},play(e){e?(this.element.play(),s.setStatusPlaying()):s.isPlaying?(this.element.pause(),s.setStatusPaused()):s.isPaused?(this.element.play(),s.setStatusPlaying()):(this.element.load(),this.element.play(),s.setStatusPlaying())}}})},1320:(e,t,l)=>{l.d(t,{l:()=>n});var a=l(1809);const n=(0,a.Q_)("playerStatus",{state:()=>({status:"stopped"}),getters:{getStatus(e){return e.status},isPlaying(e){return"playing"==e.status},isStopped(e){return"stopped"==e.status},isPaused(e){return"paused"==e.status}},actions:{setStatusPlaying(){this.status="playing"},setStatusStopped(){this.status="stopped"},setStatusPaused(){this.status="paused"}}})},1633:(e,t,l)=>{l.r(t),l.d(t,{default:()=>Z});var a=l(9835),n=l(499),s=l(6970),r=l(1957),i=l(9302),u=l(796),o=l(7712),c=l(1569),d=l(4958),m=l(1320),p=l(2719),v=l(8106);const g=(0,a._)("div",{class:"text-h6"},"Save current playlist",-1),b={__name:"CurrentPlaylistPage",setup(e){const t=(0,i.Z)(),{t:l}=(0,o.QT)(),b=(0,d.n)(),h=(0,p.d)(),k=(0,a.Fl)((()=>h.getElementsLastChangeTimestamp)),y=(0,n.iH)([]),f=[{name:"index",required:!0,label:"Index",align:"right",field:e=>e.index,sortable:!1},{name:"title",required:!0,label:"Title",align:"left",field:e=>e.title,sortable:!0},{name:"artist",required:!0,label:"Artist",align:"left",field:e=>e.artist.name,sortable:!0},{name:"albumArtist",required:!1,label:"Album artist",align:"left",field:e=>e.album.artist.name,sortable:!0},{name:"albumTitle",required:!1,label:"Album",align:"left",field:e=>e.album.title,sortable:!0},{name:"albumTrackIndex",required:!1,label:"Album Track nº",align:"right",field:e=>e.trackNumber,sortable:!0},{name:"year",required:!1,label:"Year",align:"right",field:e=>e.album.year,sortable:!0},{name:"actions",required:!0,label:"Actions",align:"center",favorited:e=>e.favorited}],w=(0,n.iH)(f.map((e=>e.name))),S=((0,n.iH)({descending:!1,page:1,rowsPerPage:64}),(0,a.Fl)((()=>x.isPlaying?"play_arrow":x.isPaused?"pause":x.isStopped?"stop":"play_arrow")));(0,a.YP)(k,(e=>{C.value=h.getElements,y.value=C.value.map(((e,t)=>(e.track.index=t+1,e.track))),P.value=h.getCurrentIndex}));const x=(0,m.l)(),C=(0,n.iH)([]),P=(0,n.iH)(0),U=(0,n.iH)(!1),I=(0,n.iH)(!1),E=(0,n.iH)(null),_=(0,n.iH)(!1);function q(){b.stop(),C.value=[],y.value=[],h.clear()}function T(e){const t=C.value[e];C.value.splice(e,1),C.value.splice(e-1,0,t)}function W(e){const t=C.value[e];C.value.splice(e,1),C.value.splice(e+1,0,t)}function z(e,a){const n=a?v.dC.unSetFavorite:v.dC.setFavorite;n(e).then((e=>{})).catch((e=>{switch(e.response.status){default:t.notify({type:"negative",message:l("API Error: error when toggling favorite flag"),caption:l("API Error: fatal error details",{status:e.response.status,statusText:e.response.statusText})});break}}))}function Q(e){C.value.splice(e,1),y.value=C.value.map(((e,t)=>(e.track.index=t+1,e.track))),h.saveElements(C.value)}function A(e,t,l){"I"!=e.target.nodeName&&"BUTTON"!=e.target.nodeName&&(b.interact(),h.saveCurrentTrackIndex(l),x.isPlaying||b.play())}function Z(){b.interact(),U.value=!0,P.value=0,c.api.track.search({},1,32,!0,null,null).then((e=>{C.value=e.data.data.items.map((e=>({track:e}))),y.value=C.value.map(((e,t)=>(e.track.index=t+1,e.track))),v.dC.play(C.value),U.value=!1})).catch((e=>{t.notify({type:"negative",message:l("API Error: error loading random tracks"),caption:l("API Error: fatal error details",{status:e.response.status,statusText:e.response.statusText})}),U.value=!1}))}const F=(0,a.Fl)((()=>h.getCurrentIndex));function H(){b.stop(),Z()}function V(){b.interact(),h.skipPrevious()}function D(){b.interact(),b.play()}function L(){b.interact(),b.play()}function N(){b.interact(),b.play()}function R(){b.stop(),b.setCurrentTime(0)}function j(){b.interact(),h.skipNext()}function B(){E.value=null,I.value=!0}function Y(){b.interact(),U.value=!0,P.value=0,c.api.playlist.add((0,u.Z)(),E.value,C.value.filter((e=>e.track)).map((e=>e.track.id)),_.value).then((e=>{U.value=!1,I.value=!1})).catch((e=>{t.notify({type:"negative",message:l("API Error: error loading random tracks"),caption:l("API Error: fatal error details",{status:e.response.status,statusText:e.response.statusText})}),U.value=!1}))}return(0,a.YP)(F,(e=>{P.value=e})),C.value=h.getElements,y.value=C.value.map(((e,t)=>(e.track.index=t+1,e.track))),P.value=h.getCurrentIndex,(e,i)=>{const u=(0,a.up)("q-breadcrumbs-el"),o=(0,a.up)("q-breadcrumbs"),c=(0,a.up)("q-btn"),d=(0,a.up)("q-btn-group"),m=(0,a.up)("q-icon"),p=(0,a.up)("q-td"),v=(0,a.up)("router-link"),b=(0,a.up)("q-table"),k=(0,a.up)("q-card"),Z=(0,a.up)("q-card-section"),F=(0,a.up)("q-input"),O=(0,a.up)("q-toggle"),G=(0,a.up)("q-card-actions"),K=(0,a.up)("q-dialog"),M=(0,a.Q2)("close-popup");return(0,a.wg)(),(0,a.iD)(a.HY,null,[(0,a.Wm)(k,{class:"q-pa-lg"},{default:(0,a.w5)((()=>[(0,a.Wm)(o,{class:"q-mb-lg"},{default:(0,a.w5)((()=>[(0,a.Wm)(u,{icon:"home",label:"Spieldose"}),(0,a.Wm)(u,{icon:"list_alt",label:(0,n.SU)(l)("Current playlist")},null,8,["label"])])),_:1}),(0,a.Wm)(d,{spread:"",class:"q-mb-md"},{default:(0,a.w5)((()=>[(0,a.Wm)(c,{size:"md",outline:"",color:"dark",label:(0,n.SU)(t).screen.gt.md?(0,n.SU)(l)("Clear"):"",icon:"clear",onClick:q,disable:U.value||!(C.value&&C.value.length>0)},null,8,["label","disable"]),(0,a.Wm)(c,{size:"md",outline:"",color:"dark",label:(0,n.SU)(t).screen.gt.md?(0,n.SU)(l)("Randomize"):"",icon:"bolt",onClick:H,disable:U.value},null,8,["label","disable"]),(0,a.Wm)(c,{size:"md",outline:"",color:"dark",label:(0,n.SU)(t).screen.gt.md?(0,n.SU)(l)("Previous"):"",icon:"skip_previous",onClick:V,disable:U.value||!(0,n.SU)(h).allowSkipPrevious},null,8,["label","disable"]),(0,n.SU)(x).isStopped?((0,a.wg)(),(0,a.j4)(c,{key:0,size:"md",outline:"",color:"dark",label:(0,n.SU)(t).screen.gt.md?(0,n.SU)(l)("Play"):"",icon:"play_arrow",onClick:D,disable:U.value||!(0,n.SU)(h).hasElements},null,8,["label","disable"])):(0,n.SU)(x).isPlaying?((0,a.wg)(),(0,a.j4)(c,{key:1,size:"md",outline:"",color:"dark",label:(0,n.SU)(t).screen.gt.md?(0,n.SU)(l)("Pause"):"",icon:"pause",onClick:L,disable:U.value||!(C.value&&C.value.length>0)},null,8,["label","disable"])):(0,n.SU)(x).isPaused?((0,a.wg)(),(0,a.j4)(c,{key:2,size:"md",outline:"",color:"dark",label:(0,n.SU)(t).screen.gt.md?(0,n.SU)(l)("Resume"):"",icon:"play_arrow",onClick:N,disable:U.value||!(C.value&&C.value.length>0)},null,8,["label","disable"])):(0,a.kq)("",!0),(0,a.Wm)(c,{size:"md",outline:"",color:"dark",label:(0,n.SU)(t).screen.gt.md?(0,n.SU)(l)("Stop"):"",icon:"stop",onClick:R,disable:U.value||(0,n.SU)(x).isStopped||!(C.value&&C.value.length>0)},null,8,["label","disable"]),(0,a.Wm)(c,{size:"md",outline:"",color:"dark",label:(0,n.SU)(t).screen.gt.md?(0,n.SU)(l)("Next"):"",icon:"skip_next",onClick:j,disable:U.value||!(0,n.SU)(h).allowSkipNext},null,8,["label","disable"]),(0,a.Wm)(c,{size:"md",outline:"",color:"dark",label:(0,n.SU)(t).screen.gt.md?(0,n.SU)(l)("Download"):"",icon:"save_alt",disable:U.value||!(0,n.SU)(h).getCurrentElementURL,href:(0,n.SU)(h).getCurrentElementURL},null,8,["label","disable","href"]),(0,a.Wm)(c,{size:"md",outline:"",color:"dark",label:(0,n.SU)(t).screen.gt.md?(0,n.SU)(l)("Save as"):"",icon:"save_alt",disable:U.value||!(C.value&&C.value.length>0),onClick:B},null,8,["label","disable"])])),_:1}),(0,a.Wm)(b,{class:"my-sticky-header-table",style:{height:"46.8em"},title:"Current playlist",rows:y.value,columns:f,"row-key":"id","virtual-scroll":"","rows-per-page-options":[0],"visible-columns":w.value,onRowClick:A,"hide-bottom":!0},{"body-cell-index":(0,a.w5)((e=>[(0,a.Wm)(p,{props:e},{default:(0,a.w5)((()=>[P.value+1==e.value?((0,a.wg)(),(0,a.j4)(m,{key:0,name:S.value,color:"pink",size:"sm",class:"q-mr-sm"},null,8,["name"])):(0,a.kq)("",!0),(0,a.Uk)(" "+(0,s.zw)(e.value)+" / "+(0,s.zw)(y.value.length),1)])),_:2},1032,["props"])])),"body-cell-artist":(0,a.w5)((e=>[(0,a.Wm)(p,{props:e},{default:(0,a.w5)((()=>[e.value?((0,a.wg)(),(0,a.j4)(v,{key:0,class:(0,s.C_)({"text-white text-bold":!1}),to:{name:"artist",params:{name:e.value}}},{default:(0,a.w5)((()=>[(0,a.Wm)(m,{name:"link",class:"q-mr-sm"}),(0,a.Uk)((0,s.zw)(e.value),1)])),_:2},1032,["to"])):(0,a.kq)("",!0)])),_:2},1032,["props"])])),"body-cell-albumArtist":(0,a.w5)((e=>[(0,a.Wm)(p,{props:e},{default:(0,a.w5)((()=>[e.value?((0,a.wg)(),(0,a.j4)(v,{key:0,class:(0,s.C_)({"text-white text-bold":!1}),to:{name:"artist",params:{name:e.value}}},{default:(0,a.w5)((()=>[(0,a.Wm)(m,{name:"link",class:"q-mr-sm"}),(0,a.Uk)((0,s.zw)(e.value),1)])),_:2},1032,["to"])):(0,a.kq)("",!0)])),_:2},1032,["props"])])),"body-cell-actions":(0,a.w5)((e=>[(0,a.Wm)(p,{props:e},{default:(0,a.w5)((()=>[(0,a.Uk)((0,s.zw)(e.value)+" ",1),(0,a.Wm)(d,{outline:""},{default:(0,a.w5)((()=>[(0,a.Wm)(c,{size:"sm",color:"white","text-color":"grey-5",icon:"north",title:(0,n.SU)(l)("Up"),disable:"",onClick:T},null,8,["title"]),(0,a.Wm)(c,{size:"sm",color:"white","text-color":"grey-5",icon:"south",title:(0,n.SU)(l)("Down"),disable:"",onClick:W},null,8,["title"]),(0,a.Wm)(c,{size:"sm",color:"white","text-color":e.row.favorited?"pink":"grey-5",icon:"favorite",title:(0,n.SU)(l)("Toggle favorite"),onClick:t=>z(e.row.id,e.row.favorited)},null,8,["text-color","title","onClick"]),(0,a.Wm)(c,{size:"sm",color:"white","text-color":"grey-5",icon:"delete",title:(0,n.SU)(l)("Remove"),onClick:(0,r.iM)((t=>Q(e.row.index-1)),["stop","prevent"])},null,8,["title","onClick"])])),_:2},1024)])),_:2},1032,["props"])])),_:1},8,["rows","visible-columns"])])),_:1}),(0,a.Wm)(K,{modelValue:I.value,"onUpdate:modelValue":i[3]||(i[3]=e=>I.value=e)},{default:(0,a.w5)((()=>[(0,a.Wm)(k,{style:{"min-width":"350px"}},{default:(0,a.w5)((()=>[(0,a.Wm)(Z,null,{default:(0,a.w5)((()=>[g])),_:1}),(0,a.Wm)(Z,{class:"q-pt-none"},{default:(0,a.w5)((()=>[(0,a.Wm)(F,{outlined:"",dense:"",modelValue:E.value,"onUpdate:modelValue":i[0]||(i[0]=e=>E.value=e),autofocus:"",onKeyup:i[1]||(i[1]=(0,r.D2)((e=>I.value=!1),["enter"])),label:"Playlist name"},null,8,["modelValue"])])),_:1}),(0,a.Wm)(Z,{class:"q-pt-none"},{default:(0,a.w5)((()=>[(0,a.Wm)(O,{label:"Public",color:"pink",modelValue:_.value,"onUpdate:modelValue":i[2]||(i[2]=e=>_.value=e)},null,8,["modelValue"])])),_:1}),(0,a.Wm)(G,{align:"right",class:""},{default:(0,a.w5)((()=>[(0,a.wy)((0,a.Wm)(c,{outline:"",label:"Cancel"},null,512),[[M]]),(0,a.Wm)(c,{outline:"",label:"Save",disable:!E.value,onClick:Y},null,8,["disable"])])),_:1})])),_:1})])),_:1},8,["modelValue"])],64)}}};var h=l(4458),k=l(2605),y=l(8052),f=l(7236),w=l(8879),S=l(3261),x=l(136),C=l(6384),P=l(7220),U=l(2857),I=l(2074),E=l(3190),_=l(6611),q=l(3175),T=l(1821),W=l(2146),z=l(9984),Q=l.n(z);const A=b,Z=A;Q()(b,"components",{QCard:h.Z,QBreadcrumbs:k.Z,QBreadcrumbsEl:y.Z,QBtnGroup:f.Z,QBtn:w.Z,QTable:S.Z,QSpace:x.Z,QSelect:C.Z,QTd:P.Z,QIcon:U.Z,QDialog:I.Z,QCardSection:E.Z,QInput:_.Z,QToggle:q.Z,QCardActions:T.Z}),Q()(b,"directives",{ClosePopup:W.Z})}}]);