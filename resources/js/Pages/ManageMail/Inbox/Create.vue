<template>
  <layout>
    <el-row :gutter="40">
      <el-col :span="24" :sm="12">
        <el-card class="box-card">
          <h4>Data Surat</h4>
          <el-form
            label-position="top"
            label-width="100px"
            :model="formData"
            :rules="rules"
            ref="form"
          >
            <!-- <el-col :span="24"> -->
            <el-form-item label="Perihal" prop="perihal">
              <el-input v-model="formData.perihal" placeholder="Isi perihal surat"></el-input>
            </el-form-item>
            <!-- </el-col> -->
            <el-row :gutter="20">
              <el-col :span="24" :sm="12">
                <el-form-item label="Asal Surat" prop="asal_surat">
                  <el-input v-model="formData.asal_surat"  placeholder="Isi asal surat"></el-input>
                </el-form-item>
              </el-col>
              <el-col :span="24" :sm="12">
                <el-form-item label="Tanggal Surat" prop="tanggal_surat">
                  <el-date-picker
                    class="width-100"
                    type="date"
                    placeholder="Pilih tanggal surat"
                    :disabled-date="dateNowAndBefore"
                    v-model="tanggal_surat_computed"
                  ></el-date-picker>
                </el-form-item>
              </el-col>
              <el-col :span="24" :sm="12">
                <el-form-item label="Sifat Surat" prop="id_sifat">
                  <el-select
                    v-model="formData.id_sifat"
                    placeholder="Pilih sifat surat"
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
              <el-col :span="24" :sm="12">
              </el-col>
              <el-col :span="24" :sm="12">
                <el-form-item label="No. Surat" prop="no_surat">
                  <el-input  placeholder="Isi nomor surat" v-model="formData.no_surat"></el-input>
                </el-form-item>
              </el-col>
              <el-col :span="24" :sm="12">
                <el-form-item label="No. Agenda" prop="no_agenda">
                  <el-input  placeholder="Isi nomor agenda" v-model="formData.no_agenda"></el-input>
                </el-form-item>
              </el-col>
              <el-col>
                <el-form-item label="Isi Ringkas" prop="isi_ringkas">
                  <el-input
                    type="textarea"
                    :rows="4"
                    placeholder="Masukkan isi ringkas surat"
                    v-model="formData.isi_ringkas"
                  />
                </el-form-item>
              </el-col>
              <el-col>
                <el-form-item>
                  <el-button
                    type="primary"
                    :disabled="processingForm"
                    @click="handleSubmitForm"
                    >Submit</el-button
                  >
                  <!-- <el-button @click="resetForm('ruleForm')">Reset</el-button> -->
                </el-form-item>
              </el-col>
            </el-row>
          </el-form>
        </el-card>
      </el-col>
      <el-col :span="24" :sm="12">
        <el-card class="box-card">
          <h4>Dokumen Surat</h4>
          <el-upload
            class="upload-demo"
            :action="routes('manage.inbox.upload.temp')"
            :headers="{ 'X-CSRF-TOKEN': csrf }"
            :on-preview="handlePreview"
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
                Hanya file gambar (jpg, png, gif), dokumen (doc, docx, pdf, odt), dan file harus dibawah 4 MB.
              </div>
            </template>
          </el-upload>
        </el-card>
      </el-col>
    </el-row>
  </layout>
</template>

<script>
import { reactive, ref, toRefs } from "@vue/reactivity";
import { computed, inject, onMounted, watch } from "@vue/runtime-core";
import { defaultProps, initializationView } from "@shared/InertiaConfig.js";
import Layout from "@shared/Layout.vue";
import { ElNotification } from "element-plus";
import {
  humanFileSize,
  classificationFileType,
} from "@shared/HelperFunction.js";
import _ from "lodash";
import axios from "axios";
import rules from "@rules/StoreInbox.js";
import { Inertia } from "@inertiajs/inertia";

export default {
  components: { Layout },
  props: {
    tempFileSurat: {
      type: [Array, Object],
      default: () => [],
    },
    errors: {
      type: Object,
      default: () => {
        return {};
      },
    },
    ...defaultProps,
  },
  setup(props) {
    initializationView(props);
    const sifatSurat = reactive(inject("sifatSurat"));
    const editMode = ref(false);
    const formData = reactive({
      perihal: "",
      tanggal_surat: "",
      no_agenda:"",
      no_surat: "",
      asal_surat: "",
      isi_ringkas: "",
      id_sifat: "",
    });
    const csrf = inject("csrf");
    const limitUploadSize = "4096";

    const form = ref(null);
    const fileSurat = ref([]);
    const processingForm = ref(false);

    const optionalData = reactive({
      pembuat: "",
      sifat: "",
      dibuat_tanggal: "",
      file_surat: {},
    });

    const tanggal_surat_computed = computed({
      get: () => formData.tanggal_surat,
      set: (value) => {
        let difference = value.getTimezoneOffset();
        let minute = value.getMinutes();
        value.setMinutes(minute - difference);
        formData.tanggal_surat = value;
      },
    });

    onMounted(() => {
      if (props.tempFileSurat instanceof Array) {
        console.log("array");
        for (const file of props.tempFileSurat) {
          fileSurat.value.push(file);
        }
      }
      if (props.tempFileSurat instanceof Object) {
        console.log("object", props.tempFileSurat);
        fileSurat.value = [];
        for (const file in props.tempFileSurat) {
          fileSurat.value.push({
            name: props.tempFileSurat[file].name,
            id: props.tempFileSurat[file].id,
          });
        }
      }
      console.log("init", fileSurat.value);
      console.log(rules);
    });

    async function handleSubmitForm() {
      let is_valid = false;
      console.log(form.value);
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
        Inertia.post(route("manage.inbox.store"), formData, {
          preserveState: true,
          onBefore: (visit) => {
            console.log("before visit", visit);
            processingForm.value = true;
          },
          onSuccess: (page) => {
            console.log("success visit", page);
            if (props._toast.type == "success") form.value.resetFields();
          },
          onError: (errors) => {
            console.log("error visit", errors);
          },
          onFinish: (visit) => {
            console.log("finish visit", visit);
            processingForm.value = false;
          },
        });
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

    function handleErrorMessage(key) {
      console.log("ss", formData);
      formData.clearErrors(key);
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
      fileSurat.value.push(response);
    }
    function handleRemove(file, fileList) {
      console.log("before remove", fileSurat.value, file);
      if (file.id === undefined){
          return false;
      }
      axios
        .post(route("manage.inbox.delete.temp"), { id: file.id })
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
    function handlePreview(file) {
      console.log(file);
    }
    function handleExceed(files, fileList) {
      this.$message.warning(
        `The limit is 3, you selected ${
          files.length
        } files this time, add up to ${files.length + fileList.length} totally`
      );
    }

    function dateNowAndBefore(time){
          return time.getTime() > Date.now()
    }
    // function beforeRemove(file, fileList) {
    //   return this.$confirm(`Cancel the transfert of ${file.name} ?`);
    // }

    return {
      humanFileSize,
      classificationFileType,
      sifatSurat,
      editMode,
      handleRemove,
      dateNowAndBefore,
      handlePreview,
      handleExceed,
      handleSuccessUpload,
      handleErrorUpload,
      handleErrorMessage,
      handleValidation,
      handleSubmitForm,
      processingForm,
      tanggal_surat_computed,
      //   handleUpload,
      //   beforeRemove,
      formData,
      form,
      fileSurat,
      optionalData,
      csrf,
      rules,
    };
  },
};
</script>

<style>
</style>
