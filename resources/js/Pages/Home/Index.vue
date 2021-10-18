<template>
  <layout>
    <edit style="width: 1em; height: 1em; margin-right: 8px" />

    <el-row v-if="useMq().smMinus" class="dashboard-mobile-container">
      <div class="dashboard-container">
        <div
          v-for="data in dashboardData"
          :key="data.key"
          class="sm-data-container shadow bg-secondary"
        >
          <el-row type="flex" align="middle">
            <el-col :span="6">
              <component :is="data.icon" />
            </el-col>
            <el-col :span="18">
              <el-row type="flex" align="middle">
                <el-col> {{ data.name }} </el-col>
                <el-col>
                  <h2>{{ data.value }}</h2>
                </el-col>
              </el-row>
            </el-col>
          </el-row>
        </div>
      </div>
    </el-row>
    <el-row v-else :gutter="20" class="dashboard-container">
      <el-col v-for="data in dashboardData" :key="data.key" :span="8">
        <div class="sm-data-container shadow bg-secondary">
          <el-row type="flex" align="middle">
            <el-col :span="6">
              <component :is="data.icon" />
            </el-col>
            <el-col :span="18">
              <el-row type="flex" align="middle">
                <el-col> {{ data.name }} </el-col>
                <el-col>
                  <h2>{{ data.value }}</h2>
                </el-col>
              </el-row>
            </el-col>
          </el-row>
        </div>
      </el-col>
    </el-row>

    <el-row
      type="flex"
      :gutter="40"
      justify="center"
      class="dashboard-container"
    >
      <el-col :span="24" :md="10">
        <div class="data-container shadow">
          <div class="data-header">
            <h4>Aktifitas Disposisi Terbaru</h4>
          </div>
          <div style="margin-bottom: 60px"></div>
          <template v-if="newActivityDisposisi.data.length > 0">
            <template v-for="data in newActivityDisposisi.data" :key="data.id">
              <el-row class="data-child-container">
                <el-col><b>No. Disposisi</b> : {{ data.no_disposisi }}</el-col>
                <el-col :span="24"><b>Status</b> : {{ data.status }}</el-col>
                <el-col :span="12"
                  ><small
                    ><b>Tanggal Perubahan : </b><br />{{
                      data.updated_at
                    }}</small
                  ></el-col
                >
                <el-col :span="12"
                  ><small
                    ><b>Batas Disposisi : </b><br />{{ data.expired_at }}</small
                  ></el-col
                >
              </el-row>
              <hr />
            </template>
          </template>
          <el-result
            v-else
            title="Tidak Ada Aktifitas"
            subTitle="Disposisi yang aktif telah tuntas."
          >
            <template #icon>
              <el-svg-icon class="t-dark"><circle-close-filled /></el-svg-icon>
            </template>
            <!-- <template #extra>
              <el-button type="primary" size="medium">Back</el-button>
            </template> -->
          </el-result>
        </div>
      </el-col>
      <el-col :span="24" :md="14">
        <div class="data-container shadow">
          <div class="data-header">
            <el-row>
              <el-col :span="12">
                <h4>Surat Terbaru</h4>
              </el-col>
              <el-col :span="4"> </el-col>
              <el-col :span="6" style="padding-top: 5px">
                <el-button-group>
                  <el-tooltip
                    content="Surat Masuk"
                    placement="top"
                    effect="light"
                  >
                    <el-button icon="el-icon-download"></el-button>
                  </el-tooltip>
                  <el-tooltip
                    content="Surat Keluar"
                    placement="top"
                    effect="light"
                  >
                    <el-button icon="el-icon-upload2"></el-button>
                  </el-tooltip>
                </el-button-group>
              </el-col>
            </el-row>
          </div>
          <div style="margin-bottom: 60px"></div>
          <el-row
            v-if="newActivitySurat.data.length > 0"
            class="data-child-container"
          >
          </el-row>
          <el-result
            v-else
            title="Tidak Ada Surat Baru"
            subTitle="Belum ada surat yang masuk hari ini."
          >
            <template #icon>
              <el-svg-icon class="t-dark"><circle-close-filled /></el-svg-icon>
            </template>
            <!-- <template #extra>
              <el-button type="primary" size="medium">Back</el-button>
            </template> -->
          </el-result>
        </div>
      </el-col>
    </el-row>
  </layout>
