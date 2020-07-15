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
                        :before-upload="beforeUpload"
                        :on-change="handleUploadChange"
                        :show-file-list="false"
                        :auto-upload="false"
                        multiple
                        :file-list="fileList">
                    <i class="el-icon-upload"></i>
                    <div class="el-upload__text">将文件拖到此处，或<em>点击上传</em></div>
                </el-upload>
            </div>
            <div class="files-preview">
                <div v-for="(item,index) in files"
                     :index="index"
                     class="file-item"
                     :class="`item-status-${item.status}`">
                    <div class="file-name">
                        {{ item.name }}
                        <span class="file-type">
                            {{item.status === 2 ? '上传失败!' : (item.status === 1 ? item.type : '正在上传...') }}
                        </span>
                    </div>
                    <div class="file-result" v-if="item.status !== 0">
                        <div class="error-result" v-if="item.status === 2">
                            <p>创建数据失败! </p>
                            <p>
                                {{ item.message }}
                            </p>
                        </div>
                        <div class="success-result" v-else>
                            <p>创建数据成功!</p>
                            <p v-if="item.count" @click="showLog(item.successLog , '成功创建的数据')">
                                成功创建了 <span>{{ item.count }}</span> 条数据.<i
                                    class="el-icon-question"></i>
                            </p>
                            <p v-if="item.failCount" @click="showLog(item.failLog,'无法识别的数据')">
                                无法识别的数据有 <span>{{ item.failCount }}</span> 条数据. <i
                                    class="el-icon-question"></i>
                            </p>
                            <p v-if="item.invalidCount">
                                其中有 {{ item.invalidCount }} 条数据没有标识.
                            </p>

                        </div>
                    </div>
                    <div class="file-label">
                        <i class="el-icon-upload-success " :class="statusIcon[item.status]"></i>
                    </div>
                </div>
            </div>
        </el-dialog>
        <el-dialog
                :title="logDialog.name"
                :visible.sync="logDialogShow"
                width="50%">
            <div v-html="logDialog.msg"></div>
            <span slot="footer" class="dialog-footer">
                <el-button type="primary" @click="logDialogShow = false">确 定</el-button>
            </span>
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
                timecase         : null,
                logDialogShow    : false,
                logDialog        : {
                    name: '',
                    msg : '',
                },
                statusIcon       : {
                    0: 'el-icon-loading',
                    1: 'el-icon-check',
                    2: 'el-icon-error',
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
                        this.files = [];
                        $.admin.reload();
                    })
                    .catch(_ => {
                    });
            },
            closeDialog() {
                this.resetForm();
                this.dialogFormVisible = false;
            },
            async handleUpload() {
                let fileList = this.files.filter((item) => {
                    return item.status === 0;
                });

                for (let i = 0 ; i < fileList.length ; i++) {
                    let item = fileList[ i ];
                    console.log('item :', item);
                    await this.beforeUpload(item);
                }

            },
            handleUploadChange(file, fileList) {
                let item = {
                    file,
                    name   : file.name,
                    status : 0,
                    message: '',
                    count  : 0,
                    type   : '',
                };
                this.files.unshift(item);

                if (this.timecase !== null) {
                    clearTimeout(this.timecase);
                }

                this.timecase = setTimeout(() => {
                    this.handleUpload();
                }, 300);
            },
            async beforeUpload(item) {
                let file = item.file.raw;
                console.log('file :', file);
                let formData = new FormData();
                formData.append('excel', file);

                let res;
                try {
                    res = await this.handleUploadFile(formData);
                    this.setItemResponse(item, res.data);
                } catch (e) {

                    this.$set(item, 'status', 2);
                    this.$set(item, 'message', '发生意外情况,请联系管理员!');
                    if (e.response) {
                        console.log(e.response.data.message);
                        Swal.fire({
                            title            : e.response.data.message,
                            type             : 'error',
                            timer            : 0,
                            showConfirmButton: false
                        })
                    }
                }
                return res;
            },
            async handleUploadFile(formData) {
                return await axios.request({
                    url   : '/api/import/auto',
                    method: 'post',
                    data  : formData,
                });
            },
            setItemResponse(item, data) {

                console.log('data.code :', data.code);
                switch (data.code) {
                    case 0:
                        item.type        = data.type;
                        item.status      = 1;
                        item.log         = data.log;
                        let successCount = data.log[ 'success_log' ][ 'code_log' ].length;
                        let failCount    = Object.keys(data.log[ 'fail_log' ][ 'code_log' ]).length;
                        this.$set(item, 'failCount', failCount);
                        this.$set(item, 'failLog', data.log[ 'fail_log' ][ 'code_log' ]);
                        this.$set(item, 'invalidCount', data.log[ 'fail_log' ][ 'code_invalid' ]);
                        this.$set(item, 'successLog', data.log[ 'success_log' ][ 'code_log' ]);
                        item.count = successCount;
                        break;
                    case 10001:
                    case 10002:
                        item.status  = 2;
                        item.message = data.message;
                        break;
                    default :
                        item.status  = 2;
                        item.message = "发生意外情况,请联系管理员!";
                        break;
                }

            },
            showLog($log, name) {
                let msg = '';
                if (Array.isArray($log)) {
                    msg = $log.map((log) => {
                        return `<p>${ log }</p>`
                    }).join('');
                } else {
                    msg = Object.keys($log).map((logName) => {
                        return `<p> <strong>${ logName } :</strong> ${ $log[ logName ] }</p>`
                    }).join('');

                }

                if (msg) {
                    this.logDialogShow = true;
                    this.logDialog     = {
                        name,
                        msg,
                    }
                }

            }
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

            &.item-status-1 {
                border-color: #67c23b;

                .file-label {
                    background-color: #67c23b;
                }

            }

            &.item-status-2 {
                border-color: #f66c6c;

                .file-label {
                    background-color: #f66c6c;
                }
            }


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
                background: #909399;
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
