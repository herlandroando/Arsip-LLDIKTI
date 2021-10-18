<template>
  <layout :hasHeaderSearch="true" @handleHeaderSearch="handleMobileSearch">
    <!-- For MOBILE - START -->
    <!-- <el-container > -->
    <el-space v-if="useMq().current === 'xs'" wrap :size="16">
      <!-- <el-card class="box-card" v-for="data in tableData" :key="data.id">
        <template #header>
          <div class="card-header">
            <el-row type="flex" align="middle" justify="center">
              <el-col :span="9">
                <span>Disposisi</span>
              </el-col>
              <el-col :span="11">
                <el-tag>{{ data.sifat }}</el-tag></el-col
              >
              <el-col :span="3" style="text-align: right">
                <el-dropdown trigger="click">
                  <span class="el-dropdown-link">
                    <i class="el-icon-more-outline el-icon--right"></i>
                  </span>
                  <template #dropdown>
                    <el-dropdown-menu>
                      <el-dropdown-item icon="el-icon-view"
                        >Lihat Detail</el-dropdown-item
                      >
                      <el-dropdown-item icon="el-icon-delete"
                        >Tong Sampah</el-dropdown-item
                      >
                    </el-dropdown-menu>
                  </template>
                </el-dropdown>
              </el-col>
            </el-row>

          </div>
        </template>
        <div class="text item">
          <b>Tanggal Surat : </b>{{ data.tanggal_disposisi }}
        </div>
        <div class="text item"><b>No. Surat : </b>{{ data.no_disposisi }}</div>
        <div class="text item"><b>Perihal : </b><br />{{ data.perihal }}</div>
      </el-card> -->
    </el-space>
    <!-- </el-container> -->
    <!-- For MOBILE - END -->

    <!-- For PC - START -->
    <template v-else>
      <el-row type="flex" class="width-100">
        <el-col :span="18">
          <el-form @submit.prevent="handleSearch">
            <p>Cari Disposisi</p>
            <el-row type="flex" align="center" gutter="40">
              <el-col :span="18">
                <el-form-item>
                  <el-input
                    placeholder="Cari Disposisi"
                    prefix-icon="el-icon-search"
                    v-model="search"
                  >
                  </el-input>
                </el-form-item>
              </el-col>
              <el-col :span="6">
                <el-button native-type="submit"> Cari </el-button>
              </el-col>
            </el-row>
          </el-form>
        </el-col>
        <el-col :offset="1" :span="5" class="text-center">
          <el-button
            @click="handleAddDisposisi"
            icon="el-icon-circle-plus-outline"
            type="primary"
            >Tambah Disposisi</el-button
          >
        </el-col>
      </el-row>
      <el-divider content-position="left">Tabel Disposisi</el-divider>
      <el-row>
        <el-col>
          <el-table :data="tableData" style="width: 100%">
            <el-table-column
              prop="tanggal_disposisi"
              label="Tanggal Disposisi"
              width="150"
            >
            </el-table-column>
            <el-table-column prop="sifat" label="Sifat Surat" width="150">
            </el-table-column>
            <el-table-column prop="no_disposisi" label="No. Surat" width="150">
            </el-table-column>
            <!-- <el-table-column prop="asal_surat" label="Asal Surat" width="150"> -->
            <!-- </el-table-column> -->
            <el-table-column prop="perihal" label="Perihal" width="400">
            </el-table-column>
            <el-table-column fixed="right" label="Tombol Operasi" width="200">
              <template #default="scope">
                <el-button
                  size="mini"
                  @click="handleDetailTable(scope.$index, scope.row.id)"
                  >Detail</el-button
                >
                <el-popconfirm
                  title="Apakah anda yakin menghapus disposisi ini?"
                  confirmButtonText="Iya"
                  cancelButtonText="Tidak"
                  @confirm="handleDeleteDisposisi(scope.$index, scope.row.id)"
                >
                  <template #reference>
                    <el-button size="mini" type="danger">Hapus</el-button>
                  </template>
                </el-popconfirm>
              </template>
            </el-table-column>
          </el-table>
        </el-col>
        <el-col class="text-center" style="margin-top: 30px">
          <el-pagination
            @current-change="handlePageClick"
            @prev-click="handlePageClick"
            @next-click="handlePageClick"
            :current-page="_pagination.currentPage"
            :page-count="10"
            background
            :total="totalPage"
          ></el-pagination>
        </el-col>
        <el-col class="text-left" style="margin-top: 30px">
          <el-row>
            <el-col :span="12">
              <p>
                Total disposisi: {{ _pagination.total }} disposisi.<br />
                <small class="text-muted" v-if="_pagination.total > 100"
                  >Kami membatasi halaman tabel hingga {{_pagination.limit}} halaman. Jika tidak
                  mendapatkan disposisi yang anda cari, silahkan mencari dengan kata
                  kunci yang lebih spesifik.</small
                >
              </p>
            </el-col>
          </el-row>
        </el-col>
      </el-row>
    </template>
    <!-- For PC - END -->
  </layout>
</template>

<script>
import { Inertia } from "@inertiajs/inertia";
// @click="handleEdit(scope.$index, scope.row)"
import { defaultProps, initializationView } from "@shared/InertiaConfig.js";
import Layout from "@shared/Layout";
import { reactive, ref } from "@vue/reactivity";
import { computed, inject } from "@vue/runtime-core";
import { useMq } from "vue3-mq";

export default {
  components: {
    Layout,
  },
  props: {
    ...defaultProps,
    tableData: { type: Object, default: () => null },
    q_search: { type: String, default: "" },
    _pagination: { type: Object, default: () => null },
    isAvailable: { type: Boolean, default: () => false },
  },
  setup(props) {
    initializationView(props);
    const cancelTokenPage = ref("");
    const totalPage = computed(() =>
      props._pagination.total > 100 ? 100 : props._pagination.total
    );
    const search = ref(props.q_search);
    const query = reactive({
      currentPage: 1,
      search: "",
    });

    function handlePageClick(current) {
      query.currentPage = current;
      if (cancelTokenPage.value !== "") {
        cancelTokenPage.value.cancel();
      }
      Inertia.get(
        route("manage.disposisi.index"),
        { page: query.currentPage, search: search.value },
        {
          only: ["tableData", "_pagination"],
          onCancelToken: (cancelToken) => (cancelTokenPage.value = cancelToken),
          preserveState: true,
        }
      );
    }
    function handleMobileSearch(value) {
      console.log(value);
    }
    function handleSearch() {
      Inertia.get(
        route("manage.disposisi.index"),
        { search: search.value },
        {
          onCancelToken: (cancelToken) => (cancelTokenPage.value = cancelToken),
          onSuccess: () => {},
        }
      );
    }

    function handleDeleteDisposisi(index, id) {
      Inertia.delete(route("manage.disposisi.destroy", { disposisi: id }));
    }
    function handleDetailTable(index, id) {
      console.log(index, id);
      Inertia.get(route("manage.disposisi.show", { disposisi: id }));
    }
    function handleAddDisposisi() {
      Inertia.get(route("manage.disposisi.create"));
    }

    return {
      handlePageClick,
      handleAddDisposisi,
      handleDeleteDisposisi,
      handleDetailTable,
      handleMobileSearch,
      handleSearch,
      search,
      totalPage,
      useMq,
    };
  },
};
</script>

<style>
</style>
