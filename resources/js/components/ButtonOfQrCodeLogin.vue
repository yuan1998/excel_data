<template>
    <div>
        <div v-if="!data">
            错误
        </div>
        <el-button v-else-if="data.login_status === 0" size="mini" @click="handleQrCodeLogin">
            未登录
        </el-button>
        <el-button v-else size="mini" @click="handleLoginCheck">
            已登录
        </el-button>


    </div>
</template>

<script>
    import axios from 'axios';

    export default {
        name   : "button-qr-code-login",
        props  : {
            data: Object,
        },
        mounted() {
            console.log('this.data :', this.data);
        },
        data() {
            return {};
        },
        methods: {
            handleQrCodeLogin() {
                this.$bus.$emit('qrcode-model-show', this.data);
            },
            async handleLoginCheck() {
                swal.fire({
                    title            : '',
                    html             : `
                            <div class="save_loading">
                                <svg viewBox="0 0 140 140" width="140" height="140"><g class="outline"><path d="m 70 28 a 1 1 0 0 0 0 84 a 1 1 0 0 0 0 -84" stroke="rgba(0,0,0,0.1)" stroke-width="4" fill="none" stroke-linecap="round" stroke-linejoin="round"></path></g><g class="circle"><path d="m 70 28 a 1 1 0 0 0 0 84 a 1 1 0 0 0 0 -84" stroke="#71BBFF" stroke-width="4" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-dashoffset="200" stroke-dasharray="300"></path></g></svg>
                            </div>
                            <div>
                                <h4>请稍等...</h4>
                            </div>
                            `,
                    showConfirmButton: false,
                    allowOutsideClick: false
                });

                let res = await axios.get('/api/weibo/auth/isLogin', {
                    params: {
                        'account_id': this.data.id
                    }
                });

                if (res.status === 200) {
                    if (res.data.code === 0) {
                        Swal("登录状态正常", '', 'success');
                    } else {
                        Swal(res.data.msg, '', 'warning');
                    }
                } else {
                    Swal("请联系管理员", '系统错误', 'error');
                }


            }
        },
    }
</script>

<style scoped lang="less">

</style>
