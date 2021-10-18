export default [
    {
        label: "Tanggal Surat", query: "tanggal_surat", type: 'date', options: {
            hasBetween: true
        }
    },
    {
        label: "Sifat Surat", query: "sifat", type: 'select', options: {
            isMultiple: true,
            list: [],
            url: { name: "manage.send.option" },
            schema: { label: "nama", value: "id" }
        }
    },
    {
        label: "Pembuat", query: "pembuat", type: 'ac', options: {
            isMultiple: true,
            list: [],
            url: { name: "manage.send.option" },
            schema: { label: "nama_pembuat", value: "nama_pembuat" }
        }
    },
    {
        label: "Bagian", query: "asal_surat", type: 'ac', options: {
            isMultiple: true,
            list: [],
            url: { name: "manage.send.option" },
            schema: { label: "nama", value: "id" }
        }
    },
]