</template>

<script>
import { defaultProps, initializationView } from "@shared/InertiaConfig.js";
import Layout from "@shared/Layout";
import { reactive, ref } from "@vue/reactivity";
import {
  CircleCloseFilled,
  Download,
  Upload,
  DocumentChecked,
} from "@element-plus/icons";
import { useMq } from "vue3-mq";

export default {
  components: { Layout, CircleCloseFilled, Download, Upload, DocumentChecked },

  props: {
    ...defaultProps,
  },
  setup(props) {
    initializationView(props);
    const dashboardData = ref([
      {
        name: "Total Surat Masuk",
        value: 0,
        icon: `download`,
        key: "datad-1",
      },
      {
        name: "Total Surat Keluar",
        value: 0,
        icon: `upload`,
        key: "datad-2",
      },
      {
        name: "Total Disposisi Aktif",
        value: 0,
        icon: `document-checked`,
        key: "datad-3",
      },
    ]);
    const disposisiData = ref([
      //   {
      //     id: 1,
      //     no_disposisi: "3232",
      //     updated_at: new Date("01/09/2021").toLocaleString(),
      //     expired_at: new Date("01/10/2021").toLocaleString(),
      //     status: "Sedang Proses",
      //   },
      //   {
      //     id: 2,
      //     no_disposisi: "3212",
      //     updated_at: new Date("01/09/2021").toLocaleString(),
      //     expired_at: new Date("02/10/2021").toLocaleString(),
      //     status: "Berhasil",
      //   },
      //   {
      //     id: 3,
      //     no_disposisi: "3212",
      //     updated_at: new Date("01/09/2021").toLocaleString(),
      //     expired_at: new Date("02/10/2021").toLocaleString(),
      //     status: "Berhasil",
      //   },
      //   {
      //     id: 4,
      //     no_disposisi: "3212",
      //     updated_at: new Date("01/09/2021").toLocaleString(),
      //     expired_at: new Date("02/10/2021").toLocaleString(),
      //     status: "Berhasil",
      //   },
    ]);
    const newActivityDisposisi = reactive({
      data: [],
      max: 7,
    });
    const newActivitySurat = reactive({
      // optionTime : [
      //   {code:"0d",label:"Hari Ini"},"1d","7d;
      // ]
      mode: "inbox",
      data: [],
      max: 7,
    });
    return {
      dashboardData,
      disposisiData,
      newActivitySurat,
      newActivityDisposisi,
      useMq,
    };
  },
};
</script>

<style scoped>
.dashboard-container {
  /* padding: 20px 10px; */
  margin-bottom: 40px;
  text-align: center;
  border-radius: 4px;
}

.sm-data-container {
  min-height: 34px;
  /* margin: 0px 10px; */
  padding: 20px 10px;
  background-color: white;
  border-radius: 4px;
  /* color: black; */
  text-align: center;
}

.data-container {
  max-height: 500px;
  min-height: 500px;
  z-index: 2;
  overflow-y: auto;
  overflow-x: hidden;
  position: relative;
  /* margin: 0px 10px; */
  padding: 10px 20px;
  background-color: white;
  border-radius: 4px;
  color: black;
  margin-bottom: 30px;
  /* width: 80%; */
  text-align: left;
}
.data-header {
  position: sticky;
  width: 110%;
  background-color: #fff;
  z-index: 1;
  top: -10px;
  margin-left: -20px;
  float: left;
  padding: 0px 10px 20px 20px;
  max-height: 45px;
  border-bottom: 2px solid var(--bg-dark);
}
.data-child-container {
  padding: 20px 20px;
  margin: 5px -20px;
  transition: all 250ms;
}
.data-child-container:hover {
  background-color: var(--bg-primary);
  color: white;
}

hr {
  margin: 0px 5px;
}

@media only screen and (max-width: 992px) {
  .dashboard-mobile-container {
    position: relative;
  }
  .dashboard-container {
    overflow-x: auto;
    white-space: nowrap;
    margin-bottom: 40px;
    text-align: center;
    border-radius: 4px;
  }
  .sm-data-container {
    margin-right: 30px;
    width: 250px;
    display: inline-block;
  }
}
</style>
