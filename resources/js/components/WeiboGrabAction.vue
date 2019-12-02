<template>
    <div style="display: inline-block;">
        <el-button type="primary" size="mini" @click="handleOpen">手动抓取微博表单</el-button>

        <el-dialog title="手动抓取微博表单"
                   ref="dialog"
                   width="550px"
                   :visible.sync="dialogFormVisible"
                   :before-close="handleClose">
            <el-form ref="form"
                     :model="form"
                     :rules="rules"
                     :label-width="formLabelWidth">
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
    import Swal        from 'sweetalert2';
    import moment      from 'moment';
    import axios       from 'axios';
    import { cloneOf } from "../utils/parse";

    export default {
        name   : "weibo-grab-action",
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
                    dates: [],
                },
                rules            : {
                    dates: [
                        { required: true, message: '请选择日期', trigger: 'change' }
                    ],
                },
            };
        },
        methods: {
            handleOpen() {
                this.dialogFormVisible = true;
            },
            closeDialog() {
                this.resetForm();
                this.dialogFormVisible = false;
                this.loading           = false;
            },
            resetForm() {
                this.$refs.form.resetFields();
            },
            handleSubmit() {
                this.$refs.form.validate(async (valid) => {
                    if (valid) {
                        let data     = cloneOf(this.form);
                        data.dates   = [
                            moment(data.dates[ 0 ]).format('YYYY-MM-DD'),
                            moment(data.dates[ 1 ]).format('YYYY-MM-DD'),
                        ];
                        this.loading = true;

                        try {
                            let res = await axios.request({
                                url   : '/api/weibo/grabFormData',
                                method: 'post',
                                data  : data,
                            });
                            console.log('res :', res);
                            if (res.status === 200) {
                                Swal.fire({
                                    title            : res.data.message,
                                    type             : 'success',
                                    timer            : 2000,
                                    showConfirmButton: false,
                                    onClose(modalElement) {
                                        $.admin.reload();
                                    }
                                });
                                this.closeDialog();
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
            }
        },

        watch: {
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

<style scoped lang="less">

</style>
