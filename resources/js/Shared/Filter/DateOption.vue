<template>
  <el-form-item>
    <el-checkbox v-if="options.hasBetween" v-model="stateBetweenComputed"
      >Mode Rentang Tanggal</el-checkbox
    >
  </el-form-item>
  <template v-if="options.hasBetween && stateBetweenActive">
    <el-form-item
      v-if="useMq().current !== 'xs'"
      label="Filter Rentang Tanggal"
    >
      <el-date-picker
        v-model="modelValueComputed"
        type="daterange"
        range-separator="-"
        start-placeholder="Mulai"
        end-placeholder="Hingga"
        :disabledDate="dateNowAndBefore"
      >
      </el-date-picker>
    </el-form-item>
    <template v-else>
      <el-form-item label="Mulai Tanggal">
        <el-date-picker
          v-model="startDateMobile"
          type="date"
          placeholder="Pilih Tanggal"
          :disabledDate="dateNowUntilEnd"
        >
        </el-date-picker>
      </el-form-item>
      <el-form-item label="Hingga Tanggal">
        <el-date-picker
          v-model="endDateMobile"
          type="date"
          placeholder="Pilih Tanggal"
          :disabledDate="dateNowAndBefore"
        >
        </el-date-picker>
      </el-form-item>
    </template>
  </template>
  <el-form-item v-else label="Filter Tanggal">
    <el-date-picker
      v-model="modelValueComputed"
      type="date"
      placeholder="Pilih Tanggal"
      @change="$emit('update:modelValue', $event.target.value)"
      :disabledDate="dateNowAndBefore"
    >
    </el-date-picker>
  </el-form-item>
</template>

<script setup>
import { dateNowAndBefore } from "@shared/HelperFunction.js";
import { computed, ref } from "@vue/reactivity";
import { onMounted, watch } from "@vue/runtime-core";

import { useMq } from "vue3-mq";

import * as Default from "@shared/Filter/DefaultOption.js";

const props = defineProps({
  options: {
    type: Object,
    default: () => {
      return { ...Default.optionDate };
    },
  },
  query: String,
  modelValue: { type: [Object, String], default: () => new Date() },
});

const emits = defineEmits(["update:modelValue"]);

onMounted(() => {
  if (stateBetweenActive.value)
    emits("update:modelValue", [
      new Date(),
      new Date(),
    ]);
  else emits("update:modelValue", new Date());
});

const stateBetweenActive = ref(false);
const stateBetweenComputed = computed({
  get: () => stateBetweenActive.value,
  set: (value) => {
    if (value) emits("update:modelValue", [props.modelValue, props.modelValue]);
    else emits("update:modelValue", new Date());
    stateBetweenActive.value = value;
  },
});

const modelValueComputed = computed({
  get: () => {
    return props.modelValue;
  },
  set: (value) => {
    emits("update:modelValue", value);
  },
});

const startDateMobile = computed({
  get: () => {
    if (Array.isArray(props.modelValue) && props.modelValue.length === 2) {
      return props.modelValue[0];
    } else {
      return new Date();
    }
  },
  set: (value) => {
    if (+value <= +props.modelValue[1]) {
      emits("update:modelValue", [value, props.modelValue[1]]);
    }
  },
});
const endDateMobile = computed({
  get: () => {
    if (Array.isArray(props.modelValue) && props.modelValue.length === 2) {
      return props.modelValue[1];
    } else {
      return new Date();
    }
  },
  set: (value) => {
    if (+value >= +props.modelValue[0]) {
      emits("update:modelValue", [props.modelValue[0], value]);
    } else {
      emits("update:modelValue", [
        value,
        value
      ]);
    }
  },
});

function dateNowUntilEnd(time) {
  return time.getTime() > endDateMobile.value;
}
</script>

<style>
</style>
