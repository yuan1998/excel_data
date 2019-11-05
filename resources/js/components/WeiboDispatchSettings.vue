<template>
    <div class="">
        <el-row :gutter="12">
            <el-col :span="12">
                <el-card class="box-card" v-loading="zxLoading">
                    <div slot="header" class="clearfix">
                        <span>整形配置</span>
                    </div>
                    <div>
                        <el-form :model="zxSettings" ref="zx_form">
                            <el-form-item label="启动分配" prop="dispatch_open">
                                <el-switch v-model="zxSettings.dispatch_open"></el-switch>
                            </el-form-item>
                            <el-form-item label="分配-全天分配" prop="all_day">
                                <el-switch
                                        v-model="zxSettings.all_day">
                                </el-switch>
                            </el-form-item>
                            <el-form-item label="分配-分配时段" prop="dates" v-if="!zxSettings.all_day">
                                <el-time-picker
                                        is-range
                                        v-model="zxSettings.dates"
                                        range-separator="至"
                                        start-placeholder="开始时间"
                                        end-placeholder="结束时间"
                                        placeholder="选择时间范围">
                                </el-time-picker>
                            </el-form-item>
                            <el-form-item label="参与分配">
                                <el-transfer :titles="transferTitles" v-model="zxSettings.dispatch_users"
                                             :data="zxUserOfTransfer"></el-transfer>
                            </el-form-item>
                            <div>
                                <el-button type="text" @click="openRuleList('zx')">高级分配规则</el-button>
                            </div>
                            <div>
                                <el-button type="primary" @click="handleSubmitFormData('zx')">
                                    保存
                                </el-button>
                            </div>
                        </el-form>
                    </div>
                </el-card>
            </el-col>
            <el-col :span="12">
                <el-card class="box-card" v-loading="kqLoading">
                    <div slot="header" class="clearfix">
                        <span>口腔配置</span>
                    </div>
                    <el-form :model="kqSettings" ref="zx_form">
                        <el-form-item label="启动分配" prop="dispatch_open">
                            <el-switch v-model="kqSettings.dispatch_open"></el-switch>
                        </el-form-item>
                        <el-form-item label="分配-全天分配" prop="all_day">
                            <el-switch
                                    v-model="kqSettings.all_day">
                            </el-switch>
                        </el-form-item>
                        <el-form-item label="分配-分配时段" prop="dates" v-if="!kqSettings.all_day">
                            <el-time-picker
                                    is-range
                                    v-model="kqSettings.dates"
                                    range-separator="至"
                                    start-placeholder="开始时间"
                                    end-placeholder="结束时间"
                                    placeholder="选择时间范围">
                            </el-time-picker>
                        </el-form-item>
                        <el-form-item label="参与分配">
                            <el-transfer :titles="transferTitles" v-model="kqSettings.dispatch_users"
                                         :data="kqUserOfTransfer"></el-transfer>
                        </el-form-item>
                        <div>
                            <el-button type="text" @click="openRuleList('kq')">高级分配规则</el-button>
                        </div>
                        <div>
                            <el-button type="primary" @click="handleSubmitFormData('kq')">
                                保存
                            </el-button>
                        </div>
                    </el-form>
                </el-card>
            </el-col>
        </el-row>
        <el-dialog :title="dialogTypeName"
                   :visible.sync="showDialog">
            <div>
                <el-button plain style="width: 100%;" type="primary" @click="openRuleDrawer()">
                    <i class="el-icon-plus"></i>
                    创建规则
                </el-button>
            </div>
            <el-table :data="dialogRuleData">
                <el-table-column property="rule_name" label="规则名称" width="150"></el-table-column>
                <el-table-column label="启用" width="88">
                    <template slot-scope="scope">
                        {{scope.row.dispatch_open ? '开' : '关' }}
                    </template>
                </el-table-column>
                <el-table-column label="启用时间" width="180">
                    <template slot-scope="scope">
                        {{scope.row.all_day ? '全天' : scope.row.start_time + ' - ' + scope.row.end_time}}
                    </template>
                </el-table-column>
                <el-table-column label="人数" width="80">
                    <template slot-scope="scope">
                        {{scope.row.dispatch_users ? scope.row.dispatch_users.length : 0}}
                    </template>
                </el-table-column>
                <el-table-column label="权重" width="50">
                    <template slot-scope="scope">
                        {{scope.row.order }}
                    </template>
                </el-table-column>
                <el-table-column label="操作" width="120" fixed="right">
                    <template slot-scope="scope">
                        <el-button type="text" @click="openRuleDrawer(scope.row)">修改</el-button>
                        <el-button type="text" @click="handleDeleteRule(scope.row.id)">删除</el-button>
                    </template>
                </el-table-column>
            </el-table>
        </el-dialog>

        <el-drawer
                title="创建高级规则"
                :before-close="handleClose"
                :visible.sync="showDrawer"
                size="50%"
                custom-class="demo-drawer"
                ref="drawer"
        >
            <div class="demo-drawer__content">
                <el-form :model="dispatchRuleForm" label-position="left">
                    <el-form-item label="规则名称">
                        <el-input v-model="dispatchRuleForm.rule_name"></el-input>
                    </el-form-item>
                    <el-form-item label="匹配词">
                        <el-input-tag v-model="dispatchRuleForm.keyword"></el-input-tag>
                    </el-form-item>
                    <el-form-item label="启动分配" prop="dispatch_open">
                        <el-switch v-model="dispatchRuleForm.dispatch_open"></el-switch>
                    </el-form-item>
                    <el-form-item label="分配-全天分配" prop="all_day">
                        <el-switch
                                v-model="dispatchRuleForm.all_day">
                        </el-switch>
                    </el-form-item>
                    <el-form-item label="分配-分配时段" prop="dates" v-if="!dispatchRuleForm.all_day">
                        <el-time-picker
                                is-range
                                v-model="dispatchRuleForm.dates"
                                range-separator="至"
                                start-placeholder="开始时间"
                                end-placeholder="结束时间"
                                placeholder="选择时间范围">
                        </el-time-picker>
                    </el-form-item>
                    <el-form-item label="参与分配" prop="dispatch_users">
                        <el-transfer :titles="transferTitles" v-model="dispatchRuleForm.dispatch_users"
                                     :data="dialogUsers"></el-transfer>
                    </el-form-item>
                    <el-form-item label="权重">
                        <el-input-number v-model="dispatchRuleForm.order" :min="0"></el-input-number>

                    </el-form-item>
                </el-form>
                <div class="demo-drawer__footer" style="text-align: right;">
                    <el-button @click="showDrawer = false">取 消</el-button>
                    <el-button type="primary" @click="handleSubmitRuleData" :loading="drawerLoading">{{ drawerLoading ?
                        '提交中...' : '确 定' }}
                    </el-button>
                </div>
            </div>
        </el-drawer>
    </div>
