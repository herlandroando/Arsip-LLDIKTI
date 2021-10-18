<template>
  <layout>
    <el-row class="error-result">
      <el-col :span="24">
        <el-result>
          <template #title>
            <p>
              <b>{{ title }}</b>
            </p>
          </template>
          <template #subTitle>
            <p>{{ message }}</p>
            <Link :href="routes('home')"><el-button style="margin-top:30px" type="primary">Ke Dashboard</el-button></Link>
          </template>
          <template #icon>
            <img class="icon-result" :src="routes('home') + icon" />
          </template>
        </el-result>
      </el-col>
    </el-row>
  </layout>
</template>

<script>
import { defaultProps, initializationView } from "@shared/InertiaConfig.js";
import Layout from "@shared/Layout.vue";
import { onMounted, ref } from "@vue/runtime-core";
import { Link } from "@inertiajs/inertia-vue3";

export default {
  components: { Layout, Link },
  props: {
    ...defaultProps,
    code: { type: Number, default: 100 },
  },
  setup(props) {
    initializationView(props);
    const title = ref("Error");
    const message = ref("Terdapat Masalah Server!");
    const icon = ref("/images/logo.png");
    onMounted(() => {
      switch (props.code) {
        case "404":
          title.value = "Halaman Tidak Ditemukan";
          message.value =
            "Kami tidak menemukan halaman yang anda cari di website ini.";
          icon.value = "/images/icons/404.svg";
          break;
        case "500":
          title.value = "Kesalahan Server";
          message.value =
            "Sepertinya kami melakukan kesalahan ketika anda memproses sesuatu. Kontak admin website ini untuk melaporkan masalah ini.";
          icon.value = "/images/icons/500.svg";
          break;
        default:
          title.value = "Tidak Ada Kesalahan";
          message.value =
            "Kami berusaha untuk tidak melakukan kesalahan lagi. Terima kasih.";
          icon.value = "/images/icons/100.svg";
          break;
      }
    });

    return { title, message, icon };
  },
};
</script>

<style scoped>
.icon-result {
  width: 50%;
  height: auto;
}
.error-result {
  width: 100%;
  margin-top: 50px;
}
</style>
