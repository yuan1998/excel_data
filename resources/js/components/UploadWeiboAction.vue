<template>
    <div style="display: inline-block;">
        <el-button type="primary" size="mini" @click="handleOpen">上传数据</el-button>

        <el-dialog title="上传数据"
                   ref="dialog"
                   width="550px"
                   :visible.sync="dialogFormVisible"
                   :before-close="handleClose">
            <el-form ref="form"
                     :model="form"
                     :rules="rules"
                     :label-width="formLabelWidth">
                <el-form-item label="表单文件" prop="types" required>
                    <el-upload
                            class="upload-demo"
                            action="https://jsonplaceholder.typicode.com/posts/"
                            :on-preview="handlePreview"
                            :on-remove="handleRemove"
                            :before-remove="beforeRemove"
                            multiple
                            :limit="3"
                            :on-exceed="handleExceed"
                            :file-list="fileList">
                        <el-button size="small" type="primary">点击上传</el-button>
                        <div slot="tip" class="el-upload__tip">只能上传jpg/png文件，且不超过500kb</div>
                    </el-upload>
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
            types : Object,
            models: Object,
        },
        data() {
            return {
                loadingInstance  : null,
                dialogFormVisible: false,
                loading          : false,
                formLabelWidth   : '80px',
                form             : {
                    types : [],
                    models: [],
                    dates : [],
                },
                rules            : {
                    types: [
                        { required: true, message: '请选择需要抓取的类型', trigger: 'blur' }
                    ],
                    models   : [
                        { required: true, message: '请选择需要抓取的模型', trigger: 'blur' }
                    ],
                    dates        : [
                        { required: true, message: '请选择日期', trigger: 'change' }
                    ],
                },
            };
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
                this.resetForm();
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
                        let data     = Object.assign({}, this.form, {
                            _token : $.admin.token,
                            _action: 'App_Admin_Actions_CrmGrabData',
                        });
                        data.dates   = [
                            moment(data.dates[ 0 ]).format('YYYY-MM-DD'),
                            moment(data.dates[ 1 ]).format('YYYY-MM-DD'),
                        ];
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
        watch: {
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
