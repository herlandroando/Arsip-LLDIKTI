<template>
  <layout>
    <el-row :gutter="40">
      <el-col :span="24">
        <el-card class="box-card">
          <el-row>
            <el-col :span="24" :md="12" :lg="14">
              <h4>Data Pengguna</h4>
            </el-col>
            <el-col
              :span="24"
              :md="{ span: 12, push: 6 }"
              :lg="{ span: 10, push: 6 }"
              style="margin-bottom: 20px"
            >
              <el-button-group>
                <el-tooltip
                  content="Ubah Data Pengguna"
                  placement="top"
                  effect="light"
                >
                  <el-button
                    :disabled="!canEdit"
                    :type="editMode ? 'primary' : null"
                    icon="el-icon-edit"
                    @click="handleToggleEdit"
                  ></el-button>
                </el-tooltip>
                <el-tooltip
                  content="Hapus Data Pengguna"
                  placement="top"
                  effect="light"
                >
                  <el-button
                    @click="handleDialogWarnOpen"
                    type="danger"
                    icon="el-icon-delete"
                  ></el-button>
                </el-tooltip>
              </el-button-group>
            </el-col>
          </el-row>
          <el-form
            :rules="rules"
            label-position="top"
            label-width="100px"
            :model="formData"
            ref="form"
          >
            <!-- <el-col :span="24"> -->

            <!-- </el-col> -->
            <el-row :gutter="20">
              <el-col :span="24" :sm="12">
                <el-form-item prop="username" label="Username">
                  <el-input
                    :readonly="!editMode"
                    :disabled="editMode && !isAdmin"
                    v-model="dataUsernameComputed"
                  ></el-input>
                </el-form-item>
              </el-col>
              <el-col :span="24" :sm="12">
                <el-form-item prop="nama" label="Nama Lengkap">
                  <el-input
                    :readonly="!editMode"
                    v-model="formData.nama"
                  ></el-input>
                </el-form-item>
              </el-col>
              <el-col :span="24" :sm="12">
                <el-form-item prop="nip" label="NIP">
                  <el-input
                    :readonly="!editMode"
                    :disabled="editMode && !isAdmin"
                    v-model="dataNipComputed"
                  ></el-input>
                </el-form-item>
              </el-col>
              <el-col :span="24" :sm="12">
                <el-form-item prop="no_telepon" label="No. Telepon">
                  <el-input
                    :readonly="!editMode"
                    v-model="formData.no_telepon"
                  ></el-input>
                </el-form-item>
              </el-col>
              <el-col :span="24" :sm="12">
                <el-form-item
                  prop="id_jabatan"
                  v-if="editMode && isAdmin"
                  label="Jabatan"
                >
                  <el-select
                    v-model="formData.id_jabatan"
                    placeholder="Pilih Sifat Surat"
                    class="width-100"
                  >
                    <el-option
                      v-for="jabatan in bagianInstansi"
                      :key="jabatan.id"
                      :label="jabatan.nama"
                      :value="jabatan.id"
                    >
                    </el-option>
                  </el-select>
                </el-form-item>
                <el-form-item v-else label="Jabatan">
                  <el-input
                    :readonly="!editMode"
                    :disabled="editMode"
                    v-model="optionalData.jabatan"
                  ></el-input>
                </el-form-item>
                <!-- <el-form-item v-if="!editMode" label="Jabatan">
                  <el-input
                    :readonly="!editMode"
                    v-model="optionalData.jabatan"
                  ></el-input>
                </el-form-item>
                -->
              </el-col>
              <el-col>
                <el-button
                  v-if="editMode"
                  type="primary"
                  :loading="processingForm"
                  @click="handleSubmitForm"
                >
                  Submit
                </el-button>
              </el-col>
            </el-row>
          </el-form>
        </el-card>
      </el-col>
    </el-row>
    <el-dialog
      title="Peringatan"
      v-model="dialogWarnVisible"
      width="30%"
      center
    >
      <span>Apakah anda yakin ingin menghapus surat ini?</span>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="handleDialogWarnCancel">Tidak</el-button>
          <el-button type="primary" @click="handleDialogWarnConfirm"
            >Confirm</el-button
          >
        </span>
      </template>
    </el-dialog>
  </layout>
</template>

