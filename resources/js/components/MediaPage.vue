<template>
    <div class="media-manager-container">
        <div class="row">
            <!-- /.col -->
            <div class="col-md-12">
                <div class="box box-primary" v-loading="boxLoading">
                    <div class="box-body no-padding">
                        <div class="mailbox-controls with-border">
                            <div class="btn-group">
                                <a href="" @click.prevent="handleRefresh" type="button"
                                   class="btn btn-default btn media-reload" title="Refresh">
                                    <i class="fa fa-refresh"></i>
                                </a>
                                <a type="button"
                                   v-if="canDelete"
                                   class="btn btn-default btn file-delete-multiple"
                                   title="Delete"
                                   @click.prevent="handleMultipleDelete">
                                    <i class="fa fa-trash-o"></i>
                                </a>
                            </div>
                            <label @click="handleOpenUpload" class="btn btn-default btn">
                                <i class="fa fa-upload"></i>&nbsp;&nbsp;上传
                            </label>

                            <!-- /.btn-group -->
                            <a class="btn btn-default btn" @click="handleMakeFolder">
                                <i class="fa fa-folder"></i>&nbsp;&nbsp;新建文件夹
                            </a>

                            <div class="btn-group">
                                <a :href="route('media-index', {'path' : path, 'view' : 'table'})"
                                   :class=" view === 'table' && 'active'"
                                   class="btn btn-default "><i
                                        class="fa fa-list"></i></a>
                                <a :href="route('media-index', {'path' : path, 'view' : 'list'})"
                                   :class=" view === 'list' && 'active'"
                                   class="btn btn-default "><i
                                        class="fa fa-th"></i></a>
                            </div>

                            <div class="input-group input-group-sm pull-right goto-url" style="width: 250px;">
                                <input type="text" name="path" class="form-control pull-right"
                                       v-mode="searchInput">

                                <div class="input-group-btn">
                                    <button type="submit" class="btn btn-default"><i class="fa fa-arrow-right"></i>
                                    </button>
                                </div>
                            </div>

                        </div>
                        <!-- /.mailbox-read-message -->
                    </div>
                    <!-- /.box-body -->
                    <div class="box-footer">
                        <ol class="breadcrumb" style="margin-bottom: 10px;">

                            <li>
                                <a @click="handleNavClick(null,$event)">
                                    <i class="fa fa-th-large"></i>
                                </a>
                            </li>

                            <li v-for="($item , index) in nav " :key="index">
                                <a @click="handleNavClick($item['url'],$event)">
                                    {{ $item['name'] }}
                                </a>
                            </li>
                        </ol>
                        <ul class="files clearfix">

                            <li v-if="!list" style="height: 200px;border: none;"></li>
                            <template v-else>
                                <li v-for="($item ,index) in list" :key="index">
                                    <div class="file-select">
                                        <input type="checkbox"
                                               :value="$item['name']"
                                               name="multiple-delete"
                                        />
                                    </div>
                                    <div v-html="$item['preview']"
                                         @click="mapToListClick($item,$event)"></div>

                                    <div class="file-info">
                                        <a @click="mapToListClick($item,$event)"
                                           :href=" $item['link']"
                                           class="file-name" :title=" $item['name']">
                                            {{ $item['icon'] }} {{ $item['basename'] }}
                                        </a>
                                        <div class="file-size">
                                            {{ $item['size'] }}&nbsp;

                                            <div class="btn-group btn-group-xs pull-right">
                                                <button type="button" class="btn btn-default btn-xs dropdown-toggle"
                                                        data-toggle="dropdown">
                                                    <span class="caret"></span>
                                                    <span class="sr-only">Toggle Dropdown</span>
                                                </button>
                                                <ul class="dropdown-menu" role="menu">
                                                    <li>
                                                        <a href="#"
                                                           class="file-rename"
                                                           @click="mapRenameOrMove($item)"
                                                        >
                                                            重命名&移动文件
                                                        </a>
                                                    </li>
                                                    <li v-if="canDelete">
                                                        <a href="#"
                                                           class="file-delete"
                                                           @click.prevent="handleDelete($item['name'])"
                                                        >
                                                            删除
                                                        </a>
                                                    </li>
                                                    <li v-if="!$item['isDir']">
                                                        <a target="_blank"
                                                           :href="$item['download']">
                                                            下载
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </template>

                        </ul>
                    </div>
                    <!-- /.box-footer -->
                    <!-- /.box-footer -->
                </div>
                <!-- /. box -->
            </div>
            <!-- /.col -->
        </div>

        <el-dialog
                :title="previewItem ? previewItem.basename : ''"
                :visible.sync="dialogVisible"
                :before-close="handleClose"
                width="50%">
            <div v-if="previewItem">
                <div v-if="previewItem.fileType === 'video'" class="video-preview">
                    <video controls="controls" preload='none'
                           :src="previewItem['url']"></video>
                </div>
                <div v-else-if="previewItem.fileType === 'image'" class="image-preview">
                    <el-image :src="previewItem.url" fit="cover">
                        <div slot="placeholder" class="image-slot">
                            加载中<span class="dot">...</span>
                        </div>
                    </el-image>
                </div>
                <div v-else class="cannot-preview">
                    <div v-html="previewItem['preview']"></div>
                    <div class="cannot-text">暂不支持该类型文件的预览.</div>
                </div>


            </div>
            <div slot="footer" class="dialog-footer">
                <el-button type="link">
                    <a href="">下载</a>
                </el-button>
                <el-button @click="dialogVisible = false">取 消</el-button>
                <el-button type="primary" @click="dialogVisible = false">关闭</el-button>
            </div>
        </el-dialog>

        <el-dialog title="上传文件"
                   width="30%"
                   :before-close="handleUploadClose"
                   :visible.sync="uploadDialog">
            <div>
                <el-upload
                        class="upload-demo"
                        action="https://jsonplaceholder.typicode.com/posts/"
                        multiple
                        :before-upload="beforeUpload"
                        :http-request="handleUploadFile"
                        :file-list="fileList">
                    <el-button size="small" type="primary">点击上传</el-button>
                    <div slot="tip" class="el-upload__tip">只能上传jpg/png文件，且不超过500kb</div>
                </el-upload>
            </div>

        </el-dialog>

    </div>
