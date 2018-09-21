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
                        <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#addCardModal">添加</button>
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
                                        <th>类型</th>
                                        <th>账号</th>
                                        <th>密码</th>
                                        <th>总额度</th>
                                        <th>剩余额度</th>
                                        <th>状态</th>
                                        <th>创建日期</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th>ID</th>
                                        <th>类型</th>
                                        <th>账号</th>
                                        <th>密码</th>
                                        <th>总额度</th>
                                        <th>剩余额度</th>
                                        <th>状态</th>
                                        <th>创建日期</th>
                                        <th>操作</th>
                                    </tr>
                                </tfoot>
                            </table>
                            <!-- /.table-responsive -->
                            <!-- Modal -->
                            <div class="modal fade" id="CardModal" tabindex="-1" role="dialog" aria-labelledby="CardModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                            <h4 class="modal-title" id="CardModalLabel">确定上线</h4>
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
                                            <button type="button" class="btn btn-primary status-submit" data-url="/card" data-method="">确定</button>
                                        </div>
                                    </div>
                                    <!-- /.modal-content -->
                                </div>
                                <!-- /.modal-dialog -->
                            </div>

                            <!-- Modal -->
                            <div class="add modal fade" id="addCardModal" tabindex="-1" role="dialog" aria-labelledby="addCardModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-md">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                            <h4 class="modal-title" id="addCardModalLabel">添加卡密</h4>
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
                                                  <label for="type" class="col-sm-2 control-label">类型</label>
                                                  <div class="col-sm-10">
                                                    <select id="type" class="form-control" name="type">
                                                        <?php foreach($cheats as $k => $c): ?>
                                                            <option value="<?= $c->id ?>"><?= $c->title ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                  </div>
                                                </div>
                                                <div class="form-group">
                                                  <label for="identify" class="col-sm-2 control-label">卡密</label>
                                                  <div class="col-sm-10">
                                                    <input type="text" class="form-control" name="identify" id="identify" placeholder="请输入卡密号码">
                                                  </div>
                                                </div>
                                                <div class="form-group">
                                                  <label for="password" class="col-sm-2 control-label">卡密密码</label>
                                                  <div class="col-sm-10">
                                                    <input type="text" class="form-control" name="password" id="password" placeholder="请输入卡密密码, 默认为空">
                                                  </div>
                                                </div>
                                                <div class="form-group">
                                                  <label for="total" class="col-sm-2 control-label">总额度</label>
                                                  <div class="col-sm-10">
                                                    <input type="number" class="form-control" name="total" id="total" placeholder="请输入总额度">
                                                  </div>
                                                </div>
                                                <div class="form-group">
                                                  <label for="remaining" class="col-sm-2 control-label">剩余额度</label>
                                                  <div class="col-sm-10">
                                                    <input type="number" class="form-control" name="remaining" id="remaining" placeholder="请输入剩余额度">
                                                  </div>
                                                </div>
                                            </form>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                                            <button type="button" class="btn btn-primary submit" data-url="/card">确定</button>
                                        </div>
                                    </div>
                                    <!-- /.modal-content -->
                                </div>
                                <!-- /.modal-dialog -->
                            </div>

                            <!-- Modal -->
                            <div class="edit modal fade" id="editConfigModal" tabindex="-1" role="dialog" aria-labelledby="editConfigModalLabel" aria-hidden="true">
                              <div class="modal-dialog modal-md">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                        <h4 class="modal-title" id="editConfigModalLabel">编辑卡密信息</h4>
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
                                              <label for="edit-type" class="col-sm-2 control-label">类型</label>
                                              <div class="col-sm-10">
                                                <select id="edit-type" class="form-control" name="edit-type">
                                                    <?php foreach($cheats as $k => $c): ?>
                                                        <option value="<?= $c->id ?>"><?= $c->title ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                              </div>
                                            </div>
                                            <div class="form-group">
                                              <label for="edit-identify" class="col-sm-2 control-label">卡密</label>
                                              <div class="col-sm-10">
                                                <input type="text" class="form-control" name="edit-identify" id="edit-identify" placeholder="请输入卡密号码" readonly>
                                              </div>
                                            </div>
                                            <div class="form-group">
                                              <label for="edit-password" class="col-sm-2 control-label">卡密密码</label>
                                              <div class="col-sm-10">
                                                <input type="text" class="form-control" name="edit-password" id="edit-password" placeholder="请输入卡密密码, 默认为空">
                                              </div>
                                            </div>
                                            <div class="form-group">
                                              <label for="edit-total" class="col-sm-2 control-label">总额度</label>
                                              <div class="col-sm-10">
                                                <input type="number" class="form-control" name="edit-total" id="edit-total" placeholder="请输入总额度">
                                              </div>
                                            </div>
                                            <div class="form-group">
                                              <label for="edit-remaining" class="col-sm-2 control-label">剩余额度</label>
                                              <div class="col-sm-10">
                                                <input type="number" class="form-control" name="edit-remaining" id="edit-remaining" placeholder="请输入剩余额度">
                                              </div>
                                            </div>
                                            <input type="hidden" name="id" value="0" />
                                        </form>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                                        <button type="button" class="btn btn-primary submit" data-url="/card/update">确定</button>
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
