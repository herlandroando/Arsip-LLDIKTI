<template>
  <layout>
    <el-row :gutter="40">
      <el-col :span="24" :sm="canEdit ? 12 : 24">
        <el-card class="box-card">
          <el-row>
            <el-col :span="24" :md="12" :lg="14">
              <h4>Data Pengguna</h4>
            </el-col>
            <el-col
              :span="24"
              :md="{ span: 12, push: canEdit ? 4 : 6 }"
              :lg="{ span: 10, push: canEdit ? 4 : 6 }"
              style="margin-bottom: 20px"
            >
              <el-button-group>
                <el-button
                  v-if="canEdit"
                  :type="editMode ? 'primary' : null"
                  icon="el-icon-edit"
                  title="Ubah"
                  @click="handleToggleEdit"
                ></el-button>
                <el-button
                  v-if="canDelete"
                  @click="handleDialogWarnOpen"
                  type="danger"
                  icon="el-icon-delete"
                  title="Hapus"
                ></el-button>
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
                  v-if="
                    editMode &&
                    isAdmin &&
                    (!detailData.ijin.admin || permission.super_admin)
                  "
                  label="Jabatan"
                >
                  <el-select
                    v-model="formData.id_jabatan"
                    placeholder="Pilih Jabatan"
                    class="width-100"
                  >
                    <el-option
                      v-for="jabatan in listJabatan"
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
                  Ubah
                </el-button>
              </el-col>
            </el-row>
          </el-form>
        </el-card>
      </el-col>
      <el-col v-if="canEdit" :span="24" :sm="12">
        <el-card class="box-card">
          <el-row>
            <el-col :span="24" :md="12" :lg="14">
              <h4>Ubah Password</h4>
            </el-col>
          </el-row>
          <el-form
            :rules="rulesPassword"
            label-position="top"
            label-width="100px"
            :model="formDataPassword"
            ref="formPassword"
          >
            <el-row :gutter="20">
              <el-col :span="24" :sm="12">
                <el-form-item prop="password" label="Password">
                  <el-input
                    show-password
                    v-model="formDataPassword.password"
                  ></el-input>
                </el-form-item> </el-col
              ><el-col :span="24" :sm="12">
                <el-form-item prop="cpassword" label="Konfirmasi Password">
                  <el-input
                    show-password
                    v-model="formDataPassword.cpassword"
                  ></el-input>
                </el-form-item>
              </el-col>
              <el-col :span="24">
                <el-button
                  type="primary"
                  :loading="processingForm"
                  @click="handleSubmitFormPassword"
                >
                  Ubah
                </el-button>
              </el-col>
            </el-row>
          </el-form>
        </el-card>
      </el-col>
      <!-- Permission -->
      <el-col :span="24" v-if="isProfile">
        <el-form label-position="top" label-width="100px">
          <el-card class="box-card">
            <el-row>
              <el-col :span="24" :md="12" :lg="14">
                <h4>Data Ijin</h4>
              </el-col>
              <el-col :span="24" prop="nama">
                <el-form-item label="Nama Ijin">
                  <el-input v-model="detailData.ijin.nama" disabled></el-input>
                </el-form-item>
              </el-col>
              <el-col :span="24">
                <el-form-item prop="r_surat">
                  <el-checkbox
                    v-model="detailData.ijin.r_surat"
                    label="Melihat Surat dan Disposisi"
                    disabled
                  ></el-checkbox>
                </el-form-item>
                <!-- <small></small> -->
              </el-col>
              <el-col v-if="detailData.ijin.r_surat" :span="24">
                <el-form-item prop="w_suratmasuk">
                  <el-checkbox
                    v-model="detailData.ijin.w_suratmasuk"
                    label="Membuat/Mengubah Surat Masuk"
                    disabled
                  ></el-checkbox>
                </el-form-item>
              </el-col>
              <el-col v-if="detailData.ijin.r_surat" :span="24">
                <el-form-item prop="w_suratkeluar">
                  <el-checkbox
                    v-model="detailData.ijin.w_suratkeluar"
                    label="Membuat/Mengubah Surat Keluar"
                    disabled
                  ></el-checkbox>
                </el-form-item>
              </el-col>
              <el-col v-if="detailData.ijin.r_surat" :span="24">
                <el-form-item prop="w_all_surat">
                  <el-checkbox
                    v-model="detailData.ijin.w_all_surat"
                    label="Membuat/Mengubah Semua Surat"
                    disabled
                  ></el-checkbox>
                </el-form-item>
              </el-col>
              <el-col v-if="detailData.ijin.r_surat" :span="24">
                <el-form-item prop="d_surat">
                  <el-checkbox
                    v-model="detailData.ijin.d_surat"
                    label="Hapus Surat"
                    disabled
                  ></el-checkbox>
                </el-form-item>
              </el-col>
              <el-col v-if="detailData.ijin.r_surat" :span="24">
                <el-form-item prop="d_miliksurat">
                  <el-checkbox
                    v-model="detailData.ijin.d_miliksurat"
                    label="Hapus Surat Milik Sendiri"
                    disabled
                  ></el-checkbox>
                </el-form-item>
              </el-col>
              <el-col v-if="detailData.ijin.r_surat" :span="24">
                <el-form-item prop="dp_surat">
                  <el-checkbox
                    v-model="detailData.ijin.dp_surat"
                    label="Hapus Surat secara Permanen"
                    disabled
                  ></el-checkbox>
                </el-form-item>
              </el-col>
              <el-col v-if="detailData.ijin.r_surat" :span="24">
                <el-form-item prop="r_all_disposisi">
                  <el-checkbox
                    v-model="detailData.ijin.r_all_disposisi"
                    label="Melihat Disposisi dari Semua Jabatan"
                    disabled
                  ></el-checkbox>
                </el-form-item>
              </el-col>
              <el-col v-if="detailData.ijin.r_surat" :span="24">
                <el-form-item prop="w_disposisi">
                  <el-checkbox
                    v-model="detailData.ijin.w_disposisi"
                    label="Mengelola Disposisi"
                    disabled
                  ></el-checkbox>
                </el-form-item>
              </el-col>
              <el-col :span="24">
                <el-form-item prop="r_laporan">
                  <el-checkbox
                    v-model="detailData.ijin.r_laporan"
                    label="Melihat Laporan"
                    disabled
                  ></el-checkbox>
                </el-form-item>
              </el-col>
              <el-col class="last-col" :span="24">
                <el-form-item prop="admin">
                  <el-checkbox
                    v-model="detailData.ijin.admin"
                    label="Administrator"
                    disabled
                  ></el-checkbox>
                </el-form-item>
              </el-col>
              <el-col class="last-col" :span="24">
                <el-form-item prop="admin">
                  <el-checkbox
                    v-model="detailData.ijin.super_admin"
                    label="Super Administrator"
                    disabled
                  ></el-checkbox>
                </el-form-item>
              </el-col>
            </el-row>
          </el-card>
        </el-form>
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
import {
  defaultProps,
  initializationView,
  getPermission,
} from "@shared/InertiaConfig.js";
import Layout from "@shared/Layout.vue";
import * as rulesForm from "@rules/EditUser.js";
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
    listJabatan: {
      type: Array,
      default: () => [],
    },
    isProfile: {
      type: Boolean,
      default: false,
    },
  },

  setup(props) {
    initializationView(props);
    const sifatSurat = reactive(inject("sifatSurat"));
    const editMode = ref(false);
    const bagianInstansi = inject("bagianInstansi");
    // const csrf = inject("csrf");
    const dialogWarnVisible = ref(false);
    const form = ref(null);
    const formPassword = ref(null);
    const processingForm = ref(false);
    const permission = getPermission(props);
    const canEdit = ref(
      (permission.admin || props._user.id == props.detailData.id) &&
        !props.detailData.ijin.super_admin
    );
    const canDelete = ref(
      // Admin tidak bisa hapus admin
      // Super admin aja yang bisa hapus semuanya
      // Admin hanya bisa hapus user biasa
      // Tidak bisa delete diri sendiri
      permission.admin &&
        props._user.id != props.detailData.id &&
        (!props.detailData.ijin.admin || permission.super_admin)
    );
    const rulesPassword = rulesForm.rulesPassword;
    const isAdmin = ref(permission.admin);
    const formData = reactive(
      permission.admin ? defaultFormData.admin : defaultFormData.user
    );
    const formDataPassword = reactive({
      password: "",
      cpassword: "",
    });
    const optionalData = reactive(
      permission.admin ? defaultOptionalData.admin : defaultOptionalData.user
    );
    const dataUsernameComputed = computed({
      get: () => {
        console.log(formData, optionalData, props._user.ijin.admin);
        return permission.admin ? formData.username : optionalData.username;
      },
      set: (value) => {
        if (permission.admin) formData.username = value;
        else optionalData.username = value;
      },
    });

    const dataNipComputed = computed({
      get: () => {
        return permission.admin ? formData.nip : optionalData.nip;
      },
      set: (value) => {
        if (permission.admin) formData.nip = value;
        else optionalData.nip = value;
      },
    });

    const rules = computed(() => {
      if (permission.admin) {
        return rulesForm.isUser;
      } else {
        return rulesForm.isAdmin;
      }
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
        if (permission.admin) {
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

      if (is_valid) {
        Inertia.put(
          route("setting.users.update", {
            user: props.detailData.id,
            username: props.detailData.username,
          }),
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
    async function handleSubmitFormPassword() {
      let is_valid = false;
      await formPassword.value.validate((valid) => {
        if (valid) {
          console.log("Valid", valid);
          is_valid = true;
        } else {
          console.log("Invalid", valid);
          return false;
        }
      });
      if (formDataPassword.password != formDataPassword.cpassword) {
        ElNotification.error({
          title: "Gagal",
          message: "Konfirmasi password tidak sama dengan password!",
        });
        return;
      }
      if (is_valid) {
        Inertia.put(
          route("setting.users.update.password", {
            user: props.detailData.id,
            username: props.detailData.username,
          }),
          formDataPassword,
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
      Inertia.delete(
        route("setting.users.destroy", {
          user: formData.id,
          username: data.username,
        })
      );
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
      handleSubmitFormPassword,
      bagianInstansi,
      //   csrf,
      rules,
      rulesPassword,
      sifatSurat,
      editMode,
      form,
      formPassword,
      formData,
      formDataPassword,
      optionalData,
      dataUsernameComputed,
      dataNipComputed,
      processingForm,
      dialogWarnVisible,
      isAdmin,
      canEdit,
      permission,
      canDelete,
    };
  },
};
</script>

<style scoped>
.box-card {
  margin: 30px 0;
}
</style>
