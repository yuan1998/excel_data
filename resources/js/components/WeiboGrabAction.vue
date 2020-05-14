<template>
    <div style="display: inline-block;margin-left: 15px;">
        <el-button style="margin-right: 10px;"
                   type="primary"
                   size="mini"
                   @click="handleOpen">
            手动抓取微博表单
        </el-button>
        <el-button @click="handleSyncAccount"
                   size="mini"
                   type="success">
            同步账户信息
        </el-button>
        <el-dialog title="手动抓取微博表单"
                   ref="dialog"
                   width="550px"
                   :visible.sync="dialogFormVisible"
                   :before-close="handleClose">
            <el-form ref="form"
                     :model="form"
                     :rules="rules"
                     :label-width="formLabelWidth">
                <el-form-item label="账户" required prop="account_id">
                    <el-radio-group v-model="form.account_id">
                        <el-radio v-for="item  in accounts"
                                  :key="item.id"
                                  border
                                  :label="item.id">
                            {{ item.username }} ({{ item.name }})
                        </el-radio>
                    </el-radio-group>

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
    import Swal        from 'sweetalert2';
    import moment      from 'moment';
    import axios       from 'axios';
    import { cloneOf } from "../utils/parse";

    export default {
        name   : "weibo-grab-action",
        props  : {
            accounts: Array,
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
                    dates     : [],
                    account_id: '',
                },
                rules            : {
                    dates     : [
                        { required: true, message: '请选择日期', trigger: 'change' }
                    ],
                    account_id: [
                        { required: true, message: '请选择账户', trigger: 'change' }
                    ]
                },
            };
        },
        mounted() {
            console.log('this.accounts :', this.accounts);
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
                                url   : '/api/weibo/pullFormData',
                                method: 'post',
                                data  : data,
                            });
                            console.log('res :', res);

                            let responseData = res.data;

                            if (responseData.status) {
                                Swal.fire({
                                    title            : responseData.msg,
                                    type             : 'success',
                                    timer            : 2000,
                                    showConfirmButton: false,
                                    onClose(modalElement) {
                                        $.admin.reload();
                                    }
                                });
                                this.closeDialog();
                            } else {
                                Swal.fire({
                                    title            : responseData.msg,
                                    type             : 'error',
                                    showConfirmButton: false,
                                });
                            }


                            // if (res.status === 200) {
                            //     Swal.fire({
                            //         title            : res.data.message,
                            //         type             : 'success',
                            //         timer            : 2000,
                            //         showConfirmButton: false,
                            //         onClose(modalElement) {
                            //             $.admin.reload();
                            //         }
                            //     });
                            //     this.closeDialog();
                            // }

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
            async handleSyncAccount() {
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

                try {
                    let res = await axios.post('/api/weibo/syncAccount');
                    if (res.status === 204) {
                        Swal.fire({
                            title: '同步成功!',
                            type : 'success',
                            onClose(modalElement) {
                                $.admin.reload();
                            }
                        });
                    } else {
                        Swal.fire(
                            '错误!',
                            '发生错误,请联系管理员!',
                            'error'
                        );
                    }
                } catch (e) {
                    Swal.fire(
                        '接口错误!',
                        '发生错误,请联系管理员!',
                        'error'
                    );
                }


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
