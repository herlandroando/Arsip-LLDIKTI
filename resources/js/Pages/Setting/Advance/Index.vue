<template>
  <layout>
    <el-row :gutter="40">
      <el-col :span="24" :sm="24">
        <el-card class="box-card">
          <el-form label-position="top" :model="formData" ref="form">
            <el-row>
              <!-- <el-col :span="24">
                <el-form-item label="Filter Retensi Surat (Tahun)">
                  <el-input-number
                    v-model="formData.retensi"
                    :min="2"
                    :max="100"
                  />
                  <p class="info">
                    <small
                      >Surat akan di filter dalam tahun yang ditentukan. Surat
                      tidak dihapus namun surat yang masuk dalam retensi bisa
                      dilihat di tempat sampah. Minimal batas retensi adalah 2
                      tahun dan maksimal 100 tahun.</small
                    >
                  </p>
                </el-form-item>
              </el-col> -->
              <el-col :span="24">
                <el-form-item label="Hapus Surat ke Tempat Sampah">
                  <el-switch v-model="formData.deleteMailNotPermanent" />
                  <p class="info">
                    <small
                      >Surat yang dihapus akan masuk dalam tempat sampah jika di
                      aktifkan. Ini akan membuat surat yang dihapus dapat
                      dikembalikan dari tempat sampah. Surat yang dihapus juga
                      dapat dihapus secara permanen pada tempat sampah.</small
                    >
                  </p>
                  <p class="info danger">
                    <small
                      ><b>Perhatian:</b> Jika anda menonaktifkan fitur ini, maka
                      surat yang dihapus otomatis akan hilang untuk selamanya
                      dan anda tidak bisa mengembalikannya.</small
                    >
                  </p>
                </el-form-item>
              </el-col>
              <!-- <el-col :span="24">
                <el-form-item label="Retensi Surat ke Tempat Sampah">
                  <el-switch v-model="formData.deleteMailNotPermanent" />
                  <p class="info">
                    <small
                      >Surat yang masuk dalam kategori Retensi akan masuk dalam
                      tempat sampah jika di aktifkan. Ini akan membuat surat
                      yang dihapus dapat dikembalikan dari tempat sampah. Surat
                      yang dihapus juga dapat dihapus secara permanen pada
                      tempat sampah.</small
                    >
                  </p>
                  <p class="info danger">
                    <small
                      ><b>Perhatian:</b> Jika anda menonaktifkan fitur ini, maka
                      surat yang dihapus otomatis akan hilang untuk selamanya
                      dan anda tidak bisa mengembalikannya.</small
                    >
                  </p>
                </el-form-item>
              </el-col> -->
              <el-col :span="24">
                <el-button
                  @click="handleSubmit"
                  :disabled="!isDirty"
                  type="primary"
                >
                  Simpan
                </el-button>
                <el-button :disabled="!isDirty" @click="handleReset">
                  Reset
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
import {
  computed,
  inject,
  onMounted,
  onUpdated,
  watch,
} from "@vue/runtime-core";
import { defaultProps, initializationView } from "@shared/InertiaConfig.js";
import Layout from "@shared/Layout.vue";
import _ from "lodash";
import { Failed, Loading } from "@element-plus/icons";
import { Inertia } from "@inertiajs/inertia";
export default {
  components: { Layout, Failed, Link },
  props: {
    ...defaultProps,
    data: {
      type: Object,
      default: () => {
        return {};
      },
    },
  },
  setup(props) {
    initializationView(props);
    // const sifatSurat = reactive(inject("sifatSurat"));

    const formData = reactive({
      //   retensi: 2,
      deleteMailNotPermanent: false,
      //   autoDeleteRetensiMail: false,
    });
    const form = ref(null);
    const isDirty = ref(false);

    watch(
      () => _.cloneDeep(formData),
      (newVal, prevVal) => {
        let data = props.data;
        let changed = false;

        changed =
          newVal.deleteMailNotPermanent != data.delete_mail_not_permanent;
        console.log(isDirty.value, "Changed", newVal, data);
        isDirty.value = changed;
      }
    );

    function handleSubmit() {
      Inertia.put(route("setting.advance.update"), formData, {
        preserveScroll: true,
      });
      isDirty.value = false;
    }

    function handleReset() {
      formData.deleteMailNotPermanent = props.data.delete_mail_not_permanent;
    }

    onUpdated(() => {
      formData.deleteMailNotPermanent = props.data.delete_mail_not_permanent;
    });
    onMounted(() => {
      if (!_.isEmpty(props.data)) {
        // formData.retensi = props.data.retensi ?? 2;
        formData.deleteMailNotPermanent =
          props.data.deleteMailNotPermanent ?? true;
        // formData.autoDeleteRetensiMail =
        //   props.data.autoDeleteRetensiMail ?? true;
      }
    });

    return { form, formData, isDirty, handleSubmit, handleReset };
  },
};
</script>

<style>
.box-card {
  margin: 30px 0;
}
p.info {
  width: 60%;
  line-height: normal;
  color: var(--el-text-color-secondary);
}
p.info.danger {
  color: var(--el-color-danger);
}
</style>
