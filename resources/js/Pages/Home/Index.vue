<template>
  <layout>
    <edit style="width: 1em; height: 1em; margin-right: 8px" />

    <el-row v-if="useMq().smMinus" class="dashboard-mobile-container">
      <div class="dashboard-container">
        <div
          v-for="data in dashboardData"
          :key="data.key"
          :class="'sm-data-container shadow'"
        >
          <el-row type="flex" align="middle">
            <el-col
              class="counter-container"
              :offset="1"
              :span="6"
              :style="'background-color:' + data.color"
            >
              <img :src="routes('home') + data.icon" alt="" />
            </el-col>
            <el-col :span="17">
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
        <div class="sm-data-container shadow">
          <el-row type="flex" align="middle">
            <el-col
              class="counter-container"
              :offset="1"
              :span="6"
              :style="'background-color:' + data.color"
            >
              <img :src="routes('home') + data.icon" alt="" />
            </el-col>
            <el-col :span="17">
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
              <el-row
                class="data-child-container"
                @click="handleClickDisposisi(data.id)"
              >
                <table
                  style="width: 100%; table-layout: fixed; overflow: hidden"
                >
                  <tr v-for="v in data.value" :key="v.id">
                    <th
                      style="
                        width: 120px;
                        vertical-align: top;
                        white-space: pre-wrap;
                      "
                    >
                      {{ v.label }}
                    </th>
                    <td
                      style="
                        text-align: center;
                        width: 30px;
                        vertical-align: top;
                        white-space: pre-wrap;
                      "
                    >
                      :
                    </td>
                    <td style="vertical-align: top; white-space: pre-wrap">
                      {{ v.value }}
                    </td>
                  </tr>
                </table>
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
              <el-col :span="12" :md="12">
                <h4>Surat Terbaru</h4>
              </el-col>
              <!-- <el-col :span="3"> </el-col> -->
              <el-col :span="10" style="padding-top: 5px; text-align: right">
                <el-button-group>
                  <el-tooltip
                    content="Surat Masuk"
                    placement="top"
                    effect="light"
                  >
                    <el-button
                      @click="handleModeChange('inbox')"
                      :type="
                        newActivitySurat.mode == 'inbox' ? 'primary' : 'default'
                      "
                      icon="el-icon-download"
                    ></el-button>
                  </el-tooltip>
                  <el-tooltip
                    content="Surat Keluar"
                    placement="top"
                    effect="light"
                  >
                    <el-button
                      @click="handleModeChange('send')"
                      :type="
                        newActivitySurat.mode == 'send' ? 'primary' : 'default'
                      "
                      icon="el-icon-upload2"
                    ></el-button>
                  </el-tooltip>
                </el-button-group>
              </el-col>
            </el-row>
          </div>
          <div style="margin-bottom: 60px"></div>

          <template v-if="newActivitySurat.data.length > 0">
            <el-row
              class="data-child-container"
              v-for="data in newActivitySurat.data"
              :key="data.id"
              @click="handleClickMail(data.id)"
            >
              <table style="width: 100%; table-layout: fixed; overflow: hidden">
                <tr v-for="v in data.value" :key="v.id">
                  <th
                    style="
                      width: 120px;
                      vertical-align: top;
                      white-space: pre-wrap;
                    "
                  >
                    {{ v.label }}
                  </th>
                  <td
                    style="
                      text-align: center;
                      width: 30px;
                      vertical-align: top;
                      white-space: pre-wrap;
                    "
                  >
                    :
                  </td>
                  <td style="vertical-align: top; white-space: pre-wrap">
                    {{ v.value }}
                  </td>
                </tr>
              </table>
            </el-row>
            <hr />
          </template>
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
import {
  defaultProps,
  getPermission,
  initializationView,
  isPermitted,
} from "@shared/InertiaConfig.js";
import Layout from "@shared/Layout";
import { computed, reactive, ref } from "@vue/reactivity";
import {
  CircleCloseFilled,
  Download,
  Upload,
  DocumentChecked,
} from "@element-plus/icons";
import { useMq } from "vue3-mq";
import { inject, onMounted } from "@vue/runtime-core";
import { Inertia } from "@inertiajs/inertia";
import { ElNotification } from "element-plus";
import { dateToString } from "@shared/HelperFunction";

