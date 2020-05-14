<template>
    <el-row :gutter="12">
        <el-col :span="12">
            <el-card class="box-card" v-loading="zxLoading">
                <div slot="header" class="clearfix">
                    <span>抓取设置</span>
                </div>
                <div>
                    <el-row :gutter="24">
                        <el-col :span="12">

                        </el-col>
                    </el-row>
                    <el-form :model="settings" ref="zx_form">
                        <el-form-item label="启动抓取" prop="grab_open">
                            <el-switch v-model="settings.grab_open"></el-switch>
                        </el-form-item>
                        <el-form-item label="全天抓取" prop="all_day">
                            <el-switch
                                    :disabled="!settings.grab_open"
                                    v-model="settings.all_day">
                            </el-switch>
                        </el-form-item>
                        <el-form-item label="抓取时段" prop="dates" v-if="!settings.all_day">
                            <el-time-picker
                                    is-range
                                    v-model="settings.dates"
                                    range-separator="至"
                                    start-placeholder="开始时间"
                                    end-placeholder="结束时间"
                                    placeholder="选择时间范围">
                            </el-time-picker>
                        </el-form-item>
                        <div>
                            <el-button type="primary" @click="handleSubmitFormData('zx')">
                                保存
                            </el-button>
                        </div>
                    </el-form>
                </div>
            </el-card>
        </el-col>

    </el-row>
</template>

<script>

    export default {
        name : "weibo-grab-settings",
        props: {
            accounts: Object,
        },
        created() {
            console.log('this.accounts :', this.accounts);
        },
        data() {
            return {
                settings: {},
            }
        }

    }
</script>
