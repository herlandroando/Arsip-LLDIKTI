import lang from '@lang/id/validation';

export default {
    keterangan: [
        {
            type: "string",
            message: lang.string.type('Pesan/Keterangan'),
            trigger: "blur",
        },
        {
            max: 500,
            message: lang.string.maximum(500, "Pesan/Keterangan"),
            trigger: "blur",
        },
        {
            min: 2,
            message: lang.string.minimum(2, "Pesan/Keterangan"),
            trigger: "blur",
        },
    ],

}
