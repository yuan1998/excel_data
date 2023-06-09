<template>
    <div style="display: inline-block;">
        <el-button type="primary" size="mini" @click="handleOpen">抓取数据</el-button>

        <el-dialog title="抓取Crm数据"
                   ref="dialog"
                   width="550px"
                   :visible.sync="dialogFormVisible"
                   :before-close="handleClose">
            <el-form ref="form"
                     :model="form"
                     :rules="rules"
                     :label-width="formLabelWidth">
                <el-form-item label="类型" prop="types" required>
                    <el-checkbox-group v-model="form.types" size="mini">
                        <el-checkbox-button :label="key"
                                            :key="key"
                                            v-for="(value , key) in types">
                            {{value}}
                        </el-checkbox-button>
                    </el-checkbox-group>
                </el-form-item>
                <el-form-item label="数据类型" prop="models" required>
                    <el-checkbox-group v-model="form.models" size="mini">
                        <el-checkbox-button v-for="(option , key) in models" :label="key" :key="key">
                            {{option}}
                        </el-checkbox-button>
                    </el-checkbox-group>
                </el-form-item>
                <el-form-item label="时间范围" required prop="dates">
                    <el-col :span="11">
                        <el-date-picker
                                size="mini"
                                v-model="form.dates"
                                type="daterange"
                                range-separator="至"
                                start-placeholder="开始日期"
                                end-placeholder="结束日期"
                                :picker-options="pickerOptions">
                        </el-date-picker>
                    </el-col>
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
                pickerOptions    : {
                    shortcuts: [
                        {
                            text: '昨天',
                            onClick(picker) {
                                const day = new Date();
                                day.setTime(day.getTime() - 3600 * 1000 * 24);
                                picker.$emit('pick', [ day, day ]);
                            }
                        },
                        {
                            text: '最近一周',
                            onClick(picker) {
                                const end   = new Date();
                                const start = new Date();
                                start.setTime(start.getTime() - 3600 * 1000 * 24 * 7);
                                picker.$emit('pick', [ start, end ]);
                            }
                        }
                    ]
                },
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
    .swal-container {
        z-index: 2500
    }
</style>
