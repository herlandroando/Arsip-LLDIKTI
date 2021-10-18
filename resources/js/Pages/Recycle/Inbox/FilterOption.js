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
            url: { name: "manage.inbox.option" },
            schema: { label: "nama", value: "id" }
        }
    },
    {
        label: "Nama Pembuat", query: "pembuat", type: 'ac', options: {
            isMultiple: true,
            list: [],
            url: { name: "manage.inbox.option" },
            schema: { label: "nama", value: "id" }
        }
    },
    {
        label: "Asal Surat", query: "asal_surat", type: 'ac', options: {
            isMultiple: true,
            list: [],
            url: { name: "manage.inbox.option" },
            schema: { label: "asal_surat", value: "asal_surat" }
        }
    },
]
