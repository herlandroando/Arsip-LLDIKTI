require('./bootstrap');

import { computed, createApp, h, onMounted, reactive, ref } from 'vue'
import { createInertiaApp } from '@inertiajs/inertia-vue3'
import { Inertia } from "@inertiajs/inertia"
import { InertiaProgress } from '@inertiajs/progress'
import "../css/index.css"
import Layout from './Shared/Layout'
import ElementPlus from "element-plus"
import { ElNotification } from "element-plus"

// import { ZiggyVue } from 'ziggy';
// import { Ziggy } from './ziggy';


InertiaProgress.init()

// Vue.prototype.$route = route

createInertiaApp({
    resolve: name => require(`./Pages/${name}`),
    setup({ el, app, props, plugin }) {
        createApp({
            render: () => {
                console.log(props);
                return h(app, props)
            }
        })
            .use(plugin).use(ElementPlus).mixin({
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
            ).provide("csrf",
                ref(document.querySelector('meta[name="csrf-token"]').content)
            )
            .mount(el)
    },
})

Inertia.on('success', (event) => {
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