</template>

<script>
    import axios                                from 'axios';
    import { basename, getFileExtension, trim } from "../utils/parse";

    const ATagReg  = /<a.*?>(.*?)<\/a>/;
    const FileType = {
        'image': 'png|jpg|jpeg|tmp|gif',
        'word' : 'doc|docx',
        'ppt'  : 'ppt|pptx',
        'pdf'  : 'pdf',
        'code' : 'php|js|java|python|ruby|go|c|cpp|sql|m|h|json|html|aspx',
        'zip'  : 'zip|tar\.gz|rar|rpm',
        'txt'  : 'txt|pac|log|md',
        'audio': 'mp3|wav|flac|3pg|aa|aac|ape|au|m4a|mpc|ogg',
        'video': 'mkv|rmvb|flv|mp4|avi|wmv|rm|asf|mpeg',
    }

    export default {
        name    : "media-page",
        props   : {
            propList       : Array,
            propNav        : Array,
            propUrl        : Object,
            routerIndexList: Object,
            permissions    : Object,
            csrf           : String,
        },
        data() {
            return {
                deleteChecked: [],
                list         : [],
                nav          : [],
                url          : {},
                path         : '',
                view         : '',
                searchInput  : '',
                boxLoading   : false,
                dialogVisible: false,
                previewItem  : null,
                uploadDialog : false,
                successUpload: false,
                uploading    : false,
                fileList     : [],
            };
        },
        created() {
            console.log('this.permissions :', this.permissions);
            let path = this.propUrl[ 'path' ];

            let urls         = new URLSearchParams(location.href);
            this.view        = urls.get('view') || 'list';
            this.searchInput = '/' + trim(path, '/');
            console.log('this.routerIndexList :', this.routerIndexList);

        },
        mounted() {
            this.fillOtherData(this.propList, this.propNav, this.propUrl);
        },
        computed: {
            canDelete() {
                return this.permissions && !!this.permissions.delete;
            }
        },
        methods : {
            parseListFile(list) {
                console.log('list :', list);
                return list.map((item) => {
                    item.ext      = getFileExtension(item.name);
                    item.basename = basename(item.name);

                    return item;
                })
            },
            fillOtherData(list, nav, url) {
                this.list = this.parseListFile(list);
                this.nav  = nav;
                this.url  = url;
                this.path = url[ 'path' ];
                this.$nextTick(() => {
                    $('.file-select>input').iCheck({ checkboxClass: 'icheckbox_minimal-blue' });
                })
            },
            handleNavClick(path = null, event) {
                event.preventDefault();

                console.log('path :', path);
                if (!path) {
                    path = '/'
                } else {
                    path = new URL(path).searchParams.get("path");
                }
                console.log('path :', path);
                this.mapLoadList(path);

            },
            mapRenameOrMove(item) {
                this.$prompt('重命名或移动文件', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText : '取消',
                    inputValue       : item[ 'name' ],
                    inputPattern     : /(([\\/])[a-zA-Z0-9\s_@\-^!#$%&+={}\[\]]+).*[^\/]$/,
                    inputErrorMessage: '路径不正确',
                    beforeClose      : async (action, instance, done) => {
                        if (action === 'confirm') {
                            instance.confirmButtonLoading = true;
                            instance.closeOnClickModal    = false;
                            instance.confirmButtonText    = '光速修改中...';
                            let value                     = instance.inputValue;

                            let res                       = await axios({
                                method: 'post',
                                url   : '/api/media/move',
                                data  : {
                                    path: item[ 'name' ],
                                    new : value,
                                }
                            });
                            instance.confirmButtonLoading = false;
                            instance.closeOnClickModal    = true;
                            console.log('res :', res);
                            if (res.status === 204) {
                                this.$message.success('创建文件夹成功.');
                                this.mapLoadList(this.path);

                            } else {
                                this.$message.warning('创建失败,请联系管理员.');
                            }


                        }
                        console.log('action :', action);
                        console.log('instance :', instance);
                        done();

                    }
                });

            },
            mapToListClick(item, event) {
                event.preventDefault();
                event.stopPropagation();

                if (item[ 'isDir' ]) {
                    this.mapClickOfDir(item);
                } else {
                    this.mapClickOfFile(item);
                }
            },
            mapClickOfDir(item) {
                this.mapLoadList(item.name);
            },
            mapClickOfFile(item) {
                this.dialogVisible = true;
                let fileType       = this.previewType(item.ext);
                this.$set(this, 'previewItem', item);

                this.$set(this.previewItem, 'fileType', fileType);
                console.log('fileType :', fileType);
            },
            handleClose(done) {
                this.previewItem   = null;
                this.dialogVisible = false;
                done();
            },
            handleOpenUpload() {
                this.uploadDialog  = true;
                this.successUpload = false;
            },
            handleUploadClose(done) {
                if (this.successUpload) {
                    this.mapLoadList(this.path);
                }
                this.successUpload = false;
                done();
            },
            beforeUpload(file) {
                console.log('beforeUpload file :', file);
                let fileExists = this.checkFileIsExists(file);

                if (fileExists) {
                    return this.$confirm('已有相同文件名的文件存在, 是否覆盖?', '提示', {
                        confirmButtonText: '确定',
                        cancelButtonText : '取消',
                        type             : 'warning'
                    })
                }
                console.log('fileExists :', fileExists);
            },
            checkFileIsExists(file) {
                let res = this.list.findIndex((item) => {
                    return item.basename === file.name;
                });

                let index = this.fileList.findIndex((item) => {
                    return item.name !== file.name
                });

                return res === -1 ? index !== -1 : true;

                console.log('res :', res);
                console.log('index :', index);
                return res;
            },
            handleUploadFile(item) {
                console.log('file :', item);

                let formData = new FormData();
                formData.append('files', item.file);
                formData.append('dir', this.path);

                axios({
                    method: 'post',
                    url   : '/api/media/upload',
                    data  : formData,
                }).then((res) => {
                    console.log('res :', res);
                    item.onSuccess(res);
                    this.$message.success('文件上成功');
                    !this.successUpload && (this.successUpload = true);
                }).catch((e) => {
                    item.onError(e);
                    this.$message.error('上传失败，请重新上传')
                    console.log('报错', e);
                })
            },
            handleMakeFolder() {
                this.$prompt('创建文件夹', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText : '取消',
                    inputPattern     : /^[^\\\\\\/:*?\\"<>|]+$/,
                    inputErrorMessage: '文件名格式不正确',
                    beforeClose      : async (action, instance, done) => {
                        if (action === 'confirm') {
                            instance.confirmButtonLoading = true;
                            instance.closeOnClickModal    = false;
                            instance.confirmButtonText    = '光速创建中...';
                            let value                     = instance.inputValue;


                            let res                       = await axios({
                                method: 'post',
                                url   : '/api/media/makeFolder',
                                data  : {
                                    dir : this.path,
                                    name: value,
                                }
                            });
                            instance.confirmButtonLoading = false;
                            instance.closeOnClickModal    = true;
                            console.log('res :', res);
                            if (res.status === 204) {
                                this.$message.success('创建文件夹成功.');
                                this.mapLoadList(this.path);

                            } else {
                                this.$message.warning('创建失败,请联系管理员.');
                            }


                        }
                        console.log('action :', action);
                        console.log('instance :', instance);
                        done();

                    }
                });

            },
            handleRefresh() {
                this.mapLoadList(this.path);
            },
            handleDelete(files) {
                if (!this.canDelete) {
                    this.$notify({
                        title  : '警告',
                        message: '你没有删除权限,请不要乱操作',
                        type   : 'warning'
                    });
                    return;
                }

                this.$confirm('此操作将永久删除该文件, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText : '取消',
                    type             : 'warning',
                    beforeClose      : async (action, instance, done) => {
                        if (action === 'confirm') {
                            instance.confirmButtonLoading = true;
                            instance.closeOnClickModal    = false;
                            instance.confirmButtonText    = '光速删除中...';

                            let res                       = await this.mapToDelete(files);
                            instance.confirmButtonLoading = false;
                            instance.closeOnClickModal    = true;
                            console.log('res :', res);

                            if (res.status === 204) {
                                this.$message.success('删除成功.');
                                this.mapLoadList(this.path);
                            } else {
                                this.$message.warning('删除失败,请联系管理员.');
                            }
                        }
                        console.log('action :', action);
                        console.log('instance :', instance);
                        done();

                    }
                })
            },
            handleMultipleDelete() {
                let files = $(".file-select input:checked")
                    .map((_, el) => $(el).val())
                    .toArray();
                this.handleDelete(files);
            },
            previewType(ext) {

                for (let [ key, value ] of Object.entries(FileType)) {
                    let reg = new RegExp(`^(${ value })$`);
                    if (reg.exec(ext) !== null) return key;
                }

                return 'unknown'
            },
            async mapToDelete(files) {
                return await axios({
                    url   : '/api/media/delete',
                    method: 'delete',
                    data  : {
                        files,
                    }
                })
            },
            async mapLoadList(path) {
                if (this.boxLoading) return;
                this.boxLoading = true;

                try {
                    let res                = await axios({
                        method: 'get',
                        url   : '/api/media/list',
                        params: {
                            path,
                        }
                    });
                    this.boxLoading        = false;
                    let { list, nav, url } = res.data;
                    this.fillOtherData(list, nav, url);
                    console.log('res :', res);

                } catch (e) {
                    this.boxLoading = false;
                }


            },
            route(name, params = null) {
                let router = this.routerIndexList[ name ];
                let url    = router;
                if (params) {
                    let paramString = new URLSearchParams(params).toString();
                    url             = router + "?" + paramString;
                }

                console.log('url :', url);
                return url;
            }
        },
    }
</script>

<style lang="less">

    .media-manager-container {
        .files {
            list-style: none;
            margin: 0;
            padding: 0;

            & > li {
                float: left;
                width: 150px;
                border: 1px solid #eee;
                margin-bottom: 10px;
                margin-right: 10px;
                position: relative;

                & > .file-select {
                    position: absolute;
                    top: -4px;
                    left: -1px;
                }
            }

            .file-icon {
                text-align: center;
                font-size: 65px;
                color: #666;
                display: block;
                height: 100px;

                &.has-img {
                    padding: 0;

                    & > img {
                        max-width: 100%;
                        height: auto;
                        max-height: 92px;
                    }
                }
            }

            .file-info {
                text-align: center;
                padding: 10px;
                background: #f4f4f4;
            }

            .file-name {
                font-weight: bold;
                color: #666;
                display: block;
                overflow: hidden !important;
                white-space: nowrap !important;
                text-overflow: ellipsis !important;
            }

            .file-size {
                color: #999;
                font-size: 12px;
                display: block;
            }
        }

        .video-preview {
            video {
                width: 100%;
            }
        }
    }


</style>
