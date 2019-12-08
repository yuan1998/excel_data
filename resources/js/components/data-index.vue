<template>
    <div>
        <el-row>
            <el-col :span="6">
                <el-card style="text-align: center;">
                    <el-button round type="primary" @click="handleOpen">
                        上传数据
                    </el-button>
                </el-card>
            </el-col>
        </el-row>


        <el-dialog title="上传数据"
                   ref="dialog"
                   width="550px"
                   :visible.sync="dialogFormVisible"
                   :before-close="handleClose">
            <div class="upload-group">
                <el-upload
                        action="/"
                        class="upload-demo"
                        :http-request="handleRequest"
                        :show-file-list="false"
                        multiple
                        :file-list="fileList">
                    <i class="el-icon-upload"></i>
                    <div class="el-upload__text">将文件拖到此处，或<em>点击上传</em></div>
                </el-upload>
            </div>
            <div class="files-preview">
                <div v-for="(item,index) in files"
                     :index="index"
                     class="file-item">
                    <div class="file-name">
                        {{ item.name }}
                        <span class="file-type" v-if="item.status === 1">
                            {{ item.type }}
                        </span>
                    </div>
                    <div class="file-result" v-if="item.status !== 0">
                        <div class="error-result" v-if="item.status === 2">
                            {{ item.error }}
                        </div>
                        <div class="success-result" v-else>
                            上传成功,一共创建了 {{ item.count }} 条表单.
                        </div>
                    </div>
                    <div class="file-label">
                        <i class="el-icon-upload-success " :class="statusIcon[item.status]"></i>
                    </div>
                </div>
            </div>
        </el-dialog>

    </div>
</template>

<script>
    export default {
        name   : "data-index",
        data() {
            return {
                loadingInstance  : null,
                dialogFormVisible: false,
                loading          : false,
                formLabelWidth   : '80px',
                fileList         : [],
                files            : [],
                statusIcon       : {
                    0: 'el-icon-loading',
                    1: 'el-icon-check',
                    2: 'el-icon-close',
                }
            };
        },
        methods: {
            handleOpen() {
                this.dialogFormVisible = true;
            },
            resetForm() {
                this.$refs.form.resetFields();
            },
            handleClose(done) {
                this.$confirm('确认关闭？')
                    .then(_ => {
                        done();
                    })
                    .catch(_ => {
                    });
            },
            closeDialog() {
                this.resetForm();
                this.dialogFormVisible = false;
            },
            handleUpload(res) {
                //weibo_test.py 2019-12-07 2019-12-07 2000 17392449035 huamei2019 7165564518

                console.log('res :', res);
            },
            handleUploadError(res) {
                console.log('res :', res);
            },
            async handleRequest(request) {
                let formData = new FormData();
                let file     = request.file;
                formData.append('model', this.model);
                formData.append('excel', file);
                let item = {
                    name   : file.name,
                    // 0 : loading
                    // 1 : success
                    // 2 : error
                    status : 0,
                    message: '',
                    count  : 0,
                    type   : '',
                };

                this.files.push(item);

                try {
                    let res  = await axios.request({
                        url   : '/api/import/auto',
                        method: 'post',
                        data  : formData,
                    });
                    let data = res.data;
                    console.log('data :', data);
                    this.$set(item, 'type', data.type);
                    this.$set(item, 'count', data.count);
                    if (data.message) {
                        this.$set(item, 'status', 2);
                        this.$set(item, 'message', data.message);
                    } else {
                        this.$set(item, 'status', 1);
                    }
                    console.log('res :', res);
                } catch (e) {
                    // request.onError(e);
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
        },
    }
</script>

<style scoped lang="less">

    .upload-group {
        text-align: center;

        .upload-demo {
            display: inline-block;
            background-color: #fff;
            border: 1px dashed #d9d9d9;
            border-radius: 6px;
            box-sizing: border-box;
            width: 360px;
            height: 180px;
            text-align: center;
            cursor: pointer;
            position: relative;
            overflow: hidden;

            .el-icon-upload {
                font-size: 67px;
                color: #c0c4cc;
                margin: 40px 0 16px;
                line-height: 50px;
            }

            .el-upload__text {
                color: #606266;
                font-size: 14px;
                text-align: center;

                em {
                    color: #409eff;
                    font-style: normal;
                }
            }
        }
    }

    .files-preview {
        .file-item {
            overflow: hidden;
            z-index: 0;
            background-color: #fff;
            border: 1px solid #c0ccda;
            border-radius: 6px;
            box-sizing: border-box;
            margin-top: 15px;
            padding: 10px 20px;
            transition: all .5s cubic-bezier(.55, 0, .1, 1);
            font-size: 14px;
            color: #606266;
            line-height: 1.8;
            position: relative;

            .file-name {
                font-size: 16px;

                .file-type {
                    margin-left: 5px;
                    font-size: 12px;
                    vertical-align: baseline;
                    color: #909399;
                }

                margin-bottom: 10px;
            }

            .file-result {

                font-size: 13px;

                .success-result {
                    color: #67c23b;
                }

                .error-result {
                    color: #f66c6c;
                }

            }

            .file-label {
                position: absolute;
                right: -17px;
                top: -7px;
                width: 46px;
                height: 26px;
                background: #13ce66;
                text-align: center;
                transform: rotate(45deg);
                box-shadow: 0 1px 1px #ccc;

                .el-icon-upload-success {
                    font-size: 12px;
                    margin-top: 12px;
                    transform: rotate(-45deg);
                    color: #ffffff;
                }
            }

        }
    }


</style>
