<template>
  <layout
    :hasHeaderSearch="true"
    @end-scroll="handleInfiniteScroll"
    @handleHeaderSearch="handleMobileSearch"
  >
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
              <p><b>Status:</b> {{ scope.row.status }}</p>
              <p><b>Tenggat Waktu:</b> {{ scope.row.tenggat_waktu }}</p>
              <p><b>Terakhir Diubah</b>: {{ scope.row.tanggal_ubah }}</p>
              <p><b>Asal:</b> {{ scope.row.asal }}</p>
              <p><b>Tujuan:</b> {{ scope.row.tujuan }}</p>
            </template>
          </el-table-column>
          <el-table-column
            width="130"
            label="No. Disposisi"
            prop="no_disposisi"
          >
          </el-table-column>
          <el-table-column label="Isi ringkas" prop="isi">
            <template #default="scope">
              {{ truncateString(scope.row.isi) }}
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
      <el-row type="flex" class="width-100">
        <el-col :span="18">
          <el-form @submit.prevent="handleSearch">
            <p>Cari Disposisi</p>
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
            @click="handleAddDisposisi"
            icon="el-icon-circle-plus-outline"
            type="primary"
            >Tambah Disposisi</el-button
          >
        </el-col>
      </el-row>
      <el-row>
        <filter-container
          :options="filterOption"
          :query="query.filterQuery"
          @submit="handleFilterSubmitted"
          @deleted="handleFilterDeleted"
        ></filter-container>
      </el-row>
      <el-divider content-position="left">Tabel Disposisi</el-divider>
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
                label="Tenggat Waktu"
                width="190"
                prop="tenggat_waktu"
                sortable="custom"
              >
                <template #default="scope">
                  {{
                    new Date(scope.row.tenggat_waktu).toLocaleString(
                      "id-ID",
                      {
                        dateStyle: "full",
                        timeStyle: "short",
                      }
                    )
                  }}
                </template>
              </el-table-column>
              <el-table-column
                prop="no_disposisi"
                sortable="custom"
                label="No. Disposisi"
                width="150"
              >
              </el-table-column>
              <el-table-column
                prop="status"
                sortable="custom"
                label="Status"
                width="150"
              >
              </el-table-column>
              <el-table-column
                prop="tanggal_ubah"
                sortable="custom"
                label="Terakhir Diubah"
                width="170"
              >
                <template #default="scope">
                  {{
                    new Date(scope.row.tanggal_ubah).toLocaleString("id-ID", {
                      dateStyle: "full",
                      timeStyle: "short",
                    })
                  }}
                </template>
              </el-table-column>
              <el-table-column
                prop="asal"
                label="Asal"
                sortable="custom"
                width="150"
              ></el-table-column>
              <el-table-column
                sortable="custom"
               prop="tujuan" label="Tujuan" width="160">
              </el-table-column>
              <el-table-column label="Isi ringkas" prop="isi" width="250">
                <template #default="scope">
                  {{ truncateString(scope.row.isi) }}
                </template>
              </el-table-column>
              <el-table-column fixed="right" label="Aksi" width="200">
                <template #default="scope">
                  <el-button
                    size="mini"
                    @click="handleDetailTable(scope.row.id)"
                    >Detail</el-button
                  >
                  <el-popconfirm
                    title="Apakah anda yakin menghapus disposisi ini?"
                    confirmButtonText="Iya"
                    cancelButtonText="Tidak"
                    @confirm="handleDeleteDisposisi(scope.row.id)"
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
                    disposisi yang anda cari, silahkan mencari dengan kata kunci
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
        <el-button
          style="width: 100%"
          @click="handleDetailTable()"
          type="primary"
          >Lihat Detail</el-button
        >
        <el-popconfirm
          title="Apakah anda yakin menghapus disposisi ini?"
          confirmButtonText="Iya"
          cancelButtonText="Tidak"
          @confirm="handleDeleteDisposisi()"
        >
          <template #reference>
            <el-button style="width: 100%" type="danger">Hapus</el-button>
          </template>
        </el-popconfirm>
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

import { Failed, DeleteFilled, List } from "@element-plus/icons";

import { truncateString } from "@shared/HelperFunction";
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
    q_search: { type: String, default: "" },
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
    const search = ref(props.q_search);
    const query = reactive({
      currentPage: 1,
      search: "",
      sort: "",
      filterQuery: props.q_filter,
    });
    //=== Mobile =====
    const tableDataMobile = ref(props.tableData);
    const actionDialogVisible = ref(false);
    const actionIdSelected = ref();
    const loadingTableMobile = ref(false);
    const limitTableMobile = ref(false);
    const mq = useMq();

    function handlePageClick(current) {
      query.currentPage = current;
      if (cancelTokenPage.value !== "") {
        cancelTokenPage.value.cancel();
      }
      Inertia.get(
        route("manage.disposisi.index"),
        {
          page: query.currentPage,
          search: search.value,
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
    function handleMobileSearch(value) {
      search.value = value;
      handleSearch();
    }
    function handleSearch() {
      Inertia.get(
        route("manage.disposisi.index"),
        { search: search.value, ...query.filterQuery, sort: query.sort },
        {
          onCancelToken: (cancelToken) => (cancelTokenPage.value = cancelToken),
          onSuccess: () => {},
        }
      );
    }

    function handleDeleteDisposisi(id = "") {
      if (id === "") {
        id = actionIdSelected.value;
      }
      actionDialogVisible.value = false;
      Inertia.delete(route("manage.disposisi.destroy", { disposisi: id }));
    }
    function handleDetailTable(id = "") {
      if (id === "") {
        id = actionIdSelected.value;
      }
      actionDialogVisible.value = false;
      Inertia.get(route("manage.disposisi.show", { disposisi: id }));
    }
    function handleAddDisposisi() {
      Inertia.get(route("manage.disposisi.create"));
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
        route("manage.disposisi.index"),
        {
          page: query.currentPage,
          search: search.value,
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
      Inertia.get(route("manage.disposisi.index"), {
        search: search.value,
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
      Inertia.get(route("manage.disposisi.index"), {
        search: search.value,
        ...query.filterQuery,
        sort: query.sort,
      });
    }

    function handleActionMobileState(row, column, event) {
      //   console.log(row, column, event);
      actionIdSelected.value = row.id;
      actionDialogVisible.value = true;
      console.log(actionDialogVisible.value, actionIdSelected.value);
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
        .get(route("manage.disposisi.index.json"), {
          params: {
            search: search.value,
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
      handlePageClick,
      handleAddDisposisi,
      handleDeleteDisposisi,
      handleDetailTable,
      handleMobileSearch,
      handleSearch,
      handleFilterSubmitted,
      handleFilterDeleted,
      handleSortTable,
      handleActionMobileState,
      handleInfiniteScroll,
      truncateString,
      actionDialogVisible,
      actionIdSelected,
      search,
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
