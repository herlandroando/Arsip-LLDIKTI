<template>
  <el-form-item label="Filter Pilihan">
    <el-select
      v-if="options.isMultiple"
      v-model="modelValueComputed"
      multiple
      collapse-tags
      placeholder="Pilih satu atau lebih nilai filter"
      :loading="hasLoading && onLoading"
      loading-text="Mohon Menunggu..."
    >
      <el-option
        v-for="item in list"
        :key="item.value"
        :label="item.label"
        :value="item.value"
        :disabled="item.disabled"
      >
      </el-option>
    </el-select>
    <el-select
      v-else
      v-model="modelValueComputed"
      placeholder="Pilih satu opsi nilai filter"
      :loading="hasLoading && onLoading"
      loading-text="Mohon Menunggu..."
    >
      <el-option
        v-for="item in list"
        :key="item.value"
        :label="item.label"
        :value="item.value"
        :disabled="item.disabled"
      >
      </el-option>
    </el-select>
  </el-form-item>
</template>

<script setup>
// import { dateNowAndBefore } from "@shared/HelperFunction.js";
import * as Default from "@shared/Filter/DefaultOption.js";
import { computed, onMounted, onUpdated, ref } from "@vue/runtime-core";
import axios from "axios";

const props = defineProps({
  options: {
    type: Object,
    default: () => {
      return { ...Default.optionSelect };
    },
  },
  query: String,
  modelValue: [Array, String, Number],
});
const hasLoading = ref(false);
const onLoading = ref(false);
const emits = defineEmits(["update:modelValue"]);
const list = ref([]);
const modelValueComputed = computed({
  get: () => {
    return props.modelValue;
  },
  set: (value) => {
    emits("update:modelValue", value);
  },
});
onMounted(() => {
  console.log(props.options);
  init();
});
onUpdated(() => {
    init();
});

function init() {
  if ("url" in props.options && props.options.url.name != "") {
    hasLoading.value = true;
    onLoading.value = true;
    axios
      .get(route(props.options.url.name, { query: props.query }))
      .then((value) => {
        console.log(value.data);
        let response = value.data;
        list.value = response.data.map((value) => {
          return {
            label: value[props.options.schema.label],
            value: value[props.options.schema.value],
          };
        });
        console.log(list.value);
        onLoading.value = false;
      })
      .catch((err) => {
        list.value = [{ label: "Kosong", value: "" }];
        onLoading.value = false;
      });
  } else if (props.options.list.length > 0) {
    list.value = props.options.list;
  } else {
    list.value = [{ label: "Kosong", value: "" }];
  }
}
function handleChangeOption(value) {
  if (props.options.isMultiple) {
    let newValue = [];
    if (Array.isArray(props.modelValue))
      props.modelValue.forEach((modelVal) => {
        newValue.push(modelVal);
      });
    newValue.push(value[0]);
    console.log(newValue);
    emits("update:modelValue", newValue);
  } else {
    emits("update:modelValue", value);
  }
}
</script>

<style>
</style>
