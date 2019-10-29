/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */
import Vue              from 'vue';
import ElementUI        from 'element-ui'
import ExampleComponent from "./components/ExampleComponent";
import ExportDataForm   from "./components/exportDataForm";
import GrabDataForm     from "./components/GrabDataForm";

Vue.use(ElementUI);
Vue.component('example-component', ExampleComponent);
Vue.component('export-data-form', ExportDataForm);
Vue.component('grab-data-form', GrabDataForm);

// const app = new Vue({
//     el: '#app'
// });
