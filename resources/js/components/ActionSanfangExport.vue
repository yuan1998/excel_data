<template>
    <div :style="'display: inline-block;'" style="display: none;">
        <div id="app-export-data-action" class="dib">
            <el-button type="primary" size="mini" @click="handleOpen">导出三方数据</el-button>

            <el-dialog title="创建导出三方数据任务"
                       ref="dialog"
                       width="550px"
                       :visible.sync="dialogFormVisible"
                       :before-close="handleClose">
                <el-form ref="form"
                         :model="form"
                         :rules="rules"
                         label-position="top"
                         :label-width="formLabelWidth">
                    <p>
                        <i class="el-icon-warning-outline"></i>
                        导出数据日期范围越大,所消耗的时间也会将会相对增加
                    </p>
                    <el-form-item label="时间范围" prop="dates">
                        <el-col :span="11">
                            <el-date-picker
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
                    <el-button @click="closeDialog">取 消</el-button>
                    <el-button type="primary" @click="handleSubmit">确 定</el-button>
                </div>
            </el-dialog>
        </div>
    </div>
</template>

<script>
    import Swal        from 'sweetalert2';
    import moment      from 'moment';
    import axios       from 'axios';
    import { cloneOf } from "../utils/parse";


    export default {
        name   : 'action-sanfang-export',
        props  : {
            formTypeList: {
                type    : Object,
                required: true,
            },
        },
        data() {
            return {
                loadingInstance  : null,
                dialogFormVisible: false,
                loading          : false,
                formLabelWidth   : '80px',
                rules            : {
                    dates: [
                        { required: true, message: '请选择日期', trigger: 'change' }
                    ],
                },
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
                    dates: [],
                },
            }
        },
        methods: {
            handleOpen() {
                this.dialogFormVisible = true;
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
                this.loading           = false;
            },
            handleSubmit() {
                this.$refs.form.validate(async (valid) => {
                    if (valid) {
                        let data     = cloneOf(this.form);
                        data.dates   = data.dates.map((date) => moment(date).format('YYYY-MM-DD'));
                        this.loading = true;

                        console.log('data :', data);
                        try {
                            let res    = await axios.request({
                                url   : '/api/export/sanfang/excel',
                                method: 'post',
                                data,
                            });
                            let result = res.data;
                            if (result.code === 0) {
                                Swal.fire({
                                    title  : result.message,
                                    type   : 'success',
                                    // showConfirmButton: false,
                                    onClose: () => {
                                        this.closeDialog();
                                        $.admin.reload();
                                    }
                                });
                            } else {
                                Swal.fire({
                                    title            : result.message,
                                    type             : 'error',
                                    timer            : 0,
                                    showConfirmButton: false
                                })
                            }
                        } catch (e) {
                            this.loading = false;
                            if (e.response) {
                                Swal.fire({
                                    title            : e.response.data.message,
                                    type             : 'error',
                                    timer            : 0,
                                    showConfirmButton: false
                                })

                            }
                        }
                        this.loading = false;

                    }
                });
            },
        },
        watch  : {
            loading(v) {
                if (v) {
                    let dialogPanel      = this.$refs.dialog.$refs.dialog // dialog面板的dom节点
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

<style lang="less">
    .swal-container {
        z-index: 2500
    }

    .dib {
        display: inline-block;
    }

    .el-checkbox.is-bordered {
        margin-left: 0 !important;
    }

</style>
