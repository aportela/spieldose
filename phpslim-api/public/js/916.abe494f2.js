"use strict";(globalThis["webpackChunkspieldose"]=globalThis["webpackChunkspieldose"]||[]).push([[916],{2719:(e,t,a)=>{a.d(t,{d:()=>u});var l=a(1809),n=a(8612),s=a.n(n);const r={namespace:"spieldose",storages:["local","cookie","session","memory"],storage:"local",expireDays:3650},u=(0,l.Q_)("currentPlaylist",{state:()=>({elements:[],currentIndex:-1}),getters:{hasElements:e=>e.elements&&e.elements.length>0,getElements:e=>e.elements,getCurrentIndex:e=>e.currentIndex,getCurrentElement:e=>e.currentIndex>=0&&e.elements.length>0?e.elements[e.currentIndex]:null,getCurrentElementURL:e=>e.currentIndex>=0&&e.elements.length>0?e.elements[e.currentIndex].url:null,allowSkipPrevious:e=>e.currentIndex>0&&e.elements.length>0,allowSkipNext:e=>e.currentIndex>=0&&e.elements.length>0&&e.currentIndex<e.elements.length-1,allowPlay:e=>!0,allowPause:e=>!0,allowResume:e=>!0,allowStop:e=>!0},actions:{load(){const e=s()(r),t=e.get("currentPlaylistElements");t&&(this.elements=t);const a=e.get("currentPlaylistElementIndex");a>=0&&(this.currentIndex=a)},saveElements(e){this.elements=e,this.currentIndex=e&&e.length>0?0:-1;const t=s()(r);t.set("currentPlaylistElements",e),t.set("currentPlaylistElementIndex",e&&e.length>0?0:-1)},saveCurrentTrackIndex(e){this.currentIndex=e>=0?e:-1;const t=s()(r);t.set("currentPlaylistElementIndex",this.currentIndex)},skipPrevious(){this.currentIndex--},skipNext(){this.currentIndex++}}})},4958:(e,t,a)=>{a.d(t,{n:()=>r});var l=a(1809),n=a(1320);const s=(0,n.l)(),r=(0,l.Q_)("player",{state:()=>({element:null,hasPreviousUserInteractions:!1}),getters:{getElement:e=>e.element,getDuration:e=>e.element.duration},actions:{interact(){this.hasPreviousUserInteractions=!0},create(){this.element=document.createElement("audio"),this.element.id="audio_player"},setVolume(e){this.element.volume=e},setCurrentTime(e){this.element.currentTime=e},stop(){this.element.pause(),s.setStatusStopped()},load(){},play(e){e?(this.element.play(),s.setStatusPlaying()):s.isPlaying?(this.element.pause(),s.setStatusPaused()):s.isPaused?(this.element.play(),s.setStatusPlaying()):(this.element.load(),this.element.play(),s.setStatusPlaying())}}})},1320:(e,t,a)=>{a.d(t,{l:()=>n});var l=a(1809);const n=(0,l.Q_)("playerStatus",{state:()=>({status:"stopped"}),getters:{getStatus(e){return e.status},isPlaying(e){return"playing"==e.status},isStopped(e){return"stopped"==e.status},isPaused(e){return"paused"==e.status}},actions:{setStatusPlaying(){this.status="playing"},setStatusStopped(){this.status="stopped"},setStatusPaused(){this.status="paused"}}})},5916:(e,a,l)=>{l.r(a),l.d(a,{default:()=>L});var n=l(9835),s=l(1957),r=l(6970),u=l(499),o=l(9302),i=l(1569),c=l(4958),d=l(1320),m=l(2719);const p=["onSubmit"],v={key:0},b={key:0,class:"q-pa-lg flex flex-center"},k={class:"q-pa-sm bg-grey-2"},h={class:"bg-grey-2 text-grey-10"},g=(0,n._)("th",{class:"text-center"},"Actions",-1),x={class:"text-left"},w={class:"text-left"},y={class:"text-left"},_={class:"text-left"},f=(0,n._)("span",{class:"is-clickable"},[(0,n._)("i",{class:"fas fa-link ml-1"})],-1),S={class:"text-right"},q={class:"text-right"},P={class:"text-center"},W={__name:"SearchPage",setup(e){const a=(0,o.Z)(),l=(0,m.d)(),W=(0,c.n)(),C=(0,d.l)(),I=(0,u.iH)(null),V=(0,u.iH)(""),E=(0,u.iH)([]),T=(0,u.iH)(!1),U=(0,u.iH)("global"),Q=((0,u.iH)(!1),(0,u.iH)(!1)),A=(0,u.iH)(0),Z=(0,u.iH)(1),z=(0,u.iH)("title"),D=[{label:"Ascending",value:"ASC"},{label:"Descending",value:"DESC"}],H=(0,u.iH)(D[0]);function j(e){Z.value=e,Y(!1)}function N(e){e==z.value?H.value="DESC"==H.value.value?D[0]:D[1]:(H.value=D[0],z.value=e),Y()}function Y(e){e&&(Z.value=1),Q.value=!1,V.value&&V.value.trim().length>0&&(T.value=!0,i.api.track.search({text:V.value},Z.value,16,!1,z.value,H.value.value).then((e=>{A.value=e.data.data.pager.totalPages,E.value=e.data.data.items.map((e=>({track:e}))),Q.value=!0,T.value=!1,(0,n.Y3)((()=>{I.value.$el.focus()}))})).catch((e=>{T.value=!1,a.notify({type:"negative",message:"API Error: error searching tracks",caption:"API Error: fatal error details: HTTP {"+e.response.status+"} ({"+e.response.statusText+"})"})})))}function B(e){W.interact(),l.saveElements([e])}function G(){W.interact(),C.isStopped||W.stop(),l.saveElements(E.value),Q.value=!1,(0,n.Y3)((()=>{W.play(!0)}))}function R(e){if(e&&e.id){const l=e.favorited?i.api.track.unSetFavorite:i.api.track.setFavorite;l(e.id).then((t=>{e.favorited=t.data.favorited})).catch((e=>{switch(e.response.status){default:a.notify({type:"negative",message:t("API Error: fatal error"),caption:t("API Error: fatal error details",{status:e.response.status,statusText:e.response.statusText})});break}}))}}return(0,n.bv)((()=>{(0,n.Y3)((()=>{I.value.$el.focus()}))})),(e,t)=>{const a=(0,n.up)("q-breadcrumbs-el"),l=(0,n.up)("q-breadcrumbs"),u=(0,n.up)("q-tab"),o=(0,n.up)("q-tabs"),i=(0,n.up)("q-separator"),c=(0,n.up)("q-icon"),d=(0,n.up)("q-btn"),m=(0,n.up)("q-input"),W=(0,n.up)("q-tab-panel"),C=(0,n.up)("q-tab-panels"),D=(0,n.up)("q-card"),F=(0,n.up)("q-pagination"),K=(0,n.up)("router-link"),L=(0,n.up)("q-btn-group"),M=(0,n.up)("q-spinner-gears"),$=(0,n.up)("q-inner-loading"),J=(0,n.up)("q-markup-table"),O=(0,n.up)("q-page");return(0,n.wg)(),(0,n.j4)(O,null,{default:(0,n.w5)((()=>[(0,n.Wm)(D,{class:"q-pa-lg"},{default:(0,n.w5)((()=>[(0,n.Wm)(l,{class:"q-mb-lg"},{default:(0,n.w5)((()=>[(0,n.Wm)(a,{icon:"home",label:"Spieldose"}),(0,n.Wm)(a,{icon:"search",label:"Search"})])),_:1}),(0,n.Wm)(D,null,{default:(0,n.w5)((()=>[(0,n.Wm)(o,{modelValue:U.value,"onUpdate:modelValue":t[0]||(t[0]=e=>U.value=e),dense:"",class2:"text-grey","active-color2":"primary","indicator-color2":"primary",align:"justify","narrow-indicator":""},{default:(0,n.w5)((()=>[(0,n.Wm)(u,{name:"global",label:"Global search"}),(0,n.Wm)(u,{name:"advanced",label:"Advanced search"})])),_:1},8,["modelValue"]),(0,n.Wm)(i),(0,n.Wm)(C,{modelValue:U.value,"onUpdate:modelValue":t[8]||(t[8]=e=>U.value=e),animated:""},{default:(0,n.w5)((()=>[(0,n.Wm)(W,{name:"global"},{default:(0,n.w5)((()=>[(0,n._)("form",{onSubmit:(0,s.iM)(Y,["prevent","stop"]),autocorrect:"off",autocapitalize:"off",autocomplete:"off",spellcheck:"false"},[(0,n.Wm)(m,{clearable:"","clear-icon":"close",dense:"",outlined:"",type:"text",name:"searchText",label:"Search text on all fields...",modelValue:V.value,"onUpdate:modelValue":t[1]||(t[1]=e=>V.value=e),"onKey:modelValue":t[2]||(t[2]=e=>Y(!0)),disable:T.value,ref_key:"inputSearchTextRef",ref:I},{prepend:(0,n.w5)((()=>[(0,n.Wm)(c,{name:"filter_alt"})])),after:(0,n.w5)((()=>[(0,n.Wm)(d,{type:"submit",label:"launch search",outline:"",icon:"search",loading:T.value,disable:T.value||!V.value,class:"q-pa-sm",onClick:Y},null,8,["loading","disable"])])),_:1},8,["modelValue","disable"])],40,p)])),_:1}),(0,n.Wm)(W,{name:"advanced"},{default:(0,n.w5)((()=>[(0,n._)("form",null,[(0,n.Wm)(m,{outlined:"",modelValue:e.text,"onUpdate:modelValue":t[3]||(t[3]=t=>e.text=t),label:"Track title",placeholder:"type text condition",hint:"Search on track title",dense:"",clearable:"","clear-icon":"close",disable:T.value},null,8,["modelValue","disable"]),(0,n.Wm)(m,{outlined:"",modelValue:e.text,"onUpdate:modelValue":t[4]||(t[4]=t=>e.text=t),label:"Artist name",placeholder:"type text condition",hint:"Search on artist name",dense:"",clearable:"","clear-icon":"close",disable:T.value},null,8,["modelValue","disable"]),(0,n.Wm)(m,{outlined:"",modelValue:e.text,"onUpdate:modelValue":t[5]||(t[5]=t=>e.text=t),label:"Album name",placeholder:"type text condition",hint:"Search on album name",dense:"",clearable:"","clear-icon":"close",disable:T.value},null,8,["modelValue","disable"]),(0,n.Wm)(m,{outlined:"",modelValue:e.text,"onUpdate:modelValue":t[6]||(t[6]=t=>e.text=t),label:"Album artist",placeholder:"type text condition",hint:"Search on album artist",dense:"",clearable:"","clear-icon":"close",disable:T.value},null,8,["modelValue","disable"]),(0,n.Wm)(m,{outlined:"",modelValue:e.text,"onUpdate:modelValue":t[7]||(t[7]=t=>e.text=t),label:"Year",placeholder:"type year condition",hint:"Search on track year",dense:"",clearable:"","clear-icon":"close",disable:T.value},null,8,["modelValue","disable"])])])),_:1})])),_:1},8,["modelValue"])])),_:1}),E.value&&E.value.length>0?((0,n.wg)(),(0,n.iD)("div",v,[A.value>1?((0,n.wg)(),(0,n.iD)("div",b,[(0,n.Wm)(F,{modelValue:Z.value,"onUpdate:modelValue":[t[9]||(t[9]=e=>Z.value=e),j],color:"dark",max:A.value,"max-pages":5,"boundary-numbers":"","direction-links":"","boundary-links":"",disable:T.value},null,8,["modelValue","max","disable"])])):(0,n.kq)("",!0),(0,n.Wm)(J,{flat:"",bordered:""},{default:(0,n.w5)((()=>[(0,n._)("caption",k,[(0,n.Wm)(d,{outline:"",onClick:G,disable:!Q.value,class:"full-width"},{default:(0,n.w5)((()=>[(0,n.Uk)("Send results to playlist")])),_:1},8,["disable"])]),(0,n._)("thead",null,[(0,n._)("tr",h,[(0,n._)("th",{class:"text-left cursor-pointer",onClick:t[10]||(t[10]=e=>N("title"))},[(0,n.Uk)("Title "),"title"==z.value?((0,n.wg)(),(0,n.j4)(c,{key:0,size:"xl",name:"DESC"==H.value.value?"arrow_drop_down":"arrow_drop_up"},null,8,["name"])):(0,n.kq)("",!0)]),(0,n._)("th",{class:"text-left cursor-pointer",onClick:t[11]||(t[11]=e=>N("artistName"))},[(0,n.Uk)("Artist "),"artistName"==z.value?((0,n.wg)(),(0,n.j4)(c,{key:0,size:"xl",name:"DESC"==H.value.value?"arrow_drop_down":"arrow_drop_up"},null,8,["name"])):(0,n.kq)("",!0)]),(0,n._)("th",{class:"text-left cursor-pointer",onClick:t[12]||(t[12]=e=>N("albumArtistName"))},[(0,n.Uk)("Album Artist "),"albumArtistName"==z.value?((0,n.wg)(),(0,n.j4)(c,{key:0,size:"xl",name:"DESC"==H.value.value?"arrow_drop_down":"arrow_drop_up"},null,8,["name"])):(0,n.kq)("",!0)]),(0,n._)("th",{class:"text-left cursor-pointer",onClick:t[13]||(t[13]=e=>N("releaseTitle"))},[(0,n.Uk)("Album "),"releaseTitle"==z.value?((0,n.wg)(),(0,n.j4)(c,{key:0,size:"xl",name:"DESC"==H.value.value?"arrow_drop_down":"arrow_drop_up"},null,8,["name"])):(0,n.kq)("",!0)]),(0,n._)("th",{class:"text-right cursor-pointer",onClick:t[14]||(t[14]=e=>N("trackNumber"))},["trackNumber"==z.value?((0,n.wg)(),(0,n.j4)(c,{key:0,size:"xl",name:"DESC"==H.value.value?"arrow_drop_down":"arrow_drop_up"},null,8,["name"])):(0,n.kq)("",!0),(0,n.Uk)(" Album Track nº")]),(0,n._)("th",{class:"text-right cursor-pointer",onClick:t[15]||(t[15]=e=>N("year"))},["year"==z.value?((0,n.wg)(),(0,n.j4)(c,{key:0,size:"xl",name:"DESC"==H.value.value?"arrow_drop_down":"arrow_drop_up"},null,8,["name"])):(0,n.kq)("",!0),(0,n.Uk)(" Year")]),g])]),(0,n._)("tbody",null,[((0,n.wg)(!0),(0,n.iD)(n.HY,null,(0,n.Ko)(E.value,(e=>((0,n.wg)(),(0,n.iD)("tr",{key:e.track.id,class:"non-selectable"},[(0,n._)("td",x,(0,r.zw)(e.track.title),1),(0,n._)("td",w,[e.track.artist&&e.track.artist.name?((0,n.wg)(),(0,n.j4)(K,{key:0,to:{name:"artist",params:{name:e.track.artist.name}}},{default:(0,n.w5)((()=>[(0,n.Wm)(c,{name:"link",class:"q-mr-sm"}),(0,n.Uk)((0,r.zw)(e.track.artist.name),1)])),_:2},1032,["to"])):(0,n.kq)("",!0)]),(0,n._)("td",y,[e.track.album&&e.track.album.artist.name?((0,n.wg)(),(0,n.j4)(K,{key:0,to:{name:"artist",params:{name:e.track.album.artist.name}}},{default:(0,n.w5)((()=>[(0,n.Wm)(c,{name:"link",class:"q-mr-sm"}),(0,n.Uk)((0,r.zw)(e.track.album.artist.name),1)])),_:2},1032,["to"])):(0,n.kq)("",!0)]),(0,n._)("td",_,[(0,n.Uk)((0,r.zw)(e.track.album?e.track.album.title:null),1),f]),(0,n._)("td",S,(0,r.zw)(e.track.trackNumber),1),(0,n._)("td",q,(0,r.zw)(e.track.album?e.track.album.year:null),1),(0,n._)("td",P,[(0,n.Wm)(L,{outline:""},{default:(0,n.w5)((()=>[(0,n.Wm)(d,{size:"sm",color:"white","text-color":"grey-5",icon:"play_arrow",title:"Play",disable:T.value,onClick:t=>B(e)},null,8,["disable","onClick"]),(0,n.Wm)(d,{size:"sm",color:"white","text-color":"grey-5",icon:"download",title:"Download",disable:T.value,href:e.track.url},null,8,["disable","href"]),(0,n.Wm)(d,{size:"sm",color:"white","text-color":e.track.favorited?"pink":"grey-5",icon:"favorite",title:"Toggle favorite",disable:T.value,onClick:t=>R(e.track)},null,8,["text-color","disable","onClick"])])),_:2},1024)])])))),128))]),(0,n.Wm)($,{showing:T.value},{default:(0,n.w5)((()=>[(0,n.Wm)(M,{size:"50px",color:"pink"})])),_:1},8,["showing"])])),_:1})])):(0,n.kq)("",!0)])),_:1})])),_:1})}}};var C=l(9885),I=l(4458),V=l(2605),E=l(8052),T=l(7817),U=l(7661),Q=l(926),A=l(9800),Z=l(4106),z=l(6611),D=l(2857),H=l(8879),j=l(996),N=l(6933),Y=l(7236),B=l(854),G=l(9120),R=l(9984),F=l.n(R);const K=W,L=K;F()(W,"components",{QPage:C.Z,QCard:I.Z,QBreadcrumbs:V.Z,QBreadcrumbsEl:E.Z,QTabs:T.Z,QTab:U.Z,QSeparator:Q.Z,QTabPanels:A.Z,QTabPanel:Z.Z,QInput:z.Z,QIcon:D.Z,QBtn:H.Z,QPagination:j.Z,QMarkupTable:N.Z,QBtnGroup:Y.Z,QInnerLoading:B.Z,QSpinnerGears:G.Z})}}]);