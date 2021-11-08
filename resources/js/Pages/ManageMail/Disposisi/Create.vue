<template>
  <layout>
    <el-row :gutter="40">
      <el-col :span="24" :sm="24">
        <el-card class="box-card">
          <el-row>
            <el-col :span="24" :md="12" :lg="14">
              <h4>Data Disposisi</h4>
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
                <el-checkbox v-model="formData.is_suratmasuk"
                  >Disposisi Surat Masuk.</el-checkbox
                >
              </el-col>
              <el-col v-if="formData.is_suratmasuk" :span="24">
                <el-form-item prop="no_suratmasuk" label="No. Surat Masuk">
                  <el-select
                    v-model="formData.no_suratmasuk"
                    filterable
                    remote
                    reserve-keyword
                    placeholder="Masukkan No. Surat Masuk."
                    :remote-method="handleListSuratMasuk"
                    :loading="processingInputRequest"
                    class="input-no-surat"
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
              <el-col :span="24" :md="12">
                <el-form-item prop="no_disposisi" label="No. Disposisi">
                  <el-input v-model="formData.no_disposisi"></el-input>
                </el-form-item>
              </el-col>
              <el-col :span="24" :md="12">
                <el-form-item prop="tenggat_waktu" label="Tenggat Waktu">
                  <el-date-picker
                    class="width-100"
                    type="datetime"
                    format="DD-MM-YYYY HH:mm"
                    placeholder="Pilih Tenggat Waktu Disposisi"
                    v-model="formData.tenggat_waktu"
                    :disabled-date="dateNowAndAfter"
                  ></el-date-picker>
                </el-form-item>
              </el-col>
              <el-col :span="24" :md="12">
                <el-form-item prop="tujuan" label="Tujuan Disposisi">
                  <el-select
                    v-model="formData.tujuan"
                    placeholder="Pilih Tujuan Disposisi"
                  >
                    <el-option
                      v-for="item in bagianInstansi"
                      :key="item.id"
                      :label="item.nama"
                      :value="item.id"
                    >
                    </el-option>
                  </el-select>
                </el-form-item>
              </el-col>
              <el-col :span="24">
                <el-form-item prop="isi" label="Isi Disposisi">
                  <el-input
                    type="textarea"
                    :rows="4"
                    placeholder="Masukkan isi disposisi"
                    v-model="formData.isi"
                  />
                </el-form-item>
              </el-col>
              <el-col>
                <el-button
                  type="primary"
                  :loading="processingForm"
                  @click="handleSubmitForm"
                >
                  Simpan
                </el-button>
              </el-col>
            </el-row>
          </el-form>
        </el-card>
      </el-col>
    </el-row>
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
import rulesWithInbox from "@rules/StoreDisposisiWithInbox";
import rulesWithoutInbox from "@rules/StoreDisposisiWithoutInbox";
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
    const bagianInstansi = reactive(inject("bagianInstansi"));
    const ruleComputed = computed(() => {
      if (formData.is_suratmasuk) {
        return rulesWithInbox;
      } else {
        return rulesWithoutInbox;
      }
    });
    // const limitUploadSize = "4096";
    const form = ref(null);
    const listSuratMasuk = ref([]);
    const processingForm = ref(false);
    const processingInputRequest = ref(false);

    const formData = reactive({
      is_suratmasuk: true,
      no_suratmasuk: "",
      tenggat_waktu: "",
      no_disposisi: "",
      tujuan: "",
      isi: "",
    });

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
        Inertia.post(route("manage.disposisi.store"), formData, {
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
        });
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

    return {
      dateNowAndAfter,
      handleSubmitForm,
      handleListSuratMasuk,
      rulesWithInbox,
      rulesWithoutInbox,
      rulesActivity,
      ruleComputed,
      //   sifatSurat,
      bagianInstansi,
      form,
      formData,
      listSuratMasuk,
      //   tenggatWaktuComputed,
      processingForm,
      processingInputRequest,
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

.input-no-surat{
    width: 49%;
}

@media only screen and (max-width: 992px) {
    .input-no-surat{
    width: 100%;
}
}
</style>
