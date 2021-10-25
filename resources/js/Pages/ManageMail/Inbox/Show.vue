<template>
  <layout>
    <el-row :gutter="40">
      <el-col :span="24" :sm="12">
        <el-card class="box-card">
          <el-row>
            <el-col :span="24" :md="12" :lg="14">
              <h4>Data Surat</h4>
            </el-col>
            <el-col
              :span="24"
              :md="{ span: 12, push: 2 }"
              :lg="{ span: 10, push: 2 }"
              style="margin-bottom: 20px"
            >
              <el-button-group>
                <template v-if="canEdit()">
                  <el-tooltip
                    content="Ubah Surat"
                    placement="top"
                    effect="light"
                  >
                    <el-button
                      :type="editMode ? 'primary' : null"
                      icon="el-icon-edit"
                      @click="handleToggleEdit"
                    ></el-button>
                  </el-tooltip>
                </template>
                <!-- <el-tooltip
                  content="Bagikan Surat"
                  placement="top"
                  effect="light"
                >
                  <el-button icon="el-icon-share"></el-button>
                </el-tooltip> -->
                <template v-if="canDelete()">
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
                </template>
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
            <el-form-item prop="perihal" label="Perihal">
              <el-input
                :readonly="!editMode"
                v-model="formData.perihal"
              ></el-input>
            </el-form-item>
            <!-- </el-col> -->
            <el-row :gutter="20">
              <el-col :span="24" :sm="12">
                <el-form-item prop="asal_surat" label="Asal Surat">
                  <el-input
                    :readonly="!editMode"
                    v-model="formData.asal_surat"
                  ></el-input>
                </el-form-item>
              </el-col>
              <el-col :span="24" :sm="12">
                <el-form-item prop="tanggal_surat" label="Tanggal Surat">
                  <el-date-picker
                    :readonly="!editMode"
                    class="width-100"
                    type="date"
                    placeholder="Pilih Tanggal Surat"
                    v-model="tanggal_surat_computed"
                    :disabled-date="dateNowAndBefore"
                  ></el-date-picker>
                </el-form-item>
              </el-col>
              <el-col :span="24" :sm="12">
                <el-form-item v-if="!editMode" label="Sifat Surat">
                  <el-input
                    :readonly="!editMode"
                    v-model="optionalData.sifat"
                  ></el-input>
                </el-form-item>
                <el-form-item prop="id_sifat" v-else label="Sifat Surat">
                  <el-select
                    v-model="formData.id_sifat"
                    placeholder="Pilih Sifat Surat"
                    class="width-100"
                  >
                    <el-option
                      v-for="sifat in sifatSurat"
                      :key="sifat.id"
                      :label="sifat.nama"
                      :value="sifat.id"
                    >
                    </el-option>
                  </el-select>
                </el-form-item>
              </el-col>
              <el-col :span="24" :sm="12"> </el-col>
              <el-col :span="24" :sm="12">
                <el-form-item prop="no_surat" label="No. Surat">
                  <el-input
                    :readonly="!editMode"
                    v-model="formData.no_surat"
                  ></el-input>
                </el-form-item>
              </el-col>
              <el-col :span="24" :sm="12">
                <el-form-item label="No. Agenda" prop="no_agenda">
                  <el-input
                    :readonly="!editMode"
                    placeholder="Isi nomor agenda"
                    v-model="formData.no_agenda"
                  ></el-input>
                </el-form-item>
              </el-col>
              <el-col>
                <el-form-item prop="isi_ringkas" label="Isi Ringkas">
                  <el-input
                    :readonly="!editMode"
                    type="textarea"
                    :rows="4"
                    placeholder="Masukkan isi ringkas surat"
                    v-model="formData.isi_ringkas"
                  />
                </el-form-item>
              </el-col>
              <el-col :span="24" :sm="12"
                ><el-form-item label="Dimasukkan oleh">
                  <el-input
                    :readonly="!editMode"
                    :disabled="editMode"
                    v-model="optionalData.pembuat"
                  ></el-input> </el-form-item
              ></el-col>
              <el-col :span="24" :sm="12"
                ><el-form-item label="Dimasukkan tanggal">
                  <el-date-picker
                    :readonly="!editMode"
                    :disabled="editMode"
                    class="width-100"
                    type="date"
                    placeholder="Pilih Tanggal Surat"
                    v-model="optionalData.dibuat_tanggal"
                  ></el-date-picker> </el-form-item
              ></el-col>
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
      <el-col :span="24" :sm="12">
        <el-card class="box-card" v-if="!editMode">
          <h4>Dokumen Surat</h4>
          <el-empty
            v-if="
              optionalData.file_surat === false ||
              optionalData.file_surat.length == 0
            "
            description="Tidak ada dokumen pada surat ini."
          ></el-empty>
          <template v-else>
            <el-card
              v-for="file in optionalData.file_surat"
              :key="file.id"
              class="box-card"
            >
              <template #header>
                <div class="card-header">
                  <span>
                    {{ file.nama }}
                  </span>
                </div>
              </template>
              <div class="text item">
                Tipe : {{ classificationFileType(file.tipe) }} <br />
                Ukuran : {{ humanFileSize(file.ukuran) }}
              </div>
              <a :href="file.url" target="_blank">
                <el-button class="button" type="text">Download</el-button></a
              >
              <a
                v-if="classificationFileType(file.tipe) !== 'Tidak Diketahui'"
                :href="file.url + '?open=1'"
                target="_blank"
              >
                <el-button class="button" type="text">Buka</el-button></a
              >
            </el-card>
          </template>
        </el-card>
        <el-card v-else class="box-card">
          <h4>Dokumen Surat</h4>
          <el-upload
            class="upload-demo"
            :action="
              routes('manage.inbox.upload.file', { surat_masuk: formData.id })
            "
            :headers="{ 'X-XSRF-TOKEN': getXSRF() }"
            :on-error="handleErrorUpload"
            :on-remove="handleRemove"
            :on-success="handleSuccessUpload"
            :limit="4"
            :before-upload="handleValidation"
            :on-exceed="handleExceed"
            :file-list="fileSurat"
          >
            <el-button size="small" type="primary">Click to upload</el-button>
            <template #tip>
              <div class="el-upload__tip">
                Hanya file gambar (jpg, png, gif), dokumen (doc, docx, pdf,
                odt), dan file harus dibawah 4 MB.
              </div>
            </template>
          </el-upload>
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
import { computed, inject, onMounted, onUpdated } from "@vue/runtime-core";
import {
  defaultProps,
  initializationView,
  getPermission,
} from "@shared/InertiaConfig.js";
import Layout from "@shared/Layout.vue";
import {
  humanFileSize,
  classificationFileType,
  getXSRF,
} from "@shared/HelperFunction.js";
import rules from "@rules/StoreInbox";
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
    console.log(sifatSurat);
    const editMode = ref(false);

    // const csrf = inject("csrf");
    const limitUploadSize = "4096";
    const dialogWarnVisible = ref(false);
    const form = ref(null);
    const fileSurat = ref([]);
    const processingForm = ref(false);
    const permission = getPermission(props);
    const canDelete = () => {
      if (permission.d_surat) {
        return true;
      }
      if (
        permission.d_miliksurat &&
        props.detailData.id_pembuat == props._user.id
      ) {
        return true;
      }

      return false;
    };
    const canEdit = () => {
    //   console.log(
    //     "canEdit",
    //     permission.w_suratmasuk,
    //     props.detailData.id_pembuat == props._user.id,
    //     permission.w_suratmasuk && permission.w_all_surat
    //   );
      if (permission.w_suratmasuk && permission.w_all_surat) {
        return true;
      }
      if (
        permission.w_suratmasuk &&
        props.detailData.id_pembuat == props._user.id
      )
        return true;
      return false;
    };

    const tanggal_surat_computed = computed({
      get: () => formData.tanggal_surat,
      set: (value) => {
        let difference = value.getTimezoneOffset();
        let minute = value.getMinutes();
        value.setMinutes(minute - difference);
        formData.tanggal_surat = value;
      },
    });

    const formData = reactive({
      id: "",
      perihal: "",
      tanggal_surat: "",
      no_surat: "",
      asal_surat: "",
      isi_ringkas: "",
      id_sifat: "",
      no_agenda: "",
    });

    const optionalData = reactive({
      pembuat: "",
      sifat: "",
      dibuat_tanggal: "",
      file_surat: [],
    });

    onMounted(() => {
      initData();
    });

    onUpdated(() => {
      initData();
    });

    function initData(withFile = true) {
      if (!_.isEmpty(props.detailData)) {
        let data = props.detailData;
        console.log("data from server", data);
        formData.id = data.id;
        formData.perihal = data.perihal;
        formData.tanggal_surat = data.tanggal_surat;
        formData.no_surat = data.no_surat;
        formData.no_agenda = data.no_agenda;
        formData.asal_surat = data.asal_surat;
        formData.isi_ringkas = data.isi_ringkas;
        formData.id_sifat = data.id_sifat;
        if (withFile) {
          optionalData.file_surat = _.isEmpty(data.file_surat)
            ? []
            : data.file_surat;
          fileSurat.value = _.isEmpty(data.file_surat_form)
            ? []
            : data.file_surat_form;
        }
        optionalData.pembuat = data.pembuat;
        optionalData.sifat = data.sifat;
        optionalData.dibuat_tanggal = data.dibuat_tanggal;
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
          route("manage.inbox.update", { surat_masuk: formData.id }),
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

    function handleValidation(file) {
      const fsize = file.size;
      const size = Math.round(fsize / 1024);
      if (size > limitUploadSize) {
        ElNotification({
          type: "error",
          title: "File Kebesaran",
          message: "File yang anda ingin upload terlalu besar!",
        });
        return false;
      }
    }

    function handleErrorUpload(err, file, fileList) {
      ElNotification({
        type: "error",
        title: "Upload File",
        message:
          "Terjadi kesalahan pada upload file. Silahkan refresh page atau jika masih terjadi kegagalan kontak admin web.",
      });
    }

    function handleSuccessUpload(response, file, fileList) {
      console.log("Upload complete  ", response, file, fileList);
      fileSurat.value.push(response.fileSuratForm);
      optionalData.file_surat.push(response.fileSurat);
    }

    function handleRemove(file, fileList) {
      console.log("before remove", fileSurat.value, file);
      if (file.id === undefined) {
        return false;
      }
      axios
        .post(route("manage.inbox.delete.file", { surat_masuk: formData.id }), {
          id: file.id,
        })
        .then((response) => {
          console.log("success remove", response);
          ElNotification({
            type: "success",
            title: "Menghapus File",
            message: "Berhasil menghapus file!",
          });
          fileSurat.value = fileSurat.value.filter((element) => {
            return element.id != file.id;
          });
          optionalData.file_surat = optionalData.file_surat.filter(
            (element) => {
              return element.id != file.id;
            }
          );
        })
        .catch((error) => {
          console.log(error);
          ElNotification({
            type: "error",
            title: "Menghapus File",
            message: "Gagal menghapus file!",
          });
        });
      //   fileSurat.value.push({ name: response });
    }
    function handleExceed(files, fileList) {
      ElNotification({
        type: "error",
        title: "Limit Upload",
        message: "Anda telah mencapai limit file yang dapat di upload.",
      });
    }

    function handleDialogWarnCancel() {
      dialogWarnVisible.value = false;
    }

    function handleDialogWarnConfirm() {
      dialogWarnVisible.value = false;
      Inertia.delete(
        route("manage.inbox.destroy", { surat_masuk: formData.id })
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
      handleExceed,
      handleRemove,
      handleSuccessUpload,
      handleErrorUpload,
      handleValidation,
      humanFileSize,
      classificationFileType,
      //   csrf,
      rules,
      sifatSurat,
      editMode,
      form,
      fileSurat,
      formData,
      tanggal_surat_computed,
      optionalData,
      processingForm,
      dialogWarnVisible,
      permission,
      canDelete,
      canEdit,
      getXSRF,
    };
  },
};
</script>

<style scoped>
.box-card {
  margin: 30px 0;
}
</style>
