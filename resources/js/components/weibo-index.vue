<template>
    <div>
        <el-card style="margin-bottom: 25px;">
            <div class="today-data">
                <el-row style="padding-bottom: 15px;">
                    <el-col :span="12">
                        <div class="header-line">
                            今日运营数据
                        </div>
                    </el-col>
                    <el-col style="text-align: right;" :span="12">
                        <el-button type="text">
                            <a href='/admin/weibo_form_data'>表单管理</a>
                        </el-button>
                    </el-col>
                </el-row>
                <el-row>
                    <el-col :span="6">
                        <div class="today-data-item">
                            <div class="item-content">
                                <div class="title">
                                    表单总
                                </div>
                                <div class="count">
                                    {{ todayData.count }}
                                </div>
                            </div>
                        </div>
                    </el-col>
                    <el-col :span="6">
                        <div class="today-data-item">
                            <div class="item-content">
                                <div class="title">
                                    已回访
                                </div>
                                <div class="count">
                                    {{ todayData.recall_count_1 }}
                                </div>
                            </div>
                        </div>
                    </el-col>
                    <el-col :span="6">
                        <div class="today-data-item">
                            <div class="item-content">
                                <div class="title">
                                    整形表单
                                </div>
                                <div class="count">
                                    {{ todayData.zx_count }}
                                </div>
                            </div>
                        </div>
                    </el-col>
                    <el-col :span="6">
                        <div class="today-data-item">
                            <div class="item-content">
                                <div class="title">
                                    口腔表单
                                </div>
                                <div class="count">
                                    {{ todayData.kq_count }}
                                </div>
                            </div>
                        </div>
                    </el-col>
                </el-row>
            </div>
        </el-card>

        <el-row :gutter="15">
            <el-col :span="14">
                <el-card class="box-card">
                    <div slot="header" class="clearfix">
                        <span>近30天数据总计</span>
                    </div>
                    <div ref="testCharts" style="height: 400px;"></div>
                </el-card>
            </el-col>
            <el-col :span="10">
                <el-card class="box-card">
                    <div slot="header" class="clearfix">
                        <span>一周数据对比</span>
                    </div>
                    <div ref="weekDiffCharts" style="height: 400px;"></div>
                </el-card>
            </el-col>
        </el-row>
    </div>
</template>

