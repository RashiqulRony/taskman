/*
 |--------------------------------------------------------------------------
 | Laravel Spark Bootstrap
 |--------------------------------------------------------------------------
 |
 | First, we will load all of the "core" dependencies for Spark which are
 | libraries such as Vue and jQuery. This also loads the Spark helpers
 | for things such as HTTP calls, forms, and form validation errors.
 |
 | Next, we'll create the root Vue application for Spark. This will start
 | the entire application and attach it to the DOM. Of course, you may
 | customize this script as you desire and load your own components.
 |
 */

require("spark-bootstrap");
require("./components/bootstrap");
window.Vue = require("vue");

// import plugin
import VueToastr from "vue-toastr";
// use plugin
Vue.use(VueToastr, {
    /* OverWrite Plugin Options if you need */
    defaultTimeout: 3000,
    defaultProgressBar: false,
    defaultProgressBarValue: 0,
    defaultClassNames: ["animated", "zoomInUp"]
});

let invoice = require("./components/invoice.vue");

let projects = require("./views/project/index.vue");

import NavbarCommon from "./views/projectDashboard/ProjectNavbar/NavbarCommon";

require("./bootstrap");

import router from "./routes";

import helpers from './helper';

Vue.use({
    install() {
        Vue.helpers = helpers;
        Vue.prototype.$helpers = helpers;
    }
});

var app = new Vue({
    router,
    mixins: [require("spark")],
    components: {
        invoice,
        projects,
        navbarcommon: NavbarCommon

    },
    data() {
        return {
            is_nav_: 1,
        }
    },
    watch:{
        '$route.name' : function (name) {
            if (name === 'Project' || name === 'Profile' || name === 'Notification') {
                this.is_nav_ = 0;
            } else {
                this.is_nav_ = 1;
            }
        }
    },
    mounted() {
        var router_name = this.$route.name;
        if (router_name === 'Project' || router_name === 'Profile' || router_name === 'Notification' ) {
            this.is_nav_ = 0;
        }
    },

})
    .$mount('#project');
