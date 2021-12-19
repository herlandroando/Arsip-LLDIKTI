<template>
  <layout @end-scroll="handleInfiniteScroll">
    <!-- For MOBILE - START -->
    <!-- <el-space v-if="useMq().current === 'xs'" wrap :size="16"> -->
    <template v-if="useMq().current === 'xs'">
      <el-row>
        <filter-container
          :options="filterOption"
          :query="query.filterQuery"
          @submit="handleFilterSubmitted"
          @deleted="handleFilterDeleted"
        ></filter-container>
      </el-row>
      <el-row
        v-if="Array.isArray(tableDataMobile) && tableDataMobile.length > 0"
      >
        <el-table
          :data="tableDataMobile"
          style="width: 100%"
          @row-click="handleActionMobileState"
        >
          <el-table-column type="expand">
            <template #default="scope">
              <p><b>Rutinitas Waktu:</b> {{ scope.row.rutinitas }}</p>
            </template>
          </el-table-column>
          <el-table-column label="Tipe" prop="tipe"> </el-table-column>
          <el-table-column label="Tanggal Buat" prop="tanggal_buat" width="200">
            <template #default="scope">
              {{ dateToString(scope.row.tanggal_buat, true) }}
            </template>
          </el-table-column>
        </el-table>
        <p v-if="loadingTableMobile">Mohon Menunggu...</p>
        <p v-if="limitTableMobile">
          Tabel telah mencapai maksimal halaman. Jika File anda belum ditemukan,
          mohon masukkan lebih spesifik informasi untuk pencarian atau
          penyaringan.
        </p>
      </el-row>
      <el-row v-else class="no-result">
        <el-result
          title="Data Tidak Ditemukan"
          subTitle="Data yang anda cari atau saring tidak ada di penyimpanan arsip."
        >
          <template #icon>
            <failed />
          </template>
        </el-result>
      </el-row>

      <!-- </el-space> -->
      <!-- For MOBILE - END -->
    </template>

    <!-- For PC - START -->
    <template v-else>
      <!-- <el-row type="flex" class="width-100">
        <el-col :span="18">
          <el-form @submit.prevent="handleSearch">
            <p>Cari Surat Masuk</p>
            <el-row type="flex" align="center" :gutter="40">
              <el-col :span="18">
                <el-form-item>
                  <el-input
                    placeholder="Cari Surat Masuk"
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
            @click="handleAddUser"
            icon="el-icon-circle-plus-outline"
            type="primary"
            >Tambah Surat</el-button
          >
        </el-col>
      </el-row> -->
      <el-row>
        <filter-container
          :options="filterOption"
          :query="query.filterQuery"
          @submit="handleFilterSubmitted"
          @deleted="handleFilterDeleted"
        ></filter-container>
      </el-row>
      <el-divider content-position="left">Tabel Laporan</el-divider>
      <el-row>
        <template v-if="Array.isArray(tableData) && tableData.length > 0">
          <el-col>
            <el-table
              @sort-change="handleSortTable"
              :data="tableData"
              :default-sort="q_sort"
              style="width: 100%"
            >
              <el-table-column
                prop="tanggal_buat"
                sortable="custom"
                label="Tanggal Buat"
                width="250"
              >
                <template #default="scope">
                  {{ dateToString(scope.row.tanggal_buat, true) }}
                </template>
              </el-table-column>
              <el-table-column
                width="250"
                prop="tipe"
                sortable="custom"
                label="Tipe"
              >
              </el-table-column>
              <el-table-column
                prop="rutinitas"
                sortable="custom"
                label="Rutinitas Waktu"
              >
              </el-table-column>
              <el-table-column fixed="right" label="Aksi">
                <template #default="scope">
                  <a :href="scope.row.linkFile" target="_blank"
                    ><el-button size="mini">Unduh</el-button></a
                  >
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
              :page-count="_pagination.lastPage"
              background
              :total="totalPage"
            ></el-pagination>
          </el-col>
          <el-col class="text-left" style="margin-top: 30px">
            <el-row>
              <el-col :span="12">
                <p>
                  Total data pada tabel: {{ _pagination.total }} data.<br />
                  <small class="text-muted" v-if="_pagination.total > 100"
                    >Kami membatasi halaman tabel hingga
                    {{ _pagination.limit }} halaman. Jika tidak mendapatkan
                    surat yang anda cari, silahkan mencari dengan kata kunci
                    yang lebih spesifik.</small
                  >
                </p>
              </el-col>
            </el-row>
          </el-col>
        </template>
        <template v-else>
          <el-col class="no-result">
            <el-result
              title="Data Tidak Ditemukan"
              subTitle="Data yang anda cari atau saring tidak ada di penyimpanan arsip."
            >
              <template #icon>
                <failed />
              </template>
            </el-result>
          </el-col>
        </template>
      </el-row>
    </template>
    <el-dialog title="Aksi" v-model="actionDialogVisible" width="80%" center>
      <el-space :fill="true" alignment="center" style="width: 100%">
        <a :href="actionLinkSelected" target="_blank"
          ><el-button style="width: 100%" type="primary">Unduh</el-button></a
        >
        <el-button style="width: 100%" @click="actionDialogVisible = false"
          >Tutup</el-button
        >
      </el-space>
    </el-dialog>
    <!-- For PC - END -->
  </layout>
