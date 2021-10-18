<template>
  <layout>
    <el-row :gutter="30">
      <el-col :span="24" :md="8">
        <el-card
          class="box-card jabatan-container"
          :class="{ 'has-content': isAvailable }"
        >
          <div class="jabatan-title-bar bg-white">
            <h4 class="title">Nama Jabatan</h4>
          </div>
          <div class="jabatan-body" :class="{ 'has-content': isAvailable }">
            <template v-if="isAvailable">
              <template v-for="list in listPermission" :key="list.id">
                <el-button
                  v-if="isSelectedAvailable && list.id == selectedContent.id"
                  type="primary"
                  @click="handleCancelList"
                  >{{ list.nama }}</el-button
                >
                <el-button v-else @click="handleClickList(list.id)">{{
                  list.nama
                }}</el-button>
              </template>
            </template>
            <template v-else>
              <el-col class="no-result">
                <el-result
                  subTitle="Segera melakukan kelola jabatan untuk pengunaan arsip yang lebih aman."
                >
                  <template #title>
                    <p><b>Pengaturan Jabatan Kosong</b></p>
                  </template>
                  <template #icon>
                    <failed />
                  </template>
                </el-result>
              </el-col>
            </template>
          </div>
          <div class="jabatan-footer-bar">
            <el-button-group
              ><el-button @click="handleAddList" type="primary"
                ><b>+</b></el-button
              ><el-button
                @click="isDialogWarnOpened = true"
                :disabled="!isSelectedAvailable"
                type="danger"
                ><b>-</b></el-button
              ></el-button-group
            >
          </div>
        </el-card>
      </el-col>
      <el-col
        v-if="useMq().current !== 'sm' && useMq().current !== 'xs'"
        :span="24"
        :sm="16"
      >
        <el-card class="box-card jabatan-action-container">
          <template v-if="isSelectedAvailable || isAdd">
            <el-row>
              <el-col :span="24" :md="12" :lg="14">
                <h4 v-if="isAdd">Tambah Jabatan</h4>
                <h4 v-else>Ubah Jabatan</h4>
              </el-col>
            </el-row>
            <el-form
              label-position="top"
              label-width="100px"
              :model="formData"
              ref="form"
            >
              <el-row class="form-container">
                <el-col v-if="isAdd" :span="24" prop="nama">
                  <el-form-item label="Nama Ijin">
                    <el-input v-model="formData.nama" :readonly="unableChange"></el-input>
                  </el-form-item>
                </el-col>
                <el-col :span="24" prop="ijin">
                  <el-form-item label="Ijin yang Digunakan">
                    <el-select
                      v-model="formData.ijin"
                      placeholder="Pilih salah satu"
                      :disabled="unableChange"
                    >
                      <el-option
                        v-for="item in permissionList"
                        :key="item.id"
                        :label="item.nama"
                        :value="item.id"
                      >
                      </el-option>
                    </el-select>
                  </el-form-item>
                </el-col>
                <el-col :span="24">
                 <el-button
                    v-if="!unableChange"
                    @click="handleSubmit"
                    :disabled="!isEdit && !isAdd"
                    type="primary"
                  >
                    Simpan
                  </el-button>
                  <el-button
                    v-if="!isAdd && !unableChange"
                    :disabled="!isEdit"
                    @click="handleReset"
                  >
                    Reset
                  </el-button>
                </el-col>
              </el-row>
            </el-form>
          </template>
          <template v-else>
            <el-col class="no-picked">
              <el-result
                subTitle="Pilih salah satu ijin yang telah disimpan pada sistem."
              >
                <template #title>
                  <p><b>Belum Dipilih</b></p>
                </template>
                <template #icon>
                  <list />
                </template>
              </el-result>
            </el-col>
          </template>
        </el-card>
      </el-col>
    </el-row>

    <el-dialog
      @close="handleCancelList"
      v-if="useMq().current === 'sm' || useMq().current === 'xs'"
      v-model="isDialogFormOpened"
      :title="isAdd ? 'Tambah Ijin' : 'Ubah Ijin'"
      width="90%"
    >
      <el-form
        label-position="top"
        label-width="100px"
        :model="formData"
        ref="form"
      >
        <el-row class="form-container">
          <el-col v-if="isAdd" :span="24" prop="nama">
            <el-form-item label="Nama Ijin">
              <el-input v-model="formData.nama" :readonly="unableChange"></el-input>
            </el-form-item>
          </el-col>
          <el-col :span="24" prop="ijin">
            <el-form-item label="Ijin yang Digunakan">
              <el-select :disabled="unableChange" v-model="formData.ijin" placeholder="Pilih salah satu">
                <el-option
                  v-for="item in permissionList"
                  :key="item.id"
                  :label="item.nama"
                  :value="item.id"
                >
                </el-option>
              </el-select>
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>

      <template #footer>
        <el-button
          @click="handleSubmit"
          :disabled="!isEdit && !isAdd && !unableChange"
          type="primary"
        >
          Simpan
        </el-button>
        <el-button
          v-if="isSelectedAvailable && !unableChange"
          :disabled="!isEdit"
          @click="handleReset"
        >
          Reset
        </el-button>
        <el-button
          @click="isDialogWarnOpened = true"
          v-if="isSelectedAvailable && !unableChange"
          type="danger"
          ><b>Hapus</b></el-button
        >
      </template>
    </el-dialog>
    <el-dialog v-model="isDialogWarnOpened" title="Peringatan" width="50%">
      <span>Apakah anda yakin ingin menghapus jabatan ini?</span><br />
      <small
        >Pengguna yang memakai jabatan ini akan terubah menjadi tidak ada
        jabatan dan perlu diubah nantinya.</small
      >
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="handleCancelList">Batal</el-button>
          <el-button type="danger" @click="handleRemoveList">Hapus</el-button>
        </span>
      </template>
    </el-dialog>
  </layout>