<script>
import { reactive, ref } from "@vue/reactivity";
import { computed, inject, onMounted } from "@vue/runtime-core";
import { defaultProps, initializationView } from "@shared/InertiaConfig.js";
import Layout from "@shared/Layout.vue";
import rules from "@rules/StoreInbox";
import {
  defaultFormData,
  defaultOptionalData,
} from "@pages/User/ShowFormData.js";
import _ from "lodash";
import { ElNotification } from "element-plus";
import { Inertia } from "@inertiajs/inertia";
export default {
  components: { Layout },
  props: {
    ...defaultProps,
    detailData: {
      type: Object,
      default: () => {},
    },
  },
  setup(props) {
    initializationView(props);
    const sifatSurat = reactive(inject("sifatSurat"));
    const editMode = ref(false);
    const bagianInstansi = inject("bagianInstansi");
    const csrf = inject("csrf");
    const dialogWarnVisible = ref(false);
    const form = ref(null);
    const processingForm = ref(false);
    const canEdit = ref(
      props._user.ijin.admin || props._user.id == props.detailData.id
    );
    const isAdmin = ref(props._user.ijin.admin);
    const formData = reactive(
      props._user.ijin.admin ? defaultFormData.admin : defaultFormData.user
    );
    const optionalData = reactive(
      props._user.ijin.admin
        ? defaultOptionalData.admin
        : defaultOptionalData.user
    );
    const dataUsernameComputed = computed({
      get: () => {
        console.log(formData, optionalData, props._user.ijin.admin);
        return props._user.ijin.admin
          ? formData.username
          : optionalData.username;
      },
      set: (value) => {
        if (props._user.ijin.admin) formData.username = value;
        else optionalData.username = value;
      },
    });

    const dataNipComputed = computed({
      get: () => {
        return props._user.ijin.admin ? formData.nip : optionalData.nip;
      },
      set: (value) => {
        if (props._user.ijin.admin) formData.nip = value;
        else optionalData.nip = value;
      },
    });

    onMounted(() => {
      initData();
    });

    function initData() {
      if (!_.isEmpty(props.detailData)) {
        let data = props.detailData;
        console.log("data from server", data);
        formData.id = data.id;
        formData.nama = data.nama;
        formData.no_telepon = data.no_telepon;
        optionalData.jabatan = data.jabatan;
        if (props._user.ijin.admin) {
          formData.nip = data.nip;
          formData.id_jabatan = data.id_jabatan;
          formData.username = data.username;
        } else {
          optionalData.username = data.username;
          optionalData.nip = data.nip;
        }

        // formData.dibuat_tanggal = data.dibuat_tanggal;
      }
    }

    function handleToggleEdit() {
      editMode.value = !editMode.value;
      if (!editMode.value) {
        initData(false);
      }
    }

    async function handleSubmitForm() {
      let is_valid = false;
      console.log(form);
      await form.value.validate((valid) => {
        if (valid) {
          console.log("Valid", valid);
          is_valid = true;
        } else {
          console.log("Invalid", valid);
          return false;
        }
      });
      console.log(is_valid);
      if (is_valid) {
        Inertia.put(
          route("setting.users.update", { user: formData.id ,username:data.username }),
          formData,
          {
            preserveState: true,
            onBefore: (visit) => {
              console.log("before visit", visit);
              processingForm.value = true;
            },
            onSuccess: (page) => {
              console.log("success visit", page);
            },
            onError: (errors) => {
              console.log("error visit", errors);
            },
            onFinish: (visit) => {
              console.log("finish visit", visit);
              processingForm.value = false;
            },
          }
        );
      }
    }

    function handleDialogWarnCancel() {
      dialogWarnVisible.value = false;
    }

    function handleDialogWarnConfirm() {
      dialogWarnVisible.value = false;
      Inertia.delete(route("setting.users.destroy", { user: formData.id,username:data.username }));
    }

    function handleDialogWarnOpen() {
      dialogWarnVisible.value = true;
    }

    function dateNowAndBefore(time) {
      return time.getTime() > Date.now();
    }

    return {
      dateNowAndBefore,
      handleToggleEdit,
      handleDialogWarnCancel,
      handleDialogWarnConfirm,
      handleDialogWarnOpen,
      handleSubmitForm,
      bagianInstansi,
      csrf,
      rules,
      sifatSurat,
      editMode,
      form,
      formData,
      optionalData,
      dataUsernameComputed,
      dataNipComputed,
      processingForm,
      dialogWarnVisible,
      isAdmin,
      canEdit,
    };
  },
};
</script>

<style scoped>
.box-card {
  margin: 30px 0;
}
</style>
