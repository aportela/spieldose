"use strict";(globalThis["webpackChunkspieldose"]=globalThis["webpackChunkspieldose"]||[]).push([[108],{2719:(e,t,a)=>{a.d(t,{d:()=>o});var l=a(1809),s=a(8612),n=a.n(s);const i={namespace:"spieldose",storages:["local","cookie","session","memory"],storage:"local",expireDays:3650},o=(0,l.Q_)("currentPlaylist",{state:()=>({elements:[],currentIndex:-1,elementsLastChangeTimestamp:null}),getters:{hasElements:e=>e.elements&&e.elements.length>0,getElements:e=>e.elements,getElementsLastChangeTimestamp:e=>e.elementsLastChangeTimestamp,getCurrentIndex:e=>e.currentIndex,getCurrentElement:e=>e.currentIndex>=0&&e.elements.length>0?e.elements[e.currentIndex]:null,getCurrentElementURL:e=>e.currentIndex>=0&&e.elements.length>0?e.elements[e.currentIndex].url:null,allowSkipPrevious:e=>e.currentIndex>0&&e.elements.length>0,allowSkipNext:e=>e.currentIndex>=0&&e.elements.length>0&&e.currentIndex<e.elements.length-1,allowPlay:e=>!0,allowPause:e=>!0,allowResume:e=>!0,allowStop:e=>!0},actions:{load(){const e=n()(i),t=e.get("currentPlaylistElements");t&&(this.elements=t);const a=e.get("currentPlaylistElementIndex");a>=0&&(this.currentIndex=a)},saveElements(e){this.elements=e,this.currentIndex=e&&e.length>0?0:-1;const t=n()(i);t.set("currentPlaylistElements",e),t.set("currentPlaylistElementIndex",e&&e.length>0?0:-1),this.elementsLastChangeTimestamp=Date.now()},saveCurrentTrackIndex(e){this.currentIndex=e>=0?e:-1;const t=n()(i);t.set("currentPlaylistElementIndex",this.currentIndex)},skipPrevious(){this.currentIndex--},skipNext(){this.currentIndex++}}})},4958:(e,t,a)=>{a.d(t,{n:()=>i});var l=a(1809),s=a(1320);const n=(0,s.l)(),i=(0,l.Q_)("player",{state:()=>({element:null,hasPreviousUserInteractions:!1}),getters:{getElement:e=>e.element,getDuration:e=>e.element.duration},actions:{interact(){this.hasPreviousUserInteractions=!0},create(){this.element=document.createElement("audio"),this.element.id="audio_player"},setVolume(e){this.element.volume=e},setCurrentTime(e){this.element.currentTime=e},stop(){this.element.pause(),n.setStatusStopped()},load(){},play(e){e?(this.element.play(),n.setStatusPlaying()):n.isPlaying?(this.element.pause(),n.setStatusPaused()):n.isPaused?(this.element.play(),n.setStatusPlaying()):(this.element.load(),this.element.play(),n.setStatusPlaying())}}})},1320:(e,t,a)=>{a.d(t,{l:()=>s});var l=a(1809);const s=(0,l.Q_)("playerStatus",{state:()=>({status:"stopped"}),getters:{getStatus(e){return e.status},isPlaying(e){return"playing"==e.status},isStopped(e){return"stopped"==e.status},isPaused(e){return"paused"==e.status}},actions:{setStatusPlaying(){this.status="playing"},setStatusStopped(){this.status="stopped"},setStatusPaused(){this.status="paused"}}})},5108:(e,t,a)=>{a.r(t),a.d(t,{default:()=>A});var l=a(9835),s=a(499),n=a(1957),i=a(6970),o=a(1569),r=a(9302),u=a(7712),d=a(4958),m=a(2719);const c={key:0,class:"q-pa-lg flex flex-center"},p={class:"q-gutter-md row items-start"},g=["radioStation","onClick"],v={class:"absolute-bottom text-subtitle1 text-center"},h={class:"absolute-full flex flex-center bg-grey-3 text-dark"},b={class:"absolute-bottom text-subtitle1 text-center bg-grey-5 q-py-md"},x={class:"row q-gutter-xs"},w={class:"col"},y={class:"col-xl-2"},f={class:"col-xl-2"},S={class:"col-xl-2"},k={key:0,class:"q-pa-lg flex flex-center"},_={class:"q-gutter-md row items-start"},P=["radioStation","onClick"],q={class:"absolute-bottom text-subtitle1 text-center"},V={class:"absolute-full flex flex-center bg-grey-3 text-dark"},I={class:"absolute-bottom text-subtitle1 text-center bg-grey-5 q-py-md"},C={__name:"BrowseRadioStationsPage",setup(e){const t=(0,d.n)(),a=(0,m.d)(),C=(0,r.Z)(),{t:W}=(0,u.QT)(),T=(0,s.iH)([{label:"My radio stations",value:"myRadioStations",icon:"person"},{label:"Public radio stations",value:"publicRadioStations",icon:"public"}]),E=(0,s.iH)(T.value[0].value),Q=(0,s.iH)(null),H=(0,s.iH)(null),Z=(0,s.iH)([]),U=(0,s.iH)(null),D=(0,s.iH)(!1),R=(0,s.iH)(!1),L=(0,s.iH)([]),N=(0,s.iH)(0),K=(0,s.iH)(1);function M(e){e&&(K.value=1),D.value=!1,R.value=!0,o.api.radioStation.search(K.value,32,{name:U.value}).then((e=>{L.value=e.data.data.items,N.value=e.data.data.pager.totalPages,U.value&&e.data.data.pager.totalResults<1&&(D.value=!0),R.value=!1})).catch((e=>{C.notify({type:"negative",message:"API Error: error loading radio stations",caption:"API Error: fatal error details: HTTP {"+e.response.status+"} ({"+e.response.statusText+"})"}),R.value=!1}))}function j(e){K.value=e,M(!1)}function z(e){t.interact(),a.saveElements([{radioStation:e}])}return M(!0),(e,t)=>{const a=(0,l.up)("q-breadcrumbs-el"),o=(0,l.up)("q-breadcrumbs"),r=(0,l.up)("q-tab"),u=(0,l.up)("q-tabs"),d=(0,l.up)("q-icon"),m=(0,l.up)("q-input"),C=(0,l.up)("q-pagination"),B=(0,l.up)("q-img"),Y=(0,l.up)("q-card-section"),A=(0,l.up)("q-tab-panel"),F=(0,l.up)("q-select"),G=(0,l.up)("q-tab-panels"),J=(0,l.up)("q-card"),O=(0,l.up)("q-page");return(0,l.wg)(),(0,l.j4)(O,null,{default:(0,l.w5)((()=>[(0,l.Wm)(J,{class:"q-pa-lg"},{default:(0,l.w5)((()=>[(0,l.Wm)(o,{class:"q-mb-lg"},{default:(0,l.w5)((()=>[(0,l.Wm)(a,{icon:"home",label:"Spieldose"}),(0,l.Wm)(a,{icon:"radio",label:"Browse radio stations"})])),_:1}),(0,l.Wm)(u,{modelValue:E.value,"onUpdate:modelValue":t[0]||(t[0]=e=>E.value=e),"inline-label":"","no-caps":"",dense:"",class:"text-pink-7 q-mb-md shadow-2"},{default:(0,l.w5)((()=>[((0,l.wg)(!0),(0,l.iD)(l.HY,null,(0,l.Ko)(T.value,(e=>((0,l.wg)(),(0,l.j4)(r,{key:e.value,name:e.value,icon:e.icon,label:(0,s.SU)(W)(e.label)},null,8,["name","icon","label"])))),128))])),_:1},8,["modelValue"]),(0,l.Wm)(G,{modelValue:E.value,"onUpdate:modelValue":t[12]||(t[12]=e=>E.value=e),animated:""},{default:(0,l.w5)((()=>[(0,l.Wm)(A,{name:"myRadioStations"},{default:(0,l.w5)((()=>[L.value?((0,l.wg)(),(0,l.j4)(Y,{key:0},{default:(0,l.w5)((()=>[(0,l.Wm)(m,{modelValue:U.value,"onUpdate:modelValue":t[1]||(t[1]=e=>U.value=e),clearable:"",type:"search",outlined:"",dense:"",placeholder:"Text condition",hint:"Search radio stations with name",loading:R.value,disable:R.value,onKeydown:t[2]||(t[2]=(0,n.D2)((0,n.iM)((e=>M(!0)),["prevent"]),["enter"])),onClear:t[3]||(t[3]=e=>{D.value=!1,M(!0)}),error:D.value,errorMessage:"No radio stations found with specified condition",ref:"personalRadioStationNameRef"},{prepend:(0,l.w5)((()=>[(0,l.Wm)(d,{name:"filter_alt"})])),append:(0,l.w5)((()=>[(0,l.Wm)(d,{name:"search",class:"cursor-pointer",onClick:M})])),_:1},8,["modelValue","loading","disable","error"]),N.value>1?((0,l.wg)(),(0,l.iD)("div",c,[(0,l.Wm)(C,{modelValue:K.value,"onUpdate:modelValue":[t[4]||(t[4]=e=>K.value=e),j],color:"dark",max:N.value,"max-pages":5,"boundary-numbers":"","direction-links":"","boundary-links":"",disable:R.value},null,8,["modelValue","max","disable"])])):(0,l.kq)("",!0),(0,l._)("div",p,[((0,l.wg)(!0),(0,l.iD)(l.HY,null,(0,l.Ko)(e.radioStations,(e=>((0,l.wg)(),(0,l.iD)("div",{key:e.id,radioStation:e,class:"cursor-pointer",onClick:t=>z(e)},[(0,l.Wm)(B,{"img-class":"radiostation_image",src:e.image||"#",width:"250px",height:"250px",fit:"cover"},{error:(0,l.w5)((()=>[(0,l._)("div",h,[(0,l._)("div",b,(0,i.zw)(e.name),1)])])),default:(0,l.w5)((()=>[(0,l._)("div",v,(0,i.zw)(e.name),1)])),_:2},1032,["src"])],8,g)))),128))])])),_:1})):(0,l.kq)("",!0)])),_:1}),(0,l.Wm)(A,{name:"publicRadioStations"},{default:(0,l.w5)((()=>[L.value?((0,l.wg)(),(0,l.j4)(Y,{key:0},{default:(0,l.w5)((()=>[(0,l._)("div",x,[(0,l._)("div",w,[(0,l.Wm)(m,{modelValue:U.value,"onUpdate:modelValue":t[5]||(t[5]=e=>U.value=e),clearable:"",type:"search",outlined:"",dense:"",placeholder:"Text condition",hint:"Search radio stations with name",loading:R.value,disable:R.value,onKeydown:t[6]||(t[6]=(0,n.D2)((0,n.iM)((e=>M(!0)),["prevent"]),["enter"])),onClear:t[7]||(t[7]=e=>{D.value=!1,M(!0)}),error:D.value,errorMessage:"No radio stations found with specified condition",ref:"personalRadioStationNameRef"},{prepend:(0,l.w5)((()=>[(0,l.Wm)(d,{name:"filter_alt"})])),append:(0,l.w5)((()=>[(0,l.Wm)(d,{name:"search",class:"cursor-pointer",onClick:M})])),_:1},8,["modelValue","loading","disable","error"])]),(0,l._)("div",y,[(0,l.Wm)(F,{label:"Country",dense:"",outlined:"",options:["Spain","Portugal","France"],modelValue:Q.value,"onUpdate:modelValue":t[8]||(t[8]=e=>Q.value=e)},null,8,["modelValue"])]),(0,l._)("div",f,[(0,l.Wm)(F,{label:"Language",dense:"",outlined:"",options:["English","Spanish","Galician"],modelValue:H.value,"onUpdate:modelValue":t[9]||(t[9]=e=>H.value=e)},null,8,["modelValue"])]),(0,l._)("div",S,[(0,l.Wm)(F,{label:"Tags",dense:"",outlined:"",options:["news","music","rock"],multiple:"",modelValue:Z.value,"onUpdate:modelValue":t[10]||(t[10]=e=>Z.value=e)},null,8,["modelValue"])])]),N.value>1?((0,l.wg)(),(0,l.iD)("div",k,[(0,l.Wm)(C,{modelValue:K.value,"onUpdate:modelValue":[t[11]||(t[11]=e=>K.value=e),j],color:"dark",max:N.value,"max-pages":5,"boundary-numbers":"","direction-links":"","boundary-links":"",disable:R.value},null,8,["modelValue","max","disable"])])):(0,l.kq)("",!0),(0,l._)("div",_,[((0,l.wg)(!0),(0,l.iD)(l.HY,null,(0,l.Ko)(e.radioStations,(e=>((0,l.wg)(),(0,l.iD)("div",{key:e.id,radioStation:e,class:"cursor-pointer",onClick:t=>z(e)},[(0,l.Wm)(B,{"img-class":"radiostation_image",src:e.image||"#",width:"250px",height:"250px",fit:"cover"},{error:(0,l.w5)((()=>[(0,l._)("div",V,[(0,l._)("div",I,(0,i.zw)(e.name),1)])])),default:(0,l.w5)((()=>[(0,l._)("div",q,(0,i.zw)(e.name),1)])),_:2},1032,["src"])],8,P)))),128))])])),_:1})):(0,l.kq)("",!0)])),_:1})])),_:1},8,["modelValue"])])),_:1})])),_:1})}}};var W=a(9885),T=a(4458),E=a(2605),Q=a(8052),H=a(7817),Z=a(7661),U=a(9800),D=a(4106),R=a(3190),L=a(6611),N=a(2857),K=a(996),M=a(335),j=a(8401),z=a(9984),B=a.n(z);const Y=C,A=Y;B()(C,"components",{QPage:W.Z,QCard:T.Z,QBreadcrumbs:E.Z,QBreadcrumbsEl:Q.Z,QTabs:H.Z,QTab:Z.Z,QTabPanels:U.Z,QTabPanel:D.Z,QCardSection:R.Z,QInput:L.Z,QIcon:N.Z,QPagination:K.Z,QImg:M.Z,QSelect:j.Z})}}]);