</template>

<script >
import { Inertia } from "@inertiajs/inertia";
import { defaultProps, initializationView } from "@shared/InertiaConfig.js";
import Layout from "@shared/Layout.vue";
import { computed, reactive, ref } from "@vue/reactivity";
import { onMounted, watch } from "@vue/runtime-core";
import _ from "lodash";
import { Failed, List } from "@element-plus/icons";
import { useMq } from "vue3-mq";

export default {
  props: {
    ...defaultProps,
    list: { type: Array, default: () => [] },
    selectedContent: { type: Object, default: () => {return {}} },
    permissionList: {type:Array, default:()=>[]},
    isAvailable: { type: Boolean, default: false },
    isSelectedAvailable: { type: Boolean, default: false },
    isAdd: { type: Boolean, default: false },
    unableChange: {type: Boolean, default:false},
  },
  components: { Layout, List, Failed },
  setup(props) {
    initializationView(props);
    console.log(props.list);
    const listPermission = ref(props.list);
    const isEdit = ref(false);
    const form = ref(null);
    const formData = reactive({
      ijin:""
    });
    const isDialogWarnOpened = ref(false);
    const isDialogFormOpened = computed(
      () => props.isAdd || props.isSelectedAvailable
    );

    function initData() {
      if (props.isSelectedAvailable) {
        let data = props.selectedContent;
        formData.ijin = data.id_ijin;
      }

    }

    onMounted(() => {
      initData();
    });

    watch(
      () => _.cloneDeep(formData),
      (newVal, prevVal) => {
        if (props.isSelectedAvailable) {
          let data = props.selectedContent;


          isEdit.value =  newVal.ijin !== data.id_ijin;
        }
      }
    );

    function handleClickList(id) {
      Inertia.get(
        route("setting.jabatan.show", { jabatan: id }),
        {},
        { preserveScroll: true }
      );
    }

    function handleCancelList() {
      Inertia.get(route("setting.jabatan.index"));
    }

    function handleAddList() {
      Inertia.get(route("setting.jabatan.create"));
    }

    function handleRemoveList() {
      Inertia.get(
        route("setting.jabatan.destroy", {
          jabatan: props.selectedContent.id,
        })
      );
    }

    function handleReset() {
      initData();
    }

    function handleSubmit() {
      let data = { ...formData };
      if (isEdit) {
        Inertia.put(
          route("setting.jabatan.update", { jabatan: id }),
          data,
          { preserveScroll: true }
        );
      } else if (isAdd) {
        Inertia.put(route("setting.jabatan.store"), data, {
          preserveScroll: true,
        });
      }
    }

    return {
      form,
      formData,
      listPermission,
      isEdit,
      isDialogWarnOpened,
      isDialogFormOpened,
      useMq,
      handleReset,
      handleCancelList,
      handleClickList,
      handleAddList,
      handleRemoveList,
      handleSubmit,
    };
  },
};
</script>

<style>
/* .form-container .el-col {
  margin-bottom: 20px;
} */
.dialog-container {
  width: 90%;
}

.form-container .last-col {
  margin-bottom: 40px;
}

.box-card {
  margin: 30px 0;
}

.jabatan-action-container {
  min-height: 550px;
}
.jabatan-container {
  max-height: 700px;

  width: 100%;
  position: relative;
  overflow-y: hidden;
}
.jabatan-container.has-content {
  overflow-y: auto !important;
}
.jabatan-title-bar {
  position: sticky;
  width: 100%;
  left: 0px;
  top: 0px;
  padding: 15px 0px;
  border-bottom: 1px solid var(--el-border-color-base);
  background-color: white;
  z-index: 100;
}
.jabatan-title-bar .title {
  margin-left: 30px !important;
}
.jabatan-footer-bar {
  position: sticky;
  bottom: 0px;
  width: 100%;
  padding: 20px 0px;
  border-top: 1px solid var(--el-border-color-base);
  left: 0px;
  background-color: white;
}
.jabatan-footer-bar .el-form {
  padding: 0px 20px;
  z-index: 100;
}
.jabatan-body {
  /* gap: 30px; */
  /* min-height: 200px; */
  /* padding-bottom: 150px; */
  display: flex;
  flex-direction: column;
  height: 300px;
}
.jabatan-body .el-button {
  margin-left: 0px !important;
}
.jabatan-body.has-content {
  padding-top: 20px;
  padding-bottom: 50px;
  /* min-height: 200px; */
  /* padding-bottom: 150px; */
  overflow-y: auto !important;
}
.message-card-other {
  margin-right: auto;
}
.message-card-self {
  margin-left: auto;
}
.message {
  width: 70%;
}
.message .title {
  margin: 10px 0px;
}
.no-result {
  margin-top: 30px;
  margin-bottom: 80px;
}

.no-picked {
  width: 100%;
  margin-top: 100px;
}
</style>


<style scoped>
.el-result__icon {
  opacity: 10%;
}
</style>
