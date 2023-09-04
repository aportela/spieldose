<template>
  <span :class="className">
    <slot name="prepend"></slot>{{ label }}<slot name="append"></slot>
  </span>
</template>

<script setup>

import { date } from 'quasar';
import { useI18n } from 'vue-i18n'

const { t } = useI18n();

const props = defineProps({
  className: {
    type: String
  },
  timestamp: {
    type: Number
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
          label = t(diff > 1 ? 'nSecondsAgo': 'oneSecondAgo', { count: diff });
        } else {
          label = t(diff > 1 ? 'nMinutesAgo': 'oneMinuteAgo', { count: diff });
        }
      } else {
        label = t(diff > 1 ? 'nHoursAgo': 'oneHourAgo', { count: diff });
      }
    } else {
      label = t(diff > 1 ? 'nDaysAgo': 'oneDayAgo', { count: diff });
    }
  } else {
    label = t(diff > 1 ? 'nMonthsAgo': 'oneMonthAgo', { count: diff });
  }
} else {
  label = t(diff > 1 ? 'nYearsAgo': 'oneYearAgo', { count: diff });
}

</script>
