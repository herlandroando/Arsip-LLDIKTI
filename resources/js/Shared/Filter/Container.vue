<template>
  <el-scrollbar>
    <div class="flex-content">
      <el-button
        style="background-color:#5451CF; color:white"
        size="small"
        @click="dialogTriggered"
      >
        + Tambah Filter
      </el-button>
      <template v-if="!isQueryEmpty">
        <el-tag
          :key="tag"
          v-for="tag in tagList"
          closable
          :disable-transitions="false"
          @close="filterDeleted(tag)"
        >
          {{ tag.label + ": " + definedLabelValue(tag) }}
        </el-tag>
      </template>
    </div>
  </el-scrollbar>
  <teleport to="body">
    <el-dialog
      title="Filter Tabel"
      v-model="dialogFormVisible"
      :width="useMq().current === 'xs' ? '80%' : '50%'"
    >
      <el-form label-position="top">
        <el-form-item label="Opsi Filter">
          <el-select
            v-model="selectedFilterComputed"
            placeholder="Pilih Tipe Filter"
            value-key="query"
          >
            <el-option label="Tidak ada pilihan" value="empty"></el-option>
            <template :key="item.query" v-for="item in options">
              <el-option
                v-if="definePermitted(item)"
                :label="item.label"
                :value="item.query"
              >
              </el-option>
            </template>
          </el-select>
        </el-form-item>
        <!-- <keep-alive> -->
        <component
          v-if="selectedFilter.type != ''"
          :query="selectedFilterQuery"
          :is="selectedFilter.type + '-option'"
          v-model="value"
          :options="selectedFilter.options"
        ></component>
        <!-- </keep-alive> -->
      </el-form>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="dialogFormVisible = false">Batal</el-button>
          <el-button type="primary" @click="dialogSubmitted">Filter</el-button>
        </span>
      </template>
    </el-dialog>
  </teleport>
</template>

<script>
import { computed, reactive, ref } from "@vue/reactivity";
import { useMq } from "vue3-mq";
import DateOption from "@shared/Filter/DateOption.vue";
import SelectOption from "@shared/Filter/SelectOption.vue";
import AcOption from "@shared/Filter/AcOption.vue";
import { ElNotification } from "element-plus";
import _ from "lodash";
import { inject, onMounted } from "@vue/runtime-core";
export default {
  props: {
    // callback: { type: Function, default: (query) => {} },
    // isNamed: { type: Boolean, default: false },
    query: Object,
    // customCallback: { type: Boolean, default: false },
    options: {
      type: Array,
      required: true,
    },
  },
  components: { DateOption, AcOption, SelectOption },
  emits: ["isDialogOpened", "isDialogClosed", "deleted", "reset", "submit"],
  setup(props, { emit }) {
    //Variable
    const tagList = ref([]);
    const dialogFormVisible = ref(false);
    const value = ref();
    const selectedFilter = ref({});
    const selectedFilterQuery = ref("empty");
    const selectedFilterComputed = computed({
      get: () => {
        return selectedFilterQuery.value;
      },
      set: (v) => {
        let index = props.options.findIndex((x) => x.query === v);
        if (index === -1) {
          console.log("Filter doesn't found.");
          selectedFilterQuery.value = "empty";
          selectedFilter.value = {};
          return;
        }
        value.value = "";
        selectedFilterQuery.value = v;
        selectedFilter.value = props.options[index];
      },
    });
    const isQueryEmpty = computed(() => _.isEmpty(props.query));
    // Built-in Function
    onMounted(() => {
      let list = inject("list_tag", []);
      tagList.value = list;
    });

    // Function
    function dialogTriggered() {
      dialogFormVisible.value = !dialogFormVisible.value;
    }
    function dialogSubmitted() {
      if (selectedFilter.type === "") {
        ElNotification({
          type: "error",
          title: "Galat",
          message: "Pilih salah satu filter yang akan digunakan!",
        });
        return;
      }
      let newObj = {
        query: selectedFilter.value,
        value: "",
      };
      console.log(value.value);
      if (value.value instanceof Object && !value.value instanceof Date) {
        //value.value instanceof String ) {
        newObj.value = Object.values(value.value);
      } else {
        newObj.value = value.value;
      }
      dialogFormVisible.value = false;
      emit("submit", newObj);
    }
    function filterDeleted(tag) {
      emit("deleted", tag);
    }
    function definedLabelValue(tag) {
      let value = tag.label_value;
      let options = {
        dateStyle: "long",
      };
      if (tag.type === "date") {
        if (Array.isArray(tag.label_value)) {
          let dateStart = new Date(tag.label_value[0]).toLocaleDateString(
            "id-ID",
            options
          );
          let dateEnd = new Date(tag.label_value[1]).toLocaleDateString(
            "id-ID",
            options
          );
          value = dateStart + " - " + dateEnd;
        } else {
          let date = new Date(tag.label_value).toLocaleDateString(
            "id-ID",
            options
          );
          value = date;
        }
      }
      return value;
    }

    function definePermitted(item) {
      let permission = inject("permission", null);
      if (permission === null) {
        return false;
      }
      if (!("permitted" in item)) {
        return true;
      }
      if (item.permitted instanceof Array) {
        for (let index = 0; index < item.permitted.length; index++) {
          let key = item.permitted[index];
          if (!(key in permission)) {
            if (!permission[key]) {
              return false;
            }
          } else {
            return false;
          }
        }
      }
      return true;
    }

    // function addFilter() {}
    // function assignFilterList(val, multi = false) {
    //   let isDate = false;
    //   if (multi && Array.isArray(val.value)) {
    //     val.value.forEach((element) => {
    //       if (!element instanceof Date)
    //         filterList.value.push({
    //           label: val.query.label,
    //           query: val.query.query,
    //           value: element,
    //           isDate: false,
    //         });
    //       else isDate = true;
    //     });
    //     if (isDate) {
    //       filterList.value.push({
    //         label: val.query.label,
    //         query: val.query.query,
    //         value: val.value,
    //         isDate: true,
    //       });
    //     }
    //   } else {
    //     filterList.value.push({
    //       label: val.query.label,
    //       query: val.query.query,
    //       value: val.value,
    //       isDate: val.value instanceof Date ?? false,
    //     });
    //   }
    // }

    return {
      //Variable
      tagList,
      dialogFormVisible,
      value,
      selectedFilter,
      isQueryEmpty,
      selectedFilterQuery,
      selectedFilterComputed,
      //Function
      filterDeleted,
      useMq,
      dialogSubmitted,
      dialogTriggered,
      definedLabelValue,
      definePermitted,
    };
  },
};
</script>

<style scoped>
/* .el-tag + .el-tag {
  margin-left: 10px;
} */
.button-new-tag {
  margin-left: 10px;
  height: 32px;
  line-height: 30px;
  padding-top: 0;
  padding-bottom: 0;
}

.flex-content {
  display: flex;
  gap: 10px;
  padding-bottom: 20px;
}
</style>