</template>

<script>
    import { cloneOf } from "../utils/parse";

    const DEFAULT_RULE_FIELDS = {
        'type'          : '',
        'rule_name'     : '',
        'order'         : 0,
        'dispatch_open' : true,
        'all_day'       : false,
        'start_time'    : '9:00:00',
        'end_time'      : '22:00:00',
        'dispatch_users': [],
        'keyword'       : ''
    };

    export default {
        name    : "weibo-dispatch-settings",
        props   : {
            users   : Array,
            rules   : Array,
            settings: Object,
        },
        created() {
            this.$set(this, 'kqSettings', this.parserPropSettings('kq'));
            this.$set(this, 'zxSettings', this.parserPropSettings('zx'));
            this.$set(this, 'ruleList', this.rules);
            console.log('this.zxSettings :', this.zxSettings);
        },
        data() {
            return {
                drawerLoading   : false,
                ruleList        : [],
                zxSettings      : {},
                kqSettings      : {},
                zxLoading       : false,
                kqLoading       : false,
                dialogType      : null,
                showDrawer      : false,
                showDialog      : false,
                transferTitles  : [
                    '所有客服',
                    '参与分配'
                ],
                dispatchRuleForm: {}
            };
        },
        computed: {
            dialogUsers() {
                let type = this.dialogType;
                if (!type) return [];
                return this[ `${ type }UserOfTransfer` ]
            },
            dialogTypeName() {
                let type = this.dialogType;
                return (type ? (type === 'zx' ? '整形' : '口腔') : '') + '高级分配规则';
            },
            dialogRuleData() {
                let type = this.dialogType;
                return type ? this.ruleList.filter((item) => {
                    return item.type === type;
                }) : [];
            },
            usersOfZxSettings() {
                let users = this.zxSettings.dispatch_users;
                if (!users || !users.length) return [];

                return this.usersOfZx.map((item) => {
                    return users.includes(item.id);
                })
            },
            usersOfKxSettings() {
                let users = this.kqSettings.dispatch_users;
                if (!users || !users.length) return [];

                return this.usersOfKq.filter((item) => {
                    return users.includes(item.id);
                })
            },
            zxUserOfTransfer() {
                return this.usersOfZx.map((item) => {
                    return {
                        key  : item.id,
                        label: item.username,
                    };
                })
            },
            kqUserOfTransfer() {
                return this.usersOfKq.map((item) => {
                    return {
                        key  : item.id,
                        label: item.username,
                    };
                })
            },
            kqUserOfId() {
                return this.usersOfKq.map((item) => {
                    return item.id;
                })
            },
            zxUserOfId() {
                return this.usersOfZx.map((item) => {
                    return item.id;
                })
            },
            usersOfZx() {
                return this.users.filter((item) => {
                    return item.type === 'zx'
                })
            },
            usersOfKq() {
                return this.users.filter((item) => {
                    return item.type === 'kq'
                })
            },
        },
        methods : {
            parserDispatchRuleForm(obj, type) {
                obj                = cloneOf(obj);
                obj.type           = type;
                obj.keyword        = obj.keyword ? obj.keyword.split(',') : [];
                obj.dates          = [
                    moment(obj.start_time, 'HH:mm:ss'),
                    moment(obj.end_time, 'HH:mm:ss'),
                ];
                obj.dispatch_users = obj.dispatch_users.filter((id) => {
                    return this[ `${ type }UserOfId` ].includes(id);
                });
                console.log('obj :', obj);
                return obj;
            },
            openRuleDrawer(obj) {
                this.showDrawer = true;
                obj             = obj || DEFAULT_RULE_FIELDS;
                console.log('param obj :', obj);
                this.$set(this, 'dispatchRuleForm', this.parserDispatchRuleForm(obj, this.dialogType));
            },
            handleClose(done) {
                this.$confirm('关闭将不会为你保存进度.确定要关闭吗')
                    .then(_ => {
                        done();
                    })
                    .catch(_ => {
                    });
            },
            openRuleList(type) {
                this.dialogType = type;
                this.showDialog = true;
            },
            parserPropSettings(type) {
                let settings = this.settings[ type ];

                settings.dates = [ moment(settings.start_time, 'HH:mm:ss'), moment(settings.end_time, 'HH:mm:ss') ];

                settings.dispatch_users = settings.dispatch_users.filter((id) => {
                    return this[ `${ type }UserOfId` ].includes(id);
                });
                return settings;
            },
            async handleSubmitFormData(type) {
                let settings        = cloneOf(this[ `${ type }Settings` ]);
                settings.start_time = moment(settings.dates[ 0 ]).format('HH:mm:ss');
                settings.end_time   = moment(settings.dates[ 1 ]).format('HH:mm:ss');

                this[ `${ type }Loading` ] = true;
                let res                    = await axios({
                    method: 'POST',
                    url   : '/api/weibo/setting/base',
                    data  : {
                        setting_type: type,
                        setting_data: settings,
                    }
                });
                this[ `${ type }Loading` ] = false;

                if (res.status === 204) {
                    Swal.fire({
                        title            : (type === 'zx' ? '整形' : '口腔') + '配置保存成功',
                        type             : 'success',
                        timer            : 2000,
                        showConfirmButton: false
                    })
                } else {
                    Swal.fire({
                        title            : '发送错误,请联系管理员',
                        type             : 'error',
                        timer            : 2000,
                        showConfirmButton: false
                    })
                }
            },
            async handleSubmitRuleData() {
                let settings        = cloneOf(this.dispatchRuleForm);
                settings.keyword    = settings.keyword.join(',');
                settings.start_time = moment(settings.dates[ 0 ]).format('HH:mm:ss');
                settings.end_time   = moment(settings.dates[ 1 ]).format('HH:mm:ss');

                this.drawerLoading = true;
                try {
                    settings.id ? await this.mapRuleDataOfUpdate(settings) : await this.mapRuleDataOfCreate(settings);
                    Swal.fire({
                        title            : (settings.id ? '修改' : '创建') + '成功!',
                        type             : 'success',
                        timer            : 2000,
                        showConfirmButton: false,
                        onClose          : () => {
                            this.showDrawer = false;
                        }
                    })
                } catch (e) {
                    Swal.fire({
                        title            : '发生错误,请联系管理员',
                        type             : 'error',
                        timer            : 2000,
                        showConfirmButton: false
                    })
                }
                this.drawerLoading = false;

            },
            async mapRuleDataOfUpdate(data) {
                let res = await axios({
                    method: 'PUT',
                    url   : '/api/weibo/setting/' + data.id,
                    data,
                });

                if (res.status === 200) {
                    let index = this.ruleList.findIndex((item) => {
                        return item.id === data.id;
                    });
                    if (index > -1) {
                        this.ruleList.splice(index, 1, res.data);
                    }
                }

            },
            async mapRuleDataOfCreate(data) {
                let res = await axios({
                    method: 'POST',
                    url   : '/api/weibo/setting',
                    data
                });

                if (res.status === 200) {
                    this.ruleList.push(res.data);
                }
            },
            async mapRUleDataOfDelete(id, index) {
                let res = await axios({
                    method: 'DELETE',
                    url   : '/api/weibo/setting/' + id,
                });
                console.log('resH :', res);
                if (res.status === 200) {
                    this.ruleList.splice(index, 1);
                    Swal.fire({
                        title            : '删除成功',
                        type             : 'success',
                        timer            : 2000,
                        showConfirmButton: false,
                    })
                }

            },
            async handleDeleteRule(id) {
                let index = this.ruleList.findIndex((item) => {
                    return item.id === id;
                });

                if (index > -1) {
                    this.$confirm('此操作将永久删除该规则, 是否继续?', '提示', {
                        confirmButtonText: '确定',
                        cancelButtonText : '取消',
                        type             : 'warning'
                    }).then(async () => {
                        try {
                            await this.mapRUleDataOfDelete(id, index);
                        } catch (e) {
                            Swal.fire({
                                title            : '发生错误,请联系管理员',
                                type             : 'error',
                                timer            : 2000,
                                showConfirmButton: false
                            })
                        }
                    });
                } else {
                    this.$notify.error({
                        title  : '错误',
                        message: '传进了错误的ID,请联系管理员'
                    });
                }

            }
        },
    }
</script>

<style scoped lang="less">
    .demo-drawer__content {
        padding: 0 20px;
    }
</style>
<style>
    .swal2-container {
        z-index: 3500 !important
    }
</style>
