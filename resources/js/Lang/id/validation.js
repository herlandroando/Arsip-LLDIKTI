export default {
    string: {
        maximum: (max, label) => `${label} telah melebihi batas maksimal ${max} karakter.`,
        minimum: (min, label) => `${label} kurang dari batas minimal ${min} karakter.`,
        length: (exact, label) => `${label} harus berisi ${exact} karakter.`,
        range: (min, max, label) => `${label} harus berisi ${min} - ${max} karakter.`,
        required: (label) => `${label} diperlukan.`,
        type: (label) => `${label} harus berbentuk teks.`
    },
    integer: {
        maximum: (max, label) => `${label} telah melebihi batas maksimal dari nilai ${max}.`,
        minimum: (min, label) => `${label} kurang dari batas minimal dari nilai ${min}.`,
        // length: (exact, label) => `${label} harus berisi ${exact} karakter.`,
        range: (min, max, label) => `${label} harus berisi dari nilai ${min} hingga ${max}.`,
        required: (label) => `${label} diperlukan.`,
        type: (label) => `${label} harus berbentuk nilai angka real.`
    },
    date: {
        format: (format = "YYYY/MM/DD", label) => `Tanggal pada ${label} harus berformat seperti ${format}.`
    }
}
