<template>
  <a :href="url" target="_blank">
    <q-chip size="md"  :icon="icon" truncate-chip-labels v-if="label && icon && url">{{ label }}</q-chip>
  </a>
</template>

<script setup>

import { ref, computed, } from "vue";

const props = defineProps({
  id: String,
  url: String
});


const icon = computed(() => {
  let labelValue = "bookmark";
  switch (props.id) {
    case "fe33d22f-c3b0-4d68-bd53-a856badf2b15":
      labelValue = 'home';
      break;
    case "d0c5cf3a-8afb-4d24-ad47-00f43dc509fe":
      labelValue = 'album';
      break;
    case "f484f897-81cc-406e-96f9-cd799a04ee24":
      labelValue = 'person_pin';
      break;
    case "78f75830-94e1-4138-8f8a-643e3bb21ce5":
      labelValue = 'description';
      break;
    case "e4d73442-3762-45a8-905c-401da65544ed":
      labelValue = 'lyrics';
      break;
    case "bac47923-ecde-4b59-822e-d08f0cd10156":
      labelValue = 'link';
      break;
    case "89e4a949-0976-440d-bda1-5f772c1e5710":
      labelValue = 'link';
      break;
    case "6a540e5b-58c6-4192-b6ba-dbc71ec8fcf0":
      labelValue = 'link';
      break;
    case "35b3a50f-bf0e-4309-a3b4-58eeed8cee6a":
      labelValue = 'forum';
      break;
    case "eb535226-f8ca-499d-9b18-6a144df4ae6f":
      labelValue = 'event_note';
      break;
    case "6b3e3c85-0002-4f34-aca6-80ace0d7e846":
      labelValue = 'link';
      break;
    case "04a5b104-a4c2-4bac-99a1-7b837c37d9e4":
      labelValue = 'link';
      break;
    case "94c8b0cc-4477-4106-932c-da60e63de61c":
      labelValue = 'link';
      break;
    case "08db8098-c0df-4b78-82c3-c8697b4bba7f":
      labelValue = 'link';
      break;
    case "689870a4-a1e4-4912-b17f-7b2664215698":
      labelValue = 'link';
      break;
    case "29651736-fa6d-48e4-aadc-a557c6add1cb":
      labelValue = 'link';
      break;
    case "221132e9-e30e-43f2-a741-15afc4c5fa7c":
      labelValue = 'photo_camera';
      break;
  }
  return (labelValue);
});

const label = computed(() => {
  let labelValue = null;
  switch (props.id) {
    case "fe33d22f-c3b0-4d68-bd53-a856badf2b15":
      labelValue = 'Official homepage';
      break;
    case "d0c5cf3a-8afb-4d24-ad47-00f43dc509fe":
      labelValue = 'Discography';
      break;
    case "f484f897-81cc-406e-96f9-cd799a04ee24":
      labelValue = 'Fanpage';
      break;
    case "78f75830-94e1-4138-8f8a-643e3bb21ce5":
      labelValue = 'Biography';
      break;
    case "e4d73442-3762-45a8-905c-401da65544ed":
      labelValue = 'Lyrics';
      break;
    case "bac47923-ecde-4b59-822e-d08f0cd10156":
      labelValue = 'MySpace';
      break;
    case "89e4a949-0976-440d-bda1-5f772c1e5710":
      labelValue = 'SoundCloud';
      break;
    case "6a540e5b-58c6-4192-b6ba-dbc71ec8fcf0":
      labelValue = 'Youtube';
      break;
    case "35b3a50f-bf0e-4309-a3b4-58eeed8cee6a":
      labelValue = 'Online community';
      break;
    case "eb535226-f8ca-499d-9b18-6a144df4ae6f":
      labelValue = 'Blog';
      break;
    case "6b3e3c85-0002-4f34-aca6-80ace0d7e846":
      labelValue = 'AllMusic';
      break;
    case "04a5b104-a4c2-4bac-99a1-7b837c37d9e4":
      labelValue = 'DiscoGS';
      break;
    case "94c8b0cc-4477-4106-932c-da60e63de61c":
      labelValue = 'IMDB';
      break;
    case "08db8098-c0df-4b78-82c3-c8697b4bba7f":
      labelValue = 'Last.FM';
      break;
    case "689870a4-a1e4-4912-b17f-7b2664215698":
      labelValue = 'Wikidata';
      break;
    case "29651736-fa6d-48e4-aadc-a557c6add1cb":
      labelValue = 'Wikipedia';
      break;
    case "221132e9-e30e-43f2-a741-15afc4c5fa7c":
      labelValue = 'Image';
      break;
  }
  return (labelValue);
});

</script>
