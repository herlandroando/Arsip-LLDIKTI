import _, { isBoolean } from "lodash";
import { provide, inject } from "vue"
import { reactive } from "@vue/reactivity"

/**
 * Default props that will out from serverside.
 * @var {Object} defaultProps
 */
export const defaultProps = {
    _title: {
        type: String,
        default: "title_not_defined"
    },
    _index: {
        type: String,
        default: ""
    },
    _showTitle: {
        type: Boolean,
        default: true
    },
    _backNav: {
        type: Object,
        default: () => { return { hasBack: false } }
    },
    _toast: {
        type: Object,
        default: () => { return {} },
    },
    _user: {
        type: Object,
        default: () => { return {} },
    }
}

/**
 * Initialization view for inertia page. **Recommended** it must defined at first run / on mounted the parent page component.
 *
 * @param {Object} props
 */
export function initializationView(props) {
    let permission = {
        admin: false,
        r_surat: false,
        w_all_surat: false,
        r_all_disposisi: false,
        r_laporan: false,
        w_suratkeluar: false,
        w_disposisi: false,
        w_suratmasuk: false,
        d_surat: false,
        dp_surat: false,
        d_miliksurat: false,
        super_admin: false,
    };
    // console.log(props);
    provide("title", props._title);
    provide("user", props._user);
    // console.log(props);
    if (!_.isEmpty(props._user) && !_.isEmpty(props._user.ijin)) {
        let value = props._user.ijin;
        permission.super_admin = value.super_admin;
        permission.admin = value.admin;
        permission.r_surat = value.r_surat;
        permission.r_all_disposisi = value.r_surat;
        permission.r_laporan = value.r_laporan;
        permission.w_suratkeluar = value.w_suratkeluar;
        permission.w_suratmasuk = value.w_suratmasuk;
        permission.w_disposisi = value.w_disposisi;
        permission.d_surat = value.d_surat;
        permission.dp_surat = value.dp_surat;
        permission.w_all_surat = value.w_all_surat;
        permission.d_miliksurat = value.d_miliksurat;
    }
    provide("permission", permission);

    // console.log(props._user, "user")
    provide("indexActive", props._index);
    provide("backNav", props._backNav);
    provide("showTitle", props._showTitle);
    document.title = props._title;
}

export function getPermission(props) {
    let permission = {
        admin: false,
        r_surat: false,
        r_all_disposisi: false,
        r_laporan: false,
        w_suratkeluar: false,
        w_suratmasuk: false,
        w_all_surat: false,
        w_disposisi: false,
        d_surat: false,
        dp_surat: false,
        d_miliksurat: false,
        super_admin: false,
    };
    if (!_.isEmpty(props._user) && !_.isEmpty(props._user.ijin)) {
        let value = props._user.ijin;
        permission.super_admin = value.super_admin;
        permission.admin = value.admin;
        permission.r_surat = value.r_surat;
        permission.r_all_disposisi = value.r_surat;
        permission.r_laporan = value.r_laporan;
        permission.w_suratkeluar = value.w_suratkeluar;
        permission.w_disposisi = value.w_disposisi;
        permission.w_suratmasuk = value.w_suratmasuk;
        permission.d_surat = value.d_surat;
        permission.dp_surat = value.dp_surat;
        permission.d_miliksurat = value.d_miliksurat;
        permission.w_all_surat = value.w_all_surat;
    }

    return permission;
}

export function isPermitted(props = false, permit = []) {
    let permission = {};
    if (isBoolean(props)) {
        permission = inject("permission", {});
    }
    else {
        if (!_.isEmpty(props._user) && !_.isEmpty(props._user.ijin)) {
            let value = props._user.ijin;
            permission.admin = value.admin;
            permission.r_surat = value.r_surat;
            permission.r_disposisi = value.r_disposisi;
            permission.r_all_disposisi = value.r_surat;
            permission.r_laporan = value.r_laporan;
            permission.w_suratkeluar = value.w_suratkeluar;
            permission.w_disposisi = value.w_disposisi;
            permission.w_suratmasuk = value.w_suratmasuk;
            permission.d_surat = value.d_surat;
            permission.dp_surat = value.dp_surat;
            permission.d_miliksurat = value.d_miliksurat;
            permission.super_admin = value.super_admin;
            permission.w_all_surat = value.w_all_surat;
        }
    }

    if (Array.isArray(permit) && permit.length > 0) {
        for (const element of permit) {
            if (element in permission) {
                if (!permission[element]) return false;
            } else {
                console.log("Permitted", false, permit);
                return false;
            }
        }
    }
    console.log("Permitted", true, permit);
    return true;

}
