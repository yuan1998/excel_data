<template>
    <div>
        <div v-if="!item">
            错误
        </div>
        <el-popover
                v-else-if="item.login_status === 0"
                placement="bottom"
                title="登录方式"
                width="200"
                trigger="click">
            <div>
                <el-button size="mini" @click="handleAutoLogin">自动登录</el-button>
                <el-button size="mini" @click="handleQrCodeLogin">扫描登录</el-button>
                <el-button size="mini" @click="handleJobLogin">Job 登录</el-button>
            </div>
            <el-button size="mini" slot="reference">未登录</el-button>
        </el-popover>
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
            this.item = this.data;
        },
        data() {
            return {
                item: null
            };
        },
        methods: {
            async handleJobLogin() {
                try {
                    let res = await axios.get('/api/weibo/auth/jobLoginClient', {
                        params: {
                            'account_id': this.item.id
                        }
                    });
                    if (res.data.code === 0) {
                        Swal("登录成功,请稍后刷新页面", '', 'success');
                    } else {
                        Swal(res.data.msg, '', 'warning');
                    }

                } catch (e) {
                    Swal("请联系管理员", '系统错误', 'error');
                }
            },
            async handleAutoLogin() {
                this.showLoading();

                try {
                    let res = await axios.get('/api/weibo/auth/loginClient', {
                        params: {
                            'account_id': this.item.id
                        }
                    });
                    if (res.status === 200) {
                        if (res.data.code === 0) {
                            this.$set(this.item, 'login_status', 1);
                            Swal("登录成功", '', 'success');
                        } else {
                            this.$set(this.item, 'login_status', 0);
                            Swal(res.data.msg, '', 'warning');
                        }
                    } else {
                        this.$set(this.item, 'login_status', 0);
                        Swal("请联系管理员", '系统错误', 'error');
                    }
                } catch (e) {
                    this.$set(this.item, 'login_status', 0);
                    Swal("请联系管理员", '系统错误', 'error');
                    throw e;
                }
            },
            handleQrCodeLogin() {
                this.$bus.$emit('qrcode-model-show', this.item);
            },
            showLoading() {
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
            },
            async handleLoginCheck() {
                this.showLoading();

                let res = await axios.get('/api/weibo/auth/isLogin', {
                    params: {
                        'account_id': this.item.id
                    }
                });

                if (res.status === 200) {
                    if (res.data.code === 0) {
                        this.$set(this.item, 'login_status', 1);
                        Swal("登录状态正常", '', 'success');
                    } else {
                        this.$set(this.item, 'login_status', 0);
                        Swal(res.data.msg, '', 'warning');
                    }
                } else {
                    this.$set(this.item, 'login_status', 0);
                    Swal("请联系管理员", '系统错误', 'error');
                }


            }
        },
    }
</script>

<style scoped lang="less">

</style>
