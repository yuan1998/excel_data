<template>
    <div>
        <el-card class="box-card">
            <el-form ref="form" :rules="rules" :model="formData" label-width="150px" v-loading="loading">
                <el-form-item label="数据文件上传">
                    <el-upload
                            v-if="!formData.file_name"
                            class="upload-demo"
                            drag
                            :show-file-list="false"
                            :limit="1"
                            action=""
                            :http-request="handleHttpRequest"
                    >
                        <i class="el-icon-upload"></i>
                        <div class="el-upload__text">将文件拖到此处，或<em>点击上传</em></div>
                        <div class="el-upload__tip" slot="tip">只能上传excel类型文件，数据只需要有表头就行,不要上传满是数据的表格,浪费时间</div>
                    </el-upload>
                    <p class="filename-item" v-else>
                        {{ formData.file_name }}
                        <el-button type="text" @click="resetData">重新上传</el-button>
                    </p>
                </el-form-item>
                <el-form-item v-if="fileStatus" label="单元表选择" prop="sheet_name">
                    <el-radio-group v-model="formData.sheet_name" @change="handleSheetChange" size="small">
                        <el-radio v-for="(item,index) in sheets" :key="index" :label="item" border>{{ item }}</el-radio>
                    </el-radio-group>
                </el-form-item>
                <template v-if="formData.sheet_name">
                    <el-form-item
                            prop="data_field"
                    >
                        <template slot="label">
                            表头选择
                            <el-tooltip class="item" effect="light"
                                        content="选择用于判断该数据的必要表头"
                                        placement="top-start">
                                <i class="el-icon-question"></i>
                            </el-tooltip>
                        </template>
                        <el-select v-model="formData.data_field"
                                   style="width: 100%"
                                   multiple placeholder="请选择">
                            <el-option
                                    v-for="(item,index) in getSheetHeader"
                                    :key="index"
                                    :label="item"
                                    :value="item">
                            </el-option>
                        </el-select>

                    </el-form-item>
                    <el-form-item label="数据源名称" prop="title">
                        <el-input v-model="formData.title"></el-input>
                    </el-form-item>
                    <el-form-item label="关联渠道" prop="channel_id">
                        <el-select v-model="formData.channel_id"
                                   style="width: 100%"
                                   multiple placeholder="请选择">
                            <el-option
                                    v-for="(title,id) in channelOptions"
                                    :key="id"
                                    :label="title"
                                    :value="id">
                            </el-option>
                        </el-select>
                    </el-form-item>

                    <el-form-item label="数据源类型" prop="data_type">
                        <el-radio-group @change="handleChangeDataType" v-model="formData.data_type" size="small">
                            <el-radio v-for="(value,key) in dataType" :key="key" :label="key" border>{{ value }}
                            </el-radio>
                        </el-radio-group>
                    </el-form-item>
                </template>
                <template v-if="formData.property_field">
                    <el-divider content-position="left">必要的数据关联</el-divider>
                    <el-form-item v-for="(item , key) in formData.property_field"
                                  :prop="'property_field.' + key "
                                  :rules="propertyFieldRule(key)"
                                  :key="key">
                        <template slot="label">
                            {{ fieldText[key].text }}
                            <el-tooltip class="item" effect="light"
                                        :content="fieldText[key].tip"
                                        placement="top-start">
                                <i class="el-icon-question"></i>
                            </el-tooltip>
                        </template>
                        <el-select v-model="formData.property_field[key]"
                                   style="width: 100%"
                                   multiple placeholder="请选择">
                            <el-option
                                    v-for="(item,index) in getSheetHeader"
                                    :key="index"
                                    :label="item"
                                    :value="item">
                            </el-option>
                        </el-select>
                    </el-form-item>

                    <el-form-item>
                        <el-button type="primary" @click="handleSubmit('form')">提交</el-button>
                    </el-form-item>
                </template>

            </el-form>
        </el-card>

    </div>
</template>

