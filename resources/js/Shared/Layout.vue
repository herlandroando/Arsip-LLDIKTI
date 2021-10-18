<template>
  <div
    ref="container"
    :infinite-scroll-delay="1000"
    v-infinite-scroll="handleEndOfScroll"
    scroll-region
  >
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
          <el-main class="content-container bg-gray">
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
              <template v-if="showTitle">
                <h4>{{ title }}</h4>
              </template>
            </el-space>
            <!-- <div :class="{'main-scrollable':true,'disable':stateOpenSidebar}"> -->
            <slot></slot>
            <!-- </div> -->
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
  </div>
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
import {
  onMounted,
  watch,
  ref,
  inject,
  provide,
  computed,
  onUpdated,
} from "vue";
import { ElNotification } from "element-plus";
import { useMq } from "vue3-mq";
import Sidebar from "./Sidebar.vue";
import HeaderCustom from "./Header.vue";
import { Inertia } from "@inertiajs/inertia";

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
  emits: ["handleHeaderSearch", "endScroll"],
  setup(props, { emit }) {
    const body = document.querySelector("body");
    const stateOpenSidebar = ref(false);
    const sidebarContents = inject("sidebars", null);
    // const breadcrumb = inject("breadcrumb", null);
    const indexActive = inject("indexActive", "0");
    const title = inject("title", "Title_Not_Defined");
    const showTitle = inject("showTitle", true);
    const backNav = inject("backNav", null);
    const container = ref(null);
    const mq = useMq();
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

    Inertia.on("success", () => {
    //   console.log(mq.mdMinus, mq);
      if (mq.mdMinus) {
        if (stateOpenSidebar.value) {
          body.style.overflowY = "hidden";
        } else {
          body.style.overflowY = "auto";
        }
      }
      //   toast(props._toast);
    });

    // function toast(toast) {
    //   if (toast instanceof Object)
    //     ElNotification({
    //       type: toast.type,
    //       title: toast.title,
    //       message: toast.message,
    //     });
    // }

    function handleSidebar(state) {
      stateOpenSidebar.value = state;
      if (state) {
        body.style.overflowY = "hidden";
      } else {
        body.style.overflowY = "auto";
      }
    }
    function handleSearch(value) {
      emit("handleHeaderSearch", value);
    }

    function handleEndOfScroll() {
      if (mq.current == "xs") emit("endScroll");
    }

    return {
      indexActive,
      //   handleDialogWarnCancel,
      //   handleDialogWarnConfirm,
      //   dialogWarn,
      title,
      showTitle,
      backNav,
      sidebarContents,
      stateOpenSidebar,
      handleSidebar,
      handleSearch,
      handleEndOfScroll,
      container,
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
  /* width: 100vw; */
  z-index: 1000;
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
