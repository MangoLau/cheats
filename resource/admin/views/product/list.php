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
                        <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#addProductModal">添加</button>
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
                                        <th>金额(分)</th>
                                        <th>类型</th>
                                        <th>数量</th>
                                        <th>状态</th>
                                        <th>创建日期</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th>ID</th>
                                        <th>金额(分)</th>
                                        <th>类型</th>
                                        <th>数量</th>
                                        <th>状态</th>
                                        <th>创建日期</th>
                                        <th>操作</th>
                                    </tr>
                                </tfoot>
                            </table>
                            <!-- /.table-responsive -->
                            <!-- Modal -->
                            <div class="modal fade" id="ProductModal" tabindex="-1" role="dialog" aria-labelledby="ProductModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                            <h4 class="modal-title" id="ProductModalLabel">确定上线</h4>
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
                                            <button type="button" class="btn btn-primary status-submit" data-url="/product" data-method="">确定</button>
                                        </div>
                                    </div>
                                    <!-- /.modal-content -->
                                </div>
                                <!-- /.modal-dialog -->
                            </div>

                            <!-- Modal -->
                            <div class="add modal fade" id="addProductModal" tabindex="-1" role="dialog" aria-labelledby="addProductModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-md">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                            <h4 class="modal-title" id="addProductModalLabel">添加产品</h4>
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
                                                  <label for="money" class="col-sm-2 control-label">金额(分)</label>
                                                  <div class="col-sm-10">
                                                    <input type="number" class="form-control" name="money" id="money" placeholder="请输入金额">
                                                  </div>
                                                </div>
                                                <div class="form-group">
                                                  <label for="type" class="col-sm-2 control-label">类型</label>
                                                  <div class="col-sm-10">
                                                    <select id="type" class="form-control" name="type">
                                                        <option value="1">积分</option>
                                                        <option value="2">VIP(月)</option>
                                                    </select>
                                                  </div>
                                                </div>
                                                <div class="form-group">
                                                  <label for="amount" class="col-sm-2 control-label">数量</label>
                                                  <div class="col-sm-10">
                                                    <input type="number" class="form-control" name="amount" id="amount" placeholder="数量">
                                                  </div>
                                                </div>
                                            </form>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                                            <button type="button" class="btn btn-primary submit" data-url="/product">确定</button>
                                        </div>
                                    </div>
                                    <!-- /.modal-content -->
                                </div>
                                <!-- /.modal-dialog -->
                            </div>

                            <div class="edit modal fade" id="editProductModal" tabindex="-1" role="dialog" aria-labelledby="editProductModalLabel" aria-hidden="true">
                              <div class="modal-dialog modal-md">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                        <h4 class="modal-title" id="editProductModalLabel">编辑充值产品</h4>
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
                                              <label for="edit-money" class="col-sm-2 control-label">金额(分)</label>
                                              <div class="col-sm-10">
                                                <input type="text" class="form-control" name="edit-money" id="edit-money" placeholder="key">
                                              </div>
                                            </div>
                                            <div class="form-group">
                                              <label for="edit-type" class="col-sm-2 control-label">类型</label>
                                              <div class="col-sm-10">
                                                <select id="edit-type" class="form-control" name="edit-type">
                                                    <option value="1">积分</option>
                                                    <option value="2">VIP(月)</option>
                                                </select>
                                              </div>
                                            </div>
                                            <div class="form-group">
                                              <label for="edit-amount" class="col-sm-2 control-label">数量</label>
                                              <div class="col-sm-10">
                                                <input type="text" class="form-control" name="edit-amount" id="edit-amount" placeholder="值">
                                              </div>
                                            </div>
                                            <input type="hidden" name="id" value="0" />
                                        </form>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                                        <button type="button" class="btn btn-primary submit" data-url="/product/update">确定</button>
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