<script>

    import { cloneOf } from "../utils/parse";

    const DEFAULT_DATA = {
        title         : '',
        sheet_name    : '',
        file_name     : '',
        data_type     : '',
        property_field: null,
        data_field    : [],
        channel_id    : [],
    };

    export default {
        name    : "data-origin-create-or-edit",
        props   : {
            dataType         : Object,
            typePropertyField: Object,
            requireProperty  : Object,
            item             : Object,
            channelOptions   : Array,
            modelItem        : Object,
        },
        data() {

            return {
                status    : 'create',
                loading   : false,
                file_name : '',
                fileResult: {},
                sheets    : [],
                fileStatus: false,
                formData  : cloneOf(DEFAULT_DATA),
                rules     : {
                    title     : [
                        { required: true, message: '请输入数据源名称', trigger: 'blur' },
                    ],
                    sheet_name: [
                        { required: true, message: '请选择单元表', trigger: 'change' },
                    ],
                    data_type : [
                        { required: true, message: '请选择数据类型', trigger: 'change' },
                    ],
                    data_field: [
                        { type: 'array', required: true, message: '请至少选择一个表头名称', trigger: 'change' },
                    ],
                    channel_id: [
                        { type: 'array', required: true, message: '请至少选择一个需要关联的渠道', trigger: 'change' },
                    ],
                },
                fieldText : {
                    'consultant_code': {
                        text: '咨询字段',
                        tip : '用于判断咨询用户',
                    },
                    'code'           : {
                        text: '主要标识',
                        tip : '用于判断渠道,账户,科室,病种等'
                    },
                    'data_type'      : {
                        text: '表单名称',
                        tip : '用于显示该表单的标识',
                    },
                    'spend_name'     : {
                        text: '消费名称',
                        tip : '用于显示该消费的标识',
                    },
                    'click'          : {
                        text: '点击量',
                        tip : '用于记录该消费的点击',
                    },
                    'show'           : {
                        text: '展现量',
                        tip : '用于记录该消费的展现',
                    },
                    'spend'          : {
                        text: '消耗',
                        tip : '用于记录该消费的消费',
                    },
                    'phone'          : {
                        text: '电话',
                        tip : '用于创建电话表单',
                    },
                    'date'           : {
                        text: '时间',
                        tip : '用于记录数据的时间'
                    },
                    'uuid'           : {
                        text: '唯一标识',
                        tip : '用于区分数据的唯一性,用于去重',
                    },
                }
            };
        },
        computed: {
            getSheetHeader() {
                let sheetName = this.formData.sheet_name;
                return this.fileResult.headers[ sheetName ];
            }
        },
        mounted() {
            if (this.modelItem && this.modelItem.id) {
                this.status     = 'update';
                this.fileResult = {
                    headers: this.modelItem.excel_snap,
                };
                this.sheets     = Object.keys(this.modelItem.excel_snap);
                this.fileStatus = true;
                this.formData   = {
                    ...this.modelItem,
                    channel_id: this.modelItem.channel_id.map((item) => String(item))
                }
            }
            console.log(this.modelItem);
            console.log(this.channelOptions);

        },
        methods : {
            propertyFieldRule(key) {
                let dataType = this.formData.data_type;
                let required = this.requireProperty[ dataType ];
                let text     = this.fieldText[ key ].text;
                let rule     = {
                    type    : 'array',
                    required: true,
                    message : '请至少选择一个表头设置为' + text + '的关联列',
                    trigger : 'change',
                };
                if (required) {
                    rule[ 'required' ] = required.includes(key);
                }
                console.log('rule :', rule);
                return rule;

            },
            readFileContent(file) {
                let reader = new FileReader();

                let promise = new Promise((resolve, reject) => {
                    reader.onload = function (e) {
                        let data     = new Uint8Array(e.target.result);
                        let workbook = XLSX.read(data, { type: 'array' });
                        let result   = {};
                        let headers  = {};
                        workbook.SheetNames.forEach(function (sheetName) {
                            let roa = XLSX.utils.sheet_to_json(workbook.Sheets[ sheetName ], { header: 1 });
                            if (roa.length) {
                                headers[ sheetName ] = roa[ 0 ].filter((item) => {
                                    return !!item;
                                });

                                result[ sheetName ] = roa;
                            }
                        });
                        // see the result, caution: it works after reader event is done.
                        resolve({
                            workbook,
                            result,
                            headers,
                        });
                    };
                });
                reader.readAsArrayBuffer(file);
                return promise;
            },
            resetData() {
                this.fileStatus = false;
                this.sheets     = [];
                this.fileResult = {};
                this.$set(this, 'formData', cloneOf(DEFAULT_DATA));
            },
            handleSheetChange(type) {
                console.log('type :', type);
                this.formData.data_field = cloneOf(this.getSheetHeader);
                if (this.formData.data_type) {
                    this.$set(this.formData, 'property_field', cloneOf(this.typePropertyField[ this.formData.data_type ]));
                }
            },
            async handleHttpRequest(event) {
                let file                = event.file;
                this.formData.file_name = file.name;
                try {
                    let result = await this.readFileContent(file);
                    if (result) {
                        this.sheets = Object.keys(result.result);
                    }
                    this.fileResult = result;
                    this.fileStatus = true;
                    console.log(result);

                } catch (e) {
                    this.fileStatus = false;
                }
            },
            handleChangeDataType(type) {
                if (type) {
                    this.$set(this.formData, 'property_field', cloneOf(this.typePropertyField[ type ]));
                }
                console.log('type :', type);
            },
            async mapToCreateDataOrigin(data) {
                let res = await this.apiOfStore(data);
            },
            async mapToUpdateDataOrigin(data) {
                let res = await this.apiOfUpdate(data);
            },
            async apiOfStore(data) {
                return await axios.post('/api/data-origin/', data);
            },
            async apiOfUpdate(data) {
                let id = this.modelItem.id;
                return await axios.put('/api/data-origin/' + id, data);
            },
            handleSubmit(formName) {
                this.$refs[ formName ].validate(async (valid) => {
                    if (valid) {
                        if (this.loading) return;

                        this.loading         = !this.loading;
                        let data             = cloneOf(this.formData);
                        data[ 'excel_snap' ] = this.fileResult.headers;

                        try {
                            let statusMsg = '创建成功!';
                            if (this.status === 'create') {
                                await this.mapToCreateDataOrigin(data);
                            } else {
                                statusMsg = '修改成功!';
                                await this.mapToUpdateDataOrigin(data);
                            }
                            this.loading = !this.loading;

                            Swal.fire({
                                title            : statusMsg,
                                type             : 'success',
                                timer            : 3000,
                                showConfirmButton: false,
                                onClose() {
                                }
                            });
                            window.history.go(-1);

                        } catch (e) {
                            this.loading = !this.loading;

                            let message = '发送未知错误,联系管理员';
                            if (e.response)
                                message = e.response.data.message;

                            Swal.fire({
                                title            : message,
                                type             : 'error',
                                timer            : 0,
                                showConfirmButton: false
                            })
                        }


                    }
                })
            }
        },
    }
</script>

<style scoped lang="less">

</style>
