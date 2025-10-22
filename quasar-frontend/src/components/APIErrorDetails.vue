<template>
  <div>
    <dl>
      <dt v-if="apiError.method">Method: <strong>{{ apiError.method }}</strong></dt>
      <dt v-if="apiError.url">URL: <strong>{{ apiError.url }}</strong></dt>
      <dt v-if="apiError.httpCode">HTTP code: <strong>{{ apiError.httpCode }} <span v-if="apiError.httpStatus">({{
        apiError.httpStatus }})</span></strong></dt>
      <div v-if="hasRequestParams || hasResponseData">
        <q-separator class="q-my-md"></q-separator>
        <q-tabs align="left" v-model="tabModel">
          <q-tab name="request" icon="upload">Request</q-tab>
          <q-tab name="response" icon="download">Response</q-tab>
        </q-tabs>
        <q-tab-panels v-model="tabModel">
          <q-tab-panel name="request" class="q-tab-panel-fixed-height">
            <pre class="text-bold" v-if="apiError.request.params.query">{{ apiError.request.params.query }}</pre>
            <pre class="text-bold" v-if="formattedBodyParams">{{ formattedBodyParams }}</pre>
            <pre class="text-bold" v-else-if="apiError.request.params.data">{{ apiError.request.params.data }}</pre>
          </q-tab-panel>
          <q-tab-panel name="response" class="q-tab-panel-fixed-height">
            <pre v-if="formattedResponse" class="formatted-json">{{ formattedResponse }}</pre>
            <div v-else-if="apiError.response">{{ apiError.response }}</div>
            <div v-else>{{ t("API response was empty") }}</div>
          </q-tab-panel>
        </q-tab-panels>
      </div>
    </dl>
  </div>
</template>

<script setup>

import { ref, computed } from "vue";

const props = defineProps({
  apiError: {
    type: Object,
    required: true
  }
});

const tabModel = ref("request");

const formattedBodyParams = ref(null);
const formattedResponse = ref(null);

if (props.apiError.request.params.data) {
  try {
    formattedBodyParams.value = JSON.stringify(JSON.parse(props.apiError.request.params.data), null, 4);
  } catch (e) {
    // invalid JSON ?
  }
}

if (props.apiError.response) {
  if (typeof props.apiError.response === "string") {
    try {
      formattedResponse.value = JSON.stringify(JSON.parse(props.apiError.response), null, 4);
    } catch (e) {
      // invalid JSON ?
    }
  } else if (typeof props.apiError.response === "object") {
    try {
      formattedResponse.value = JSON.stringify(props.apiError.response, null, 4);
    } catch (e) {
      // serialize error ?
    }
  }
}

const hasRequestParams = computed(() => props.apiError.request.params.query || formattedBodyParams.value || props.apiError.request.params.data);
const hasResponseData = computed(() => formattedResponse.value || props.apiError.response);

</script>

<style lang="css" scoped>
.q-tab-panel-fixed-height {
  height: 40vh;
}
</style>