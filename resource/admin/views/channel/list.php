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
                        <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#addChannelModal">添加</button>
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
                                        <th>标识</th>
                                        <th>备注</th>
                                        <th>状态</th>
                                        <th>开启的业务</th>
                                        <th>创建日期</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th>ID</th>
                                        <th>标识</th>
                                        <th>备注</th>
                                        <th>状态</th>
                                        <th>开启的业务</th>
                                        <th>创建日期</th>
                                        <th>操作</th>
                                    </tr>
                                </tfoot>
                            </table>
                            <!-- /.table-responsive -->
                            <div class="modal fade" id="ChannelModal" tabindex="-1" role="dialog" aria-labelledby="ChannelModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                            <h4 class="modal-title" id="ChannelModalLabel">确定上线</h4>
                                        </div>
                                        <div class="modal-body">
                                            <div class="notifications hidden">
                                                <div class="alert alert-success alert-dismissable">
                                                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                                    <span class="blockRetMsg">成功</span>
                                                </div>
                                            </div>
                                            <p class="content"><code></code></p>
                                            <input type="hidden" name="id" value="0">
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                                            <button type="button" class="btn btn-primary status-submit" data-url="/channel" data-method="">确定</button>
                                        </div>
                                    </div>
                                    <!-- /.modal-content -->
                                </div>
                                <!-- /.modal-dialog -->
                            </div>

                            <!-- Modal -->
                            <div class="add modal fade" id="addChannelModal" tabindex="-1" role="dialog" aria-labelledby="addChannelModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-md">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                            <h4 class="modal-title" id="addChannelModalLabel">添加渠道</h4>
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
                                                  <label for="name" class="col-sm-2 control-label">标识</label>
                                                  <div class="col-sm-10">
                                                    <input type="text" class="form-control" name="name" id="name" placeholder="标识">
                                                  </div>
                                                </div>
                                                <div class="form-group">
                                                  <label for="remark" class="col-sm-2 control-label">备注</label>
                                                  <div class="col-sm-10">
                                                    <input type="text" class="form-control" name="remark" id="remark" placeholder="备注">
                                                  </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="cheats" class="col-sm-2 control-label">开启的业务</label>
                                                    <div class="col-sm-10">
                                                        <div class="checkbox">
                                                            <label>
                                                                <input type="checkbox" class="check-all" ><strong>全选</strong>
                                                            </label>
                                                        </div>
                                                        <?php foreach ($cheats as $k => $v): ?>
                                                            <div class="checkbox">
                                                                <label>
                                                                    <input type="checkbox" class="check-single" name="cheats[]" value="<?= $v->id ?>"><?= $v->title ?>
                                                                </label>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                                            <button type="button" class="btn btn-primary submit" data-url="/channel">确定</button>
                                        </div>
                                    </div>
                                    <!-- /.modal-content -->
                                </div>
                                <!-- /.modal-dialog -->
                            </div>
                        </div>

                        <!-- Modal -->
                        <div class="edit modal fade" id="editChannelModal" tabindex="-1" role="dialog" aria-labelledby="editChannelModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-md">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                        <h4 class="modal-title" id="editChannelModalLabel">编辑渠道</h4>
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
                                              <label for="edit-name" class="col-sm-2 control-label">name</label>
                                              <div class="col-sm-10">
                                                <input type="text" class="form-control" name="edit-name" id="edit-name" placeholder="标识" readonly>
                                              </div>
                                            </div>
                                            <div class="form-group">
                                              <label for="edit-remark" class="col-sm-2 control-label">备注</label>
                                              <div class="col-sm-10">
                                                <input type="text" class="form-control" name="edit-remark" id="edit-remark" placeholder="备注">
                                              </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="cheats" class="col-sm-2 control-label">开启的业务</label>
                                                <div class="col-sm-10">
                                                    <div class="checkbox">
                                                        <label>
                                                            <input type="checkbox" class="check-all" ><strong>全选</strong>
                                                        </label>
                                                    </div>
                                                    <?php foreach ($cheats as $k => $v): ?>
                                                        <div class="checkbox">
                                                            <label>
                                                                <input type="checkbox" class="check-single" name="cheats[]" value="<?= $v->id ?>"><?= $v->title ?>
                                                            </label>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                            <input type="hidden" name="id" value="0" />
                                        </form>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                                        <button type="button" class="btn btn-primary submit" data-url="/channel/update">确定</button>
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
