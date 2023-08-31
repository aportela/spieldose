<template>
  <span :class="className">
    <slot name="prepend">
    </slot>
    {{ label }}
    <slot name="append">
    </slot>
  </span>
</template>

<script setup>

import { date } from 'quasar';

const props = defineProps({
  className: {
    type: String
  },
  timestamp: {
    type: Number,
    required: true
  }
});

let label = "";
let diff = date.getDateDiff(Date.now(), props.timestamp, 'years');
if (diff < 1) {
  diff = date.getDateDiff(Date.now(), props.timestamp, 'months');
  if (diff < 1) {
    diff = date.getDateDiff(Date.now(), props.timestamp, 'days');
    if (diff < 1) {
      diff = date.getDateDiff(Date.now(), props.timestamp, 'hours');
      if (diff < 1) {
        diff = date.getDateDiff(Date.now(), props.timestamp, 'minutes');
        if (diff < 1) {
          diff = date.getDateDiff(Date.now(), props.timestamp, 'seconds');
          label = diff + ' second' + (diff > 1 ? 's': '') + ' ago';
        } else {
          label = diff + ' minute' + (diff > 1 ? 's': '') + ' ago';
        }
      } else {
        label = diff + ' hour' + (diff > 1 ? 's': '') + ' ago';
      }
    } else {
      label = diff + ' day' + (diff > 1 ? 's': '') + ' ago';
    }
  } else {
    label = diff + ' month' + (diff > 1 ? 's': '') + ' ago';
  }
} else {
  label = diff + ' year' + (diff > 1 ? 's': '') + ' ago';
}

</script>
