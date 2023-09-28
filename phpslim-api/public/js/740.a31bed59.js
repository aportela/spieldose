"use strict";(globalThis["webpackChunkspieldose"]=globalThis["webpackChunkspieldose"]||[]).push([[740],{8740:(e,l,a)=>{a.r(l),a.d(l,{default:()=>E});var t=a(9835),r=a(499),i=a(6970),n=a(1957),s=a(9302),o=a(796),u=a(6647),d=a(1569),c=a(5090),m=a(8106);const p={class:"title-4"},b={class:"text-weight-bold"},v=(0,t._)("div",{class:"text-h6"},"Save current playlist",-1),k={__name:"CurrentPlaylistPage",setup(e){const l=(0,s.Z)(),{t:a}=(0,u.QT)(),k=(0,c.H)(),g=(0,t.Fl)((()=>k.getCurrentPlaylistLastChangedTimestamp)),w=(0,r.iH)(null),f=(0,r.iH)([]),y=[{name:"index",required:!0,label:"Index",align:"right",field:e=>e.index,sortable:!1},{name:"title",required:!0,label:"Title",align:"left",field:e=>e.title,sortable:!0},{name:"artist",required:!0,label:"Artist",align:"left",field:e=>e.artist.name,sortable:!0},{name:"albumArtist",required:!1,label:"Album artist",align:"left",field:e=>e.album.artist.name,sortable:!0},{name:"albumTitle",required:!1,label:"Album",align:"left",field:e=>e.album.title,sortable:!0},{name:"albumTrackIndex",required:!1,label:"Album Track nº",align:"right",field:e=>e.trackNumber,sortable:!0},{name:"year",required:!1,label:"Year",align:"right",field:e=>e.album.year,sortable:!0},{name:"actions",required:!0,label:"Actions",align:"center",favorited:e=>e.favorited}],U=(0,r.iH)(y.map((e=>e.name))),S=((0,r.iH)({descending:!1,page:1,rowsPerPage:64}),(0,t.Fl)((()=>k.isPlaying?"play_arrow":k.isPaused?"pause":k.isStopped?"stop":"play_arrow")));(0,t.YP)(g,(e=>{C.value=k.getCurrentPlaylist.elements,f.value=C.value.map(((e,l)=>(e.track.index=l+1,e.track))),h.value=k.getCurrentPlaylistIndex}));const C=(0,r.iH)([]),h=(0,r.iH)(0),_=(0,r.iH)(!1),x=(0,r.iH)(!1),W=(0,r.iH)(null),P=(0,r.iH)(!1);function q(){k.clearCurrentPlaylist()}function T(e){const l=C.value[e];C.value.splice(e,1),C.value.splice(e-1,0,l)}function z(e){const l=C.value[e];C.value.splice(e,1),C.value.splice(e+1,0,l)}function Z(e,t){const r=t?m.dC.unSetFavorite:m.dC.setFavorite;r(e).then((e=>{})).catch((e=>{switch(e.response.status){default:l.notify({type:"negative",message:a("API Error: error when toggling favorite flag"),caption:a("API Error: fatal error details",{status:e.response.status,statusText:e.response.statusText})});break}}))}function Q(e){}function I(e,l,a){"A"!=e.target.nodeName&&"I"!=e.target.nodeName&&"BUTTON"!=e.target.nodeName&&(k.interact(),k.skipToIndex(a))}function A(){k.interact(),_.value=!0,h.value=0,d.api.track.search({},1,32,!0,null,null).then((e=>{C.value=e.data.data.items.map((e=>({track:e}))),f.value=C.value.map(((e,l)=>(e.track.index=l+1,e.track))),k.sendElementsToCurrentPlaylist(C.value),w.value.scrollTo(0,"center-force"),_.value=!1})).catch((e=>{l.notify({type:"negative",message:a("API Error: error loading random tracks"),caption:a("API Error: fatal error details",{status:e&&e.response?e.response.status:"undefined",statusText:e&&e.response?e.response.statusText:"undefined"})}),_.value=!1}))}const H=(0,t.Fl)((()=>k.getShuffle?k.getShuffleCurrentPlaylistIndex:k.getCurrentPlaylistIndex));function E(){k.stop(),A()}function N(){k.interact(),m.oS.skipPrevious()}function V(){k.interact(),k.play()}function j(){k.interact(),k.play()}function B(){k.interact(),k.play()}function R(){k.stop(),k.setCurrentTime(0)}function D(){k.interact(),k.skipNext()}function F(){W.value=null,x.value=!0}function Y(){k.interact(),_.value=!0,h.value=0,d.api.playlist.add((0,o.Z)(),W.value,C.value.filter((e=>e.track)).map((e=>e.track.id)),P.value).then((e=>{_.value=!1,x.value=!1})).catch((e=>{l.notify({type:"negative",message:a("API Error: error loading random tracks"),caption:a("API Error: fatal error details",{status:e&&e.response?e.response.status:"undefined",statusText:e&&e.response?e.response.statusText:"undefined"})}),_.value=!1}))}return(0,t.YP)(H,(e=>{h.value=e,w.value.scrollTo(e,"center-force")})),C.value=k.getCurrentPlaylist.elements,f.value=C.value.map(((e,l)=>(e.track.index=l+1,e.track))),h.value=H.value,(0,t.bv)((()=>{h.value>0&&w.value.scrollTo(h.value,"center-force")})),(e,s)=>{const o=(0,t.up)("q-breadcrumbs-el"),u=(0,t.up)("q-breadcrumbs"),d=(0,t.up)("q-btn"),c=(0,t.up)("q-btn-group"),m=(0,t.up)("router-link"),g=(0,t.up)("q-icon"),A=(0,t.up)("q-td"),H=(0,t.up)("q-tr"),L=(0,t.up)("q-table"),G=(0,t.up)("q-card"),K=(0,t.up)("q-card-section"),M=(0,t.up)("q-input"),O=(0,t.up)("q-toggle"),J=(0,t.up)("q-card-actions"),X=(0,t.up)("q-dialog"),$=(0,t.Q2)("close-popup");return(0,t.wg)(),(0,t.iD)(t.HY,null,[(0,t.Wm)(G,{class:"q-pa-lg"},{default:(0,t.w5)((()=>[(0,t.Wm)(u,{class:"q-mb-lg"},{default:(0,t.w5)((()=>[(0,t.Wm)(o,{icon:"home",label:"Spieldose"}),(0,t.Wm)(o,{icon:"list_alt",label:(0,r.SU)(a)("Current playlist")},null,8,["label"])])),_:1}),(0,t.Wm)(c,{spread:"",class:"q-mb-md"},{default:(0,t.w5)((()=>[(0,t.Wm)(d,{size:"md",outline:"",color:"dark",label:(0,r.SU)(l).screen.gt.md?(0,r.SU)(a)("Clear"):"",icon:"clear",onClick:q,disable:_.value||!(C.value&&C.value.length>0)},null,8,["label","disable"]),(0,t.Wm)(d,{size:"md",outline:"",color:"dark",label:(0,r.SU)(l).screen.gt.md?(0,r.SU)(a)("Randomize"):"",icon:"bolt",onClick:E,disable:_.value},null,8,["label","disable"]),(0,t.Wm)(d,{size:"md",outline:"",color:"dark",label:(0,r.SU)(l).screen.gt.md?(0,r.SU)(a)("Previous"):"",icon:"skip_previous",onClick:N,disable:_.value||!(0,r.SU)(k).allowSkipPrevious},null,8,["label","disable"]),(0,r.SU)(k).isStopped?((0,t.wg)(),(0,t.j4)(d,{key:0,size:"md",outline:"",color:"dark",label:(0,r.SU)(l).screen.gt.md?(0,r.SU)(a)("Play"):"",icon:"play_arrow",onClick:V,disable:_.value||!(0,r.SU)(k).hasCurrentPlaylistElements},null,8,["label","disable"])):(0,r.SU)(k).isPlaying?((0,t.wg)(),(0,t.j4)(d,{key:1,size:"md",outline:"",color:"dark",label:(0,r.SU)(l).screen.gt.md?(0,r.SU)(a)("Pause"):"",icon:"pause",onClick:j,disable:_.value||!(C.value&&C.value.length>0)},null,8,["label","disable"])):(0,r.SU)(k).isPaused?((0,t.wg)(),(0,t.j4)(d,{key:2,size:"md",outline:"",color:"dark",label:(0,r.SU)(l).screen.gt.md?(0,r.SU)(a)("Resume"):"",icon:"play_arrow",onClick:B,disable:_.value||!(C.value&&C.value.length>0)},null,8,["label","disable"])):(0,t.kq)("",!0),(0,t.Wm)(d,{size:"md",outline:"",color:"dark",label:(0,r.SU)(l).screen.gt.md?(0,r.SU)(a)("Stop"):"",icon:"stop",onClick:R,disable:_.value||(0,r.SU)(k).isStopped||!(C.value&&C.value.length>0)},null,8,["label","disable"]),(0,t.Wm)(d,{size:"md",outline:"",color:"dark",label:(0,r.SU)(l).screen.gt.md?(0,r.SU)(a)("Next"):"",icon:"skip_next",onClick:D,disable:_.value||!(0,r.SU)(k).allowSkipNext},null,8,["label","disable"]),(0,t.Wm)(d,{size:"md",outline:"",color:"dark",label:(0,r.SU)(l).screen.gt.md?(0,r.SU)(a)("Download"):"",icon:"save_alt",disable:_.value||!(0,r.SU)(k).getCurrentElementURL,href:(0,r.SU)(k).getCurrentElementURL},null,8,["label","disable","href"]),(0,t.Wm)(d,{size:"md",outline:"",color:"dark",label:(0,r.SU)(l).screen.gt.md?(0,r.SU)(a)("Save as"):"",icon:"save_alt",disable:_.value||!(C.value&&C.value.length>0),onClick:F},null,8,["label","disable"])])),_:1}),(0,t.Wm)(L,{ref_key:"tableRef",ref:w,class:"my-sticky-header-table",style:{height:"46.8em"},rows:f.value,columns:y,"row-key":"id","virtual-scroll":"","rows-per-page-options":[0],"visible-columns":U.value,"hide-bottom":!0},(0,t.Nv)({body:(0,t.w5)((e=>[(0,t.Wm)(H,{class:(0,i.C_)(["cursor-pointer",{"selected-row":h.value+1==e.row.index}]),props:e,onClick:l=>I(l,e.row,e.row.index-1)},{default:(0,t.w5)((()=>[(0,t.Wm)(A,{key:"index",props:e},{default:(0,t.w5)((()=>[h.value+1==e.row.index?((0,t.wg)(),(0,t.j4)(g,{key:0,name:S.value,color:"pink",size:"sm",class:"q-mr-sm"},null,8,["name"])):(0,t.kq)("",!0),(0,t.Uk)(" "+(0,i.zw)(e.row.index)+" / "+(0,i.zw)(f.value.length),1)])),_:2},1032,["props"]),(0,t.Wm)(A,{key:"title",props:e},{default:(0,t.w5)((()=>[(0,t.Uk)((0,i.zw)(e.row.title),1)])),_:2},1032,["props"]),(0,t.Wm)(A,{key:"artist",props:e},{default:(0,t.w5)((()=>[e.row.artist.name?((0,t.wg)(),(0,t.j4)(m,{key:0,class:(0,i.C_)({"text-white text-bold":!1}),to:{name:"artist",params:{name:e.row.artist.name}}},{default:(0,t.w5)((()=>[(0,t.Wm)(g,{name:"link",class:"q-mr-sm"}),(0,t.Uk)((0,i.zw)(e.row.artist.name),1)])),_:2},1032,["to"])):(0,t.kq)("",!0)])),_:2},1032,["props"]),(0,t.Wm)(A,{key:"albumArtist",props:e},{default:(0,t.w5)((()=>[e.row.album.artist.name?((0,t.wg)(),(0,t.j4)(m,{key:0,class:(0,i.C_)({"text-white text-bold":!1}),to:{name:"artist",params:{name:e.row.album.artist.name}}},{default:(0,t.w5)((()=>[(0,t.Wm)(g,{name:"link",class:"q-mr-sm"}),(0,t.Uk)((0,i.zw)(e.row.album.artist.name),1)])),_:2},1032,["to"])):(0,t.kq)("",!0)])),_:2},1032,["props"]),(0,t.Wm)(A,{key:"albumTitle",props:e},{default:(0,t.w5)((()=>[(0,t.Uk)((0,i.zw)(e.row.album.title),1)])),_:2},1032,["props"]),(0,t.Wm)(A,{key:"albumTrackIndex",props:e},{default:(0,t.w5)((()=>[(0,t.Uk)((0,i.zw)(e.row.trackNumber),1)])),_:2},1032,["props"]),(0,t.Wm)(A,{key:"year",props:e},{default:(0,t.w5)((()=>[(0,t.Uk)((0,i.zw)(e.row.album.year),1)])),_:2},1032,["props"]),(0,t.Wm)(A,{key:"actions",props:e},{default:(0,t.w5)((()=>[(0,t.Wm)(c,{outline:""},{default:(0,t.w5)((()=>[(0,t.Wm)(d,{size:"sm",color:"white","text-color":"grey-5",icon:"north",title:(0,r.SU)(a)("Up"),disable:"",onClick:T},null,8,["title"]),(0,t.Wm)(d,{size:"sm",color:"white","text-color":"grey-5",icon:"south",title:(0,r.SU)(a)("Down"),disable:"",onClick:z},null,8,["title"]),(0,t.Wm)(d,{size:"sm",color:"white","text-color":e.row.favorited?"pink":"grey-5",icon:"favorite",title:(0,r.SU)(a)("Toggle favorite"),onClick:l=>Z(e.row.id,e.row.favorited)},null,8,["text-color","title","onClick"]),(0,t.Wm)(d,{size:"sm",color:"white","text-color":"grey-5",icon:"delete",title:(0,r.SU)(a)("Remove"),onClick:(0,n.iM)((l=>Q(e.row.index-1)),["stop","prevent"])},null,8,["title","onClick"])])),_:2},1024)])),_:2},1032,["props"])])),_:2},1032,["props","onClick","class"])])),_:2},[(0,r.SU)(k).getCurrentPlaylist.id?{name:"top",fn:(0,t.w5)((()=>[(0,t._)("p",p,[(0,t.Uk)(" Playlist: “"),(0,t._)("span",b,(0,i.zw)((0,r.SU)(k).getCurrentPlaylist.name),1),(0,t.Uk)("” by "),(0,t.Wm)(m,{to:{name:"playlistsByUserId",params:{id:(0,r.SU)(k).getCurrentPlaylist.owner.id}}},{default:(0,t.w5)((()=>[(0,t.Uk)((0,i.zw)((0,r.SU)(k).getCurrentPlaylist.owner.name),1)])),_:1},8,["to"])])])),key:"0"}:void 0]),1032,["rows","visible-columns"])])),_:1}),(0,t.Wm)(X,{modelValue:x.value,"onUpdate:modelValue":s[3]||(s[3]=e=>x.value=e)},{default:(0,t.w5)((()=>[(0,t.Wm)(G,{style:{"min-width":"350px"}},{default:(0,t.w5)((()=>[(0,t.Wm)(K,null,{default:(0,t.w5)((()=>[v])),_:1}),(0,t.Wm)(K,{class:"q-pt-none"},{default:(0,t.w5)((()=>[(0,t.Wm)(M,{outlined:"",dense:"",modelValue:W.value,"onUpdate:modelValue":s[0]||(s[0]=e=>W.value=e),autofocus:"",onKeyup:s[1]||(s[1]=(0,n.D2)((e=>x.value=!1),["enter"])),label:"Playlist name"},null,8,["modelValue"])])),_:1}),(0,t.Wm)(K,{class:"q-pt-none"},{default:(0,t.w5)((()=>[(0,t.Wm)(O,{label:"Public",color:"pink",modelValue:P.value,"onUpdate:modelValue":s[2]||(s[2]=e=>P.value=e)},null,8,["modelValue"])])),_:1}),(0,t.Wm)(J,{align:"right",class:""},{default:(0,t.w5)((()=>[(0,t.wy)((0,t.Wm)(d,{outline:"",label:"Cancel"},null,512),[[$]]),(0,t.Wm)(d,{outline:"",label:"Save",disable:!W.value,onClick:Y},null,8,["disable"])])),_:1})])),_:1})])),_:1},8,["modelValue"])],64)}}};var g=a(4458),w=a(2605),f=a(8052),y=a(7236),U=a(8879),S=a(9994),C=a(136),h=a(6384),_=a(3532),x=a(7220),W=a(2857),P=a(2074),q=a(3190),T=a(6611),z=a(3175),Z=a(1821),Q=a(2146),I=a(9984),A=a.n(I);const H=k,E=H;A()(k,"components",{QCard:g.Z,QBreadcrumbs:w.Z,QBreadcrumbsEl:f.Z,QBtnGroup:y.Z,QBtn:U.Z,QTable:S.Z,QSpace:C.Z,QSelect:h.Z,QTr:_.Z,QTd:x.Z,QIcon:W.Z,QDialog:P.Z,QCardSection:q.Z,QInput:T.Z,QToggle:z.Z,QCardActions:Z.Z}),A()(k,"directives",{ClosePopup:Q.Z})}}]);