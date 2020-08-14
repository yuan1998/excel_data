<template>
    <div>
        <el-dialog
                :title="itemData ? itemData.name : '生成授权URL'"
                :visible.sync="dialogVisible"
                width="50%"
                :before-close="handleCloseBefore">
            <div>
                <div v-if="qrLoading">
                    二维码请求中
                </div>
                <div v-else-if="qrUrl">
                    <img :src="qrUrl" alt="" class="mc-img">
                    <p>
                        请扫描二维码
                    </p>
                </div>
                <div v-else-if="qrError">

                </div>

            </div>

        </el-dialog>
    </div>
</template>

<script>
    import axios from 'axios';

    export default {
        name   : "model-generate-weibo-qr-code",
        data() {
            return {
                itemData     : null,
                dialogVisible: false,
                qrLoading    : false,
                qrError      : '',
                qrData       : null,
                qrUrl        : '',
            };
        },
        mounted() {
            this.$bus.$on('qrcode-model-show', (item) => {
                this.$set(this, 'itemData', item);
                this.generateQrCode();
                console.log('item :', item);
            })
        },
        methods: {
            async checkSanQrCode() {
                if (this.qrData) {
                    let res = await axios.get('/api/weibo/auth/scanQrCode', {
                        params: {
                            'qrid'      : this.qrData.qrid,
                            'account_id': this.itemData.id,
                        }
                    });

                    if (res.status === 200) {
                        if (res.data.code === 0) {
                            let data = res.data.data;
                            switch (data.retcode) {
                                case 50114001:
                                    console.log('未扫描');
                                    setTimeout(this.checkSanQrCode.bind(this), 3000);
                                    break;
                                case 50114002:
                                    console.log('成功扫描，请在手机点击确认以登录');
                                    setTimeout(this.checkSanQrCode.bind(this), 3000);
                                    break;
                                case 20000000:
                                    console.log('登录成功');
                                    this.handleScanLogin(data.data.alt);
                                    break;
                            }
                        } else {
                            Swal(res.data.msg, '', 'warning');
                        }
                    } else {
                        Swal("请联系管理员", '系统错误', 'error');
                    }
                    console.log("checkSanQrCode res", res);

                }
            },
            async handleScanLogin(alt) {
                let res = await axios.get('/api/weibo/auth/loginQrCode', {
                    params: {
                        'alt'       : alt,
                        'account_id': this.itemData.id,
                    }
                });

                if (res.status === 200) {
                    if (res.data.code === 0) {
                        Swal("登录成功!", '', 'success');
                    } else {
                        Swal(res.data.msg, '', 'error');
                    }

                } else {
                    Swal("请联系管理员", '系统错误', 'error');
                }

            },
            async generateQrCode() {
                this.qrError = '';

                this.qrLoading = true;
                let res        = await axios.get('/api/weibo/auth/qrcode', {
                    params: {
                        'account_id': this.itemData.id,
                    }
                });

                this.qrLoading = false;
                if (res.status === 200) {
                    let data = res.data;
                    if (data.code === 0) {
                        this.qrData = data = data.data;
                        this.qrUrl  = data.image;
                        setTimeout(this.checkSanQrCode.bind(this), 5000);
                    } else {
                        this.qrError = data.msg;
                        Swal(data.msg, '', 'error');
                    }
                } else {
                    console.log(res);
                    this.qrError = '发生未知错误,请联系管理员';
                    Swal("请联系管理员", '系统错误', 'error');
                }

                console.log(res);
            },
            handleCloseBefore(done) {
                this.$confirm('确认关闭？')
                    .then(_ => {
                        done();
                        this.handleClose();
                    })
                    .catch(_ => {
                    });
            },
            handleClose() {
                this.url     = '';
                this.qrError = '';

                this.qrUrl = '';
                this.$set(this, 'qrData', null);
                this.$set(this, 'itemData', null);
            }
        },
        watch  : {
            itemData(val) {
                this.dialogVisible = !!val;
            }
        },
    }
</script>

<style scoped lang="less">

</style>
