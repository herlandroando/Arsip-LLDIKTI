<template>
  <layout>
    <el-row :gutter="40">
      <el-col :span="24" :sm="12">
        <el-card
          class="box-card activity-container"
          :class="{ 'has-content': listActivity.length > 0 }"
        >
          <div class="activity-title-bar bg-white">
            <h4 class="title">Aktifitas Disposisi</h4>
          </div>
          <div
            class="activity-body"
            :class="{ 'has-content': listActivity.length > 0 }"
          >
            <template v-if="listActivity.length > 0">
              <template v-for="list in listActivity" :key="list.id">
                <el-tooltip
                  :content="list.tanggal_buat"
                  placement="bottom"
                  effect="light"
                >
                  <el-card
                    shadow="hover"
                    v-if="!list.isStatus"
                    class="message bg-light"
                    :class="{
                      'message-card-other': !list.isMe,
                      'message-card-self': list.isMe,
                    }"
                  >
                    <h5 class="title">{{ list.nama }} ({{ list.jabatan }})</h5>

                    <p>{{ list.keterangan }}</p>
                  </el-card>
                  <el-divider v-else class="text-center"
                    ><small
                      >Status telah diubah oleh <b>{{ list.nama }}</b> menjadi
                      <b>{{ list.keterangan }}</b></small
                    ></el-divider
                  >
                </el-tooltip>
              </template>
            </template>
            <template v-else>
              <el-col class="no-result">
                <el-result
                  title="Tidak Ada Aktifitas Terbaru"
                  subTitle="Silahkan memulai mengirim sebuah keterangan/pesan kepada pengirim maupun penerima."
                >
                  <template #icon>
                    <failed />
                  </template>
                </el-result>
              </el-col>
            </template>
          </div>
          <div class="activity-footer-bar">
            <el-form
              :rules="rulesActivity"
              label-position="top"
              label-width="100px"
              :model="activityFormData"
              ref="activityForm"
            >
              <el-row>
                <el-col>
                  <el-form-item prop="keterangan" label="Pesan/Keterangan">
                    <el-input
                      type="textarea"
                      :rows="4"
                      placeholder="Masukkan pesan/keterangan yang akan dikirim."
                      v-model="activityFormData.keterangan"
                    />
                  </el-form-item>
                </el-col>
                <el-col>
                  <el-button
                    type="primary"
                    :loading="processingForm"
                    @click="handleSubmitMessage"
                  >
                    Kirim
                  </el-button>
                </el-col>
              </el-row>
            </el-form>
          </div>
        </el-card>
      </el-col>
      <el-col :span="24" :sm="12">
        <el-card class="box-card">
          <el-row>
            <el-col :span="24" :md="12" :lg="14">
              <h4>Data Disposisi</h4>
            </el-col>
            <el-col
              :span="24"
              :md="{ span: 12, push: 2 }"
              :lg="{ span: 10, push: 2 }"
              style="margin-bottom: 20px"
            >
              <el-button-group>
                <el-tooltip content="Ubah Surat" placement="top" effect="light">
                  <el-button
                    :type="editMode ? 'primary' : null"
                    icon="el-icon-edit"
                    @click="handleToggleEdit"
                  ></el-button>
                </el-tooltip>
                <el-tooltip
                  content="Bagikan Surat"
                  placement="top"
                  effect="light"
                >
                  <el-button icon="el-icon-share"></el-button>
                </el-tooltip>
                <el-tooltip
                  content="Hapus Surat"
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
            :rules="ruleComputed"
            label-position="top"
            label-width="100px"
            :model="formData"
            ref="form"
          >
            <el-row :gutter="20">
              <el-col :span="24">
                <el-checkbox
                  v-model="formData.is_suratmasuk"
                  :disabled="!editMode"
                  >Disposisi Surat Masuk.</el-checkbox
                >
              </el-col>
              <el-col v-if="formData.is_suratmasuk" :span="24">
                <el-form-item prop="no_suratmasuk" label="No. Surat Masuk">
                  <el-input
                    v-if="!editMode"
                    :readonly="!editMode"
                    v-model="formData.no_suratmasuk"
                  >
                  </el-input>

                  <el-select
                    v-else
                    v-model="formData.no_suratmasuk"
                    filterable
                    remote
                    reserve-keyword
                    placeholder="Masukkan No. Surat Masuk."
                    :remote-method="handleListSuratMasuk"
                    :loading="processingInputRequest"
                    style="width: 100%"
                  >
                    <el-option
                      v-for="item in listSuratMasuk"
                      :key="item.value"
                      :label="item.no_surat"
                      :value="item.no_surat"
                    >
                    </el-option>
                  </el-select>
                </el-form-item>
              </el-col>
              <el-col v-if="formData.is_suratmasuk && !editMode" :span="24">
                <Link
                  :href="
                    routes('manage.inbox.show', {
                      surat_masuk: optionalData.id_suratmasuk,
                    })
                  "
                >
                  <el-button type="primary">Menuju Referensi Surat</el-button>
                </Link>
              </el-col>
              <el-col :span="24">
                <el-form-item prop="no_disposisi" label="No. Disposisi">
                  <el-input
                    :readonly="!editMode"
                    v-model="formData.no_disposisi"
                  ></el-input>
                </el-form-item>
              </el-col>
              <el-col :span="24" :sm="12">
                <el-form-item prop="tujuan" label="Tujuan Disposisi">
                  <el-input
                    readonly
                    :disabled="editMode"
                    v-model="formData.no_disposisi"
                  ></el-input>
                </el-form-item>
              </el-col>
              <el-col :span="24" :sm="12">
                <el-form-item prop="tenggat_waktu" label="Tenggat Waktu">
                  <el-date-picker
                    :readonly="!editMode"
                    class="width-100"
                    type="datetime"
                    format="DD-MM-YYYY HH:mm"
                    placeholder="Pilih Tenggat Waktu Disposisi"
                    v-model="formData.tenggat_waktu"
                    :disabled-date="dateNowAndAfter"
                  ></el-date-picker>
                </el-form-item>
              </el-col>
              <el-col>
                <el-form-item prop="isi" label="Isi Disposisi">
                  <el-input
                    :readonly="!editMode"
                    type="textarea"
                    :rows="4"
                    placeholder="Masukkan isi disposisi"
                    v-model="formData.isi"
                  />
                </el-form-item>
              </el-col>
              <el-col :span="24" :sm="12">
                <el-form-item label="Status Disposisi">
                  <el-input
                    readonly
                    :disabled="editMode"
                    v-model="optionalData.status"
                  ></el-input>
                </el-form-item>
              </el-col>
              <el-col :span="24" :sm="12"
                ><el-form-item label="Dibuat oleh">
                  <el-input
                    readonly
                    :disabled="editMode"
                    v-model="optionalData.pengirim"
                  ></el-input> </el-form-item
              ></el-col>
              <el-col :span="24" :sm="12"
                ><el-form-item label="Dibuat Tanggal">
                  <el-date-picker
                    readonly
                    :disabled="editMode"
                    class="width-100"
                    type="date"
                    placeholder="Pilih Tanggal Surat"
                    v-model="optionalData.tanggal_buat"
                  ></el-date-picker> </el-form-item
              ></el-col>
              <el-col :span="24" :sm="12"
                ><el-form-item label="Perubahan Terakhir">
                  <el-date-picker
                    readonly
                    :disabled="editMode"
                    class="width-100"
                    type="date"
                    placeholder="Pilih Tanggal Surat"
                    v-model="optionalData.tanggal_ubah"
                  ></el-date-picker> </el-form-item
              ></el-col>
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
    </el-row>
    <el-dialog
      title="Peringatan"
      v-model="dialogWarnVisible"
      width="30%"
      center
    >
      <span>Apakah anda yakin ingin menghapus disposisi ini?</span>
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
import { Link } from "@inertiajs/inertia-vue3";

