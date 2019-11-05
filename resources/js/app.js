/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */
import Vue                   from 'vue';
import ElementUI             from 'element-ui'
import ElInputTag            from 'el-input-tag'
import ExampleComponent      from "./components/ExampleComponent";
import ExportDataForm        from "./components/exportDataForm";
import WeiConfigAction       from "./components/WeiboConfigAction";
import GrabDataForm          from "./components/GrabDataForm";
import WeiboDispatchSettings from "./components/WeiboDispatchSettings";

Vue.use(ElementUI);
Vue.use(ElInputTag);
Vue.component('example-component', ExampleComponent);
Vue.component('export-data-form', ExportDataForm);
Vue.component('grab-data-form', GrabDataForm);
Vue.component('weibo-config-action', WeiConfigAction);
Vue.component('weibo-dispatch-settings', WeiboDispatchSettings);

// const app = new Vue({
//     el: '#app'
// });
