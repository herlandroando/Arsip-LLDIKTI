export default [
    {
        label: "Jabatan", query: "jabatan", type: 'select', options: {
            isMultiple: true,
            list: [],
            url: { name: "setting.users.option" },
            schema: { label: "nama", value: "id" }
        }
    },
]
