<template>
    <el-card>
        <el-timeline v-if="logData && logData.length">
            <el-timeline-item
                    placement="top"
                    v-for="(log, index) in logData"
                    :key="index"
                    :timestamp="log.created_at">
                <el-card>
                    <h4>
                        <el-tag>{{ log.tags ? tagList[log.tags]: '-' }}</el-tag>
                        {{ log.comment }}
                    </h4>
                    <p>{{ log.change_by.username }} 提交于 {{ log.created_at }}</p>
                </el-card>
            </el-timeline-item>
        </el-timeline>
        <div v-else>
            <h4>
                没有回访记录
            </h4>
        </div>
    </el-card>
</template>

<script>
    export default {
        name   : "recall-log",
        props  : {
            logData: Array
        },
        mounted() {
            console.log('this.logData :', this.logData);
        },
        data() {
            return {
                tagList: {
                    '1': '转微',
                    '2': '未接通',
                    '3': '空号',
                    '4': '无需求',
                    '5': '重复建档',
                    '6': '已到院',
                    '7': '有需求',
                }
            };
        },
        methods: {},
    }
</script>

<style scoped lang="less">

</style>
