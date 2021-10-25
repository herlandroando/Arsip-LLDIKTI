import lang from '@lang/id/validation';

export default {
    username: [
        {
            type: "string",
            required: true,
            message: lang.string.required("Username"),
            trigger: "blur",
        },
        {
            max: 70,
            message: lang.string.maximum(100, "Username"),
            trigger: "blur",
        },
        {
            min: 3,
            message: lang.string.minimum(3, "Username"),
            trigger: "blur",
        },
        {
            validator(rule, value) {
                return /^[0-9A-Za-z.\-_]+$/.test(value);
            },
            message: "Format username tidak sesuai.",
            trigger: "blur",
        }
    ],
    nama: [
        {
            type: "string",
            required: true,
            message: lang.string.required("Nama"),
            trigger: "blur",
        },
        {
            max: 255,
            message: lang.string.maximum(100, "Nama"),
            trigger: "blur",
        },
    ],
    nip: [
        {
            type: "string",
            required: true,
            message: lang.string.required("NIP"),
            trigger: "blur",
        },
        {
            len: 18,
            message: lang.string.length(18, "NIP"),
            trigger: "blur",
        },
    ],
    id_jabatan: [
        {
            required: true,
            message: lang.string.required('Jabatan'),
            trigger: "change",
        },
    ],
    no_telepon: [
        {
            required: true,
            message: lang.string.required('No. Telepon'),
            trigger: "blur",
        },
        {
            max: 18,
            message: lang.string.maximum(18, "No. Telepon"),
            trigger: "blur",
        },
        {
            min: 10,
            message: lang.string.minimum(10, "No. Telepon"),
            trigger: "blur",
        },
    ],
    password: [
        {
            type: "string",
            required: true,
            message: lang.string.required("Password"),
            trigger: "blur",
        },
        {
            max: 50,
            message: lang.string.maximum(100, "Password"),
            trigger: "blur",
        },
        {
            min: 8,
            message: lang.string.minimum(8, "Password"),
            trigger: "blur",
        },
    ],
    cpassword: [
        {
            type: "string",
            required: true,
            message: lang.string.required("Konfirmasi Password"),
            trigger: "blur",
        },
        {
            max: 50,
            message: lang.string.maximum(100, "Konfirmasi Password"),
            trigger: "blur",
        },
        {
            min: 8,
            message: lang.string.minimum(8, "Konfirmasi Password"),
            trigger: "blur",
        },
    ],

}
