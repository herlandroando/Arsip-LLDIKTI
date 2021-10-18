export default [
    {
        label: "Tenggat Waktu", query: "tenggat_waktu", type: 'date', options: {
            hasBetween: true
        }
    },
    {
        label: "Status", query: "status", type: 'select', options: {
            isMultiple: true,
            list: [],
            url: { name: "manage.disposisi.option" },
            schema: { label: "status", value: "status" }
        }
    },
    {
        label: "Asal Disposisi", query: "asal",  type: 'ac', options: {
            isMultiple: true,
            list: [],
            url: { name: "manage.disposisi.option" },
            schema: { label: "nama", value: "id" }
        }
    },
    {
        label: "Tujuan", query: "tujuan", type: 'ac', permitted: ['r_all_disposisi'], options: {
            isMultiple: true,
            list: [],
            url: { name: "manage.disposisi.option" },
            schema: { label: "nama", value: "id" }
        }
    },
]
