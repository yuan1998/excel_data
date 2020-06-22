<template>
    <div :style="'display: inline-block;'" style="display: none;">
        <div id="app-export-data-action" class="dib">
            <el-button type="primary" size="mini" @click="handleOpen">创建导出数据</el-button>

            <el-dialog title="创建导出数据"
                       ref="dialog"
                       width="650px"
                       :visible.sync="dialogFormVisible"
                       :before-close="handleClose">
                <el-form ref="form"
                         :model="form"
                         :rules="rules"
                         :label-width="formLabelWidth">
                    <el-form-item label="时间" prop="dates">
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
                    <el-form-item label="医院类型" prop="type">
                        <el-radio-group v-model="form.type" size="mini">
                            <el-radio label="zx" border
                            >
                                整形医院
                            </el-radio>
                            <el-radio label="kq" border
                            >
                                口腔医院
                            </el-radio>
                        </el-radio-group>
                    </el-form-item>
                    <el-form-item label="渠道类型" prop="channel_id">
                        <el-checkbox-group v-model="form.channel_id" size="mini">
                            <el-checkbox border
                                         :label="key"
                                         :key="key"
                                         v-for="(value , key) in channelOptions">
                                {{value}}
                            </el-checkbox>
                        </el-checkbox-group>
                    </el-form-item>
                    <el-form-item label="科室类型" prop="department_id">
                        <el-checkbox-group v-model="form.department_id" size="mini">
                            <el-checkbox border v-for="(option , key) in departmentOptions" :label="option['id']"
                                         :key="key">
                                {{option['title']}}
                            </el-checkbox>
                        </el-checkbox-group>
                    </el-form-item>
                    <el-button type="text"
                               :disabled="disabledProject"
                               @click="handleToggleProject"
                    >
                        病种设置
                        <i class="el-icon "
                           :class="projectsListShow ? 'el-icon-arrow-down': 'el-icon-arrow-up'"></i>
                    </el-button>
                    <el-form-item label="显示 - 病种汇总" prop="project_id" label-width="85px" v-if="projectsListShow">

                        <el-transfer v-model="form.project_id"
                                     :button-texts="['病种列表', '显示的病种']"
                                     :props="{
      key: 'id',
      label: 'title'
    }"
                                     :data="projectList"></el-transfer>

                    </el-form-item>
                </el-form>
                <div slot="footer" class="dialog-footer">
                    <el-button size="mini" @click="closeDialog">取 消</el-button>
                    <el-button size="mini" type="primary" @click="handleSubmit">确 定</el-button>
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
        name    : 'ExportDataForm',
        props   : {
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
                projectsListShow : false,
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
                test             : [],
                form             : {
                    department_id: [],
                    channel_id   : [],
                    project_id   : [],
                    type         : '',
                    dates        : [],
                },
            }
        },
        computed: {
            disabledProject() {
                return !this.form.department_id.length;
            },
            projectList() {
                let result        = [];
                let departmentIds = this.form.department_id;
                if (!departmentIds.length) return result;

                this.departmentOptions
                    .forEach((item) => {
                        if (departmentIds.includes(item.id)) {
                            result = [
                                ...result,
                                ...item.projects,
                            ];
                        }
                    });
                return result;
            }
        },
        methods : {
            handleToggleProject() {
                this.projectsListShow = !this.projectsListShow;
            },
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

    .dib {
        display: inline-block;
    }

</style>
