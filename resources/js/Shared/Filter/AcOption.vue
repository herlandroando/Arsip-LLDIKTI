<template>
  <el-form-item label="Filter Pilihan">
    <el-select
      v-if="options.isMultiple"
      v-model="modelValueComputed"
      multiple
      filterable
      remote
      reserve-keyword
      placeholder="Please enter a keyword"
      :remote-method="handleSearchSelect"
      :loading="onLoading"
    >
      <el-option
        v-for="item in list"
        :key="item.value"
        :label="item.label"
        :value="item.value"
      >
      </el-option>
    </el-select>

    <!-- <el-button v-else class="button-new-tag" size="small" @click="showInput"
        >+ Tambah Pilihan</el-button
      >
      <el-tag
        :key="tag.value"
        v-for="tag in modelValue"
        closable
        :disable-transitions="false"
        @close="handleClose(tag.value)"
      >
        {{ tag.label }}
      </el-tag> -->
    <el-autocomplete
      v-else
      v-model="modelValueComputed"
      filterable
      remote
      placeholder="Please enter a keyword"
      :remote-method="handleSearchSelect"
      :loading="onLoading"
    >
      <el-option
        v-for="item in list"
        :key="item.value"
        :label="item.label"
        :value="item.value"
      >
      </el-option>
    </el-autocomplete>
  </el-form-item>
</template>

<script setup>
import * as Default from "@shared/Filter/DefaultOption.js";
import { computed, onMounted, ref } from "@vue/runtime-core";
import axios from "axios";

const props = defineProps({
  options: {
    type: Object,
    default: () => {
      return { ...Default.optionAutocomplete };
    },
  },
  query: String,
  modelValue: [Array, String, Number],
});
const emits = defineEmits(["update:modelValue"]);

const onLoading = ref(false);
const list = ref([]);

function handleSearchSelect(text) {
  //   hasLoading.value = true;
  onLoading.value = true;
  axios
    .get(route(props.options.url.name, { query: props.query, search: text }))
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
}
const modelValueComputed = computed({
  get: () => {
    return props.modelValue;
  },
  set: (value) => {
    emits("update:modelValue", value);
  },
});

//Handle multiple select control.
// function handleMultipleSelect(item) {
//   let newValue = "";
//   if (props.modelValue instanceof Array) {
//     newValue = props.modelValue.push(item.value);
//   } else {
//     newValue = [item];
//   }
//   emit("update:modelValue", newValue);
// }
// function handleClose(item) {
//   let newValue = "";
//   newValue = props.modelValue.splice(props.modelValue.indexOf(item), 1);
//   emit("update:modelValue", newValue);
// }
// //Handle single select control
// function handleSelect(item) {
//   emit("update:modelValue", item.id);
// }
</script>

<style scoped>
/* .el-tag + .el-tag {
  margin-left: 10px;
}
.button-new-tag {
  margin-left: 10px;
  height: 32px;
  line-height: 30px;
  padding-top: 0;
  padding-bottom: 0;
}
.input-new-tag {
  width: 90px;
  margin-left: 10px;
  vertical-align: bottom;
} */
</style>
