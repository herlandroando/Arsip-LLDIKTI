<template>
  <div class="login">
    <el-row>
      <el-col class="column first" :sm="24" :md="10">
        <!-- For Mobile -->
        <el-row type="flex" class="height-100" justify="center" align="middle">
          <el-col :xs="10" :sm="10" :md="24">
            <img
              class="logo"
              :src="routes('home') + '/images/logo.png'"
              alt="Logo LLDIKTI"
            />
            <h2 class="hidden-md-and-down text-center text-title">
              Sistem Informasi Pengarsipan Surat LLDIKTI Wilayah XIV
            </h2>
            <h4 class="hidden-sm-and-down hidden-lg-and-up text-center text-title">
              Sistem Informasi Pengarsipan Surat LLDIKTI Wilayah XIV
            </h4>
          </el-col>

          <el-col class="hidden-md-and-up" :xs="14" :sm="14" :md="24">
            <h4 class="hidden-md-and-up text-title">
              Sistem Informasi Pengarsipan Surat LLDIKTI Wilayah XIV
            </h4>
            <h2 class="hidden-sm-and-down text-title">
              Sistem Informasi Pengarsipan Surat LLDIKTI Wilayah XIV
            </h2>
          </el-col>
        </el-row>
        <!-- For PC -->
      </el-col>
      <el-col class="column second bg-primary" :sm="24" :md="14">
        <el-row type="flex" class="height-100" justify="center" align="middle">
          <el-col :xs="24" :sm="24" :md="16">
            <el-card shadow="hover" class="box-card">
              <h5>Login Untuk Masuk Aplikasi SIPAS</h5>
              <el-form :model="form" label-position="top">
                <el-form-item label="Username">
                  <el-input v-model="form.username"></el-input>
                </el-form-item>
                <el-form-item label="Password">
                  <el-input show-password v-model="form.password"></el-input>
                </el-form-item>
                <el-form-item>
                  <el-button type="primary" @click="login">Login</el-button>
                </el-form-item>
              </el-form>
            </el-card>
          </el-col>
        </el-row>
      </el-col>
    </el-row>
  </div>
  <!-- </master> -->
</template>

<script>
import { ref, reactive, toRefs, onMounted, watch } from "vue";
import { Inertia } from "@inertiajs/inertia";
import { useForm } from "@inertiajs/inertia-vue3";
import { ElNotification } from "element-plus";

export default {
  components: {},
  props: {
    _toast: {
      type: Object,
      default: null,
    },
    success: {
      type: Boolean,
      default: false,
    },
    sessionExpired: Boolean,
    logout: Boolean,
    flash: Object,
  },
  setup(props) {
    // console.log(props);

    // const startCompleted = ref(false);



    // const logo = ref(Path.logo);
    const form = useForm({
      username: "",
      password: "",
      remember: false,
    });
    const processXhr = ref(false);
    function login() {
      form.post(route("login.post"), {
        onSuccess: () => {
          // if (props.success){
          // }
        },
      });

      //   Inertia.post("/login", param, {
      //     onStart: () => {
      //       processXhr.value = true;
      //     },
      //     onFinish: () => {
      //       processXhr.value = false;
      //     },
      //   });
      //   if (this.success) {
      //     this.$inertia.visit("/home", { replace: true });
      //   }
      //   console.log(this.$page);
    }

    // const messages = reactive({ success: props.success, error: props.error });

    return { form, /*logo,*/ processXhr, login };
  },
};
</script>

<style>
body {
  margin: 0px;
}

.login > div {
  height: 100%;
}

.centered {
  display: flex;
  justify-content: center;
  align-items: center;
}

.text-center {
  text-align: center;
}

.bg-primary {
  background: #0c4b8e !important;
  color: #fff;
}

.bg-secondary {
  background: #134172 !important;
  color: #fff;
}

.bg-dark {
  background: #303742 !important;
  color: #fff;
}

.bg-gray {
  background: #f7f8f9 !important;
}

.bg-success {
  background: #32b643 !important;
  color: #fff;
}

.bg-warning {
  background: #ffb700 !important;
  color: #fff;
}

.bg-error {
  background: #e85600 !important;
  color: #fff;
}
.height-100 {
  height: 100%;
}
</style>

<style scoped>
.text-title{
    margin: 0px 30px
}
.text-center {
  text-align: center;
}
.bg-white {
  background-color: #fff;
}
.login {
  height: 100vh;
  overflow: hidden;
}

.mt-10 {
  margin-top: 10%;
}

.logo {
  display: block;
  margin-left: auto;
  margin-right: auto;
  width: 30%;
  height: auto;
}
.column.second {
  padding-left: 5%;
  padding-right: 5%;
}

@media only screen and (max-width: 992px) {
  .login {
    height: 100vh;
    overflow: hidden;
  }
  .column.first {
    height: 30vh;
  }

  .logo {
    display: block;
    margin-left: auto;
    margin-right: auto;
    width: 40%;
  }

  .column.second {
    height: 70vh;
    width: 100%;
    padding-left: 10%;
    padding-right: 10%;
  }

  .centered.form {
    display: flex;
    justify-content: flex-start;
    align-items: flex-start;
    padding-top: 15%;
  }
}

@media only screen and (max-width: 768px) {
  .logo {
    display: block;
    margin-left: auto;
    margin-right: auto;
    width: 50%;
  }
}
</style>
