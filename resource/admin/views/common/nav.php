<body>

    <div id="wrapper">

        <!-- Navigation -->
        <nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="/">空间刷赞大师后台</a>
            </div>
            <!-- /.navbar-header -->

            <ul class="nav navbar-top-links navbar-right">
                <!-- /.dropdown -->
                <li class="dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                        <i class="fa fa-user fa-fw"></i> <?=$_SESSION['userinfo']['username']?> <i class="fa fa-caret-down"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-user">
                        <li><a href="/profile"><i class="fa fa-user fa-fw"></i> 个人中心</a>
                        </li>
                        <li class="divider"></li>
                        <li><a href="/logout"><i class="fa fa-sign-out fa-fw"></i> 退出</a>
                        </li>
                    </ul>
                    <!-- /.dropdown-user -->
                </li>
                <!-- /.dropdown -->
            </ul>
            <!-- /.navbar-top-links -->

            <!-- 左侧栏菜单 -->
            <div class="navbar-default sidebar" role="navigation">
                <div class="sidebar-nav navbar-collapse">
                    <ul class="nav" id="side-menu">
                        <li>
                            <a href="/"><i class="fa fa-dashboard fa-fw"></i> 概览</a>
                        </li>
                        <li>
                            <a href="/users"><i class="fa fa-users fa-fw"></i> 用户</a>
                        </li>
                        <li>
                            <a href="/attendances"><i class="fa fa-flag fa-fw"></i> 签到</a>
                        </li>

                        <li>
                            <a href="/cards"><i class="fa fa-credit-card fa-fw"></i> 卡密列表</a>
                        </li>
                        
                        <li>
                            <a href="/cheatproducts"><i class="fa fa-database fa-fw"></i> 刷赞产品列表</a>
                        </li>

                        <li>
                            <a href="/channels"><i class="fa fa-bar-chart-o fa-fw"></i> 渠道列表</a>
                        </li>

                        <li>
                            <a href="/cheats"><i class="fa fa-th fa-fw"></i> 刷赞类型</a>
                        </li>

                        <li>
                            <a href="/configs"><i class="fa fa-windows fa-fw"></i> 动态参数</a>
                        </li>

                        <li>
                            <a href="/hotpeoples"><i class="fa fa-user fa-fw"></i> 空间红人榜</a>
                        </li>

                        <li>
                            <a href="#"><i class="fa fa-tasks fa-fw"></i> 订单<span class="fa arrow"></span></a>
                            <ul class="nav nav-second-level">
                                <li>
                                    <a href="/orders">所有订单</a>
                                </li>
                                <li>
                                    <a href="/orders/laquanquan">拉圈圈</a>
                                </li>
                                <li>
                                    <a href="/orders/dealing">处理中订单</a>
                                </li>
                            </ul>
                            <!-- /.nav-second-level -->
                        </li>

                        <li>
                            <a href="/products"><i class="fa fa-users fa-fw"></i> 充值产品</a>
                        </li>

                        <li>
                            <a href="/recharges"><i class="fa fa-institution"></i> 充值列表</a>
                        </li>

                        <li>
                            <a href="/banners"><i class="fa fa-archive fa-fw"></i> banner列表</a>
                        </li>

                        <li>
                            <a href="/feedbacks"><i class="fa fa-comments fa-fw"></i> 留言板</a>
                        </li>

                        <li>
                            <a href="/complaints"><i class="fa fa-comments fa-fw"></i> 投诉</a>
                        </li>

                        <li>
                            <a href="#"><i class="glyphicon glyphicon-user"></i> 管理员<span class="fa arrow"></span></a>
                            <ul class="nav nav-second-level">
                                <li>
                                    <a href="/administors">列表</a>
                                </li>
                                <li>
                                    <a href="/profile">设置</a>
                                </li>
                                <li>
                                    <a href="/add">添加</a>
                                </li>
                            </ul>
                            <!-- /.nav-second-level -->
                        </li>
                    </ul>
                </div>
                <!-- /.sidebar-collapse -->
            </div>
            <!-- /.navbar-static-side -->
        </nav>