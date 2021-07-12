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
.bg-primary {
  background: #0c4b8e !important;
  color: #fff;
}

.bg-secondary {
  background: #134172 !important;
  color: #fff;
}

.bg-dark {
  background: #303742 !important;
  color: #fff;
}

.bg-gray {
  background: #f7f8f9 !important;
}

.bg-success {
  background: #32b643 !important;
  color: #fff;
}

.bg-warning {
  background: #ffb700 !important;
  color: #fff;
}

.bg-error {
  background: #e85600 !important;
  color: #fff;
}
.height-100 {
  height: 100%;
}
</style>

