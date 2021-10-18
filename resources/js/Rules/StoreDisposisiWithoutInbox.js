import lang from '@lang/id/validation';

export default {
    tenggat_waktu: [
        {
            type: "date",
            required: true,
            message: lang.string.required("Tenggat Waktu"),
            trigger: "change",
        }
    ],
    no_disposisi: [
        {
            type: "string",
            required: true,
            message: lang.string.required("No. Disposisi"),
            trigger: "blur",
        },
        {
            max: 100,
            message: lang.string.maximum(100, "No. Disposisi"),
            trigger: "blur",
        },
    ],
    isi: [
        {
            required: true,
            type: "string",
            message: lang.string.type('Isi'),
            trigger: "blur",
        },
        {
            max: 500,
            message: lang.string.maximum(500, "Isi"),
            trigger: "blur",
        },
    ],
    tujuan: [
        {
            required: true,
            message: lang.string.required('Tujuan'),
            trigger: "change",
        },
    ],

}
