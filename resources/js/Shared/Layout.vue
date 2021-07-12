<template>
  <el-container direction="vertical" class="app-main">
    <el-header class="header-container">
      <header-custom
        :hasHeaderSearch="hasHeaderSearch"
        @handleSidebar="handleSidebar"
        @handleSearch="handleSearch"
      />
    </el-header>
    <el-container class="height-100" direction="horizontal">
      <sidebar
        :indexActive="indexActive"
        :contents="sidebarContents"
        :isOpen="stateOpenSidebar"
      />
      <el-container direction="vertical">
        <el-main class="content-container">
          <el-space direction="horizontal">
            <template v-if="backNav.hasBack !== false">
              <a
                style="text-decoration: none"
                v-if="backNav.hasBack && backNav.urlBack === ''"
                href="javascript:history.back()"
              >
                <i class="el-icon-arrow-left"></i></a
              ><inertia-link v-else :href="backNav.urlBack"
                ><i class="el-icon-arrow-left"></i
              ></inertia-link>
            </template>
            <h4>{{ title }}</h4>
          </el-space>
          <slot></slot>
        </el-main>
        <el-footer
          height="40px"
          class="footer-container"
          style="background-color: #6d6d64"
          >Footer</el-footer
        >
      </el-container>
    </el-container>
  </el-container>
  <!-- <el-dialog
    :title="dialogWarn.title"
    v-model="dialogWarn.visible"
    width="30%"
    center
  >
    <span>{{ dialogWarn.description }}</span>
    <template #footer>
      <span class="dialog-footer">
        <el-button @click="handleDialogWarnCancel">Tidak</el-button>
        <el-button type="primary" @click="handleDialogWarnConfirm"
          >Iya</el-button
        >
      </span>
    </template>
  </el-dialog> -->
</template>

<script>
// import Sidebar from "./Sidebar.vue";
import { onMounted, watch, ref, inject, provide } from "vue";
import { ElNotification } from "element-plus";
import Sidebar from "./Sidebar.vue";
import HeaderCustom from "./Header.vue";

export default {
  components: { Sidebar, HeaderCustom },
  props: {
    _toast: {
      type: Object,
      default: null,
    },
    hasHeaderSearch: {
      type: Boolean,
      default: false,
    },
  },
  emits: ["handleHeaderSearch"],
  setup(props, { emit }) {
    const stateOpenSidebar = ref(false);
    const sidebarContents = inject("sidebars", null);
    // const breadcrumb = inject("breadcrumb", null);
    const indexActive = inject("indexActive", "0");
    const title = inject("title", "Title_Not_Defined");
    const backNav = inject("backNav", null);
    // const dialogWarn = reactive({
    //   visible: false,
    //   title: "Dialog",
    //   description: "Apakah anda yakin dengan tindakan yang anda lakukan?",
    //   clickCancelCallback: (fn = () => {}) => {
    //     return fn();
    //   },
    //   clickConfirmCallback: (fn = (fn = () => {})) => {
    //     return fn();
    //   },
    // });
    // console.log({
    //   backNav: backNav.value,
    //   title: title.value,
    //   indexActive: indexActive.value,
    // });
    // watch(stateOpenSidebar, (value) => {
    //   if (value) document.body.classList.add("isolation");
    //   else document.body.classList.remove("isolation");
    // });

    // function handleDialogWarnCancel() {
    //   dialogWarn.visible = false;
    //   dialogWarn.clickCancelCallback();
    // }
    // function handleDialogWarnConfirm() {
    //   dialogWarn.clickCancelCallback();
    // }

    function handleSidebar(state) {
      stateOpenSidebar.value = state;
    }
    function handleSearch(value) {
      emit("handleHeaderSearch", value);
    }

    return {
      indexActive,
    //   handleDialogWarnCancel,
    //   handleDialogWarnConfirm,
    //   dialogWarn,
      title,
      backNav,
      sidebarContents,
      stateOpenSidebar,
      handleSidebar,
      handleSearch,
    };
  },
};
</script>

<style>
body {
  margin: 0px;
  overflow-y: hidden;
}
.width-100 {
  width: 100% !important;
}
.isolation {
  overflow: hidden;
}
.content-container {
  max-height: 85vh;
}
.footer-container {
  min-height: 5vh;
}
.header-container {
  position: relative;
  width: 100vw;
  z-index: 1900;
}

.app-main {
  min-height: 100vh;
}

.text-white {
  color: #fff;
}

.text-error {
  color: #721c24;
}

.text-muted {
  color: #999999;
}

.text-right {
  text-align: right;
}

.text-left {
  text-align: left;
}

.text-center {
  text-align: center;
}

.el-header {
  padding: 0px;
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
@media only screen and (max-width: 768px) {
  .content-container {
    max-height: none;
    min-height: 82vh;
  }
  .footer-container {
    min-height: 8vh;
  }
  .header-container {
    position: sticky;
    top: 0;
  }
  body {
    overflow-y: visible;
  }
}
</style>
