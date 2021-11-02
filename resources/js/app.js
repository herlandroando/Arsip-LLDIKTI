require('./bootstrap');

import { computed, createApp, h, onMounted, reactive, ref } from 'vue'
import { createInertiaApp } from '@inertiajs/inertia-vue3'
import { Inertia } from "@inertiajs/inertia"
import { InertiaProgress } from '@inertiajs/progress'
import { Vue3Mq } from "vue3-mq";
import Layout from './Shared/Layout'
import ElementPlus from "element-plus"
import { ElNotification } from "element-plus"

// import "../css/index.css"

// import { ZiggyVue } from 'ziggy';
// import { Ziggy } from './ziggy';


InertiaProgress.init({
    // The delay after which the progress bar will
    // appear during navigation, in milliseconds.
    delay: 250,

    // The color of the progress bar.
    color: '#fff',

    // Whether to include the default NProgress styles.
    includeCSS: true,

    // Whether the NProgress spinner will be shown.
    showSpinner: false,
})

const optionResponsive = {
    xs: 0,
    sm: 768,
    md: 992,
    lg: 1200,
    xl: 1920,
}


// Vue.prototype.$route = route

createInertiaApp({
    resolve: name => require(`./Pages/${name}`),
    setup({ el, app, props, plugin }) {
        createApp({
            render: () => {
                // onMounted(() => {
                console.log("Toast", props.initialPage.props._toast);
                if (props.initialPage.props._toast instanceof Object) {
                    let toast = props.initialPage.props._toast;
                    ElNotification({
                        type: toast.type,
                        title: toast.title,
                        message: toast.message,
                    });
                }
                //   //   startCompleted.value = true;
                //   });
                console.log();
                return h(app, props)
            }
        })
            .use(plugin).use(ElementPlus).use(Vue3Mq, { breakpoints: optionResponsive }).mixin({
                methods: {
                    routes: (name, param = {}) => {
                        console.log("name_url", name);
                        try {
                            return route(name, param);
                        } catch (error) {
                            console.log(error);

                            return "";
                        }
                    },
                    isMobile: () => {
                        console.log(screen.width);
                        if (screen.width <= 760) {
                            return true
                        } else {
                            return false
                        }
                    }
                }

            }).provide("sidebars",
                _sidebars
            ).provide("sifatSurat",
                _sifatSurat
            ).provide("bagianInstansi",
                _bagianInstansi
            )
            .mount(el)
    },
})


Inertia.on('success', (event) => {
    console.log("inertia request success", event);
    toast(event.detail.page.props._toast)
})

axios.interceptors.request.use(function (config) {
    return config
}, function (error) {
    console.log(error);
    return Promise.reject(error);
});


function toast(toast) {
    if (toast instanceof Object)
        ElNotification({
            type: toast.type,
            title: toast.title,
            message: toast.message,
        });
}

