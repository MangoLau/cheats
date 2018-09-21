<?php $this->render('common/header') ?>

<?php $this->render('common/nav'); ?>
        
        <style type="text/css">
            .form-inline .input-group>.form-control {
                width: auto;
            }
        </style>

        <div id="page-wrapper">
            <div class="row">
                <h1 class="page-header">收入统计<small>(单位:元)</small></h1>
                <div class="col-lg-6 col-lg-offset-3 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 col-xs-12">
                    <form class="form-horizontal" action="/" method="GET">
                        <div class="input-group input-daterange">
                            <input type="text" class="form-control" name="start-date" placeholder="开始日期" value="<?= $start_date ?>">
                            <span class="input-group-addon">to</span>
                            <input type="text" class="form-control" name="end-date" placeholder="截止日期" value="<?= $end_date ?>">
                        </div>
                        <br/>
                        <div align="center">
                            <button class="btn btn-info" type="sbumit">确定</button>
                        </div>
                    </form>
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <br/>
            <!-- /.row -->
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            充值类型（积分、VIP）
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                            <div id="recharge-types"></div>
                        </div>
                        <!-- /.panel-body -->
                    </div>
                    <!-- /.panel -->
                </div>
                <!-- /.col-lg-12 -->
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            充值支付方式（微信、支付宝、其他）
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                            <div id="pay_types"></div>
                        </div>
                        <!-- /.panel-body -->
                    </div>
                    <!-- /.panel -->
                </div>
                <!-- /.col-lg-12 -->
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            充值平台（安卓、iOS）
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                            <div id="pay_platforms"></div>
                        </div>
                        <!-- /.panel-body -->
                    </div>
                    <!-- /.panel -->
                </div>
                <!-- /.col-lg-12 -->
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            app渠道
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                            <div id="pay_channels"></div>
                        </div>
                        <!-- /.panel-body -->
                    </div>
                    <!-- /.panel -->
                </div>
            </div>
            <!-- /.row -->
            </div>
        </div>

        <!-- /#page-wrapper -->

        <script src="../assets/vendor/raphael/raphael.min.js"></script>
        <script src="../assets/vendor/morrisjs/morris.min.js"></script>
        <script src="../assets/js/bootstrap-datepicker.min.js"></script>
        <script src="../assets/js/bootstrap-datepicker.zh-CN.min.js"></script>
        <script type="text/javascript">
            $('input[name="start-date"]').datepicker({
                format: 'yyyy-mm-dd',
                language: 'zh-CN',
                autoclose: true,
                todayBtn: 'linked',
                todayHighlight: true
            });

            $('input[name="end-date"]').datepicker({
                format: 'yyyy-mm-dd',
                language: 'zh-CN',
                autoclose: true,
                todayBtn: 'linked',
                todayHighlight: true
            });

            Morris.Bar({
                element: 'recharge-types',
                data: <?= json_encode($recharge_type_data) ?>,
                xkey: 'created_day',
                ykeys: ['total_vip_fee', 'total_scores_fee'],
                labels: ['购买VIP', '购买积分'],
                // pointSize: 2,
                hideHover: 'auto',
                resize: true
            });
        
            Morris.Bar({
                element: 'pay_types',
                data: <?= json_encode($pay_type_data) ?>,
                xkey: 'created_day',
                ykeys: ['total_wechat_fee', 'total_alipay_fee', 'total_other_fee'],
                labels: ['微信支付', '支付宝支付', '其他'],
                hideHover: 'auto',
                resize: true
            });

            Morris.Bar({
                element: 'pay_platforms',
                data: <?= json_encode($pay_platform_data) ?>,
                xkey: 'created_day',
                ykeys: ['total_android_fee', 'total_ios_fee'],
                labels: ['安卓', 'iOS'],
                // pointSize: 2,
                hideHover: 'auto',
                resize: true
            });

            Morris.Bar({
                element: 'pay_channels',
                data: <?= json_encode($channel_data) ?>,
                xkey: 'created_day',
                ykeys: <?= json_encode($morris_channel['ykeys']) ?>,
                labels: <?= json_encode($morris_channel['labels']) ?>,
                // pointSize: 2,
                hideHover: 'auto',
                resize: true
            });
        </script>

<?php $this->render('common/footer'); ?>
