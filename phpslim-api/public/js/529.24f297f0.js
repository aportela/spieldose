"use strict";(globalThis["webpackChunkspieldose"]=globalThis["webpackChunkspieldose"]||[]).push([[529],{8417:(e,l,a)=>{a.r(l),a.d(l,{default:()=>H});var t=a(9835),s=a(499),u=a(6970),o=a(1957),i=a(1569),r=a(6647),n=a(9302),d=a(5226),c=a(8106);const m={class:"row q-gutter-xs"},p={class:"col-xl-2 col-lg-2 col-md-3 col-sm-4 col-xs-4"},v={class:"col"},b={class:"col-xl-1 col-lg-2 col-md-3 col-sm-4 col-xs-4"},g={class:"col-xl-1 col-lg-2 col-md-3 col-sm-4 col-xs-4"},f={key:0,class:"q-pa-lg flex flex-center"},w={class:"q-gutter-md row items-start"},h={__name:"BrowseAlbumsPage",setup(e){const{t:l}=(0,r.QT)(),a=(0,n.Z)(),h=(0,s.iH)(null),y=[{label:"Title",value:"title"},{label:"Artist",value:"albumArtistName"},{label:"Title & Artist",value:"all"}],x=(0,s.iH)(y[2]),k=[{label:"Title",value:"title"},{label:"Artist",value:"albumArtistName"},{label:"Year",value:"year"}],q=(0,s.iH)(k[0]),_=[{label:"Ascending",value:"ASC"},{label:"Descending",value:"DESC"}],A=(0,s.iH)(_[0]),V=(0,s.iH)(!1),T=(0,s.iH)(!1);let P=[];const S=(0,s.iH)(0),E=(0,s.iH)(1),H=(0,s.iH)(null);function U(e){e&&(E.value=1),V.value=!1,T.value=!0;let s={title:"title"==x.value.value?h.value:null,albumArtistName:"albumArtistName"==x.value.value?h.value:null,text:"all"==x.value.value?h.value:null};i.api.album.search(s,E.value,32,q.value.value,A.value.value).then((e=>{P=e.data.data.items.map((e=>(e.image=e.covers.small,e))),S.value=e.data.data.pager.totalPages,h.value&&e.data.data.pager.totalResults<1&&(V.value=!0),(0,t.Y3)((()=>{H.value.$el.focus()})),T.value=!1})).catch((e=>{a.notify({type:"negative",message:"API Error: error loading albums",caption:l("API Error: fatal error details",{status:e.response.status,statusText:e.response.statusText})}),T.value=!1}))}function W(e){E.value=e,U(!1)}function Z(e){c.up.play(e).then((e=>{})).catch((e=>{switch(e.response.status){default:a.notify({type:"negative",message:l("API Error: error playing album"),caption:l("API Error: fatal error details",{status:e.response.status,statusText:e.response.statusText})});break}}))}function I(e){c.up.enqueue(e).then((e=>{})).catch((e=>{switch(e.response.status){default:a.notify({type:"negative",message:l("API Error: error enqueueing album"),caption:l("API Error: fatal error details",{status:e.response.status,statusText:e.response.statusText})});break}}))}return U(!0),(e,l)=>{const a=(0,t.up)("q-breadcrumbs-el"),i=(0,t.up)("q-breadcrumbs"),r=(0,t.up)("q-select"),n=(0,t.up)("q-icon"),c=(0,t.up)("q-input"),Q=(0,t.up)("q-pagination"),C=(0,t.up)("q-card-section"),N=(0,t.up)("q-card");return(0,t.wg)(),(0,t.j4)(N,{class:"q-pa-lg"},{default:(0,t.w5)((()=>[(0,t.Wm)(i,{class:"q-mb-lg"},{default:(0,t.w5)((()=>[(0,t.Wm)(a,{icon:"home",label:"Spieldose"}),(0,t.Wm)(a,{icon:"album",label:"Browse albums"})])),_:1}),(0,s.SU)(P)?((0,t.wg)(),(0,t.j4)(C,{key:0},{default:(0,t.w5)((()=>[(0,t._)("div",m,[(0,t._)("div",p,[(0,t.Wm)(r,{outlined:"",dense:"",modelValue:x.value,"onUpdate:modelValue":[l[0]||(l[0]=e=>x.value=e),l[1]||(l[1]=e=>U(!0))],options:y,"options-dense":"",label:"Search on",disable:T.value},{"selected-item":(0,t.w5)((e=>[(0,t.Uk)((0,u.zw)(e.opt.label),1)])),_:1},8,["modelValue","disable"])]),(0,t._)("div",v,[(0,t.Wm)(c,{modelValue:h.value,"onUpdate:modelValue":l[2]||(l[2]=e=>h.value=e),clearable:"",type:"search",outlined:"",dense:"",placeholder:"Text condition",hint:"Search albums with specified condition",loading:T.value,disable:T.value,onKeydown:l[3]||(l[3]=(0,o.D2)((0,o.iM)((e=>U(!0)),["prevent"]),["enter"])),onClear:l[4]||(l[4]=e=>{V.value=!1,U(!0)}),error:V.value,errorMessage:"No albums found with specified condition",ref_key:"searchTextRef",ref:H},{prepend:(0,t.w5)((()=>[(0,t.Wm)(n,{name:"filter_alt"})])),append:(0,t.w5)((()=>[(0,t.Wm)(n,{name:"search",class:"cursor-pointer",onClick:U})])),_:1},8,["modelValue","loading","disable","error"])]),(0,t._)("div",b,[(0,t.Wm)(r,{outlined:"",dense:"",modelValue:q.value,"onUpdate:modelValue":[l[5]||(l[5]=e=>q.value=e),l[6]||(l[6]=e=>U(!0))],options:k,"options-dense":"",label:"Sort field",disable:T.value},{"selected-item":(0,t.w5)((e=>[(0,t.Uk)((0,u.zw)(e.opt.label),1)])),_:1},8,["modelValue","disable"])]),(0,t._)("div",g,[(0,t.Wm)(r,{outlined:"",dense:"",modelValue:A.value,"onUpdate:modelValue":[l[7]||(l[7]=e=>A.value=e),l[8]||(l[8]=e=>U(!0))],options:_,"options-dense":"",label:"Sort order",disable:T.value},{"selected-item":(0,t.w5)((e=>[(0,t.Uk)((0,u.zw)(e.opt.label),1)])),_:1},8,["modelValue","disable"])])]),S.value>1?((0,t.wg)(),(0,t.iD)("div",f,[(0,t.Wm)(Q,{modelValue:E.value,"onUpdate:modelValue":[l[9]||(l[9]=e=>E.value=e),W],color:"dark",max:S.value,"max-pages":5,"boundary-numbers":"","direction-links":"","boundary-links":"",disable:T.value},null,8,["modelValue","max","disable"])])):(0,t.kq)("",!0),(0,t._)("div",w,[((0,t.wg)(!0),(0,t.iD)(t.HY,null,(0,t.Ko)((0,s.SU)(P),(e=>((0,t.wg)(),(0,t.j4)(d.Z,{key:e.mbId||e.title,image:e.image,title:e.title,artistName:e.artist.name,year:e.year,onPlay:l=>Z(e),onEnqueue:l=>I(e)},null,8,["image","title","artistName","year","onPlay","onEnqueue"])))),128))])])),_:1})):(0,t.kq)("",!0)])),_:1})}}};var y=a(4458),x=a(2605),k=a(8052),q=a(3190),_=a(6384),A=a(6611),V=a(2857),T=a(996),P=a(9984),S=a.n(P);const E=h,H=E;S()(h,"components",{QCard:y.Z,QBreadcrumbs:x.Z,QBreadcrumbsEl:k.Z,QCardSection:q.Z,QSelect:_.Z,QInput:A.Z,QIcon:V.Z,QPagination:T.Z})}}]);