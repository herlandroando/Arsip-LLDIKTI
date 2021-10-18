<template>
  <el-select
    v-model="value2"
    multiple
    collapse-tags
    style="margin-left: 20px"
    placeholder="Select"
  >
    <el-option
      v-for="item in options"
      :key="item.value"
      :label="item.label"
      :value="item.value"
    >
    </el-option>
  </el-select>
</template>

<script setup>
import { ref } from "@vue/reactivity";

const value2 = ref();
const options = ref([
  { label: "ok", value: 0 },
  { label: "ock", value: 1 },
]);
</script>

<style>
</style>
