<template>
    <div id="app-export-data-action" :style="'display: inline-block;'" style="display: none;">
        <el-button type="primary" size="mini" @click="handleOpen">创建导出数据</el-button>

        <el-dialog title="创建导出数据"
                   ref="dialog"
                   width="550px"
                   :visible.sync="dialogFormVisible"
                   :before-close="handleClose">
            <el-form ref="form"
                     :model="form"
                     :rules="rules"
                     :label-width="formLabelWidth">
                <el-form-item label="类型" prop="type" required>
                    <el-radio-group v-model="form.type" size="mini">
                        <el-radio-button label="zx"
                        >
                            整形
                        </el-radio-button>
                        <el-radio-button label="kq"
                        >
                            口腔
                        </el-radio-button>

                    </el-radio-group>
                </el-form-item>
                <el-form-item label="渠道" prop="channel_id" required>
                    <el-checkbox-group v-model="form.channel_id" size="mini">
                        <el-checkbox-button :label="key"
                                            :key="key"
                                            v-for="(value , key) in channelOptions">
                            {{value}}
                        </el-checkbox-button>
                    </el-checkbox-group>
                </el-form-item>


                <el-form-item label="科室" prop="department_id" required>
                    <el-checkbox-group v-model="form.department_id" size="mini">
                        <el-checkbox-button v-for="(option , key) in departmentOptions" :label="key" :key="key">
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
    import Swal        from 'sweetalert2';
    import moment      from 'moment';
    import axios       from 'axios';
    import { cloneOf } from "../utils/parse";


    export default {
        name   : 'ExportDataForm',
        props  : {
            channelOptions   : {
                type    : Object,
                required: true,
            },
            departmentOptions: {
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
                    department_id: [
                        { required: true, message: '请选择需要导出的科室', trigger: 'blur' }
                    ],
                    channel_id   : [
                        { required: true, message: '请选择需要导出的渠道', trigger: 'blur' }
                    ],
                    type         : [
                        { required: true, message: '请选择需要导出的类型', trigger: 'blur' }
                    ],

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
                    department_id: [],
                    channel_id   : [],
                    type         : '',
                    dates        : [],
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
                        data.dates   = [
                            moment(data.dates[ 0 ]).format('YYYY-MM-DD') + ' 00:00:00',
                            moment(data.dates[ 1 ]).format('YYYY-MM-DD') + ' 23:59:59',
                        ];
                        this.loading = true;

                        try {
                            let res = await axios.request({
                                url   : '/api/export/excel',
                                method: 'post',
                                data  : data,
                            });
                            console.log('res :', res);
                            if (res.status === 200) {
                                Swal.fire({
                                    title            : "提交成功,请等待生成...",
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
            },
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

<style lang="less">
    .swal-container {
        z-index: 2500
    }

</style>
