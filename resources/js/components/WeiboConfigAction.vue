<template>
    <div style="display: inline-block;">
        <el-button type="primary" size="mini" @click="handleOpen">自动分配设置</el-button>

        <el-dialog title="自动分配设置"
                   ref="dialog"
                   width="550px"
                   :visible.sync="dialogFormVisible"
                   :before-close="handleClose">
            <el-form ref="form"
                     label-position="top"
                     :model="form">
                <el-form-item label="时间段停用">
                    <el-switch
                            v-model="form.stop_open"
                            active-text="开启"
                            inactive-text="关闭">
                    </el-switch>
                    <el-row v-if="form.stop_open">
                        <el-col :span="12">
                            <el-time-picker
                                    v-model="form.stop_open_start"
                                    placeholder="任意时间点">
                            </el-time-picker>
                        </el-col>
                        <el-col :span="12">
                            <el-time-picker
                                    v-model="form.stop_open_end"
                                    placeholder="任意时间点">
                            </el-time-picker>
                        </el-col>
                    </el-row>
                </el-form-item>
                <el-form-item label="手动开关" required prop="dispatch_start">
                    <el-switch
                            v-model="form.dispatch_start"
                            active-text="开启"
                            inactive-text="关闭">
                    </el-switch>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button size="mini" @click="closeDialog">取 消</el-button>
                <el-button size="mini" type="primary" @click="handleSubmit">确 定</el-button>
            </div>
        </el-dialog>
    </div>
</template>

<script>
    import Swal   from 'sweetalert2';
    import moment from 'moment';

    export default {
        name   : "grab-data-form",
        props  : {
            formData: Object,
        },
        data() {
            return {
                loadingInstance  : null,
                dialogFormVisible: false,
                loading          : false,

                form: {
                    stop_open      : false,
                    dispatch_start : false,
                    stop_open_start: '',
                    stop_open_end  : '',
                },
            };
        },
        mounted() {
            let data             = this.formData;
            console.log('this.formDatas :', this.formData);
            data.stop_open_start = moment(data.stop_open_start, 'HH:mm:ss');
            data.stop_open_end   = moment(data.stop_open_end, 'HH:mm:ss');
            this.$set(this  ,'form' , data );

        },
        methods: {
            handleOpen() {
                this.dialogFormVisible = true;
            },
            actionThen(then) {
                if (then.action === 'refresh') {
                    $.admin.reload();
                }
                if (then.action === 'download') {
                    window.open(then.value, '_blank');
                }
                if (then.action === 'redirect') {
                    $.admin.redirect(then.value);
                }
                if (then.action === 'location') {
                    window.location = then.value;
                }
            },
            handleClose(done) {
                this.$confirm('确认关闭？')
                    .then(_ => {
                        done();
                    })
                    .catch(_ => {
                    });
            },
            resetForm() {
                this.$refs.form.resetFields();
            },
            closeDialog() {
                this.dialogFormVisible = false;
            },
            responseResolver(res) {
                if (typeof res !== 'object') {
                    this.$alert('这个错误', '错误', {
                        confirmButtonText: '确定',
                    });
                    return;
                }
                this.loading = false;
                this.closeDialog();

                if (typeof res.toastr === 'object') {
                    let content = res.toastr.content;
                    Swal.fire({
                        title            : content,
                        type             : 'success',
                        timer            : 2000,
                        showConfirmButton: false
                    })
                }

                this.actionThen(res.then);
            },
            handleSubmit() {
                this.$refs.form.validate((valid) => {
                    if (valid) {
                        let data             = Object.assign({}, this.form, {
                            _token : $.admin.token,
                            _action: 'App_Admin_Actions_WeiboConfigAction',
                        });
                        data.stop_open_start = moment(data.stop_open_start).format('HH:mm:ss');
                        data.stop_open_end   = moment(data.stop_open_end).format('HH:mm:ss');

                        console.log('data :', data);

                        this.loading = true;
                        new Promise((resolve, reject) => {
                            $.ajax({
                                method : 'POST',
                                url    : '/admin/_handle_action_',
                                data   : data,
                                success: function (data) {
                                    resolve(data);
                                },
                                error  : function (request) {
                                    reject(request);
                                }
                            });
                        }).then((res) => {
                            this.responseResolver(res);
                        }).catch((err) => {
                            this.actionCatcher(err);
                        });
                    }
                });
            },
            actionCatcher(res) {
                if (res && typeof res.responseJSON === 'object') {
                    let content = res.responseJSON.message;
                    Swal.fire({
                        title            : content,
                        type             : 'error',
                        timer            : 2000,
                        showConfirmButton: false
                    })
                }
            },
        },
        watch  : {
            loading(v) {
                if (v) {
                    let dialogPanel      = this.$refs.dialog.$refs.dialog; // dialog面板的dom节点
                    this.loadingInstance = this.$loading({
                        target: dialogPanel
                    })
                } else if (this.loadingInstance) {
                    this.loadingInstance.close()
                }
            }
        }
    }
</script>

<style scoped lang="less">

</style>
