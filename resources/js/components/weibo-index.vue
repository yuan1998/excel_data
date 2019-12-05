<template>
    <div>
        <h1>
            Weibo Index
        </h1>
    </div>
</template>

<script>
    export default {
        name   : "weibo-index",
        props  : {
            data: Array
        },
        data() {
            return {};
        },
        mounted() {
            console.log('this.data :', this.data);
        },
        methods: {},
    }
</script>

<style scoped lang="less">

</style>
