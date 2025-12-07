<template>
  <q-page>
    <q-card class="q-pa-none">
      <q-card-section>
        <BreadCrumb icon="explore" label="Explore" />
      </q-card-section>
      <q-separator />
      <q-card-section class="q-pa-none">
        <div class="row">
          <div class="col-3" v-for="section, index in artistsSections" :key="index">
            <q-card class="q-ma-sm">
              <q-card-section class="text-center">
                {{ section }}
                <div v-if="index === 0">
                  <q-tabs v-model="tab" dense no-caps class="text-grey" active-color="primary" indicator-color="primary"
                    align="justify" narrow-indicator>
                    <q-tab name="a1" label="Global" />
                    <q-tab name="a2" label="Today" />
                    <q-tab name="a3" label="This week" />
                    <q-tab name="a4" label="This month" />
                    <q-tab name="a5" label="This year" />
                  </q-tabs>
                </div>
                <div v-else class="q-pa-md"></div>
              </q-card-section>
              <q-separator />
              <q-card-section class="row">
                <div class="col-4 text-center" v-for="i in 6" :key="i">
                  <q-skeleton width="40%" style="height: auto; aspect-ratio: 1; margin: 0px auto" animation="none" />
                  artist name
                </div>
              </q-card-section>
            </q-card>
          </div>
        </div>
        <div class="row">
          <div class="col-3" v-for="section, index in albumsSections" :key="index">
            <q-card class="q-ma-sm">
              <q-card-section class="text-center">
                {{ section }}
                <div v-if="index === 0">
                  <q-tabs v-model="tab" dense no-caps class="text-grey" active-color="primary" indicator-color="primary"
                    align="justify" narrow-indicator>
                    <q-tab name="a1" label="Global" />
                    <q-tab name="a2" label="Today" />
                    <q-tab name="a3" label="This week" />
                    <q-tab name="a4" label="This month" />
                    <q-tab name="a5" label="This year" />
                  </q-tabs>
                </div>
                <div v-else class="q-pa-md"></div>
              </q-card-section>
              <q-separator />
              <q-card-section class="row">
                <div class="col-4 text-center" v-for="i in 6" :key="i">
                  <q-skeleton width="40%" style="height: auto; aspect-ratio: 1; margin: 0px auto" animation="none" />
                  album title
                  <p>(year)</p>
                </div>
              </q-card-section>
            </q-card>
          </div>
        </div>
      </q-card-section>
    </q-card>
  </q-page>
</template>

<script setup lang="ts">
import { ref } from "vue";
import { default as BreadCrumb } from "src/components/BreadCrumb.vue";

const artistsSections = ref<string[]>(["Top artists", "Recommended artists", "Random artists", "New artists"]);
const albumsSections = ref<string[]>(["Top albums", "Recommended albums", "Random albums", "New albums"]);

const tab = ref<string>("a1");

</script>