<script>

    import { cloneOf } from "../utils/parse";
    import moment      from "moment";

    const defaultDayResult = {
        count         : 0,
        zx_count      : 0,
        kq_count      : 0,
        recall_count_0: 0,
        recall_count_1: 0,
    };

    export default {
        name    : "weibo-index",
        props   : {
            weiboFormData: Array
        },
        data() {
            return {
                testCharts    : null,
                weekDiffCharts: null,
            };
        },
        computed: {
            todayData() {
                let today  = moment().format('YYYY-MM-DD');
                let result = this.testData[ today ];
                return result || defaultDayResult;
            },
            testData() {
                let dayResult = {};
                this.weiboFormData.forEach((item) => {
                    if (!dayResult[ item.post_date ]) {
                        dayResult[ item.post_date ] = cloneOf(defaultDayResult);
                    }
                    dayResult[ item.post_date ].count++;
                    dayResult[ item.post_date ][ `${ item.type }_count` ]++;
                    dayResult[ item.post_date ][ `recall_count_${ item.recall_date ? 1 : 0 }` ]++;

                    if (!dayResult[ item.post_date ][ `user_count_${ item.weibo_user_id }` ]) {
                        dayResult[ item.post_date ][ `user_count_${ item.weibo_user_id }` ] = 0;
                    }
                    dayResult[ item.post_date ][ `user_count_${ item.weibo_user_id }` ]++;

                    if (!dayResult[ item.post_date ][ `tag_count_${ item.tags }` ]) {
                        dayResult[ item.post_date ][ `tag_count_${ item.tags }` ] = 0;
                    }
                    dayResult[ item.post_date ][ `tag_count_${ item.tags }` ]++;
                });
                return dayResult;
            },
            testChartsData() {
                let days = [ '日期' ];
                let zx   = [ '整形表单' ];
                let kq   = [ '口腔表单' ];

                let data = this.testData;
                Object.keys(data).forEach((key) => {
                    let item = data[ key ];
                    days.push(key);
                    zx.push(item.zx_count);
                    kq.push(item.kq_count);
                });

                return [
                    days,
                    zx,
                    kq
                ]
            },
            weekDiffChartsData() {
                let data        = this.testData;
                let values      = Object.values(data);
                let indexLength = values.length - 1;
                // 20  19
                // 13  12
                let weekData   = [];
                let weekZxData = [];
                let weekKqData = [];

                let preWeekData   = [];
                let preWeekZxData = [];
                let preWeekKqData = [];
                for (let i = 7 ; i >= 0 ; i--) {
                    let current = values[ indexLength - i ];
                    let pre     = values[ indexLength - i - 7 ];

                    weekData.push(current.count);
                    weekZxData.push(current.zx_count);
                    weekKqData.push(current.kq_count);

                    preWeekData.push(pre.count);
                    preWeekZxData.push(pre.zx_count);
                    preWeekKqData.push(pre.kq_count);
                }

                return [
                    {
                        name : '整形表单',
                        type : 'bar',
                        data : preWeekZxData,
                        stack: '上周',
                    },
                    {
                        name : '口腔表单',
                        type : 'bar',
                        data : preWeekKqData,
                        stack: '上周',
                    },
                    {
                        name : '整形表单',
                        type : 'bar',
                        data : weekZxData,
                        stack: '本周',
                    },
                    {
                        name : '口腔表单',
                        type : 'bar',
                        data : weekKqData,
                        stack: '本周',
                    },
                ]
            }
        },
        mounted() {
            this.initTestCharts();
            this.initWeekDiffCharts();
            console.log('this.todayData :', this.todayData);
        },
        methods : {
            initTestCharts() {
                this.testCharts = echarts.init(this.$refs.testCharts);
                this.setTestCharts();
            },
            setTestCharts() {
                let today = moment().format('YYYY-MM-DD');

                let option = {
                    legend : {},
                    tooltip: {
                        trigger    : 'axis',
                        showContent: false
                    },
                    dataset: {
                        source: this.testChartsData
                    },
                    xAxis  : { type: 'category' },
                    yAxis  : { gridIndex: 0 },
                    grid   : { top: '55%' },
                    series : [
                        { type: 'line', smooth: true, seriesLayoutBy: 'row' },
                        { type: 'line', smooth: true, seriesLayoutBy: 'row' },
                        {
                            type  : 'pie',
                            id    : 'pie',
                            radius: '30%',
                            center: [ '50%', '25%' ],
                            label : {
                                formatter: `{b}: {@${ today }} ({d}%)`
                            },
                            encode: {
                                itemName: '日期',
                                value   : today,
                                tooltip : today,
                            },
                        }
                    ]
                };

                this.testCharts.on('updateAxisPointer', (event) => {
                    var xAxisInfo = event.axesInfo[ 0 ];
                    if (xAxisInfo) {
                        var dimension = xAxisInfo.value + 1;
                        this.testCharts.setOption({
                            series: {
                                id    : 'pie',
                                label : {
                                    formatter: '{b}: {@[' + dimension + ']} ({d}%)'
                                },
                                encode: {
                                    value  : dimension,
                                    tooltip: dimension
                                }
                            }
                        });
                    }
                });

                this.testCharts.setOption(option);
            },
            initWeekDiffCharts() {
                this.weekDiffCharts = echarts.init(this.$refs.weekDiffCharts);
                this.setWeekDiffCharts();
            },
            setWeekDiffCharts() {
                let option = {
                    tooltip: {
                        trigger    : 'axis',
                        axisPointer: {            // 坐标轴指示器，坐标轴触发有效
                            type: 'shadow'        // 默认为直线，可选为：'line' | 'shadow'
                        }
                    },
                    legend : {
                        data: [ '整形表单', '口腔表单' ]
                    },
                    grid   : {
                        left        : '3%',
                        right       : '4%',
                        bottom      : '3%',
                        containLabel: true
                    },
                    xAxis  : [
                        {
                            type: 'category',
                            data: [ '周一', '周二', '周三', '周四', '周五', moment().add(-1, 'days').format(), '今天' ]
                        }
                    ],
                    yAxis  : [
                        {
                            type: 'value'
                        }
                    ],
                    series : this.weekDiffChartsData
                };

                this.weekDiffCharts.setOption(option);

            }

        },
    }
</script>

<style scoped lang="less">

    .today-data {
        .header-line {
            color: #3a3b3c;
            font-size: 16px;
        }

        .el-col:not(:last-child) .today-data-item {
            border-right: 1px solid rgba(0, 0, 0, .3);

        }

        .today-data-item {
            display: flex;
            align-items: center;
            justify-content: center;

            .content {
                flex: 1;
            }

            .title {
                font-size: 14px;
            }

            .count {
                font-size: 66px;
                line-height: 1;
                font-weight: bold;
                color: #2f4554;
            }


        }
    }

</style>
