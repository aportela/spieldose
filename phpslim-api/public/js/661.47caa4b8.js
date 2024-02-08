"use strict";(globalThis["webpackChunkspieldose"]=globalThis["webpackChunkspieldose"]||[]).push([[661],{9661:(e,a,s)=>{s.r(a),s.d(a,{default:()=>U});var r=s(9835),l=s(499),o=s(6970),u=s(1957),n=s(6647),i=s(9302),t=s(1569);const d={class:"text-h3"},m={class:"row q-col-gutter-md"},v={class:"col-6"},p={class:"col-6"},c={class:"row q-col-gutter-md"},f={class:"col-6"},g={class:"col-6"},w={class:"row q-mt-md"},h={class:"col-12"},b={__name:"ProfilePage",setup(e){const{t:a}=(0,n.QT)(),s=(0,i.Z)(),b=(0,l.iH)(!1),E=(0,l.iH)({email:{hasErrors:!1,message:null},name:{hasErrors:!1,message:null},password:{hasErrors:!1,message:null},confirmedPassword:{hasErrors:!1,message:null}}),P=[e=>!!e||a("Field is required")],y=(0,l.iH)(null),_=(0,l.iH)(null),k=(0,l.iH)(null),V=(0,l.iH)(null),q=(0,l.iH)(null),M=(0,l.iH)(null),W=(0,l.iH)(null),H=(0,l.iH)(null),S=(0,l.iH)(!0);function U(){b.value=!0,t.api.user.getProfile().then((e=>{y.value=e.data.email,k.value=e.data.name,q.value=null,W.value=null,b.value=!1,(0,r.Y3)((()=>{_.value.focus()}))})).catch((e=>{as,s.notify({type:"negative",message:a("API Error: error loading profile"),caption:a("API Error: fatal error details",{status:e.response.status,statusText:e.response.statusText})}),b.value=!1}))}function I(){E.value.email.hasErrors=!1,E.value.email.message=null,E.value.name.hasErrors=!1,E.value.name.message=null,E.value.password.hasErrors=!1,E.value.password.message=null,E.value.confirmedPassword.hasErrors=!1,E.value.confirmedPassword.message=null,_.value.resetValidation(),M.value.resetValidation(),H.value.resetValidation()}function x(){I(),_.value.validate(),V.value.validate(),M.value.validate(),(0,r.Y3)((()=>{_.value.hasError||V.value.hasError||M.value.hasError||H.value.hasError||A()}))}function A(){b.value=!0,t.api.user.updateProfile(y.value,k.value,q.value).then((e=>{y.value=e.data.email,k.value=e.data.name,q.value=null,W.value=null,s.notify({type:"positive",message:a("Profile has been updated")}),b.value=!1,(0,r.Y3)((()=>{_.value.focus()}))})).catch((e=>{switch(b.value=!1,e.response.status){case 400:e.response.data.invalidOrMissingParams.find((function(e){return"email"===e}))?(s.notify({type:"negative",message:a("API Error: missing email param")}),_.value.focus()):s.notify({type:"negative",message:a("API Error: invalid/missing param")});break;case 409:e.response.data.invalidOrMissingParams.find((function(e){return"email"===e}))?(E.value.email.hasErrors=!0,E.value.email.message=a("Email already used"),(0,r.Y3)((()=>{_.value.focus()}))):e.response.data.invalidOrMissingParams.find((function(e){return"name"===e}))?(E.value.name.hasErrors=!0,E.value.name.message=a("Name already used"),(0,r.Y3)((()=>{V.value.focus()}))):s.notify({type:"negative",message:a("API Error: fatal error"),caption:a("API Error: fatal error details",{status:e.response.status,statusText:e.response.statusText})});break;case 410:E.value.email.hasErrors=!0,E.value.email.message=a("Account has been deleted"),(0,r.Y3)((()=>{_.value.focus()}));break;default:s.notify({type:"negative",message:a("API Error: fatal error"),caption:a("API Error: fatal error details",{status:e.response.status,statusText:e.response.statusText})});break}}))}return(0,r.YP)(q,(e=>{S.value=(e||"")==(W.value||""),S.value?(E.value.password.hasErrors=!1,E.value.password.message=null,E.value.confirmedPassword.hasErrors=!1,E.value.confirmedPassword.message=null):(E.value.password.hasErrors=!0,E.value.password.message=E.value.password.hasErrors?a("Passwords don't match"):null)})),(0,r.YP)(W,(e=>{S.value=(e||"")==(q.value||""),S.value?(E.value.password.hasErrors=!1,E.value.password.message=null,E.value.confirmedPassword.hasErrors=!1,E.value.confirmedPassword.message=null):(E.value.confirmedPassword.hasErrors=!0,E.value.confirmedPassword.message=E.value.confirmedPassword.hasErrors?a("Passwords don't match"):null)})),(0,r.bv)((()=>{U()})),(e,s)=>{const n=(0,r.up)("q-breadcrumbs-el"),i=(0,r.up)("q-breadcrumbs"),t=(0,r.up)("q-icon"),U=(0,r.up)("q-input"),I=(0,r.up)("q-spinner-hourglass"),A=(0,r.up)("q-btn"),Q=(0,r.up)("q-card-section"),T=(0,r.up)("q-card");return(0,r.wg)(),(0,r.j4)(T,{class:"q-pa-lg"},{default:(0,r.w5)((()=>[(0,r.Wm)(i,{class:"q-mb-lg"},{default:(0,r.w5)((()=>[(0,r.Wm)(n,{icon:"home",label:"Spieldose"}),(0,r.Wm)(n,{icon:"person",label:(0,l.SU)(a)("My profile")},null,8,["label"])])),_:1}),(0,r.Wm)(Q,{style:{height:"702px"}},{default:(0,r.w5)((()=>[(0,r._)("h3",d,(0,o.zw)((0,l.SU)(a)("My profile")),1),(0,r._)("form",{onSubmit:(0,u.iM)(x,["prevent","stop"]),autocorrect:"off",autocapitalize:"off",autocomplete:"off"},[(0,r._)("div",m,[(0,r._)("div",v,[(0,r.Wm)(U,{dense:"",outlined:"",ref_key:"emailRef",ref:_,modelValue:y.value,"onUpdate:modelValue":s[0]||(s[0]=e=>y.value=e),type:"email",name:"email",label:(0,l.SU)(a)("Email"),disable:b.value,autofocus:!0,rules:P,"lazy-rules":"",error:E.value.email.hasErrors,errorMessage:E.value.email.message},{prepend:(0,r.w5)((()=>[(0,r.Wm)(t,{name:"alternate_email"})])),_:1},8,["modelValue","label","disable","error","errorMessage"])]),(0,r._)("div",p,[(0,r.Wm)(U,{dense:"",outlined:"",ref_key:"nameRef",ref:V,modelValue:k.value,"onUpdate:modelValue":s[1]||(s[1]=e=>k.value=e),type:"text",name:"name",label:(0,l.SU)(a)("Name"),disable:b.value,rules:P,"lazy-rules":"",error:E.value.name.hasErrors,errorMessage:E.value.name.message},{prepend:(0,r.w5)((()=>[(0,r.Wm)(t,{name:"badge"})])),_:1},8,["modelValue","label","disable","error","errorMessage"])])]),(0,r._)("div",c,[(0,r._)("div",f,[(0,r.Wm)(U,{dense:"",outlined:"",ref_key:"passwordRef",ref:M,modelValue:q.value,"onUpdate:modelValue":s[2]||(s[2]=e=>q.value=e),name:"password",type:"password",label:(0,l.SU)(a)("New password"),disable:b.value,error:E.value.password.hasErrors,errorMessage:E.value.password.message},{prepend:(0,r.w5)((()=>[(0,r.Wm)(t,{name:"key"})])),_:1},8,["modelValue","label","disable","error","errorMessage"])]),(0,r._)("div",g,[(0,r.Wm)(U,{dense:"",outlined:"",ref_key:"confirmedPasswordRef",ref:H,modelValue:W.value,"onUpdate:modelValue":s[3]||(s[3]=e=>W.value=e),name:"confirmedPassword",type:"password",label:(0,l.SU)(a)("Confirm password"),disable:b.value,error:E.value.confirmedPassword.hasErrors,errorMessage:E.value.confirmedPassword.message},{prepend:(0,r.w5)((()=>[(0,r.Wm)(t,{name:"key"})])),_:1},8,["modelValue","label","disable","error","errorMessage"])])]),(0,r._)("div",w,[(0,r._)("div",h,[(0,r.Wm)(A,{color:"dark",size:"md",label:e.$t("Save profile changes"),"no-caps":"",class:"full-width",icon:"save",disable:b.value||!(y.value&&k.value&&S.value),loading:b.value,type:"submit"},{loading:(0,r.w5)((()=>[(0,r.Wm)(I,{class:"on-left"}),(0,r.Uk)(" "+(0,o.zw)((0,l.SU)(a)("Loading...")),1)])),_:1},8,["label","disable","loading"])])])],32)])),_:1})])),_:1})}}};var E=s(4458),P=s(2605),y=s(8052),_=s(3190),k=s(6611),V=s(2857),q=s(8879),M=s(6335),W=s(9984),H=s.n(W);const S=b,U=S;H()(b,"components",{QCard:E.Z,QBreadcrumbs:P.Z,QBreadcrumbsEl:y.Z,QCardSection:_.Z,QInput:k.Z,QIcon:V.Z,QBtn:q.Z,QSpinnerHourglass:M.Z})}}]);