<?php

namespace App\Http\Middleware;

use App\Models\PengaturanUmum;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     * -------------------------------------------------
     * Nama template halaman HTML yang akan dikirimkan ketika pertama kali mengakses.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     * ------------------------------------
     * Memberikan versi aset Inertia.
     *
     * @see https://inertiajs.com/asset-versioning
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    public function version(Request $request)
    {
        return parent::version($request);
    }

    /**
     * Defines the props that are shared by default.
     * --------------------------------------------
     * Mengirimkan atribut yang akan di bagikan ke sisi klien
     * setiap permintaan yang diminta di sisi klien
     *
     * @see https://inertiajs.com/shared-data
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function share(Request $request)
    {
        $user_result = [];
        // Mengambil versi terakhir sistem
        $version_timestamp = PengaturanUmum::where("nama", "update_terakhir")->first();
        // Mengambil pengaturan sistem
        $settings = PengaturanUmum::where("nama", "!=", "update_terakhir")->get()->makeHidden("id");
        $user = $request->user();
        if (!empty($user)) {
            // Mendefinisikan data pengguna dan ijin-ijinnya
            $ijin = $user->jabatan->ijin->toArray();
            $ijin["super_admin"] = $user->id == 1;
            $user_result = [
                "id" => $user->id,
                "nama" => $user->nama,
                "username" => $user->username,
                "id_jabatan" => $user->id_jabatan,
                "jabatan" => $user->jabatan->nama,
                "ijin" => $ijin,
            ];
        }
        // Mendefinisikan data pengguna dan ijin-ijinnya yang akan dikirimkan ke sisi klien
        return array_merge(parent::share($request), [
            "_last_updated" => $version_timestamp->nilai ?? null,
            "_settings" => $settings,
            "_user" => empty($user_result) ? "" : $user_result,
        ]);
    }
}

// contentsHome: [{
//     id: 0,
//     name: "Dashboard",
//     icon: "ri-dashboard-fill",
//     hasChildren: false,
//     url: Path.contentsHome.home.index,
// },
// {
//     id: 1,
//     name: "Kelola Surat",
//     icon: "ri-mail-settings-fill",
//     hasChildren: true,
//     showChildren: false,
//     children: [
//         {
//             id: 0,
//             name: "Surat Masuk",
//             title: "Kelola Surat Masuk",
//             icon: "ri-mail-download-fill",
//             url: Path.contentsHome.manageinbox.index,
//             hasChildren: false,
//         },
//         {
//             id: 1,
//             name: "Surat Keluar",
//             title: "Kelola Surat Keluar",
//             icon: "ri-mail-send-fill",
//             url: Path.contentsHome.managesent.index,
//             hasChildren: false,
//         },
//     ],
// },
// {
//     id: 2,
//     name: "Laporan Surat",
//     icon: "ri-archive-fill",
//     hasChildren: true,
//     showChildren: false,
//     children: [
//         {
//             id: 0,
//             name: "Surat Masuk",
//             title: "Laporan Surat Masuk",
//             icon: "ri-inbox-archive-fill",
//             url: Path.contentsHome.reportinbox.index,
//             hasChildren: false,
//         },
//         {
//             id: 1,
//             name: "Surat Keluar",
//             title: "Laporan Surat Keluar",
//             icon: "ri-inbox-unarchive-fill",
//             url: Path.contentsHome.reportsent.index,
//             hasChildren: false,
//         },
//     ],
// },
// {
//     id: 3,
//     name: "Tempat Sampah",
//     title: "Tempat Sampah",
//     icon: "ri-delete-bin-5-fill",
//     hasChildren: false,
//     url: Path.contentsHome.trash.index,
// },
// {
//     id: 4,
//     name: "Pengaturan",
//     icon: "ri-settings-5-fill",
//     hasChildren: true,
//     showChildren: false,
//     children: [
//         {
//             id: 0,
//             name: "Data Pegawai",
//             title: "Pengaturan Data Pegawai",
//             icon: "ri-user-settings-fill",
//             url: Path.contentsHome.employees.index,
//             hasChildren: false,
//         },
//         {
//             id: 1,
//             name: "Perijinan",
//             title: "Pengaturan Perijinan",
//             icon: "ri-git-repository-private-fill",
//             url: Path.contentsHome.permissions.index,
//             hasChildren: false,
//         },
//         {
//             id: 2,
//             name: "Lanjutan",
//             title: "Pengaturan Lanjutan",
//             icon: "ri-settings-6-fill",
//             url: Path.contentsHome.advanced.index,
//             hasChildren: false,
//         },
//     ],
// },
// {
//     id: 5,
//     name: "Logout",
//     icon: "ri-logout-circle-r-fill",
//     hasChildren: false,
//     customColor: 'error',
//     url: "/logout",
// },],
