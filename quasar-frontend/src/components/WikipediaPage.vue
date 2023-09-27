<template>
  <div v-html="htmlPage" id="custom_wikipedia_styles">
  </div>
</template>

<style>
/* wikipedia (html bio page) styles are stripped, put here some required classes */

#custom_wikipedia_styles .infobox {
  border: 1px solid #a2a9b1;
  border-spacing: 3px;
  background-color: #f8f9fa;
  color: black;
  margin: 0.5em 0 0.5em 1em;
  padding: 0.2em;
  float: right;
  clear: right;
  font-size: 88%;
  line-height: 1.5em;
  width: 22em;
}

#custom_wikipedia_styles figure[typeof~="mw:File/Thumb"]>.mw-file-description,
figure[typeof~="mw:File/Thumb"]>.mw-file-magnify {
  display: block;
  position: relative;
}

#custom_wikipedia_styles figure[typeof~="mw:File/Thumb"].mw-halign-left,
#custom_wikipedia_styles figure[typeof~="mw:File/Frame"].mw-halign-left {
  margin: 0.5em 1.4em 1.3em 0;
  clear: left;
  float: left;
}

#custom_wikipedia_styles figure[typeof~="mw:File/Thumb"].mw-halign-right,
#custom_wikipedia_styles figure[typeof~="mw:File/Frame"].mw-halign-right {
  margin: 0.5em 0 1.3em 1.4em;
  clear: right;
  float: right;
}


#custom_wikipedia_styles figure[typeof~="mw:File/Thumb"],
#custom_wikipedia_styles figure[typeof~="mw:File/Frame"] {
  border: 1px solid #c8ccd1;
  border-bottom-width: 1px;
  border-bottom-style: solid;
  border-bottom-color: rgb(200, 204, 209);
  border-bottom: 0;
  background-color: #f8f9fa;
}


#custom_wikipedia_styles figure[typeof~="mw:File/Thumb"],
#custom_wikipedia_styles figure[typeof~="mw:File/Frame"] {
  display: table;
  text-align: center;
  border-collapse: collapse;
  line-height: 0;
  margin: 0.5em 0 1.3em 1.4em;
  clear: right;
  float: right;
}

#custom_wikipedia_styles figure[typeof~="mw:File/Thumb"]> :not(figcaption) .mw-file-element,
#custom_wikipedia_styles figure[typeof~="mw:File/Frame"]> :not(figcaption) .mw-file-element {
  border: 1px solid #c8ccd1;
  background: #ffffff;
}

#custom_wikipedia_styles .wikitable {
    background-color: #f8f9fa;
    color: #202122;
    margin: 1em 0;
    border: 1px solid #a2a9b1;
    border-collapse: collapse;
  }

  #custom_wikipedia_styles  .wikitable > caption {
    font-weight: bold;
  }

  #custom_wikipedia_styles .wikitable > tr > th, #custom_wikipedia_styles .wikitable > * > tr > th {
    background-color: #eaecf0;
    text-align: center;
  }


  #custom_wikipedia_styles .wikitable > tr > th, #custom_wikipedia_styles .wikitable > tr > td, #custom_wikipedia_styles .wikitable > * > tr > th, #custom_wikipedia_styles .wikitable > * > tr > td {
    border: 1px solid #a2a9b1;
    padding: 0.2em 0.4em;
  }

  #custom_wikipedia_styles .mw-parser-output .reflist {
  font-size: 90%;
  margin-bottom: 0.5em;
  list-style-type: decimal;
}

#custom_wikipedia_styles   .mw-references-columns {
  -webkit-column-width: 30em;
  -moz-column-width: 30em;
  column-width: 30em;
  margin-top: 0.3em;
}


#custom_wikipedia_styles ol.references > li {
  counter-increment: mw-ref-extends-parent mw-references;
  counter-reset: mw-ref-extends-child;
}
#custom_wikipedia_styles .mw-references-columns li {
  -webkit-column-break-inside: avoid;
  page-break-inside: avoid;
  break-inside: avoid-column;
}
#custom_wikipedia_styles  li {
    margin-bottom: 0.1em;
  }



  #custom_wikipedia_styles .mw-parser-output .navbox, #custom_wikipedia_styles .mw-parser-output .navbox-subgroup {
  background-color: #fdfdfd;
}
#custom_wikipedia_styles .mw-parser-output .navbox {
  box-sizing: border-box;
  border: 1px solid #a2a9b1;
  width: 100%;
  clear: both;
  font-size: 88%;
  text-align: center;
  padding: 1px;
  margin: 1em auto 0;
}

#custom_wikipedia_styles .mw-parser-output .hlist dd, #custom_wikipedia_styles .mw-parser-output .hlist dt, #custom_wikipedia_styles .mw-parser-output .hlist li {
  margin: 0;
  display: inline;
}</style>

<script setup>
const props = defineProps({
  htmlPage: {
    type: String
  }
});

</script>