export default {
  components: { Layout, CircleCloseFilled, Download, Upload, DocumentChecked },

  props: {
    ...defaultProps,
    suratMasukBaru: { type: Array, default: () => [] },
    suratKeluarBaru: { type: Array, default: () => [] },
    disposisiAktif: { type: Array, default: () => [] },
    totalDisposisiAktif: { type: Number, default: 0 },
    totalSuratMasuk: { type: Number, default: 0 },
    totalSuratKeluar: { type: Number, default: 0 },
  },
  setup(props) {
    initializationView(props);
    const dashboardData = ref([
      {
        name: "Total Surat Masuk",
        value: 0,
        // icon: `download`,
        icon: "/images/icons/icon-sm.svg",
        color: "#538AEC",
        key: "datad-1",
      },
      {
        name: "Total Surat Keluar",
        value: 0,
        icon: "/images/icons/icon-sk.svg",
        // icon: `upload`,
        color: "#38CD56",
        key: "datad-2",
      },
      {
        name: "Total Disposisi Aktif",
        value: 0,
        icon: `document-checked`,
        icon: "/images/icons/icon-dp.svg",
        color: "#F17041",
        key: "datad-3",
      },
    ]);
    const disposisiData = ref([]);
    const newActivityDisposisi = reactive({
      data: [],
      max: 10,
    });
    const newActivitySurat = reactive({
      // optionTime : [
      //   {code:"0d",label:"Hari Ini"},"1d","7d;
      // ]
      mode: "inbox",
      data: [],
      max: 10,
    });

    onMounted(() => {
      dashboardData.value[0].value = props.totalSuratMasuk;
      dashboardData.value[1].value = props.totalSuratKeluar;
      dashboardData.value[2].value = props.totalDisposisiAktif;
      newActivityDisposisi.data = props.disposisiAktif.map((val) => {
        console.log(val);
        return {
          id: val.id,
          value: [
            {
              id: "a" + val.id,
              label: "No. Disposisi",
              value: val.no_disposisi,
            },
            { id: "b" + val.id, label: "Status", value: val.status },
            {
              id: "c" + val.id,
              label: "Terakhir Diubah",
              value: dateToString(val.updated_at),
            },
            {
              id: "d" + val.id,
              label: "Batas Waktu",
              value: dateToString(val.expired_at),
            },
          ],
        };
      });
      changeDataMode();
    });

    function changeDataMode() {
      if (newActivitySurat.mode == "inbox")
        newActivitySurat.data = props.suratMasukBaru.map((val) => {
          return {
            id: val.id,
            value: [
              { id: "a" + val.id, label: "No. Surat", value: val.no_surat },
              {
                id: "b" + val.id,
                label: "No. Agenda",
                value: val.no_surat,
              },
              {
                id: "c" + val.id,
                label: "Perihal",
                value: val.perihal,
              },
              {
                id: "d" + val.id,
                label: "Tanggal Surat",
                value: dateToString(val.tanggal_surat),
              },
              {
                id: "e" + val.id,
                label: "Tanggal Buat",
                value: dateToString(val.created_at),
              },
            ],
          };
        });
      else {
        newActivitySurat.data = props.suratKeluarBaru.map((val) => {
          return {
            id: val.id,
            value: [
              { id: "a" + val.id, label: "No. Surat", value: val.no_surat },
              {
                id: "b" + val.id,
                label: "Perihal",
                value: val.perihal,
              },
              {
                id: "c" + val.id,
                label: "Tanggal Surat",
                value: dateToString(val.tanggal_surat),
              },
              {
                id: "d" + val.id,
                label: "Tanggal Buat",
                value: dateToString(val.created_at),
              },
            ],
          };
        });
      }
    }
    function handleModeChange(mode) {
      newActivitySurat.mode = mode;
      changeDataMode();
    }

    function handleClickDisposisi(id) {
      if (!isPermitted(props, ["r_surat"])) {
        ElNotification.error({
          title: "Tidak Diijinkan",
          message: "Anda perlu ijin untuk membaca surat/disposisi.",
        });
      }
      Inertia.get(route("manage.disposisi.show", { disposisi: id }));
    }
    function handleClickMail(id) {
      if (!isPermitted(props, ["r_surat"])) {
        ElNotification.error({
          title: "Tidak Diijinkan",
          message: "Anda perlu ijin untuk membaca surat/disposisi.",
        });
      }
      if (newActivitySurat.mode == "inbox")
        Inertia.get(route(`manage.inbox.show`, { surat_masuk: id }));
      else {
        Inertia.get(route(`manage.send.show`, { surat_keluar: id }));
      }
    }
    return {
      dashboardData,
      disposisiData,
      newActivitySurat,
      newActivityDisposisi,
      handleModeChange,
      handleClickDisposisi,
      handleClickMail,
      isPermitted,
      useMq,
      dateToString,
    };
  },
};
</script>

<style scoped>
.grid-container {
  display: grid;
  grid-column-gap: 50px;
  grid-row-gap: 50px;
  grid-template-columns: auto auto auto;
}
.counter-container {
  height: 70px;
  border-radius: 10px;
}
.counter-container img {
  height: auto;
  width: 40%;
  margin-top: 18px;
}

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
  cursor: pointer;
}
.data-child-container:hover {
  background-color: var(--bg-primary);
  color: white;
}

hr {
  margin: 0px 5px;
}

@media only screen and (max-width: 992px) {
  .counter-container img {
    height: auto;
    width: 50%;
    margin-top: 15px;
  }
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
