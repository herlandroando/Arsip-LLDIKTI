<template>
  <layout>
    <el-row :gutter="40">
      <el-col :span="24">
        <el-card class="box-card">
          <el-row>
            <el-col :span="24" :md="12" :lg="14">
              <h4>Tambah Pengguna</h4>
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
                  <el-input v-model="formData.username"></el-input>
                </el-form-item>
              </el-col>
              <el-col :span="24" :sm="12">
                <el-form-item prop="nama" label="Nama Lengkap">
                  <el-input v-model="formData.nama"></el-input>
                </el-form-item>
              </el-col>
              <el-col :span="24" :sm="12">
                <el-form-item prop="password" label="Password">
                  <el-input
                    show-password
                    v-model="formData.password"
                  ></el-input>
                </el-form-item> </el-col
              ><el-col :span="24" :sm="12">
                <el-form-item prop="cpassword" label="Konfirmasi Password">
                  <el-input
                    show-password
                    v-model="formData.cpassword"
                  ></el-input>
                </el-form-item> </el-col
              ><el-col :span="24" :sm="12">
                <el-form-item prop="nip" label="NIP">
                  <el-input v-model="formData.nip"></el-input>
                </el-form-item>
              </el-col>
              <el-col :span="24" :sm="12">
                <el-form-item prop="no_telepon" label="No. Telepon">
                  <el-input v-model="formData.no_telepon"></el-input>
                </el-form-item>
              </el-col>
              <el-col :span="24" :sm="12">
                <el-form-item prop="id_jabatan" label="Jabatan">
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
import { reactive, ref } from "@vue/reactivity";
import { computed, inject, onMounted } from "@vue/runtime-core";
import {
  defaultProps,
  initializationView,
  getPermission,
} from "@shared/InertiaConfig.js";
import Layout from "@shared/Layout.vue";
import rules from "@rules/StoreUser.js";
import { defaultFormData } from "@pages/User/ShowFormData.js";
import _ from "lodash";
import { ElNotification } from "element-plus";
import { Inertia } from "@inertiajs/inertia";
import { useForm } from "@inertiajs/inertia-vue3";
export default {
  components: { Layout },
  props: {
    ...defaultProps,
    listJabatan: {
      type: Array,
      default: () => [],
    },
  },

  setup(props) {
    initializationView(props);
    // const bagianInstansi = inject("bagianInstansi");
    // const csrf = inject("csrf");
    const dialogWarnVisible = ref(false);
    const form = ref(null);
    const processingForm = ref(false);
    const permission = getPermission(props);
    const formData = useForm({
      ...defaultFormData.admin,
      password: "",
      cpassword: "",
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
      if (formData.password != formData.cpassword) {
        ElNotification.error({
          title: "Gagal",
          message: "Konfirmasi password tidak sama dengan password!",
        });
        return;
      }
      console.log(is_valid);
      if (is_valid) {
        Inertia.post(route("setting.users.create"), formData, {
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

    function dateNowAndBefore(time) {
      return time.getTime() > Date.now();
    }

    return {
      dateNowAndBefore,
      handleSubmitForm,
      //   csrf,
      rules,
      form,
      formData,
      processingForm,
      dialogWarnVisible,
      permission,
    };
  },
};
</script>

<style scoped>
.box-card {
  margin: 30px 0;
}
</style>
