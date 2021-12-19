import _ from "lodash"


/**
 * Format bytes as human-readable text.
 *
 * @param bytes Number of bytes.
 * @param si True to use metric (SI) units, aka powers of 1000. False to use
 *           binary (IEC), aka powers of 1024.
 * @param dp Number of decimal places to display.
 *
 * @return Formatted string.
 */
export function humanFileSize(bytes, si = false, dp = 1) {
    const thresh = si ? 1000 : 1024;

    if (Math.abs(bytes) < thresh) {
        return bytes + " B";
    }

    const units = si
        ? ["kB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB"]
        : ["KiB", "MiB", "GiB", "TiB", "PiB", "EiB", "ZiB", "YiB"];
    let u = -1;
    const r = 10 ** dp;

    do {
        bytes /= thresh;
        ++u;
    } while (
        Math.round(Math.abs(bytes) * r) / r >= thresh &&
        u < units.length - 1
    );

    return bytes.toFixed(dp) + " " + units[u];
}

/**
 * Classification file like image, document, and unknown type.
 *
 * @param {string} ext Extension of file.
 */
export function classificationFileType(ext = "") {
    const type = {
        gambar: ["png", "jpg", "jpeg", "gif", "bmp"],
        dokumen: ["docx", "doc", "txt", "odt"],
        dokumenPdf: ["pdf"]
    }

    //Not support IE
    for (let [key, value] of Object.entries(type)) {
        if (value.find((element) => element == ext) !== undefined) {
            return _.startCase(key)
        }
    }
    return "Tidak Diketahui"
}

/**
 * If time was now and before today it will false (If true it will disabled). It was helper for
 * limited date for Element Plus's Date form.
 *
 * @param {Date} time
 * @returns
 */
export function dateNowAndBefore(time) {
    return time.getTime() > Date.now();
}

/**
 * If time was now and after today it will false (If true it will disabled). It was helper for
 * limited date for Element Plus's Date form.
 *
 * @param {Date} time
 * @returns
 */
export function dateNowAndAfter(time) {
    return time.getTime() < Date.now();
}

/**
 * Truncate string if get maximum character or it will stop on first dot. Default maximum
 * character is 25 character.
 */
export function truncateString(string, max = 25) {
    return _.truncate(string, {
        'length': max,
        'separator': /[.]/
    });
}

export function dateToString(format = "", withTime = true) {
    let date = new Date(format);
    let option = { dateStyle: "full" }
    if (withTime) {
        option["timeStyle"] = "short"
    }
    return date.toLocaleString("id-ID", option)
}

export function getXSRF() {
    let name = 'XSRF-TOKEN' + "=";
    let ca = document.cookie.split(';');
    for (let i = 0; i < ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length).replace('%3D','=');
        }
    }
    return "";
}


// console.log(humanFileSize(1551859712)); // 1.4 GiB
// console.log(humanFileSize(5000, true)); // 5.0 kB
// console.log(humanFileSize(5000, false)); // 4.9 KiB
// console.log(humanFileSize(-10000000000000000000000000000)); // -8271.8 YiB
// console.log(humanFileSize(999949, true)); // 999.9 kB
// console.log(humanFileSize(999950, true)); // 1.0 MB
// console.log(humanFileSize(999950, true, 2)); // 999.95 kB
// console.log(humanFileSize(999500, true, 0)); // 1 MB
