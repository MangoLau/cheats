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
                        <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#addConfigModal">添加</button>
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
                                        <th>key</th>
                                        <th>value</th>
                                        <th>平台</th>
                                        <th>备注</th>
                                        <th>状态</th>
                                        <th>创建时间</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th>ID</th>
                                        <th>key</th>
                                        <th>value</th>
                                        <th>平台</th>
                                        <th>备注</th>
                                        <th>状态</th>
                                        <th>创建时间</th>
                                        <th>操作</th>
                                    </tr>
                                </tfoot>
                            </table>
                            <!-- /.table-responsive -->
                            <div class="modal fade" id="ConfigModal" tabindex="-1" role="dialog" aria-labelledby="ConfigModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                            <h4 class="modal-title" id="ConfigModalLabel">确定上线</h4>
                                        </div>
                                        <div class="modal-body">
                                            <div class="notifications hide">
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
                                            <button type="button" class="btn btn-primary status-submit" data-url="/config" data-method="">确定</button>
                                        </div>
                                    </div>
                                    <!-- /.modal-content -->
                                </div>
                                <!-- /.modal-dialog -->
                            </div>

                            <!-- Modal -->
                            <div class="add modal fade" id="addConfigModal" tabindex="-1" role="dialog" aria-labelledby="addConfigModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-md">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                            <h4 class="modal-title" id="addConfigModalLabel">添加动态参数</h4>
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
                                                  <label for="platform" class="col-sm-2 control-label">使用平台</label>
                                                  <div class="col-sm-10">
                                                    <select id="platform" class="form-control" name="platform">
                                                        <option value="1">服务端</option>
                                                        <option value="2">客户端</option>
                                                        <option value="3">所有</option>
                                                    </select>
                                                  </div>
                                                </div>
                                                <div class="form-group">
                                                  <label for="key" class="col-sm-2 control-label">key</label>
                                                  <div class="col-sm-10">
                                                    <input type="text" class="form-control" name="key" id="key" placeholder="key">
                                                  </div>
                                                </div>
                                                <div class="form-group">
                                                  <label for="value" class="col-sm-2 control-label">value</label>
                                                  <div class="col-sm-10">
                                                    <input type="text" class="form-control" name="value" id="value" placeholder="值">
                                                  </div>
                                                </div>
                                                <div class="form-group">
                                                  <label for="remark" class="col-sm-2 control-label">备注</label>
                                                  <div class="col-sm-10">
                                                    <input type="text" class="form-control" name="remark" id="remark" placeholder="备注">
                                                  </div>
                                                </div>
                                            </form>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                                            <button type="button" class="btn btn-primary submit" data-url="/config">确定</button>
                                        </div>
                                    </div>
                                    <!-- /.modal-content -->
                                </div>
                                <!-- /.modal-dialog -->
                            </div>
                        </div>

                        <!-- Modal -->
                        <div class="edit modal fade" id="editConfigModal" tabindex="-1" role="dialog" aria-labelledby="editConfigModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-md">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                        <h4 class="modal-title" id="editConfigModalLabel">编辑动态参数</h4>
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
                                              <label for="edit-platform" class="col-sm-2 control-label">使用平台</label>
                                              <div class="col-sm-10">
                                                <select id="edit-platform" class="form-control" name="edit-platform">
                                                    <option value="1">服务端</option>
                                                    <option value="2">客户端</option>
                                                    <option value="3">所有</option>
                                                </select>
                                              </div>
                                            </div>
                                            <div class="form-group">
                                              <label for="edit-key" class="col-sm-2 control-label">key</label>
                                              <div class="col-sm-10">
                                                <input type="text" class="form-control" name="edit-key" id="edit-key" placeholder="key" readonly>
                                              </div>
                                            </div>
                                            <div class="form-group hidden" id="value_input">
                                              <label for="edit-value" class="col-sm-2 control-label">value</label>
                                              <div class="col-sm-10">
                                                <input type="text" class="form-control" name="edit-value" id="edit-value" placeholder="值">
                                              </div>
                                            </div>
                                            <div class="form-group hidden" id="value_radio">
                                                <label class="col-sm-2 control-label">选项</label>
                                                <div class="col-sm-10">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                              <label for="edit-remark" class="col-sm-2 control-label">备注</label>
                                              <div class="col-sm-10">
                                                <input type="text" class="form-control" name="edit-remark" id="edit-remark" placeholder="备注">
                                              </div>
                                            </div>
                                            <input type="hidden" name="id" value="0" />
                                        </form>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                                        <button type="button" class="btn btn-primary submit" data-url="/config/update">确定</button>
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
