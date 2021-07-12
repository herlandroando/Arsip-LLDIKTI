import { provide } from "vue"

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
    _backNav: {
        type: Object,
        default: () => { return { hasBack: false } }
    },
    // _toast: {
    //     type: Object,
    //     default: () => { return {} },
    // },
}

/**
 * Initialization view for inertia page. **Recommended** it must defined at first run / mounted the parent page component.
 *
 * @param {Object} props
 */
export function initializationView(props) {
    console.log(props);
    provide("title", props._title);
    provide("indexActive", props._index);
    provide("backNav", props._backNav);
}
