<template>
  <layout>

  </layout>
</template>

<script>
import { defaultProps, initializationView } from "@shared/InertiaConfig.js";
import Layout from "@shared/Layout.vue";

export default {
  components: { Layout },
  props: {
    ...defaultProps,
  },
  setup(props) {
    initializationView(props);
  },
};
</script>

<style>
</style>
