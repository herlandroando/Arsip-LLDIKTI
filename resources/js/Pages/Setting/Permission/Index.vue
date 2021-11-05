<template>
  <layout>
    <el-row :gutter="30">
      <el-col :span="24" :md="8">
        <el-card
          class="box-card permission-container"
          :class="{ 'has-content': isAvailable }"
        >
          <div class="permission-title-bar bg-white">
            <h4 class="title">Nama Alias Akses</h4>
          </div>
          <div class="permission-body" :class="{ 'has-content': isAvailable }">
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
                  subTitle="Segera melakukan kelola ijin untuk pengunaan arsip yang lebih aman."
                >
                  <template #title>
                    <p><b>Pengaturan Ijin Kosong</b></p>
                  </template>
                  <template #icon>
                    <failed />
                  </template>
                </el-result>
              </el-col>
            </template>
          </div>
          <div class="permission-footer-bar">
            <el-button-group
              ><el-button
                :disabled="unableChange"
                @click="handleAddList"
                type="primary"
                ><b>+</b></el-button
              ><el-button
                @click="isDialogWarnOpened = true"
                :disabled="!isSelectedAvailable || unableChange"
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
        <el-card class="box-card permission-action-container">
          <template v-if="isSelectedAvailable || isAdd">
            <el-row>
              <el-col :span="24" :md="12" :lg="14">
                <h4 v-if="isAdd">Tambah Ijin</h4>
                <h4 v-else>Ubah Ijin</h4>
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
                    <el-input
                      v-model="formData.nama"
                      :readonly="unableChange"
                    ></el-input>
                  </el-form-item>
                </el-col>
                <el-col :span="24">
                  <el-form-item prop="r_surat">
                    <el-checkbox
                      v-model="formData.r_surat"
                      label="Melihat Surat dan Disposisi"
                      :disabled="unableChange"
                    ></el-checkbox>
                  </el-form-item>
                  <!-- <small></small> -->
                </el-col>
                <el-col v-if="formData.r_surat" :span="24">
                  <el-form-item prop="w_suratmasuk">
                    <el-checkbox
                      v-model="formData.w_suratmasuk"
                      label="Membuat/Mengubah Surat Masuk"
                      :disabled="unableChange"
                    ></el-checkbox>
                  </el-form-item>
                </el-col>
                <el-col v-if="formData.r_surat" :span="24">
                  <el-form-item prop="w_suratkeluar">
                    <el-checkbox
                      v-model="formData.w_suratkeluar"
                      label="Membuat/Mengubah Surat Keluar"
                      :disabled="unableChange"
                    ></el-checkbox>
                  </el-form-item>
                </el-col>
                <el-col v-if="formData.r_surat" :span="24">
                  <el-form-item prop="w_all_surat">
                    <el-checkbox
                      v-model="formData.w_all_surat"
                      label="Mengubah Semua Milik Surat"
                      :disabled="unableChange"
                    ></el-checkbox>
                  </el-form-item>
                </el-col>
                <el-col v-if="formData.r_surat" :span="24">
                  <el-form-item prop="d_surat">
                    <el-checkbox
                      v-model="formData.d_surat"
                      label="Hapus Surat"
                      :disabled="unableChange"
                    ></el-checkbox>
                  </el-form-item>
                </el-col>
                <el-col v-if="formData.r_surat" :span="24">
                  <el-form-item prop="d_miliksurat">
                    <el-checkbox
                      v-model="formData.d_miliksurat"
                      label="Hapus Surat Milik Sendiri"
                      :disabled="unableChange"
                    ></el-checkbox>
                  </el-form-item>
                </el-col>
                <el-col v-if="formData.r_surat" :span="24">
                  <el-form-item prop="dp_surat">
                    <el-checkbox
                      v-model="formData.dp_surat"
                      label="Hapus Surat secara Permanen"
                      :disabled="unableChange"
                    ></el-checkbox>
                  </el-form-item>
                </el-col>
                <el-col v-if="formData.r_surat" :span="24">
                  <el-form-item prop="r_all_disposisi">
                    <el-checkbox
                      v-model="formData.r_all_disposisi"
                      label="Melihat Disposisi dari Semua Jabatan"
                      :disabled="unableChange"
                    ></el-checkbox>
                  </el-form-item>
                </el-col>
                <el-col v-if="formData.r_surat" :span="24">
                  <el-form-item prop="w_disposisi">
                    <el-checkbox
                      v-model="formData.w_disposisi"
                      label="Mengelola Disposisi"
                      :disabled="unableChange"
                    ></el-checkbox>
                  </el-form-item>
                </el-col>
                <el-col :span="24">
                  <el-form-item prop="r_laporan">
                    <el-checkbox
                      v-model="formData.r_laporan"
                      label="Melihat Laporan"
                      :disabled="unableChange"
                    ></el-checkbox>
                  </el-form-item>
                </el-col>
                <el-col class="last-col" :span="24">
                  <el-form-item prop="admin">
                    <el-checkbox
                      v-model="formData.admin"
                      label="Administrator"
                      :disabled="unableChange"
                    ></el-checkbox>
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
              <el-input v-model="formData.nama"></el-input>
            </el-form-item>
          </el-col>
          <el-col :span="24">
            <el-form-item prop="r_surat">
              <el-checkbox
                v-model="formData.r_surat"
                :disabled="unableChange"
                label="Melihat Surat dan Disposisi"
              ></el-checkbox>
            </el-form-item>
            <!-- <small></small> -->
          </el-col>
          <el-col v-if="formData.r_surat" :span="24">
            <el-form-item prop="w_suratmasuk">
              <el-checkbox
                v-model="formData.w_suratmasuk"
                label="Membuat/Mengubah Surat Masuk"
                :disabled="unableChange"
              ></el-checkbox>
            </el-form-item>
          </el-col>
          <el-col v-if="formData.r_surat" :span="24">
            <el-form-item prop="w_suratkeluar">
              <el-checkbox
                v-model="formData.w_suratkeluar"
                label="Membuat/Mengubah Surat Keluar"
                :disabled="unableChange"
              ></el-checkbox>
            </el-form-item>
          </el-col>
          <el-col v-if="formData.r_surat" :span="24">
            <el-form-item prop="w_all_surat">
              <el-checkbox
                v-model="formData.w_all_surat"
                label="Mengubah Semua Milik Surat"
                :disabled="unableChange"
              ></el-checkbox>
            </el-form-item>
          </el-col>
          <el-col v-if="formData.r_surat" :span="24">
            <el-form-item prop="d_surat">
              <el-checkbox
                v-model="formData.d_surat"
                :disabled="unableChange"
                label="Hapus Surat"
              ></el-checkbox>
            </el-form-item>
          </el-col>
          <el-col v-if="formData.r_surat" :span="24">
            <el-form-item prop="d_miliksurat">
              <el-checkbox
                :disabled="unableChange"
                v-model="formData.d_miliksurat"
                label="Hapus Surat Milik Sendiri"
              ></el-checkbox>
            </el-form-item>
          </el-col>
          <el-col v-if="formData.r_surat" :span="24">
            <el-form-item prop="dp_surat">
              <el-checkbox
                :disabled="unableChange"
                v-model="formData.dp_surat"
                label="Hapus Surat secara Permanen"
              ></el-checkbox>
            </el-form-item>
          </el-col>
          <el-col v-if="formData.r_surat" :span="24">
            <el-form-item prop="r_all_disposisi">
              <el-checkbox
                :disabled="unableChange"
                v-model="formData.r_all_disposisi"
                label="Melihat Disposisi dari Semua Jabatan"
              ></el-checkbox>
            </el-form-item>
          </el-col>
          <el-col v-if="formData.r_surat" :span="24">
            <el-form-item prop="w_disposisi">
              <el-checkbox
                v-model="formData.w_disposisi"
                :disabled="unableChange"
                label="Mengelola Disposisi"
              ></el-checkbox>
            </el-form-item>
          </el-col>
          <el-col :span="24">
            <el-form-item prop="r_laporan">
              <el-checkbox
                v-model="formData.r_laporan"
                :disabled="unableChange"
                label="Melihat Laporan"
              ></el-checkbox>
            </el-form-item>
          </el-col>
          <el-col class="last-col" :span="24">
            <el-form-item prop="admin">
              <el-checkbox
                v-model="formData.admin"
                :disabled="unableChange || !permission.super_admin"
                label="Administrator"
              ></el-checkbox>
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>

      <template #footer>
        <el-button
          v-if="!unableChange"
          @click="handleSubmit"
          :disabled="!isEdit && !isAdd"
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
      <span>Apakah anda yakin ingin menghapus ijin ini?</span>
      <small
        >Jabatan yang memakai ijin ini akan terubah menjadi tidak ada ijin dan
        perlu diubah nantinya.</small
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
import {
  defaultProps,
  initializationView,
  getPermission,
} from "@shared/InertiaConfig.js";
import Layout from "@shared/Layout.vue";
import { computed, reactive, ref } from "@vue/reactivity";
import { onMounted, onUpdated, watch } from "@vue/runtime-core";
import _ from "lodash";
import { Failed, List } from "@element-plus/icons";
import { useMq } from "vue3-mq";

