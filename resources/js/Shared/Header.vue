<template>
  <el-row
    type="flex"
    class="hidden-sm-and-down header height-100 bg-primary"
    align="middle"
  >
    <el-col :span="8" style="padding-left: 2%">
      <el-row>
        <el-col :span="3">
          <img
            class="logo"
            :src="routes('home') + '/images/logo.png'"
            alt="Logo LLDIKTI"
          />
        </el-col>
        <el-col :span="21">
          <span style="font-size: 14px">
            <b>SIPAS</b> <br />
            LLDIKTI Wilayah XIV Papua - Papua Barat</span
          >
        </el-col>
      </el-row>
    </el-col>
    <el-col :offset="9" :span="6" class="" style="text-align: right">
      <Link :href="routes('logout')" >
        <span style="font-size: 14px"> Logout</span>
      </Link></el-col
    >
  </el-row>
  <el-row
    type="flex"
    class="hidden-md-and-up header height-100 bg-primary"
    align="middle"
    justify="center"
  >
    <el-col :span="24" style="padding-left: 3%">
      <el-row align="middle" type="flex">
        <el-col :span="6">
          <el-button @click="handleSidebar" type="text" style="width: 30px"
            ><i
              v-if="stateOpenSidebar"
              style="font-size: 20px"
              class="el-icon-s-unfold text-white"
            ></i
            ><i
              v-else
              style="font-size: 20px"
              class="el-icon-s-fold text-white"
            ></i
          ></el-button>
        </el-col>
        <el-col
          class="search-input"
          v-if="hasHeaderSearch && useMq().mdMinus"
          :span="17"
        >
          <form @submit.prevent="handleSearch" class="search-form">
            <input v-model="search" type="search" placeholder="Search" />
          </form>
        </el-col>
        <el-col :span="1"></el-col>
      </el-row>
    </el-col>
  </el-row>
</template>

<script>
import { computed, ref } from "@vue/reactivity";
import { inject } from "@vue/runtime-core";
import { useMq } from "vue3-mq";
import { Link } from "@inertiajs/inertia-vue3";

export default {
  components: { Link },
  emits: ["handleSidebar", "handleSearch"],
  props: {
    isActiveSidebar: { type: Boolean, default: false },
    hasHeaderSearch: { type: Boolean, default: false },
  },
  setup(props, { emit }) {
    const stateOpenSidebar = ref(false);
    const search = ref("");
    console.log(props.hasHeaderSearch, "header search active?");
    function handleSidebar() {
      stateOpenSidebar.value = !stateOpenSidebar.value;
      emit("handleSidebar", stateOpenSidebar.value);
      console.log(stateOpenSidebar.value);
    }

    function handleSearch() {
      emit("handleSearch", search.value);
    }
    return {
      handleSidebar,
      stateOpenSidebar,
      handleSearch,
      search,
      useMq,
    };
  },
};
</script>

<style scoped>
/* .header {
  padding-top: 7px;
  font-size:small;

} */
.logo {
  /* display: block; */
  /* margin-left: auto; */
  /* margin-right: auto; */
  width: 80%;
  object-fit: cover;
  height: auto;
}
.search-input {
  right: 0;
  position: absolute;
  margin-right: 40px;
}

input {
  outline: none;
}
input[type="search"] {
  -webkit-appearance: textfield;
  -webkit-box-sizing: content-box;
  font-family: inherit;
  font-size: 100%;
}
input::-webkit-search-decoration,
input::-webkit-search-cancel-button {
  display: none;
}

input[type="search"] {
  background: #ededed
    url(https://static.tumblr.com/ftv85bp/MIXmud4tx/search-icon.png) no-repeat
    9px center;
  border: solid 1px #ccc;
  padding: 9px 10px 9px 32px;
  width: 55px;

  -webkit-border-radius: 10em;
  -moz-border-radius: 10em;
  border-radius: 10em;

  -webkit-transition: all 0.5s;
  -moz-transition: all 0.5s;
  transition: all 0.5s;
}
input[type="search"]:focus {
  width: 130px;
  background-color: #fff;
  border-color: #66cc75;

  -webkit-box-shadow: 0 0 5px rgba(109, 207, 246, 0.5);
  -moz-box-shadow: 0 0 5px rgba(109, 207, 246, 0.5);
  box-shadow: 0 0 5px rgba(109, 207, 246, 0.5);
}

input:-moz-placeholder {
  color: #999;
}
input::-webkit-input-placeholder {
  color: #999;
}

/* Demo 2 */
.search-form input[type="search"] {
  width: 15px;
  padding-left: 10px;
  color: transparent;
  cursor: pointer;
}
.search-form input[type="search"]:hover {
  background-color: #fff;
}
.search-form input[type="search"]:focus {
  width: 170px;
  padding-left: 32px;
  color: #000;
  background-color: #fff;
  cursor: auto;
}
.search-form input:-moz-placeholder {
  color: transparent;
}
.search-form input::-webkit-input-placeholder {
  color: transparent;
}

@media only screen and (max-width: 768px) {
  .logo {
    /* display: block; */
    /* margin-left: auto; */
    /* margin-right: auto; */
    width: 50%;
    object-fit: cover;
    height: auto;
  }
}
</style>
