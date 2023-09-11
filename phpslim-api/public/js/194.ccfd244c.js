"use strict";(globalThis["webpackChunkspieldose"]=globalThis["webpackChunkspieldose"]||[]).push([[194],{2719:(e,a,t)=>{t.d(a,{d:()=>i});var l=t(1809),s=t(8612),n=t.n(s);const r={namespace:"spieldose",storages:["local","cookie","session","memory"],storage:"local",expireDays:3650},i=(0,l.Q_)("currentPlaylist",{state:()=>({elements:[],currentIndex:-1,elementsLastChangeTimestamp:null}),getters:{hasElements:e=>e.elements&&e.elements.length>0,getElements:e=>e.elements,getElementsLastChangeTimestamp:e=>e.elementsLastChangeTimestamp,getCurrentIndex:e=>e.currentIndex,getCurrentElement:e=>e.currentIndex>=0&&e.elements.length>0?e.elements[e.currentIndex]:null,getCurrentElementURL:e=>e.currentIndex>=0&&e.elements.length>0?e.elements[e.currentIndex].url:null,allowSkipPrevious:e=>e.currentIndex>0&&e.elements.length>0,allowSkipNext:e=>e.currentIndex>=0&&e.elements.length>0&&e.currentIndex<e.elements.length-1,allowPlay:e=>!0,allowPause:e=>!0,allowResume:e=>!0,allowStop:e=>!0},actions:{load(){const e=n()(r),a=e.get("currentPlaylistElements");a&&(this.elements=a);const t=e.get("currentPlaylistElementIndex");t>=0&&(this.currentIndex=t)},saveElements(e){this.elements=e,this.currentIndex=e&&e.length>0?0:-1;const a=n()(r);a.set("currentPlaylistElements",e),a.set("currentPlaylistElementIndex",e&&e.length>0?0:-1),this.elementsLastChangeTimestamp=Date.now()},appendElements(e){this.elements=this.elements.concat(e);const a=n()(r);a.set("currentPlaylistElements",this.elements),a.set("currentPlaylistElementIndex",this.currentIndex),this.elementsLastChangeTimestamp=Date.now()},saveCurrentTrackIndex(e){this.currentIndex=e>=0?e:-1;const a=n()(r);a.set("currentPlaylistElementIndex",this.currentIndex)},skipPrevious(){this.currentIndex--},skipNext(){this.currentIndex++}}})},4958:(e,a,t)=>{t.d(a,{n:()=>r});var l=t(1809),s=t(1320);const n=(0,s.l)(),r=(0,l.Q_)("player",{state:()=>({element:null,hasPreviousUserInteractions:!1}),getters:{getElement:e=>e.element,getDuration:e=>e.element.duration},actions:{interact(){this.hasPreviousUserInteractions=!0},create(){this.element=document.createElement("audio"),this.element.id="audio_player",this.element.crossOrigin="anonymous"},setVolume(e){this.element.volume=e},setCurrentTime(e){this.element.currentTime=e},stop(){this.element.pause(),n.setStatusStopped()},load(){},play(e){e?(this.element.play(),n.setStatusPlaying()):n.isPlaying?(this.element.pause(),n.setStatusPaused()):n.isPaused?(this.element.play(),n.setStatusPlaying()):(this.element.load(),this.element.play(),n.setStatusPlaying())}}})},1320:(e,a,t)=>{t.d(a,{l:()=>s});var l=t(1809);const s=(0,l.Q_)("playerStatus",{state:()=>({status:"stopped"}),getters:{getStatus(e){return e.status},isPlaying(e){return"playing"==e.status},isStopped(e){return"stopped"==e.status},isPaused(e){return"paused"==e.status}},actions:{setStatusPlaying(){this.status="playing"},setStatusStopped(){this.status="stopped"},setStatusPaused(){this.status="paused"}}})},6729:(e,a,t)=>{t.d(a,{Z:()=>x});var l=t(9835),s=t(6970),n=t(499);const r={class:"animated-album-cover-item"},i={class:"play-album"},u={class:"album-actions"},c=(0,l._)("span",{class:"clear: both;"},null,-1),o={key:0,class:"vinyl no-cover",src:"images/vinyl.png"},m={class:"album-info"},d=["title"],p={key:1,class:"artist-name"},b={key:0},g={key:2},v={__name:"AnimatedAlbumCover",props:{title:String,artistName:String,year:Number,image:String},emits:["play","enqueue"],setup(e,{emit:a}){const t=e,v=(0,n.iH)(!1),k=(0,n.iH)(!1),h=(0,l.Fl)((()=>t.image&&!k.value?t.image:"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII="));function w(){v.value=!0}function f(){k.value=!0}function y(){a("play")}function x(){a("enqueue")}return(a,t)=>{const n=(0,l.up)("q-img"),_=(0,l.up)("q-icon"),q=(0,l.up)("router-link");return(0,l.wg)(),(0,l.iD)("div",r,[(0,l._)("div",i,[(0,l.Wm)(n,{class:(0,s.C_)(["album-thumbnail",{"album-thumbnail-animated":v.value||k.value}]),src:h.value,width:"174px",height:"174px","spinner-color":"pink",onLoad:w,onError:f},null,8,["class","src"]),(0,l._)("div",u,[(0,l.Wm)(_,{class:"cursor-pointer",name:"add_box",size:"80px",color:"pink",title:"Enqueue album",style:{left:"10px"},onClick:x}),(0,l.Wm)(_,{class:"cursor-pointer",name:"play_arrow",size:"80px",color:"pink",title:"Play album",style:{left:"80px"},onClick:y}),c]),v.value||k.value?((0,l.wg)(),(0,l.iD)("img",o)):(0,l.kq)("",!0)]),(0,l._)("div",m,[e.title?((0,l.wg)(),(0,l.iD)("p",{key:0,class:"album-name",title:e.title},(0,s.zw)(e.title),9,d)):(0,l.kq)("",!0),e.artistName?((0,l.wg)(),(0,l.iD)("p",p,[(0,l.Uk)("by "),(0,l.Wm)(q,{title:e.artistName,to:{name:"artist",params:{name:e.artistName}}},{default:(0,l.w5)((()=>[(0,l.Uk)((0,s.zw)(e.artistName),1)])),_:1},8,["title","to"]),(0,l.Uk)(),e.year?((0,l.wg)(),(0,l.iD)("span",b,"("+(0,s.zw)(e.year)+")",1)):(0,l.kq)("",!0)])):e.year?((0,l.wg)(),(0,l.iD)("p",g,"("+(0,s.zw)(e.year)+")",1)):(0,l.kq)("",!0)])])}}};var k=t(335),h=t(2857),w=t(9984),f=t.n(w);const y=v,x=y;f()(v,"components",{QImg:k.Z,QIcon:h.Z})},4194:(e,a,t)=>{t.r(a),t.d(a,{default:()=>Ke});var l=t(9835),s=t(6970),n=t(499),r=t(8339),i=t(7712),u=t(9302),c=(t(4376),t(1569));const o=["href"],m={__name:"ArtistURLRelationshipChip",props:{id:String,url:String},setup(e){const a=e,t=(0,l.Fl)((()=>{let e="bookmark";switch(a.id){case"fe33d22f-c3b0-4d68-bd53-a856badf2b15":e="home";break;case"d0c5cf3a-8afb-4d24-ad47-00f43dc509fe":e="album";break;case"f484f897-81cc-406e-96f9-cd799a04ee24":e="person_pin";break;case"78f75830-94e1-4138-8f8a-643e3bb21ce5":e="description";break;case"e4d73442-3762-45a8-905c-401da65544ed":e="lyrics";break;case"bac47923-ecde-4b59-822e-d08f0cd10156":e="link";break;case"89e4a949-0976-440d-bda1-5f772c1e5710":e="link";break;case"6a540e5b-58c6-4192-b6ba-dbc71ec8fcf0":e="link";break;case"35b3a50f-bf0e-4309-a3b4-58eeed8cee6a":e="forum";break;case"eb535226-f8ca-499d-9b18-6a144df4ae6f":e="event_note";break;case"6b3e3c85-0002-4f34-aca6-80ace0d7e846":e="link";break;case"04a5b104-a4c2-4bac-99a1-7b837c37d9e4":e="link";break;case"94c8b0cc-4477-4106-932c-da60e63de61c":e="link";break;case"08db8098-c0df-4b78-82c3-c8697b4bba7f":e="link";break;case"689870a4-a1e4-4912-b17f-7b2664215698":e="link";break;case"29651736-fa6d-48e4-aadc-a557c6add1cb":e="link";break;case"221132e9-e30e-43f2-a741-15afc4c5fa7c":e="photo_camera";break}return e})),n=(0,l.Fl)((()=>{let e=null;switch(a.id){case"fe33d22f-c3b0-4d68-bd53-a856badf2b15":e="Official homepage";break;case"d0c5cf3a-8afb-4d24-ad47-00f43dc509fe":e="Discography";break;case"f484f897-81cc-406e-96f9-cd799a04ee24":e="Fanpage";break;case"78f75830-94e1-4138-8f8a-643e3bb21ce5":e="Biography";break;case"e4d73442-3762-45a8-905c-401da65544ed":e="Lyrics";break;case"bac47923-ecde-4b59-822e-d08f0cd10156":e="MySpace";break;case"89e4a949-0976-440d-bda1-5f772c1e5710":e="SoundCloud";break;case"6a540e5b-58c6-4192-b6ba-dbc71ec8fcf0":e="Youtube";break;case"35b3a50f-bf0e-4309-a3b4-58eeed8cee6a":e="Online community";break;case"eb535226-f8ca-499d-9b18-6a144df4ae6f":e="Blog";break;case"6b3e3c85-0002-4f34-aca6-80ace0d7e846":e="AllMusic";break;case"04a5b104-a4c2-4bac-99a1-7b837c37d9e4":e="DiscoGS";break;case"94c8b0cc-4477-4106-932c-da60e63de61c":e="IMDB";break;case"08db8098-c0df-4b78-82c3-c8697b4bba7f":e="Last.FM";break;case"689870a4-a1e4-4912-b17f-7b2664215698":e="Wikidata";break;case"29651736-fa6d-48e4-aadc-a557c6add1cb":e="Wikipedia";break;case"221132e9-e30e-43f2-a741-15afc4c5fa7c":e="Image";break}return e}));return(a,r)=>{const i=(0,l.up)("q-chip");return(0,l.wg)(),(0,l.iD)("a",{href:e.url,target:"_blank"},[n.value&&t.value&&e.url?((0,l.wg)(),(0,l.j4)(i,{key:0,size:"md",icon:t.value,"truncate-chip-labels":""},{default:(0,l.w5)((()=>[(0,l.Uk)((0,s.zw)(n.value),1)])),_:1},8,["icon"])):(0,l.kq)("",!0)],8,o)}}};var d=t(7691),p=t(9984),b=t.n(p);const g=m,v=g;b()(m,"components",{QChip:d.Z});var k=t(6729),h=t(4958),w=t(2719);const f={id:"artist-header-block"},y=(0,l._)("div",{id:"artist-header-block-background-overlay"},null,-1),x={id:"artist-header-block-content"},_={class:"q-pl-xl q-pt-xl"},q={class:"text-h3 text-bold"},A={class:"text-white text-bold"},W={class:"text-grey"},I=(0,l._)("span",{class:"text-white"},"0 user/s",-1),P={class:"text-white text-bold"},C={class:"text-grey"},E=(0,l._)("span",{class:"text-white"},"0 times",-1),S={class:"row q-mt-xl"},U={key:0,class:"col-6"},T={class:"float-left",style:{width:"96px",height:"96px"}},D=(0,l._)("div",{class:"absolute-full flex flex-center bg-grey-0"},null,-1),z={class:"float-left oneline-ellipsis",style:{"margin-left":"24px","margin-top":"10px"}},Q=(0,l._)("p",{class:"q-mb-none text-grey"},"LATEST RELEASE",-1),L={class:"q-my-none text-white text-weight-bolder header-mini-album-title"},Z={key:0,class:"q-mt-none text-white"},j={key:1,class:"col-6"},H={class:"float-left oneline-ellipsis",style:{width:"96px",height:"96px"}},N=(0,l._)("div",{class:"absolute-full flex flex-center bg-grey-0"},null,-1),R={class:"float-left oneline-ellipsis",style:{"margin-left":"24px","margin-top":"10px"}},Y=(0,l._)("p",{class:"q-mb-none text-grey"},"POPULAR",-1),M={class:"q-my-none text-white text-weight-bolder header-mini-album-title"},O={key:0,class:"q-mt-none text-white"},B={class:"row q-col-gutter-lg"},K={class:"col-10"},V=(0,l._)("div",{class:"text-h6"},"Overview",-1),F={key:1},$=["innerHTML"],G={key:0,class:"q-mt-md"},J=(0,l._)("div",{class:"text-h6"},"Top tracks",-1),X={class:"text-center col-1"},ee={class:"text-bold col-4"},ae={class:"text-left col-4"},te={class:"col-2"},le={class:"absolute-full flex flex-center"},se={key:2,class:"text-h5 text-center"},ne=(0,l._)("div",{class:"text-h6"},"Top albums",-1),re={key:1,class:"q-pa-lg flex flex-center"},ie={class:"q-gutter-md row items-start"},ue=(0,l._)("div",{class:"text-h6"},"Appears on",-1),ce={key:1,class:"q-pa-lg flex flex-center"},oe={class:"q-gutter-md row items-start"},me={class:"col-2"},de={class:"row"},pe={class:"text-center"},be=(0,l._)("br",null,null,-1),ge=(0,l._)("div",{class:"ct-chart"},null,-1),ve={class:"row q-col-gutter-lg"},ke={class:"col-12"},he=(0,l._)("div",{class:"text-h6"},"Biography",-1),we=["innerHTML"],fe=(0,l._)("div",{class:"text-h6"},"Similar artists",-1),ye={class:"q-gutter-md row items-start"},xe={class:"absolute-bottom text-subtitle1 text-center"},_e={class:"absolute-full flex flex-center bg-grey-3 text-dark"},qe={class:"absolute-bottom text-subtitle1 text-center bg-grey-5 q-py-md"},Ae=(0,l._)("div",{class:"text-h6 q-mb-xl"},"Albums",-1),We={class:"q-gutter-md row items-start"},Ie=(0,l._)("div",{class:"text-h6"},"Tracks",-1),Pe=(0,l._)("div",{class:"text-h6"},"Stats",-1),Ce={__name:"ArtistPage",setup(e){const a=(0,h.n)(),t=(0,w.d)(),{t:o}=(0,i.QT)(),m=(0,u.Z)(),d=(0,r.yj)(),p=(0,n.iH)(d.params.name),b=(0,n.iH)("overview"),g=(0,n.iH)(!1),Ce=(0,n.iH)({mbId:null,name:null,popularAlbum:{title:null,year:null,image:null},latestAlbum:{title:null,year:null,image:null},topAlbums:[],topTracks:[],similar:[]}),Ee=(0,n.iH)(null),Se=(0,l.Fl)((()=>d.params.name));function Ue(e,a,t){var l=t?"<br />":"<br>",s=a?"$1"+l:"$1"+l+"$2";return(e+"").replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g,s)}function Te(e){a.stop(),t.saveElements([{track:e}]),a.interact(),a.play(!1)}function De(e){if(e&&e.id){const a=e.favorited?c.api.track.unSetFavorite:c.api.track.setFavorite;a(e.id).then((a=>{e.favorited=a.data.favorited})).catch((e=>{switch(e.response.status){default:m.notify({type:"negative",message:o("API Error: fatal error"),caption:o("API Error: fatal error details",{status:e.response.status,statusText:e.response.statusText})});break}}))}}function ze(e){g.value=!0,c.api.artist.get(null,e).then((e=>{Ce.value=e.data.artist,Ee.value=Ce.value.image;let a=0;Ce.value.topTracks.forEach((e=>{a+=e.playCount})),Ce.value.topTracks=Ce.value.topTracks.map((e=>(e.coverPathId?e.image="api/2/thumbnail/small/local/album/?path="+encodeURIComponent(e.coverPathId):e.covertArtArchiveURL?e.image="api/2/thumbnail/small/remote/album/?url="+encodeURIComponent(e.covertArtArchiveURL):e.image=null,e.percentPlay=Math.round(100*e.playCount/a)/100,e))),Ce.value.topAlbums.value=Ce.value.topAlbums.map((e=>(e.coverPathId?e.image="api/2/thumbnail/normal/local/album/?path="+encodeURIComponent(e.coverPathId):e.covertArtArchiveURL?e.image="api/2/thumbnail/normal/remote/album/?url="+encodeURIComponent(e.covertArtArchiveURL):e.image=null,e))),g.value=!1})).catch((e=>{switch(g.value=!1,e.response.status){default:m.notify({type:"negative",message:o("API Error: fatal error"),caption:o("API Error: fatal error details",{status:e.response.status,statusText:e.response.statusText})});break}}))}function Qe(e){a.interact(),g.value=!0,c.api.track.search({albumMbId:e.mbId},1,0,!1,"trackNumber","ASC").then((e=>{t.saveElements(e.data.data.items.map((e=>({track:e})))),g.value=!1})).catch((e=>{g.value=!1}))}function Le(e){a.interact(),g.value=!0,c.api.track.search({albumMbId:e.mbId},1,0,!1,"trackNumber","ASC").then((e=>{t.appendElements(e.data.data.items.map((e=>({track:e})))),g.value=!1})).catch((e=>{g.value=!1}))}return(0,l.YP)(Se,((e,a)=>{p.value=e,p.value&&(Ce.value={mbId:null,name:null,popularAlbum:{title:null,year:null,image:null},latestAlbum:{title:null,year:null,image:null},topTracks:[],similar:[]},Ee.value=null,ze(p.value))})),(0,l.bv)((()=>{})),ze(p.value),(e,a)=>{const t=(0,l.up)("q-icon"),n=(0,l.up)("q-img"),r=(0,l.up)("q-tab"),i=(0,l.up)("q-badge"),u=(0,l.up)("q-tabs"),c=(0,l.up)("q-card-section"),o=(0,l.up)("q-separator"),m=(0,l.up)("q-skeleton"),d=(0,l.up)("q-card"),h=(0,l.up)("q-avatar"),w=(0,l.up)("q-linear-progress"),Se=(0,l.up)("q-markup-table"),ze=(0,l.up)("q-btn"),Ze=(0,l.up)("router-link"),je=(0,l.up)("q-tab-panel"),He=(0,l.up)("q-tab-panels"),Ne=(0,l.up)("q-page");return(0,l.wg)(),(0,l.j4)(Ne,null,{default:(0,l.w5)((()=>[(0,l._)("div",f,[(0,l._)("div",{id:"artist-header-block-background-image",style:(0,s.j5)("background-image: url("+(Ee.value||"#")+")")},null,4),y,(0,l._)("div",x,[(0,l._)("div",_,[(0,l._)("p",q,[(0,l.Uk)((0,s.zw)(p.value)+" ",1),g.value?((0,l.wg)(),(0,l.j4)(t,{key:0,name:"settings",class:"rotate"})):(0,l.kq)("",!0)]),(0,l._)("p",A,[(0,l._)("span",W,[(0,l.Wm)(t,{name:"groups",size:"sm",class:"q-mr-sm"}),(0,l.Uk)(" Listeners: ")]),I]),(0,l._)("p",P,[(0,l._)("span",C,[(0,l.Wm)(t,{name:"album",size:"sm",class:"q-mr-sm"}),(0,l.Uk)(" Total plays: ")]),(0,l.Uk)(),E]),(0,l._)("div",S,[Ce.value.latestAlbum.title?((0,l.wg)(),(0,l.iD)("div",U,[(0,l._)("div",T,[(0,l.Wm)(n,{src:Ce.value.latestAlbum.image,"spinner-color":"white",style:{height:"96px","max-width":"96px"}},{error:(0,l.w5)((()=>[D])),_:1},8,["src"])]),(0,l._)("div",z,[Q,(0,l._)("p",L,(0,s.zw)(Ce.value.latestAlbum.title),1),Ce.value.latestAlbum.year?((0,l.wg)(),(0,l.iD)("p",Z,(0,s.zw)(Ce.value.latestAlbum.year),1)):(0,l.kq)("",!0)])])):(0,l.kq)("",!0),Ce.value.popularAlbum.title?((0,l.wg)(),(0,l.iD)("div",j,[(0,l._)("div",H,[(0,l.Wm)(n,{src:Ce.value.popularAlbum.image,"spinner-color":"white",style:{height:"96px","max-width":"96px"}},{error:(0,l.w5)((()=>[N])),_:1},8,["src"])]),(0,l._)("div",R,[Y,(0,l._)("p",M,(0,s.zw)(Ce.value.popularAlbum.title),1),Ce.value.popularAlbum.year?((0,l.wg)(),(0,l.iD)("p",O,(0,s.zw)(Ce.value.popularAlbum.year),1)):(0,l.kq)("",!0)])])):(0,l.kq)("",!0)])]),(0,l.Wm)(u,{modelValue:b.value,"onUpdate:modelValue":a[0]||(a[0]=e=>b.value=e),class:"tex-white q-mt-md"},{default:(0,l.w5)((()=>[(0,l.Wm)(r,{icon:"summarize",name:"overview",label:"Overview"}),(0,l.Wm)(r,{icon:"menu_book",name:"biography",label:"Biography"}),(0,l.Wm)(r,{icon:"groups",name:"similarArtists",label:"Similar artists"},{default:(0,l.w5)((()=>[(0,l.Wm)(i,{color:"pink",floating:""},{default:(0,l.w5)((()=>[(0,l.Uk)((0,s.zw)(Ce.value.similar.length),1)])),_:1})])),_:1}),(0,l.Wm)(r,{icon:"album",name:"albums",label:"Albums"},{default:(0,l.w5)((()=>[(0,l.Wm)(i,{color:"pink",floating:""},{default:(0,l.w5)((()=>[(0,l.Uk)((0,s.zw)(Ce.value.topAlbums.length),1)])),_:1})])),_:1}),(0,l.Wm)(r,{icon:"audiotrack",name:"tracks",label:"Tracks"}),(0,l.Wm)(r,{icon:"analytics",name:"stats",label:"Stats"})])),_:1},8,["modelValue"])])]),Ce.value?((0,l.wg)(),(0,l.j4)(He,{key:0,modelValue:b.value,"onUpdate:modelValue":a[4]||(a[4]=e=>b.value=e),animated:""},{default:(0,l.w5)((()=>[(0,l.Wm)(je,{name:"overview"},{default:(0,l.w5)((()=>[(0,l._)("div",B,[(0,l._)("div",K,[(0,l.Wm)(d,{class:"my-card shadow-box shadow-10 q-pa-lg",bordered:""},{default:(0,l.w5)((()=>[(0,l.Wm)(c,null,{default:(0,l.w5)((()=>[V])),_:1}),(0,l.Wm)(o),(0,l.Wm)(c,null,{default:(0,l.w5)((()=>[g.value?((0,l.wg)(),(0,l.j4)(m,{key:0,type:"text",square:"",animation:"blink",height:"300px"})):((0,l.wg)(),(0,l.iD)("div",F,[(0,l._)("div",{innerHTML:Ce.value.bio?Ue(Ce.value.bio.summary||""):""},null,8,$),Ce.value.relations?((0,l.wg)(),(0,l.iD)("p",G,[((0,l.wg)(!0),(0,l.iD)(l.HY,null,(0,l.Ko)(Ce.value.relations,(e=>((0,l.wg)(),(0,l.j4)(v,{key:e.url,id:e["type-id"],url:e.url},null,8,["id","url"])))),128))])):(0,l.kq)("",!0)]))])),_:1})])),_:1}),(0,l.Wm)(d,{class:"my-card shadow-box shadow-10 q-pa-lg q-mt-lg",bordered:""},{default:(0,l.w5)((()=>[(0,l.Wm)(c,null,{default:(0,l.w5)((()=>[J])),_:1}),(0,l.Wm)(o),(0,l.Wm)(c,null,{default:(0,l.w5)((()=>[g.value?((0,l.wg)(),(0,l.j4)(m,{key:0,type:"text",square:"",animation:"blink",height:"300px"})):((0,l.wg)(),(0,l.j4)(Se,{key:1},{default:(0,l.w5)((()=>[(0,l._)("tbody",null,[((0,l.wg)(!0),(0,l.iD)(l.HY,null,(0,l.Ko)(Ce.value.topTracks,(e=>((0,l.wg)(),(0,l.iD)("tr",{class:"row",key:e.id},[(0,l._)("td",X,[(0,l.Wm)(t,{name:"play_arrow",size:"lg",class:"cursor-pointer",onClick:a=>Te(e)},null,8,["onClick"]),(0,l.Wm)(t,{name:"favorite",size:"md",class:"cursor-pointer",color:e.favorited?"pink":"",onClick:a=>De(e)},null,8,["color","onClick"])]),(0,l._)("td",ee,(0,s.zw)(e.title),1),(0,l._)("td",ae,[(0,l.Wm)(h,{square:""},{default:(0,l.w5)((()=>[(0,l.Wm)(n,{src:e.covers.small,onError:a=>e.covers.small="images/vinyl.png",width:"48px",height:"48px","spinner-color":"pink"},null,8,["src","onError"])])),_:2},1024),(0,l.Uk)(" "+(0,s.zw)(e.album.title),1)]),(0,l._)("td",te,[(0,l.Wm)(w,{size:"32px",value:e.percentPlay,color:"pink-2"},{default:(0,l.w5)((()=>[(0,l._)("div",le,[(0,l.Wm)(i,{class:"transparent","text-color":"grey-10",label:e.playCount+" plays"},null,8,["label"])])])),_:2},1032,["value"])])])))),128))])])),_:1})),g.value||Ce.value.topTracks.length>0?(0,l.kq)("",!0):((0,l.wg)(),(0,l.iD)("h5",se,[(0,l.Wm)(t,{name:"warning",size:"xl"}),(0,l.Uk)(" No enought data")]))])),_:1})])),_:1}),Ce.value.topAlbums&&Ce.value.topAlbums.length>0?((0,l.wg)(),(0,l.j4)(d,{key:0,class:"my-card shadow-box shadow-10 q-pa-lg q-mt-lg",bordered:""},{default:(0,l.w5)((()=>[(0,l.Wm)(c,null,{default:(0,l.w5)((()=>[ne])),_:1}),(0,l.Wm)(c,null,{default:(0,l.w5)((()=>[g.value?((0,l.wg)(),(0,l.j4)(m,{key:0,type:"text",square:"",animation:"blink",height:"300px"})):((0,l.wg)(),(0,l.iD)("div",re,[(0,l._)("div",ie,[((0,l.wg)(!0),(0,l.iD)(l.HY,null,(0,l.Ko)(Ce.value.topAlbums.slice(0,6),(e=>((0,l.wg)(),(0,l.j4)(k.Z,{key:e.title,image:e.image,title:e.title,artistName:e.artist.name,year:e.year,onPlay:a=>Qe(e),onEnqueue:a=>Le(e)},null,8,["image","title","artistName","year","onPlay","onEnqueue"])))),128))])])),Ce.value.topAlbums.length>6?((0,l.wg)(),(0,l.j4)(ze,{key:2,size:"sm",onClick:a[1]||(a[1]=e=>b.value="albums")},{default:(0,l.w5)((()=>[(0,l.Uk)("view more")])),_:1})):(0,l.kq)("",!0)])),_:1})])),_:1})):(0,l.kq)("",!0),Ce.value.appearsOnAlbums&&Ce.value.appearsOnAlbums.length>0?((0,l.wg)(),(0,l.j4)(d,{key:1,class:"my-card shadow-box shadow-10 q-pa-lg q-mt-lg",bordered:""},{default:(0,l.w5)((()=>[(0,l.Wm)(c,null,{default:(0,l.w5)((()=>[ue])),_:1}),(0,l.Wm)(c,null,{default:(0,l.w5)((()=>[g.value?((0,l.wg)(),(0,l.j4)(m,{key:0,type:"text",square:"",animation:"blink",height:"300px"})):((0,l.wg)(),(0,l.iD)("div",ce,[(0,l._)("div",oe,[((0,l.wg)(!0),(0,l.iD)(l.HY,null,(0,l.Ko)(Ce.value.appearsOnAlbums.slice(0,6),(e=>((0,l.wg)(),(0,l.j4)(k.Z,{key:e.title,image:e.image,title:e.title,artistName:e.artist.name,year:e.year,onPlay:a=>Qe(e),onEnqueue:a=>Le(e)},null,8,["image","title","artistName","year","onPlay","onEnqueue"])))),128))]),Ce.value.appearsOnAlbums.length>6?((0,l.wg)(),(0,l.j4)(ze,{key:0,size:"sm",onClick:a[2]||(a[2]=e=>b.value="albums")},{default:(0,l.w5)((()=>[(0,l.Uk)("view more")])),_:1})):(0,l.kq)("",!0)]))])),_:1})])),_:1})):(0,l.kq)("",!0)]),(0,l._)("div",me,[Ce.value.relations?((0,l.wg)(),(0,l.j4)(d,{key:0,class:"my-card shadow-box shadow-10",bordered:""},{default:(0,l.w5)((()=>[(0,l.Wm)(c,null,{default:(0,l.w5)((()=>[(0,l.Uk)(" Relations ")])),_:1}),(0,l.Wm)(o),(0,l.Wm)(c,null,{default:(0,l.w5)((()=>[g.value?((0,l.wg)(),(0,l.j4)(m,{key:0,type:"text",square:"",animation:"blink",height:"300px"})):(0,l.kq)("",!0),((0,l.wg)(!0),(0,l.iD)(l.HY,null,(0,l.Ko)(Ce.value.relations,(e=>((0,l.wg)(),(0,l.iD)("p",{key:e.url},[(0,l.Wm)(v,{id:e["type-id"],url:e.url},null,8,["id","url"])])))),128))])),_:1})])),_:1})):(0,l.kq)("",!0),(0,l.Wm)(d,{class:"my-card shadow-box shadow-10 q-mt-lg",bordered:""},{default:(0,l.w5)((()=>[(0,l.Wm)(c,null,{default:(0,l.w5)((()=>[(0,l.Uk)(" Similar ")])),_:1}),(0,l.Wm)(o),(0,l.Wm)(c,null,{default:(0,l.w5)((()=>[(0,l._)("div",de,[((0,l.wg)(!0),(0,l.iD)(l.HY,null,(0,l.Ko)(Ce.value.similar.slice(0,3),(e=>((0,l.wg)(),(0,l.iD)("div",{class:"col-4",key:e.name},[(0,l._)("p",pe,[(0,l.Wm)(Ze,{to:{name:"artist",params:{name:e.name}},style:{"text-decoration":"none"}},{default:(0,l.w5)((()=>[(0,l.Wm)(n,{class:"q-mr-sm q-mb-sm rounded-borders",style:{"border-radius":"50%"},src:e.image,fit:"cover",width:"96px",height:"96px","spinner-color":"pink"},null,8,["src"]),be,(0,l.Uk)((0,s.zw)(e.name),1)])),_:2},1032,["to"])])])))),128)),(0,l.Wm)(ze,{size:"sm",onClick:a[3]||(a[3]=e=>b.value="similarArtists")},{default:(0,l.w5)((()=>[(0,l.Uk)("view more")])),_:1})])])),_:1})])),_:1}),(0,l.Wm)(d,{class:"my-card shadow-box shadow-10 q-mt-lg",bordered:""},{default:(0,l.w5)((()=>[(0,l.Wm)(c,null,{default:(0,l.w5)((()=>[(0,l.Uk)(" Stats ")])),_:1}),(0,l.Wm)(o),(0,l.Wm)(c,null,{default:(0,l.w5)((()=>[ge])),_:1})])),_:1})])])])),_:1}),(0,l.Wm)(je,{name:"biography"},{default:(0,l.w5)((()=>[(0,l._)("div",ve,[(0,l._)("div",ke,[(0,l.Wm)(d,{class:"my-card shadow-box shadow-10 q-pa-lg",bordered:""},{default:(0,l.w5)((()=>[(0,l.Wm)(c,null,{default:(0,l.w5)((()=>[he])),_:1}),(0,l.Wm)(o),(0,l.Wm)(c,null,{default:(0,l.w5)((()=>[(0,l._)("div",{innerHTML:Ce.value.bio?Ue(Ce.value.bio.content||""):""},null,8,we)])),_:1})])),_:1})])])])),_:1}),(0,l.Wm)(je,{name:"similarArtists"},{default:(0,l.w5)((()=>[fe,(0,l._)("div",ye,[((0,l.wg)(!0),(0,l.iD)(l.HY,null,(0,l.Ko)(Ce.value.similar,(e=>((0,l.wg)(),(0,l.j4)(Ze,{to:{name:"artist",params:{name:e.name}},key:e},{default:(0,l.w5)((()=>[(0,l.Wm)(n,{src:e.image||"#",width:"250px",height:"250px",fit:"cover"},{error:(0,l.w5)((()=>[(0,l._)("div",_e,[(0,l._)("div",qe,(0,s.zw)(e.name),1)])])),default:(0,l.w5)((()=>[(0,l._)("div",xe,(0,s.zw)(e.name),1)])),_:2},1032,["src"])])),_:2},1032,["to"])))),128))])])),_:1}),(0,l.Wm)(je,{name:"albums"},{default:(0,l.w5)((()=>[Ae,(0,l._)("div",We,[((0,l.wg)(!0),(0,l.iD)(l.HY,null,(0,l.Ko)(Ce.value.topAlbums,(e=>((0,l.wg)(),(0,l.j4)(k.Z,{key:e.title,image:e.image,title:e.title,artistName:e.artist.name,year:e.year,onPlay:a=>Qe(e),onEnqueue:a=>Le(e)},null,8,["image","title","artistName","year","onPlay","onEnqueue"])))),128))])])),_:1}),(0,l.Wm)(je,{name:"tracks"},{default:(0,l.w5)((()=>[Ie,(0,l.Uk)(" Lorem ipsum dolor sit amet consectetur adipisicing elit. ")])),_:1}),(0,l.Wm)(je,{name:"stats"},{default:(0,l.w5)((()=>[Pe,(0,l.Uk)(" Lorem ipsum dolor sit amet consectetur adipisicing elit. ")])),_:1})])),_:1},8,["modelValue"])):(0,l.kq)("",!0)])),_:1})}}};var Ee=t(9885),Se=t(2857),Ue=t(335),Te=t(7817),De=t(7661),ze=t(990),Qe=t(9800),Le=t(4106),Ze=t(4458),je=t(3190),He=t(926),Ne=t(7133),Re=t(6933),Ye=t(1357),Me=t(8289),Oe=t(8879);const Be=Ce,Ke=Be;b()(Ce,"components",{QPage:Ee.Z,QIcon:Se.Z,QImg:Ue.Z,QTabs:Te.Z,QTab:De.Z,QBadge:ze.Z,QTabPanels:Qe.Z,QTabPanel:Le.Z,QCard:Ze.Z,QCardSection:je.Z,QSeparator:He.Z,QSkeleton:Ne.ZP,QMarkupTable:Re.Z,QAvatar:Ye.Z,QLinearProgress:Me.Z,QBtn:Oe.Z})}}]);