export default {
  props: {
    ...defaultProps,
    list: { type: Array, default: () => [] },
    selectedContent: { type: Object, default: () => {} },
    isAvailable: { type: Boolean, default: false },
    isSelectedAvailable: { type: Boolean, default: false },
    isAdd: { type: Boolean, default: false },
    unableChange: { type: Boolean, default: false },
  },
  components: { Layout, List, Failed },
  setup(props) {
    initializationView(props);
    console.log(props.list);
    const listPermission = ref(props.list);
    const isEdit = ref(false);
    const form = ref(null);
    const formData = reactive({
      r_surat: false,
      r_all_disposisi: false,
      r_laporan: false,
      d_surat: false,
      d_miliksurat: false,
      dp_surat: false,
      w_disposisi: false,
      w_suratmasuk: false,
      w_suratkeluar: false,
      w_all_surat: false,
      admin: false,
    });
    const isDialogWarnOpened = ref(false);
    const isDialogFormOpened = computed(
      () => props.isAdd || props.isSelectedAvailable
    );
    const permission = getPermission(props);

    function initData() {
      if (props.isSelectedAvailable) {
        let data = props.selectedContent;
        formData.r_surat = data.r_surat;
        formData.r_all_disposisi = data.r_all_disposisi;
        formData.r_laporan = data.r_surat;
        formData.d_surat = data.d_surat;
        formData.d_miliksurat = data.d_miliksurat;
        formData.dp_surat = data.dp_surat;
        formData.w_disposisi = data.w_disposisi;
        formData.w_suratmasuk = data.w_suratmasuk;
        formData.w_all_surat = data.w_all_surat;
        formData.w_suratkeluar = data.w_suratkeluar;
        formData.admin = data.admin;
      }
      if (props.isAdd) {
        defaultAddData();
      }
    }

    function defaultAddData() {
      formData.nama = "";
      formData.r_surat = false;
      formData.r_all_disposisi = false;
      formData.r_laporan = false;
      formData.d_surat = false;
      formData.d_miliksurat = false;
      formData.dp_surat = false;
      formData.w_disposisi = false;
      formData.w_suratmasuk = false;
      formData.w_all_surat = false;
      formData.w_suratkeluar = false;
      formData.admin = false;
    }

    onMounted(() => {
      initData();
    });

    onUpdated(() => {
      initData();
    });

    watch(
      () => _.cloneDeep(formData),
      (newVal, prevVal) => {
        if (props.isSelectedAvailable) {
          let data = props.selectedContent;
          let changed = false;

          for (const key in newVal) {
            //   console.log(newVal[key] != data[key], key);
            if (newVal[key] != data[key]) changed = true;
          }
          console.log(isEdit.value, "Changed");
          isEdit.value = changed;
        }
      }
    );

    function handleClickList(id) {
      Inertia.get(
        route("setting.permission.show", { permission: id }),
        {},
        { preserveScroll: true }
      );
    }

    function handleCancelList() {
      Inertia.get(route("setting.permission.index"));
    }

    function handleAddList() {
      Inertia.get(route("setting.permission.create"));
    }

    function handleRemoveList() {
      Inertia.delete(
        route("setting.permission.destroy", {
          permission: props.selectedContent.id,
        })
      );
    }

    function handleReset() {
      initData();
    }

    function handleSubmit() {
      let data = { ...formData };
      if (isEdit.value) {
        Inertia.put(
          route("setting.permission.update", {
            permission: props.selectedContent.id,
          }),
          data,
          { preserveScroll: true }
        );
      } else if (props.isAdd) {
        Inertia.post(route("setting.permission.store"), data, {
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
      permission,
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

.permission-action-container {
  min-height: 550px;
}
.permission-container {
  max-height: 700px;

  width: 100%;
  position: relative;
  overflow-y: hidden;
}
.permission-container.has-content {
  overflow-y: auto !important;
}
.permission-title-bar {
  position: sticky;
  width: 100%;
  left: 0px;
  top: 0px;
  padding: 15px 0px;
  border-bottom: 1px solid var(--el-border-color-base);
  background-color: white;
  z-index: 100;
}
.permission-title-bar .title {
  margin-left: 30px !important;
}
.permission-footer-bar {
  position: sticky;
  bottom: 0px;
  width: 100%;
  padding: 20px 0px;
  border-top: 1px solid var(--el-border-color-base);
  left: 0px;
  background-color: white;
}
.permission-footer-bar .el-form {
  padding: 0px 20px;
  z-index: 100;
}
.permission-body {
  /* gap: 30px; */
  /* min-height: 200px; */
  /* padding-bottom: 150px; */
  display: flex;
  flex-direction: column;
  height: 300px;
}
.permission-body .el-button {
  margin-left: 0px !important;
}
.permission-body.has-content {
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
