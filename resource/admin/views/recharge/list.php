<?php $this->render('common/header') ?>

<?php $this->render('common/nav'); ?>
        
        <style type="text/css">
            .form-inline .input-group>.form-control {
                width: auto;
            }
        </style>

        <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header"><?= empty($title) ? '列表' : $title ?></h1>
                    <p class="text-right">
                        <a class="btn btn-success btn-sm" href="/recharges/export">导出excel</a>
                    </p>
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
                                        <th>UID</th>
                                        <!-- <th>比目平台订单号</th> -->
                                        <th>金额(分)</th>
                                        <th>类型</th>
                                        <th>数量</th>
                                        <th>支付方式</th>
                                        <th>平台</th>
                                        <th>渠道</th>
                                        <th>状态</th>
                                        <th>创建时间</th>
                                        <!-- <th>操作</th> -->
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th>ID</th>
                                        <th>UID</th>
                                        <!-- <th>比目平台订单号</th> -->
                                        <th>金额(分)</th>
                                        <th>类型</th>
                                        <th>数量</th>
                                        <th>支付方式</th>
                                        <th>平台</th>
                                        <th>渠道</th>
                                        <th>状态</th>
                                        <th>创建时间</th>
                                        <!-- <th>操作</th> -->
                                    </tr>
                                </tfoot>
                            </table>
                            <!-- /.table-responsive -->
                            <!-- Modal -->
                            <div class="edit modal fade" id="editConfigModal" tabindex="-1" role="dialog" aria-labelledby="editConfigModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-md">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                            <h4 class="modal-title" id="editConfigModalLabel">更新bmob订单号</h4>
                                        </div>
                                        <div class="modal-body">
                                            <div class="notifications hide">
                                                <div class="alert alert-success alert-dismissable">
                                                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                                    <span class="blockRetMsg">成功</span>
                                                </div>
                                            </div>
                                            <form class="form-horizontal">
                                                <div class="form-group">
                                                    <label for="edit-id" class="col-sm-2 control-label">订单id</label>
                                                    <div class="col-sm-10">
                                                        <input type="text" class="form-control" name="edit-id" id="edit-id" placeholder="id" readonly>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="edit-bmoborderid" class="col-sm-2 control-label">比目订单号</label>
                                                    <div class="col-sm-10">
                                                        <input type="text" class="form-control" name="edit-bmoborderid" id="edit-bmoborderid" placeholder="比目订单号">
                                                    </div>
                                                </div>
                                                <input type="hidden" name="id" value="0" />
                                            </form>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                                            <button type="button" class="btn btn-primary submit" data-url="/recharge/updateBmobOrderID">确定</button>
                                        </div>
                                    </div>
                                <!-- /.modal-content -->
                            </div>
                            <!-- /.modal-dialog -->
                        </div>
                        </div>
                    <!-- /.panel -->
                </div>
                <!-- /.col-lg-12 -->
            </div>
        </div>

        <!-- /#page-wrapper -->

<?php $this->render('common/footer'); ?>
