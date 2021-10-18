<template>
  <n-config-provider :theme-overrides="lldikti_theme">
    <n-theme-editor>
      <slot></slot>
    </n-theme-editor>
  </n-config-provider>
</template>

<script>
import { NConfigProvider } from "naive-ui";
//Added theme editor for edit the theme.
import { NThemeEditor } from "naive-ui";
//Configure custom theme.
const lldikti_theme = {
  common: {
    primaryColor: "#0C4B8EFF",
    primaryColorHover: "#356FADFF",
    primaryColorPressed: "#08284BFF",
    primaryColorSuppl: "#346EABFF",
  },
};
export default {
  components: {
    NThemeEditor,
    NConfigProvider,
  },
  setup() {
    return { NThemeEditor, NConfigProvider, lldikti_theme };
  },
};
</script>

<style>

</style>

