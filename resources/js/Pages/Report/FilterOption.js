export default [
    {
        label: "Tipe", query: "tipe", type: 'select',
        options: {
            isMultiple: true,
            list: [{ label: 'Laporan Surat Masuk', value: 'sm_report' }, { label: 'Laporan Surat Keluar', value: 'sk_report' }],
            schema: { label: "label", value: "value" }
        }
    },
    {
        label: "Rutinitas", query: "rutinitas", type: 'select', options: {
            isMultiple: true,
            list: [{ label: 'Harian', value: 'daily' }, { label: 'Bulanan', value: 'monthly' }],
            schema: { label: "label", value: "value" }
        }
    },
    {
        label: "Tanggal Buat", query: "tanggal_buat", type: 'date', options: {
            hasBetween: true
        }
    },
]