import { reactive, ref } from "@vue/reactivity";
import { computed, inject, onMounted } from "@vue/runtime-core";
import { defaultProps, initializationView } from "@shared/InertiaConfig.js";
import Layout from "@shared/Layout.vue";
import { dateNowAndAfter } from "@shared/HelperFunction.js";
import rulesActivity from "@rules/ActivityDisposisi";
import rulesWithInbox from "@rules/EditDisposisiWithInbox";
import rulesWithoutInbox from "@rules/EditDisposisiWithoutInbox";
import _ from "lodash";
import { ElNotification } from "element-plus";
import { Failed, Loading } from "@element-plus/icons";
import { Inertia } from "@inertiajs/inertia";
import axios from "axios";
export default {
  components: { Layout, Failed, Link },
  props: {
    ...defaultProps,
    detailData: {
      type: Object,
      default: () => {},
    },
    activityData: {
      type: Object,
      default: () => {},
    },
  },
  setup(props) {
    initializationView(props);
    // const sifatSurat = reactive(inject("sifatSurat"));
    const editMode = ref(false);
    const ruleComputed = computed(() => {
      if (formData.is_suratmasuk) {
        return rulesWithInbox;
      } else {
        return rulesWithoutInbox;
      }
    });
    const csrf = inject("csrf");
    // const limitUploadSize = "4096";
    const dialogWarnVisible = ref(false);
    const form = ref(null);
    const activityForm = ref(null);
    const listActivity = ref([]);
    const listSuratMasuk = ref([]);
    const processingForm = ref(false);
    const processingInputRequest = ref(false);
    const activityFormData = reactive({
      keterangan: "",
    });

    const formData = reactive({
      id: "",
      is_suratmasuk: false,
      no_suratmasuk: "",
      tenggat_waktu: "",
      no_disposisi: "",
      isi: "",
    });

    const optionalData = reactive({
      status: "",
      pengirim: "",
      tujuan: "",
      tanggal_buat: "",
      tanggal_ubah: "",
      id_suratmasuk: "",
    });

    onMounted(() => {
      initData();
    });

    function initData() {
      if (!_.isEmpty(props.detailData)) {
        let data = props.detailData;
        console.log("data from server", data);
        formData.id = data.id;
        formData.is_suratmasuk = data.is_suratmasuk;
        formData.no_suratmasuk = data.no_suratmasuk;
        formData.no_disposisi = data.no_disposisi;
        formData.tenggat_waktu = data.tenggat_waktu;
        formData.isi = data.isi;
        optionalData.id_suratmasuk = data.id_suratmasuk;
        optionalData.status = data.status;
        optionalData.pengirim = data.pengirim;
        optionalData.tujuan = data.tujuan;
        optionalData.tanggal_buat = data.tanggal_buat;
        optionalData.tanggal_ubah = data.tanggal_ubah;
      }
      if (!_.isEmpty(props.activityData)) {
        listActivity.value = props.activityData;
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
          route("manage.disposisi.update", { disposisi: formData.id }),
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

    async function handleListSuratMasuk(value) {
      processingInputRequest.value = true;
      await axios
        .get(route("manage.disposisi.list.surat"), {
          params: {
            no_surat: value,
          },
        })
        .then((result) => {
          listSuratMasuk.value = result.data;
        });
      processingInputRequest.value = false;
    }

    async function handleSubmitMessage() {
      let is_valid = false;
      await form.value.validate((valid) => {
        if (valid) {
          is_valid = true;
        } else {
          return false;
        }
      });
      if (is_valid) {
        Inertia.post(
          route("manage.disposisi.activity", { disposisi: formData.id }),
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
      Inertia.delete(
        route("manage.disposisi.destroy", { disposisi: formData.id })
      );
    }

    function handleDialogWarnOpen() {
      dialogWarnVisible.value = true;
    }

    return {
      dateNowAndAfter,
      handleToggleEdit,
      handleDialogWarnCancel,
      handleDialogWarnConfirm,
      handleDialogWarnOpen,
      handleSubmitForm,
      handleSubmitMessage,
      handleListSuratMasuk,
      csrf,
      rulesWithInbox,
      rulesWithoutInbox,
      ruleComputed,
      rulesActivity,
      //   sifatSurat,
      editMode,
      form,
      activityForm,
      activityFormData,
      formData,
      listActivity,
      listSuratMasuk,
      //   tenggatWaktuComputed,
      optionalData,
      processingForm,
      processingInputRequest,
      dialogWarnVisible,
    };
  },
};
</script>

<style scoped>
.box-card {
  margin: 30px 0;
}
.activity-container {
  max-height: 700px;
  height: 700px;
  width: 100%;
  position: relative;
  overflow-y: hidden;
}
.activity-container.has-content {
  overflow-y: auto !important;
}
.activity-title-bar {
  position: sticky;
  width: 100%;
  left: 0px;
  top: 0px;
  padding: 15px 0px;
  border-bottom: 1px solid var(--el-border-color-base);
  background-color: white;
  z-index: 100;
}
.activity-title-bar .title {
  margin-left: 30px !important;
}
.activity-footer-bar {
  position: sticky;
  bottom: 0px;
  width: 100%;
  padding: 20px 0px;
  border-top: 1px solid var(--el-border-color-base);
  left: 0px;
  background-color: white;
}
.activity-footer-bar .el-form {
  padding: 0px 20px;
  z-index: 100;
}
.activity-body {
  gap: 30px;
  /* min-height: 200px; */
  /* padding-bottom: 150px; */
  display: flex;
  flex-direction: column;
}
.activity-body.has-content {
  padding-top: 50px;
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
</style>
