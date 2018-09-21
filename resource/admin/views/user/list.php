<?php $this->render('common/header') ?>

<?php $this->render('common/nav'); ?>

        <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header"><?= empty($title) ? '列表' : $title ?></h1>
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <?= empty($title) ? '列表' : $title ?>
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                            <table width="100%" class="table table-striped table-bordered table-hover" id="dataTables-example">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>QQ</th>
                                        <th>昵称</th>
                                        <th>头像</th>
                                        <th>Openid</th>
                                        <th>历史总积分</th>
                                        <th>可用积分</th>
                                        <th>VIP</th>
                                        <th>创建日期</th>
                                        <!-- <th>操作</th> -->
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th>ID</th>
                                        <th>QQ</th>
                                        <th>昵称</th>
                                        <th>头像</th>
                                        <th>Openid</th>
                                        <th>历史总积分</th>
                                        <th>可用积分</th>
                                        <th>VIP</th>
                                        <th>创建日期</th>
                                        <!-- <th>操作</th> -->
                                    </tr>
                                </tfoot>
                            </table>
                            <!-- /.table-responsive -->
                        </div>
                        <!-- /.panel-body -->
                    </div>
                    <!-- /.panel -->
                </div>
                <!-- /.col-lg-12 -->
            </div>
        </div>
        <!-- /#page-wrapper -->

<?php $this->render('common/footer'); ?>
