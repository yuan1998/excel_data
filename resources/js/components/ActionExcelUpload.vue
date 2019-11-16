<template>
    <div style="display: inline-block;">
        <el-button type="primary" size="mini" @click="handleOpen">导入数据</el-button>

        <el-dialog title="导入数据"
                   ref="dialog"
                   width="550px"
                   :visible.sync="dialogFormVisible"
                   :before-close="handleClose">
            <div>
                <el-radio-group v-model="model" size="mini">
                    <el-radio-button :label="key"
                                     :key="key"
                                     v-for="(value , key) in models">
                        {{ value }}
                    </el-radio-button>
                </el-radio-group>
            </div>
            <div class="upload-group">
                <h5 class="title">
                    {{ model ? `上传 ${models[model]} 文件.` : '请选择模型Type.' }}
                </h5>
                <el-upload
                        action="/"
                        class="upload-demo"
                        :http-request="handleRequest"
                        :on-preview="handlePreview"
                        multiple
                        :file-list="fileList">
                    <el-button :disabled="!model" size="small" type="primary">点击上传</el-button>
                </el-upload>
            </div>
        </el-dialog>
    </div>
</template>

<script>
    import Swal   from 'sweetalert2';
    import moment from 'moment';
    import axios  from 'axios';

    export default {
        name   : "grab-data-form",
        props  : {
            models: Object,
        },
        data() {
            return {
                dialogFormVisible: false,
                loading          : false,
                fileList         : [],
                model            : '',
            };
        },
        methods: {
            handleUpload(res) {

                console.log('res :', res);
            },
            handleUploadError(res) {
                console.log('res :', res);
            },
            async handleRequest(request) {
                let formData = new FormData();
                formData.append('model', this.model);
                formData.append('excel', request.file);

                try {
                    let res = await axios.request({
                        url             : '/api/import/formExcel',
                        method          : 'post',
                        data            : formData,
                        onUploadProgress: progressEvent => {
                            const complete = (progressEvent.loaded / progressEvent.total * 100 | 0)
                            request.onProgress({ percent: complete })
                        },
                    });
                    console.log('res :', res);

                    if (res.status === 200) {
                        request.onSuccess(res);
                        let count = res.data.count;
                        this.$notify({
                            title  : '成功',
                            message: '上传成功!一共创建了' + count + '条',
                            type   : 'success'
                        });
                    }
                } catch (e) {
                    request.onError(e);
                    if (e.response) {
                        Swal.fire({
                            title            : e.response.data.message,
                            type             : 'error',
                            timer            : 0,
                            showConfirmButton: false
                        })

                    }
                }
            },
            handleOpen() {
                this.dialogFormVisible = true;
            },
            handleClose(done) {
                this.$confirm('确认关闭？')
                    .then(_ => {
                        done();
                        $.admin.reload();
                    })
                    .catch(_ => {
                    });
            },
            closeDialog() {
                this.resetForm();
                this.dialogFormVisible = false;
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
    }
</script>
<style>
    .el-upload__input {
        display: none !important;
    }

</style>

<style scoped lang="less">
    .swal-container {
        z-index: 2500
    }


    .upload-group {
        position: relative;
    }
</style>
