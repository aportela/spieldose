<template>
  <div id="player_controls">
    <ul class="list list--buttons">
      <li><a href="#" class="list__link"><q-icon name="skip_previous"></q-icon></a></li>
      <li><a href="#" class="list__link" style=""><q-icon name="play_arrow" @click="onPlay"></q-icon></a></li>
      <li><a href="#" class="list__link"><q-icon name="skip_next"></q-icon></a></li>
    </ul>
  </div>
</template>

<style>
div#player_controls {
  padding-bottom: 1rem;
}

.list--buttons {
  align-items: center;
  justify-content: center;
}

.list {
  display: flex;
  margin: 0;
  padding: 0;
  list-style-type: none;
}

ul {
  list-style: none;
  list-style-type: none;
}

.list--buttons li:first-of-type a,
.list--buttons li:last-of-type a {
  font-size: 0.95rem;
  color: #212121;
  opacity: 0.5;
}

.list--buttons li:nth-of-type(n+2) {
  margin-left: 1.25rem;
}

.list--buttons a {
  padding-top: 0.45rem;
  padding-right: 0.75rem;
  padding-bottom: 0.45rem;
  padding-left: 0.75rem;
  font-size: 1rem;
  border-radius: 50%;
  box-shadow: 0 3px 6px rgba(33, 33, 33, 0.1), 0 3px 12px rgba(33, 33, 33, 0.15);
}

.list__link {
  transition: all 0.25s cubic-bezier(0.4, 0, 1, 1);
}

a {
  color: #485fc7;
  cursor: pointer;
  text-decoration: none;
}
</style>

<script setup>

const emit = defineEmits(['play']);
function onPlay() {
  emit('play');
}
</script>
