<template>
  <el-menu
    :routes="false"
    :default-active="indexActive"
    class="menu"
    :class="{ open: isOpen }"
    :collapse="!isMobile()"
  >
    <el-row class="hidden-md-and-up" style="padding: 15px 16px">
      <el-col :span="8">
        <img
          class="logo"
          :src="routes('home') + '/images/small-logo.png'"
          alt="Logo LLDIKTI"
        />
      </el-col>
      <el-col :span="16">
        <span style="font-size: 14px">
          <b>SIPAS</b> <br />
          LLDIKTI Wilayah XIV Papua - Papua Barat</span
        >
      </el-col>
    </el-row>
    <template v-for="content in contents">
      <el-submenu
        v-if="content.has_child"
        :key="content.index"
        :index="content.index"
      >
        <template #title>
          <i v-if="content.icon !== null" :class="content.icon"></i>
          <span>{{ content.label }}</span>
        </template>
        <el-menu-item-group>
          <template #title
            ><span>{{ content.label }}</span></template
          >
          <el-menu-item
            @click="handleClickMenuItem(child.url)"
            v-for="child in content.childs"
            :key="child.index"
            :index="child.index"
          >
            {{ child.label }}
          </el-menu-item>
        </el-menu-item-group>
      </el-submenu>
      <el-menu-item
        @click="handleClickMenuItem(content.url)"
        v-else
        :key="content.index"
        :index="content.index"
      >
        <i v-if="content.icon !== null" :class="content.icon"></i>
        <template #title>
          {{ content.label }}
        </template>
      </el-menu-item>
    </template>
  </el-menu>
</template>

<script>
import { Inertia } from "@inertiajs/inertia";
import { ref } from "@vue/reactivity";
export default {
  components: {},
  props: {
    contents: Object,
    indexActive: { type: String, default: "0" },
    isOpen: { type: Boolean, default: false },
  },
  setup(props) {
    // const stateOpenSidebar = ref(props.isOpen);
    function handleClickMenuItem(url) {
      Inertia.visit(route(url));
    }
    return { handleClickMenuItem };
  },
};
</script>

<style scoped>
.menu {
  position: relative;
  left: 0px;
  transition: left 0.5s;
  max-width: 250px;
  z-index: 2000;
}
.logo {
  /* margin-left: auto; */
  /* margin-right: auto; */
  /* position: inherit; */
  width: 80%;
  height: auto;
}
@media only screen and (max-width: 768px) {
  .menu {
    position: fixed;
    min-height: 100vh;
    left: -300px;
  }

  .menu.open {
    left: 0px;
  }
}
</style>
