<template>
  <el-menu
    :routes="false"
    :default-active="indexActive"
    :collapse="useMq().mdPlus"
    class="menu"
    :class="{ open: isOpen }"
  >
    <div class="scrollable">
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
      <hr class="hidden-md-and-up" style="width: 100%" />

      <el-row
        class="hidden-md-and-up"
        style="padding: 15px 16px; font-size: 14px"
      >
        <el-col :span="24" class="profile-data-container">
          <p>
            <b>{{ profile.nama }}</b>
          </p>
          <p style="color: var('--el-text-color-regular')">
            @{{ profile.username }}
          </p>
          <p>
            <b>{{ profile.jabatan }}</b>
          </p>
        </el-col>
        <el-col class="hidden-md-and-up" style="text-align: left" :span="24">
          <Link
            :href="
              routes('profile', {
                user: profile.id,
                username: profile.username,
              })
            "
          >
            <el-button style="margin-right: 10px" type="primary">
              Lihat Profil</el-button
            >
          </Link>
        </el-col>
      </el-row>
      <hr class="hidden-md-and-up" style="width: 100%" />
      <template v-for="content in contents">
        <template v-if="isPermitted(content.permission)">
          <el-sub-menu
            v-if="content.has_child"
            :key="content.index"
            :index="content.index"
          >
            <template #title>
              <i v-if="content.icon !== null" :class="content.icon"></i>
              <span class="title-sidebar">{{ content.label }}</span>
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
          </el-sub-menu>
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
      </template>
      <el-menu-item index="5">
        <i class="el-icon-info"></i>
        <template #title> Manual Program </template>
      </el-menu-item>
      <el-menu-item
        v-if="useMq().mdMinus"
        class="text-error"
        @click="handleLogout"
        index="6"
      >
        <i class="el-icon-top-left"></i>
        <template #title> Logout </template>
      </el-menu-item>
    </div>
  </el-menu>
</template>

<script>
import { Inertia } from "@inertiajs/inertia";
import { Link } from "@inertiajs/inertia-vue3";
import { useMq } from "vue3-mq";
import { inject } from "@vue/runtime-core";

export default {
  components: { Link },
  props: {
    contents: Object,
    indexActive: { type: String, default: "0" },
    isOpen: { type: Boolean, default: false },
  },
  setup(props) {
    // const stateOpenSidebar = ref(props.isOpen);
    const profile = inject("user");
    const permission = inject("permission", null);
    const isPermitted = (pm) => {
      if (Array.isArray(pm) && pm.length > 0) {
        for (const element of pm) {
          if (element in permission) {
            if (!permission[element]) return false;
          } else {
            console.log("Permitted", false, pm);
            return false;
          }
        }
      }
      console.log("Permitted", true, pm);
      return true;
    };

    function handleClickMenuItem(url) {
      Inertia.visit(route(url));
    }
    function handleLogout() {
      Inertia.get(route("logout"));
    }
    return {
      handleLogout,
      handleClickMenuItem,
      useMq,
      permission,
      isPermitted,
      profile,
    };
  },
};
</script>

<style scoped>
.profile-container div {
  padding: 10px 0px;
}
.profile-data-container p {
  margin: 5px 0px;
}
.title-sidebar {
  visibility: hidden;
}
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
@media only screen and (max-width: 992px) {
  .menu {
    position: fixed;
    min-height: 100vh;
    left: -300px;
  }
  .scrollable {
    overflow-y: auto;
    overflow-x: hidden;
    max-height: 87vh;
    height: 87vh;
  }

  .menu.open {
    left: 0px;
  }

  .title-sidebar {
    visibility: visible;
  }
}
</style>

<style>
.el-sub-menu__icon-arrow {
  visibility: hidden;
}
@media only screen and (max-width: 768px) {
  .el-sub-menu__icon-arrow {
    visibility: visible;
  }
}
</style>
