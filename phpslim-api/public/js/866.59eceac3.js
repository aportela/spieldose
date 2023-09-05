"use strict";(globalThis["webpackChunkspieldose"]=globalThis["webpackChunkspieldose"]||[]).push([[866],{6344:(e,a,s)=>{s.d(a,{Z:()=>d});var l=s(9835);const r={xmlns:"http://www.w3.org/2000/svg",class:"equilizer",viewBox:"0 0 128 128"},t=(0,l.uE)('<g><title>Audio Equalizer</title><rect class="bar" transform="translate(0,0)" y="15"></rect><rect class="bar" transform="translate(25,0)" y="15"></rect><rect class="bar" transform="translate(50,0)" y="15"></rect><rect class="bar" transform="translate(75,0)" y="15"></rect><rect class="bar" transform="translate(100,0)" y="15"></rect></g>',1),n=[t];function o(e,a){return(0,l.wg)(),(0,l.iD)("svg",r,n)}var i=s(1639);const u={},c=(0,i.Z)(u,[["render",o]]),d=c},3788:(e,a,s)=>{s.d(a,{Z:()=>m});var l=s(9835),r=s(6970),t=s(499),n=s(1569);const o={id:"spieldose-album-cover-tiles-container"},i=["src"],u="images/vinyl.png",c={__name:"TileAlbumImages",setup(e){const a=(0,t.iH)([]);function s(){const e="ABCDEF0123456789";let a="#";while(a.length<7)a+=e.charAt(Math.floor(16*Math.random()+1));return a}function c(e){return e<this.images.length?this.images[e]:this.defaultImage}function d(){n.api.album.getSmallRandomCovers(42).then((e=>{a.value=Array.isArray(e.data.coverURLs)?e.data.coverURLs:[]})).catch((e=>{console.error(e.response)}))}function m(e){e.target.src=u}return d(),(e,t)=>((0,l.wg)(),(0,l.iD)("div",o,[((0,l.wg)(),(0,l.iD)(l.HY,null,(0,l.Ko)([0,1,2,3,4,5,6],(e=>(0,l._)("div",{class:"row",key:e},[((0,l.wg)(),(0,l.iD)(l.HY,null,(0,l.Ko)([0,1,2,3,4,5],(n=>(0,l._)("div",{class:"col-2",key:n,style:(0,r.j5)("background-color: "+s()+";")},[a.value.length>0?((0,l.wg)(),(0,l.iD)("img",{key:0,class:"spieldose-album-cover-tile",src:c(6*e+n),onError:t[0]||(t[0]=e=>m(e))},null,40,i)):((0,l.wg)(),(0,l.iD)("img",{key:1,class:"spieldose-album-cover-tile",src:u}))],4))),64))]))),64))]))}},d=c,m=d},9866:(e,a,s)=>{s.r(a),s.d(a,{default:()=>I});s(9665);var l=s(9835),r=s(1957),t=s(6970),n=s(499),o=s(796),i=s(9302),u=s(8339),c=s(7712),d=s(1569),m=s(6344),p=s(3788);const g={class:"row items-center"},f={class:"col-xs-10 offset-xs-1 col-sm-8 offset-sm-2 col-md-6 offset-md-3 col-lg-4 offset-lg-0 col-xl-3 offset-xl-0 desktop-only justify-center q-pa-xl"},v=["onSubmit"],w={class:"text-grey-8"},y={class:"text-grey-8"},b={class:"text-dark text-weight-bold",style:{"text-decoration":"none"}},h={class:"gt-md col-lg-8 col-xl-9 container_tiles"},_={__name:"SignUpPage",setup(e){const{t:a}=(0,c.QT)(),s=(0,i.Z)(),_=(0,u.tv)(),k=(0,n.iH)(!1),x=(0,n.iH)({email:{hasErrors:!1,message:null},password:{hasErrors:!1,message:null}}),E=[e=>!!e||a("Field is required")],S=(0,n.iH)(null),q=(0,n.iH)(null),W=(0,n.iH)(null),Z=(0,n.iH)(null);function U(){k.value=!0,d.api.user.signUp((0,o.Z)(),S.value,W.value).then((e=>{s.notify({type:"positive",message:a("Your account has been created"),actions:[{label:a("Sign in"),color:"white",handler:()=>{_.push({name:"signIn"})}}]}),k.value=!1})).catch((e=>{switch(k.value=!1,e.response.status){case 400:e.response.data.invalidOrMissingParams.find((function(e){return"email"===e}))?(s.notify({type:"negative",message:a("API Error: missing email param")}),q.value.focus()):e.response.data.invalidOrMissingParams.find((function(e){return"password"===e}))?(s.notify({type:"negative",message:a("API Error: missing password param")}),Z.value.focus()):s.notify({type:"negative",message:a("API Error: invalid/missing param")});break;case 409:x.value.email.hasErrors=!0,x.value.email.message=a("Email already used"),nextTick((()=>{q.value.focus()}));break;default:s.notify({type:"negative",message:a("API Error: fatal error"),caption:a("API Error: fatal error details",{status:e.response.status,statusText:e.response.statusText})});break}}))}return(e,s)=>{const o=(0,l.up)("q-card-section"),i=(0,l.up)("q-icon"),u=(0,l.up)("q-input"),c=(0,l.up)("q-spinner-hourglass"),d=(0,l.up)("q-btn"),_=(0,l.up)("router-link"),A=(0,l.up)("q-card"),z=(0,l.up)("q-page");return(0,l.wg)(),(0,l.j4)(z,null,{default:(0,l.w5)((()=>[(0,l._)("div",g,[(0,l._)("div",f,[(0,l.Wm)(m.Z),(0,l.Wm)(A,{class:"q-pa-md my_card"},{default:(0,l.w5)((()=>[(0,l._)("form",{onSubmit:(0,r.iM)(U,["prevent","stop"]),autocorrect:"off",autocapitalize:"off",autocomplete:"off",spellcheck:"false"},[(0,l.Wm)(o,{class:"text-center"},{default:(0,l.w5)((()=>[(0,l._)("h3",null,(0,t.zw)(e.$t("Spieldose")),1),(0,l._)("div",w,(0,t.zw)(e.$t("Sign up below to create your account")),1)])),_:1}),(0,l.Wm)(o,null,{default:(0,l.w5)((()=>[(0,l.Wm)(u,{dense:"",outlined:"",ref_key:"emailRef",ref:q,modelValue:S.value,"onUpdate:modelValue":s[0]||(s[0]=e=>S.value=e),type:"email",name:"email",label:(0,n.SU)(a)("Email"),disable:k.value,autofocus:!0,rules:E,"lazy-rules":"",error:x.value.email.hasErrors,errorMessage:x.value.email.message},{prepend:(0,l.w5)((()=>[(0,l.Wm)(i,{name:"alternate_email"})])),_:1},8,["modelValue","label","disable","error","errorMessage"]),(0,l.Wm)(u,{dense:"",outlined:"",class:"q-mt-md",ref_key:"passwordRef",ref:Z,modelValue:W.value,"onUpdate:modelValue":s[1]||(s[1]=e=>W.value=e),name:"password",type:"password",label:(0,n.SU)(a)("Password"),disable:k.value,rules:E,"lazy-rules":"",error:x.value.password.hasErrors,errorMessage:x.value.password.message},{prepend:(0,l.w5)((()=>[(0,l.Wm)(i,{name:"key"})])),_:1},8,["modelValue","label","disable","error","errorMessage"])])),_:1}),(0,l.Wm)(o,null,{default:(0,l.w5)((()=>[(0,l.Wm)(d,{color:"dark",size:"md",label:e.$t("Sign up"),"no-caps":"",class:"full-width",icon:"account_circle",disable:k.value||!(S.value&&W.value),loading:k.value,type:"submit"},{loading:(0,l.w5)((()=>[(0,l.Wm)(c,{class:"on-left"}),(0,l.Uk)(" "+(0,t.zw)((0,n.SU)(a)("Loading...")),1)])),_:1},8,["label","disable","loading"])])),_:1}),(0,l.Wm)(o,{class:"text-center q-pt-none"},{default:(0,l.w5)((()=>[(0,l._)("div",y,[(0,l.Uk)((0,t.zw)((0,n.SU)(a)("Already have an account ?"))+" ",1),(0,l.Wm)(_,{to:{name:"signIn"}},{default:(0,l.w5)((()=>[(0,l._)("span",b,(0,t.zw)((0,n.SU)(a)("Click here to sign in")),1)])),_:1})])])),_:1})],40,v)])),_:1})]),(0,l._)("div",h,[(0,l.Wm)(p.Z)])])])),_:1})}}};var k=s(9885),x=s(4458),E=s(3190),S=s(6611),q=s(2857),W=s(8879),Z=s(3358),U=s(9984),A=s.n(U);const z=_,I=z;A()(_,"components",{QPage:k.Z,QCard:x.Z,QCardSection:E.Z,QInput:S.Z,QIcon:q.Z,QBtn:W.Z,QSpinnerHourglass:Z.Z})}}]);