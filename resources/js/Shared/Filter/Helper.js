import { provide } from "@vue/runtime-core";
import _, { isArrayLikeObject } from "lodash";

/**
 * Config props for filter work properly
 */
export const defaultFilter = {
    "q_filter": {
        type: Object, default: () => { return {} }
    },
    "q_filter_tag": { type: Array, default: () => { return [] } }
}

/**
 * Initialization for filter work properly.
 *
 * @param {Object} props
 */
export function initializationFilter(props) {
    provide("list_tag", props.q_filter_tag);
    provide("list_query", props.q_filter);
}

export function assignFilter(v, filterQuery, filterOptions = []) {
    console.log("Filter Submitted", v, filterQuery, filterOptions)
    let singleOnly = false;
    let index = filterOptions.findIndex((x) => x.query === v.query.query);
    if (index === -1) {
        console.error("Index not found on filter.", index, " on ", filterOptions);
        return;
    }
    if (filterOptions[index].type === 'date') {
        singleOnly = true;
    }

    if (v.query in filterQuery && !singleOnly) {
        assignChildrenFilter(v.value, filterQuery[v.query.query])
    } else {
        filterQuery[v.query.query] = v.value;
    }
    console.log("update filter", filterQuery, "value", v.value, v);

}

function assignChildrenFilter(v, q) {
    if (Array.isArray(q)) {
        console.log("q is Array")
        q = q.concat(v);
        console.log(q);
    }
    else {
        q = v;
    }
}

