const template=function(){return'\n        <input class="input" type="text" v-bind:placeholder="placeholder" v-bind:disabled="loading" v-model.trim="text" v-on:keyup.esc="onClear" v-on:keyup="onChange">\n    '};export default{name:"spieldose-input-typeahead",template:'\n        <input class="input" type="text" v-bind:placeholder="placeholder" v-bind:disabled="loading" v-model.trim="text" v-on:keyup.esc="onClear" v-on:keyup="onChange">\n    ',data:function(){return{text:null,timeout:null}},props:["placeholder","loading"],methods:{onClear:function(){this.text=null},onChange:function(e){"Alt"!=e.key&&"Tab"!=e.key&&"Control"!=e.key&&"Shift"!=e.key&&"CapsLock"!=e.key&&"AltGraph"!=e.key&&"Enter"!=e.key&&"ArrowLeft"!=e.key&&"ArrowRight"!=e.key&&"ArrowUp"!=e.key&&"ArrowDown"!=e.key&&(this.timeout&&clearTimeout(this.timeout),this.timeout=setTimeout((()=>{this.$emit("on-value-change",this.text)}),256))}}};