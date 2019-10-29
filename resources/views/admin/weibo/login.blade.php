<!doctype html>
<html lang="zh-cn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>登录 - Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/element-ui@2.12.0/lib/theme-chalk/index.css">
    <style>
        * {
            -webkit-box-sizing: border-box;
            -moz-box-sizing: border-box;
            box-sizing: border-box;
        }

        .login-container {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 500px;
            height: 340px;
            border: 1px solid #000;
            padding: 20px 40px 30px 10px;
        }

        .login-container .title {
            text-align: center;
            font-size: 55px;
            color: #555;
            padding-bottom: 20px;
        }
    </style>
</head>
<body>
@verbatim
    <div id="app" style="display: none;" :style="'display: block;'">
        <div class="login-container">
            <div class="title">
                Log In
            </div>
            <el-form label-position="right"
                     :model="form"
                     :rule="rules"
                     ref="form"
                     label-width="80px">
                <el-form-item label="用户名">
                    <el-input v-model="form.username"></el-input>
                </el-form-item>
                <el-form-item label="密码">
                    <el-input type="password" v-model="form.password"></el-input>
                </el-form-item>
            </el-form>
            <div style="text-align: center;padding-top: 15px;">
                <el-button type="primary" @click="handleSubmit">
                    登录
                </el-button>
                <el-button>
                    重置
                </el-button>
            </div>
        </div>
        {{ test }}
    </div>
@endverbatim
<script src="//cdn.jsdelivr.net/npm/vue@2.6.10/dist/vue.min.js"></script>
<script src="//cdn.jsdelivr.net/npm/axios@0.19.0/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/element-ui@2.12.0/lib/index.js"></script>
<script>
    ;(function () {
        'use strict';

        new Vue({
            el     : '#app',
            data   : {
                test : 'hahaha',
                form : {
                    username: '',
                    password: '',
                },
                rules: {
                    username: [
                        { required: true, message: '请输入用户名', trigger: 'blur' },
                    ],
                    password: [
                        { required: true, message: '请输入密码', trigger: 'blur' },
                    ],
                }
            },
            methods: {
                handleSubmit() {
                    this.$refs.form.validate((valid) => {
                        if (valid) {
                            axios.post('/api/weibo/authenticate', this.form)
                                 .then((a, b, c) => {
                                     console.log('a :', a);
                                     console.log('b :', b);
                                     console.log('c :', c);
                                 })
                                 .catch((res) => {
                                     console.log('res :', res);
                                 });
                        }

                    });

                }
            }

        });

    })();
</script>
</body>
</html>
