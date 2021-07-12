<template>
  <layout> TEST </layout>
</template>

<script>
import { defaultProps, initializationView } from "@shared/InertiaConfig.js";
import Layout from "@shared/Layout";

export default {
  components: { Layout },

  props: {
    ...defaultProps,
  },
  setup(props) {
    initializationView(props);
    console.log(props, "log on UI Dynamic");
  },
};
</script>

<style>
</style>
