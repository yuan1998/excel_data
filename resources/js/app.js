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
import RecallLog             from "./components/RecallLog";
import ActionExcelUpload     from "./components/ActionExcelUpload";
import WeiboGrabAction       from "./components/WeiboGrabAction";
import weiboIndex            from "./components/weibo-index";
import dataIndex             from "./components/data-index";
import mediaPage             from "./components/MediaPage";
import ActionRecheck         from "./components/ActionRecheck";
import ActionSanfangExport         from "./components/ActionSanfangExport";

Vue.use(ElementUI);
Vue.use(ElInputTag);
Vue.component('recall-log', RecallLog);
Vue.component('example-component', ExampleComponent);
Vue.component('export-data-form', ExportDataForm);
Vue.component('grab-data-form', GrabDataForm);
Vue.component('weibo-config-action', WeiConfigAction);
Vue.component('weibo-dispatch-settings', WeiboDispatchSettings);
Vue.component('action-excel-upload', ActionExcelUpload);
Vue.component('weibo-grab-action', WeiboGrabAction);
Vue.component('weibo-index', weiboIndex);
Vue.component('data-index', dataIndex);
Vue.component('media-page', mediaPage);
Vue.component('action-recheck', ActionRecheck);
Vue.component('action-sanfang-export', ActionSanfangExport);

