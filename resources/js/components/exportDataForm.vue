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

                    <el-tabs v-model="data_type">
                        <el-tab-pane label="信息流报表" name="xxl_data_excel">
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
                                    <el-checkbox border v-for="(option , key) in departmentOptions"
                                                 :label="option['id']"
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
                            <el-form-item label="显示 - 病种汇总" prop="project_id" label-width="85px"
                                          v-if="projectsListShow">

                                <el-transfer v-model="form.project_id"
                                             :titles="['病种列表', '病种汇总']"
                                             :props="{
                                                  key: 'id',
                                                  label: 'title'
                                                }"
                                             :data="projectList"></el-transfer>

                            </el-form-item>
                        </el-tab-pane>
                        <el-tab-pane label="百度计划报表" name="baidu_plan">
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
                        </el-tab-pane>
                        <el-tab-pane label="客服数据报表" name="consultant_group_excel">
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
                            <el-form-item label="客服分组" prop="consultant_group_id">
                                <el-select v-model="form.consultant_group_id"
                                           placeholder="请选择导出的客服分组">
                                    <el-option v-for="item in consultantGroupOptions"
                                               :key="item.id"
                                               :label="item.title"
                                               :value="item.id"></el-option>
                                </el-select>
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
                                    <el-checkbox border v-for="(option , key) in departmentOptions"
                                                 :label="option['id']"
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
                            <el-form-item label="显示 - 病种汇总" prop="project_id" label-width="85px"
                                          v-if="projectsListShow">

                                <el-transfer v-model="form.project_id"
                                             :titles="['病种列表', '病种汇总']"
                                             :props="{
                                                  key: 'id',
                                                  label: 'title'
                                                }"
                                             :data="projectList"></el-transfer>

                            </el-form-item>
                        </el-tab-pane>

                    </el-tabs>


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
            channelOptions        : {
                type    : Object,
                required: true,
            },
            departmentOptions     : {
                type    : Array,
                required: true,
            },
            consultantGroupOptions: {
                type    : Array,
                required: true,
            }
        },
        data() {

            let validateDepartment = (rule, value, callback) => {
                if (this.data_type === 'xxl_data_excel') {
                    if (!value || !value.length) callback(new Error('请选择需要导出的科室'));
                }

                callback();
            };
            let validateChannel    = (_, value, callback) => {
                if (this.data_type === 'xxl_data_excel') {
                    if (!value || !value.length) callback(new Error('请选择需要导出的渠道'));
                }

                callback();
            };

            let validateConsultantGroupId = (_, value, callback) => {
                if (this.data_type === 'consultant_group_excel') {
                    if (!value) callback(new Error('请选择需要导出的客服分组'));

                    let index = this.consultantGroupOptions.findIndex((element) => {
                        return element.id === value;
                    });
                    if (index < 0) callback(new Error('错误的客服分组'));

                }
                callback();
            };


            return {
                data_type        : 'xxl_data_excel',
                projectsListShow : false,
                loadingInstance  : null,
                dialogFormVisible: false,
                loading          : false,
                formLabelWidth   : '80px',
                rules            : {
                    department_id      : [
                        { validator: validateDepartment, trigger: 'blur' }
                    ],
                    channel_id         : [
                        { validator: validateChannel, trigger: 'blur' }
                    ],
                    consultant_group_id: [
                        { validator: validateConsultantGroupId, trigger: 'blur' }
                    ],
                    type               : [
                        { required: true, message: '请选择需要导出的类型', trigger: 'blur' }
                    ],
                    dates              : [
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
                    consultant_group_id: '',
                    department_id      : [],
                    channel_id         : [],
                    project_id         : [],
                    type               : '',
                    dates              : [],
                },
            }
        },
        mounted() {
            console.log('this.consultantGroupOptions :', this.consultantGroupOptions);
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
                        let data            = cloneOf(this.form);
                        data.dates          = [
                            moment(data.dates[ 0 ]).format('YYYY-MM-DD') + ' 00:00:00',
                            moment(data.dates[ 1 ]).format('YYYY-MM-DD') + ' 23:59:59',
                        ];
                        data[ 'data_type' ] = this.data_type;

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
