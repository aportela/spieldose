"use strict";(globalThis["webpackChunkspieldose"]=globalThis["webpackChunkspieldose"]||[]).push([[295],{1295:(l,e,a)=>{a.r(e),a.d(e,{default:()=>ll});var s=a(9835),o=a(1957),t=a(499),c=a(1569),i=a(9302),r=a(6970);const n={class:"row"},u={class:"col-4"},d=["src"],m={class:"col-4"},p=["src"],v={class:"col-4"},_=["src"],g={class:"row"},y={class:"col-4"},w=["src"],b={class:"col-4"},k=["src"],q={class:"col-4"},f=["src"],h={class:"row"},W={class:"col-4"},Z=["src"],Q={class:"col-4"},x=["src"],C={class:"col-4"},H=["src"],j=["src"],P="images/vinyl.png",V={__name:"BrowsePlaylistItem",props:{playlist:Object,mode:String},emits:["play"],setup(l,{emit:e}){const a=l,o=(0,s.Fl)((()=>"mosaic"==a.mode)),t=(0,s.Fl)((()=>"vinylCollection"==a.mode));return(e,a)=>{const c=(0,s.up)("q-card-section"),i=(0,s.up)("q-separator"),V=(0,s.up)("q-avatar"),B=(0,s.up)("q-btn"),T=(0,s.up)("q-btn-group"),D=(0,s.up)("q-card");return(0,s.wg)(),(0,s.j4)(D,{class:"col-xl-2 col-lg-3 col-md-12 col-sm-12 col-xs-12 shadow-box shadow-10 q-mt-lg",bordered:""},{default:(0,s.w5)((()=>[(0,s.Wm)(c,null,{default:(0,s.w5)((()=>[(0,s.Uk)((0,r.zw)(l.playlist.name)+" ("+(0,r.zw)(l.playlist.trackCount)+" track/s) ",1)])),_:1}),(0,s.Wm)(i),o.value?((0,s.wg)(),(0,s.j4)(c,{key:0},{default:(0,s.w5)((()=>[(0,s._)("div",n,[(0,s._)("div",u,[(0,s._)("img",{class:"mosaic_cover_element",src:l.playlist.covers[0]||P},null,8,d)]),(0,s._)("div",m,[(0,s._)("img",{class:"mosaic_cover_element",src:l.playlist.covers[1]||P},null,8,p)]),(0,s._)("div",v,[(0,s._)("img",{class:"mosaic_cover_element",src:l.playlist.covers[2]||P},null,8,_)])]),(0,s._)("div",g,[(0,s._)("div",y,[(0,s._)("img",{class:"mosaic_cover_element",src:l.playlist.covers[3]||P},null,8,w)]),(0,s._)("div",b,[(0,s._)("img",{class:"mosaic_cover_element",src:l.playlist.covers[4]||P},null,8,k)]),(0,s._)("div",q,[(0,s._)("img",{class:"mosaic_cover_element",src:l.playlist.covers[5]||P},null,8,f)])]),(0,s._)("div",h,[(0,s._)("div",W,[(0,s._)("img",{class:"mosaic_cover_element",src:l.playlist.covers[6]||P},null,8,Z)]),(0,s._)("div",Q,[(0,s._)("img",{class:"mosaic_cover_element",src:l.playlist.covers[7]||P},null,8,x)]),(0,s._)("div",C,[(0,s._)("img",{class:"mosaic_cover_element",src:l.playlist.covers[8]||P},null,8,H)])])])),_:1})):t.value?((0,s.wg)(),(0,s.j4)(c,{key:1,style:{height:"120px"}},{default:(0,s.w5)((()=>[((0,s.wg)(),(0,s.iD)(s.HY,null,(0,s.Ko)(5,(a=>(0,s.Wm)(V,{key:a,size:"80px",class:"overlapping",style:(0,r.j5)(`left: ${25*a}px`)},{default:(0,s.w5)((()=>[l.playlist.covers[a]?((0,s.wg)(),(0,s.iD)("img",{key:0,src:l.playlist.covers[a],class:(0,r.C_)("mosaic_cover_element rotate-"+45*(a+3))},null,10,j)):((0,s.wg)(),(0,s.iD)("div",{key:1,class:"no_cover",style:(0,r.j5)("background: "+e.getRandomColor())},null,4))])),_:2},1032,["style"]))),64))])),_:1})):(0,s.kq)("",!0),(0,s.Wm)(i),(0,s.Wm)(c,null,{default:(0,s.w5)((()=>[(0,s.Wm)(T,{spread:"",outline:""},{default:(0,s.w5)((()=>[(0,s.Wm)(B,{label:"play",stack:"",icon:"play_arrow"}),(0,s.Wm)(B,{label:"edit",stack:"",icon:"edit"}),(0,s.Wm)(B,{label:"delete",stack:"",icon:"delete"})])),_:1})])),_:1})])),_:1})}}};var B=a(4458),T=a(3190),D=a(926),S=a(1357),I=a(7236),U=a(8879),z=a(9984),A=a.n(z);const E=V,K=E;A()(V,"components",{QCard:B.Z,QCardSection:T.Z,QSeparator:D.Z,QAvatar:S.Z,QBtnGroup:I.Z,QBtn:U.Z});const F={key:0,class:"q-pa-lg flex flex-center"},M={class:"q-gutter-md row items-start"},R={__name:"BrowsePlaylistsPage",setup(l){const e=(0,i.Z)(),a=(0,t.iH)(null),r=(0,t.iH)(!1),n=(0,t.iH)(!1),u=(0,t.iH)([]),d=(0,t.iH)(0),m=(0,t.iH)(1),p=(0,t.iH)("mosaic");function v(l){l&&(m.value=1),r.value=!1,n.value=!0,c.api.playlist.search(m.value,32,{name:a.value}).then((l=>{u.value=l.data.data.items,d.value=l.data.data.pager.totalPages,a.value&&l.data.data.pager.totalResults<1&&(r.value=!0),n.value=!1})).catch((l=>{e.notify({type:"negative",message:"API Error: error loading playlists",caption:"API Error: fatal error details: HTTP {"+l.response.status+"} ({"+l.response.statusText+"})"}),n.value=!1}))}function _(l){m.value=l,v(!1)}return v(!0),(l,e)=>{const t=(0,s.up)("q-breadcrumbs-el"),c=(0,s.up)("q-breadcrumbs"),i=(0,s.up)("q-icon"),g=(0,s.up)("q-input"),y=(0,s.up)("q-btn-toggle"),w=(0,s.up)("q-pagination"),b=(0,s.up)("q-card-section"),k=(0,s.up)("q-card"),q=(0,s.up)("q-page");return(0,s.wg)(),(0,s.j4)(q,null,{default:(0,s.w5)((()=>[(0,s.Wm)(k,{class:"q-pa-lg"},{default:(0,s.w5)((()=>[(0,s.Wm)(c,{class:"q-mb-lg"},{default:(0,s.w5)((()=>[(0,s.Wm)(t,{icon:"home",label:"Spieldose"}),(0,s.Wm)(t,{icon:"list",label:"Browse playlists"})])),_:1}),u.value?((0,s.wg)(),(0,s.j4)(b,{key:0},{default:(0,s.w5)((()=>[(0,s.Wm)(g,{modelValue:a.value,"onUpdate:modelValue":e[0]||(e[0]=l=>a.value=l),rounded:"",clearable:"",type:"search",outlined:"",dense:"",placeholder:"Text condition",hint:"Search playlists with name",loading:n.value,disable:n.value,onKeydown:e[1]||(e[1]=(0,o.D2)((0,o.iM)((l=>v(!0)),["prevent"]),["enter"])),onClear:e[2]||(e[2]=l=>{r.value=!1,v(!0)}),error:r.value,errorMessage:"No playlists found with specified condition"},{prepend:(0,s.w5)((()=>[(0,s.Wm)(i,{name:"filter_alt"})])),append:(0,s.w5)((()=>[(0,s.Wm)(i,{name:"search",class:"cursor-pointer",onClick:v})])),_:1},8,["modelValue","loading","disable","error"]),(0,s.Wm)(y,{class:"q-my-md",push:"","toggle-color":"pink",modelValue:p.value,"onUpdate:modelValue":e[3]||(e[3]=l=>p.value=l),options:[{label:"style: mosaic",value:"mosaic"},{label:"style: vinyl collection",value:"vinylCollection"}]},null,8,["modelValue"]),d.value>1?((0,s.wg)(),(0,s.iD)("div",F,[(0,s.Wm)(w,{modelValue:m.value,"onUpdate:modelValue":[e[4]||(e[4]=l=>m.value=l),_],color:"dark",max:d.value,"max-pages":5,"boundary-numbers":"","direction-links":"","boundary-links":"",disable:n.value},null,8,["modelValue","max","disable"])])):(0,s.kq)("",!0),(0,s._)("div",M,[((0,s.wg)(!0),(0,s.iD)(s.HY,null,(0,s.Ko)(u.value,(l=>((0,s.wg)(),(0,s.j4)(K,{key:l.id,playlist:l,mode:p.value},null,8,["playlist","mode"])))),128))])])),_:1})):(0,s.kq)("",!0)])),_:1})])),_:1})}}};var Y=a(9885),G=a(2605),N=a(8052),O=a(6611),$=a(2857),J=a(1389),L=a(996);const X=R,ll=X;A()(R,"components",{QPage:Y.Z,QCard:B.Z,QBreadcrumbs:G.Z,QBreadcrumbsEl:N.Z,QCardSection:T.Z,QInput:O.Z,QIcon:$.Z,QBtnToggle:J.Z,QPagination:L.Z})}}]);