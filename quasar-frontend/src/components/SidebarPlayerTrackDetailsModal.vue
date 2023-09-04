<template>
  <q-dialog v-model="visible" @hide="onHide">
    <q-card class="my-card">
      <q-img :src="coverImage" width="400px" height="400px" />
      <q-card-section>
        <div class="-mb-md">
          <q-input size="xs" disable square outlined type="text" label="Title"
            :model-value="track ? track.title : 'No title'"></q-input>
          <q-input size="xs" disable square outlined type="text" label="Album"
            :model-value="track ? track.album : 'No album'"></q-input>
          <q-input size="xs" disable square outlined type="text" label="Artist"
            :model-value="track ? track.artist : 'No artist'">
            <template v-slot:append>
              <q-icon name="link" />
              <q-icon name="link" />
            </template>
          </q-input>
        </div>
      </q-card-section>
      <q-separator />
    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref } from "vue";

const props = defineProps({
  coverImage: String,
  track: Object
});

const emit = defineEmits(['hide']);

const visible = ref(true);

function onHide() {
  emit('hide');
}
</script>
