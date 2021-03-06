import lang from '@lang/id/validation';

export default {
    perihal: [
        {
            type: "string",
            required: true,
            message: lang.string.required("Perihal"),
            trigger: "blur",
        },
        {
            max: 254,
            message: lang.string.maximum(254, "Perihal"),
            trigger: "blur",
        },
    ],
    pembuat: [
        {
            required: true,
            message: lang.string.required("Nama Pembuat Surat"),
            trigger: "blur",
        }
    ],
    tujuan: [
        {
            type: "string",
            required: true,
            message: lang.string.required("Tujuan"),
            trigger: "blur",
        },
        {
            max: 254,
            message: lang.string.maximum(254, "Tujuan"),
            trigger: "blur",
        },
    ],
    tanggal_surat: [
        {
            type: "date",
            required: true,
            message: lang.string.required("Tanggal Surat"),
            trigger: "change",
        },
    ],
    asal_surat: [
        {
            required: true,
            message: lang.string.required("Asal Surat"),
            trigger: "change",
        },
    ],
    isi_ringkas: [
        {
            type: "string",
            message: lang.string.type('Isi Ringkas'),
            trigger: "blur",
        },
        {
            max: 500,
            message: lang.string.maximum(500, "Perihal"),
            trigger: "blur",
        },
    ],
    id_sifat: [
        {
            required: true,
            message: lang.string.required('Isi Ringkas'),
            trigger: "change",
        },
    ],
    no_surat: [
        {
            required: true,
            message: lang.string.required('Nomor Surat'),
            trigger: "blur",
        },
        {
            max: 254,
            message: lang.string.maximum(254, "Nomor Surat"),
            trigger: "blur",
        },
    ],
    no_agenda: [
        {
            required: true,
            message: lang.string.required('Nomor Surat'),
            trigger: "blur",
        },
        {
            max: 254,
            message: lang.string.maximum(254, "Nomor Surat"),
            trigger: "blur",
        },
    ]
}