</template>

<script>
import { Inertia } from "@inertiajs/inertia";
import { defaultProps, initializationView } from "@shared/InertiaConfig.js";
import { dateToString } from "@shared/HelperFunction";

import { Failed, DeleteFilled, List } from "@element-plus/icons";

import Layout from "@shared/Layout";
import { reactive, ref } from "@vue/reactivity";
import { computed, inject } from "@vue/runtime-core";
import { useMq } from "vue3-mq";
import _ from "lodash";
import FilterContainer from "@shared/Filter/Container.vue";
import filterOption from "./FilterOption.js";
import {
  assignFilter,
  defaultFilter,
  initializationFilter,
} from "@shared/Filter/Helper";
import axios from "axios";

export default {
  components: {
    Layout,
    FilterContainer,
    Failed,
    DeleteFilled,
    List,
  },
  props: {
    ...defaultProps,
    ...defaultFilter,
    tableData: { type: Object, default: () => null },
    q_sort: {
      type: Object,
      default: () => {
        return { prop: "" };
      },
    },
    _pagination: { type: Object, default: () => null },
    isAvailable: { type: Boolean, default: () => false },
  },
  setup(props) {
    initializationView(props);
    initializationFilter(props);
    const cancelTokenPage = ref("");
    const totalPage = computed(() =>
      props._pagination.total > 100 ? 100 : props._pagination.total
    );
    const query = reactive({
      currentPage: 1,
      sort: "",
      filterQuery: props.q_filter,
    });
    //=== Mobile =====
    const tableDataMobile = ref(props.tableData);
    const actionDialogVisible = ref(false);
    const actionLinkSelected = ref();
    const actionUsernameSelected = ref();
    const loadingTableMobile = ref(false);
    const limitTableMobile = ref(false);
    const mq = useMq();

    function handlePageClick(current) {
      query.currentPage = current;
      if (cancelTokenPage.value !== "") {
        cancelTokenPage.value.cancel();
      }
      Inertia.get(
        route("report.index"),
        {
          page: query.currentPage,
          ...query.filterQuery,
          sort: query.sort,
        },
        {
          only: ["tableData", "_pagination"],
          onCancelToken: (cancelToken) => (cancelTokenPage.value = cancelToken),
          preserveState: true,
        }
      );
    }
    function handleSortTable(column, prop) {
      console.log(column, column.prop, column.order);
      if (prop === null) {
        query.sort = "";
      } else {
        let order = column.order === "ascending" ? "asc" : "desc";
        query.sort = order + "!" + column.prop;
      }
      Inertia.get(
        route("report.index"),
        {
          page: query.currentPage,
          ...query.filterQuery,
          sort: query.sort,
        },
        {
          only: ["tableData", "_pagination"],
          onCancelToken: (cancelToken) => (cancelTokenPage.value = cancelToken),
          preserveState: true,
        }
      );
    }

    function handleFilterSubmitted(v) {
      assignFilter(v, query.filterQuery, filterOption);
      Inertia.get(route("report.index"), {
        ...query.filterQuery,
        sort: query.sort,
      });
    }

    function handleFilterDeleted(tag) {
      let index = query.filterQuery[tag.query].indexOf(tag.value);
      console.log(query.filterQuery[tag.query], tag, index);

      let isEmpty = false;
      if (index !== -1) query.filterQuery[tag.query].splice(index, 1);
      else isEmpty = true;
      if (query.filterQuery[tag.query].length <= 0) isEmpty = true;
      if (isEmpty) query.filterQuery = _.omit(query.filterQuery, tag.query);
      Inertia.get(route("report.index"), {
        ...query.filterQuery,
        sort: query.sort,
      });
    }

    function handleActionMobileState(row, column, event) {
      //   console.log(row, column, event);
      actionLinkSelected.value = row.linkFile;
      actionDialogVisible.value = true;
    }

    async function handleInfiniteScroll() {
      if (
        mq.current != "xs" ||
        limitTableMobile.value ||
        loadingTableMobile.value
      ) {
        return;
      }
      loadingTableMobile.value = true;
      await axios
        .get(route("report.index.json"), {
          params: {
            page: query.currentPage + 1,
            ...query.filterQuery,
            sort: query.sort,
          },
        })
        .then((v) => {
          let except = false;
          if (v.data.empty) {
            tableDataMobile.value = [];
            except = true;
          }
          if (v.data.limit) {
            limitTableMobile.value = true;
            except = true;
          }
          if (except) {
            return;
          }
          query.currentPage = v.data.currentPage;
          tableDataMobile.value = tableDataMobile.value.concat(v.data.result);
        });
      loadingTableMobile.value = false;
      window.scrollBy(0, -100 * query.currentPage);
    }

    return {
      dateToString,
      handlePageClick,
      handleFilterSubmitted,
      handleFilterDeleted,
      handleSortTable,
      handleActionMobileState,
      handleInfiniteScroll,
      actionDialogVisible,
      actionUsernameSelected,
      actionLinkSelected,
      totalPage,
      filterOption,
      tableDataMobile,
      limitTableMobile,
      loadingTableMobile,
      useMq,
      query,
    };
  },
};
</script>

<style scoped>
.no-result {
  margin-top: 30px;
  margin-bottom: 80px;
}
</style>
