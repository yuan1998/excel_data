<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laravel</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">

    <!-- Styles -->
    <style>
        html, body {
            background-color: #fff;
            color: #636b6f;
            font-family: 'Nunito', sans-serif;
            font-weight: 200;
            height: 100vh;
            margin: 0;
        }

        .full-height {
            height: 100vh;
        }

        .flex-center {
            align-items: center;
            display: flex;
            justify-content: center;
        }

        .position-ref {
            position: relative;
        }

        .top-right {
            position: absolute;
            right: 10px;
            top: 18px;
        }

        .content {
            text-align: center;
        }

        .title {
            font-size: 84px;
        }

        .links > a {
            color: #636b6f;
            padding: 0 25px;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: .1rem;
            text-decoration: none;
            text-transform: uppercase;
        }

        .m-b-md {
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
<div class="flex-center position-ref full-height">
    @if (Route::has('login'))
        <div class="top-right links">
            @auth
                <a href="{{ url('/home') }}">Home</a>
            @else
                <a href="{{ route('login') }}">Login</a>

                @if (Route::has('register'))
                    <a href="{{ route('register') }}">Register</a>
                @endif
            @endauth
        </div>
    @endif

    <div class="content">
        <div class="title m-b-md">
            Laravel
        </div>

        <div class="links">
            <a href="https://laravel.com/docs">Docs</a>
            <a href="https://laracasts.com">Laracasts</a>
            <a href="https://laravel-news.com">News</a>
            <a href="https://blog.laravel.com">Blog</a>
            <a href="https://nova.laravel.com">Nova</a>
            <a href="https://forge.laravel.com">Forge</a>
            <a href="https://github.com/laravel/laravel">GitHub</a>
        </div>
    </div>
</div>

<table data-toggle="datagrid" class="table table-bordered table-hover table-striped"
       data-options="{                                 height: '100%',                                 fieldSortable: false,                                 filterThead: false,                                 sortAll: false,                                 filterAll: false,                                 filterMult: false,                                 showLinenumber: false,                                 columnFilter: false,                                 columnSort: false,                                 paging: false,                                 fullGrid: true,                                 hScrollbar: true                             }">
    <thead>
    <tr>
        <th data-options=""><input type="checkbox" class="checkboxCtrl" data-group="ids" onclick="checkall(this);"/>
        </th>
        <th data-options="">序号</th>
        <th data-options="">操作</th>
        <th data-options="">是否下单</th>
        <th data-options="">是否到院</th>
        <th data-options="">网电客户</th>
        <th data-options="">电话</th>
        <th data-options="">性别</th>
        <th data-options="">建档类型</th>
        <th data-options="">线上客服</th>
        <th data-options="">回访次数</th>
        <th data-options="">建档人</th>
        <th data-options="">回访人</th>
        <th data-options="">客户推荐人</th>
        <th data-options="">建档时间</th>
        <th data-options="">到院时间</th>
        <th data-options="">最后回访时间</th>
        <th data-options="">媒介类型</th>
        <th data-options="">媒介来源</th>
        <th data-options="">美容院类型</th>
        <th data-options="">美容院名称</th>
        <th data-options="">标签名称</th>
        <th data-options="">备注</th>
        <th data-options="">关注问题</th>
        <th data-options="">婚姻状况</th>
        <th data-options="">年龄</th>
        <th data-options="">经济能力</th>
        <th data-options="">省份</th>
        <th data-options="">县市</th>
        <th data-options="">区</th>
        <th data-options="">地址</th>
        <th data-options="">职业</th>
        <th data-options="">微信号</th>
        <th data-options="">QQ</th>
        <th data-options="">预约号</th>
        <th data-options="">访客ID</th>
        <th data-options="">员工推荐人</th>
    </tr>
    </thead>
    <tbody>
    <tr data-id="8F9584535CC141A791C7AAD90172D894" onclick="selectcheckbox(this);" class="">
        <td class="center"><input type="checkbox" class="checkboxes" name='ids' value='8F9584535CC141A791C7AAD90172D894'
                                  data-phone="177****1660" data-isordered="Y"
                                  data-custid="8F9584535CC141A791C7AAD90172D894"/></td>
        <td class="center">1</td>
        <td><a title='临客详情_' data-toggle="navtab"
               href="/Reservation/TempCustSearch/Edit/8F9584535CC141A791C7AAD90172D894" data-options=""> <i
                        class="icon-edit"></i> 编辑 </a></td>
        <td><b class="color-lovered">是</b></td>
        <td><b class="color-lovered">是 </b></td>
        <td><input type="hidden" id="custid" name="custid" value="8F9584535CC141A791C7AAD90172D894"/> <a class=""
                                                                                                         href="javascript:void(0);"
                                                                                                         onclick="EditTempCustInfo('8F9584535CC141A791C7AAD90172D894');">赵凤</a>
        </td>
        <td> 177****1660</td>
        <td>女</td>
        <td>水光针-水光针-皮肤</td>
        <td>朱恬</td>
        <td class="right">1</td>
        <td>朱恬</td>
        <td>朱恬</td>
        <td></td>
        <td>2019-10-01 22:29:04</td>
        <td>2019-10-02 17:04:54</td>
        <td>2019-10-02 19:45:39</td>
        <td>老带新</td>
        <td>老带新</td>
        <td></td>
        <td></td>
        <td></td>
        <td title="1.咨询项目：水光针 2.顾客情况：无 3.设计方案：无 4.专家及报价：来院报价 5.顾客反馈和预算：无 6.备注：朋友推荐" style="word-break:break-all;">
            1.咨询项目：水光针 2.顾客情况：无 3.设计方案：无 4.专家及报价：来院报价 5.顾客反馈和预算：无 6.备注：朋友推荐
        </td>
        <td>治疗是否无痛,医生的技术,价格</td>
        <td></td>
        <td class="right">32</td>
        <td>低(1W以下)</td>
        <td>陕西省</td>
        <td>西安市</td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td>朋友介绍</td>
        <td></td>
        <td></td>
        <td></td>
    </tr>
    </tbody>
</table>
</body>
</html>
