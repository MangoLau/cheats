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
                        <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#addCheatProductModal">添加</button>
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
                                        <th>数量</th>
                                        <th>积分</th>
                                        <th>状态</th>
                                        <th>创建日期</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th>ID</th>
                                        <th>类型</th>
                                        <th>数量</th>
                                        <th>积分</th>
                                        <th>状态</th>
                                        <th>创建日期</th>
                                        <th>操作</th>
                                    </tr>
                                </tfoot>
                            </table>
                            <!-- /.table-responsive -->
                            <!-- Modal -->
                            <div class="modal fade" id="CheatProductModal" tabindex="-1" role="dialog" aria-labelledby="CheatProductModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                            <h4 class="modal-title" id="CheatProductModalLabel">确定上线</h4>
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
                                            <button type="button" class="btn btn-primary status-submit" data-url="/cheatproduct" data-method="">确定</button>
                                        </div>
                                    </div>
                                    <!-- /.modal-content -->
                                </div>
                                <!-- /.modal-dialog -->
                            </div>

                            <!-- Modal -->
                            <div class="add modal fade" id="addCheatProductModal" tabindex="-1" role="dialog" aria-labelledby="addCheatProductModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-md">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                            <h4 class="modal-title" id="addCheatProductModalLabel">添加产品</h4>
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
                                                  <label for="cid" class="col-sm-2 control-label">类型</label>
                                                  <div class="col-sm-10">
                                                    <select id="cid" class="form-control" name="cid">
                                                        <?php foreach($cheats as $k => $c): ?>
                                                            <option value="<?= $c->id ?>"><?= $c->title ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                  </div>
                                                </div>
                                                <div class="form-group">
                                                  <label for="amount" class="col-sm-2 control-label">数量</label>
                                                  <div class="col-sm-10">
                                                    <input type="number" class="form-control" name="amount" id="amount" placeholder="数量">
                                                  </div>
                                                </div>
                                                <div class="form-group">
                                                  <label for="scores" class="col-sm-2 control-label">积分数</label>
                                                  <div class="col-sm-10">
                                                    <input type="number" class="form-control" name="scores" id="scores" placeholder="消耗的积分数">
                                                  </div>
                                                </div>
                                            </form>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                                            <button type="button" class="btn btn-primary submit" data-url="/cheatproduct">确定</button>
                                        </div>
                                    </div>
                                    <!-- /.modal-content -->
                                </div>
                                <!-- /.modal-dialog -->
                            </div>

                            <div class="edit modal fade" id="editCheatProductModal" tabindex="-1" role="dialog" aria-labelledby="editCheatProductModalLabel" aria-hidden="true">
                              <div class="modal-dialog modal-md">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                        <h4 class="modal-title" id="editCheatProductModalLabel">编辑产品</h4>
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
                                                <label for="edit-amount" class="col-sm-2 control-label">数量</label>
                                                <div class="col-sm-10">
                                                    <input type="number" class="form-control" name="edit-amount" id="edit-amount" placeholder="数量">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="edit-scores" class="col-sm-2 control-label">积分数</label>
                                                <div class="col-sm-10">
                                                    <input type="number" class="form-control" name="edit-scores" id="edit-scores" placeholder="消耗的积分数">
                                                </div>
                                            </div>
                                            <input type="hidden" name="id" value="0" />
                                        </form>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                                        <button type="button" class="btn btn-primary submit" data-url="/cheatproduct/update">确定</button>
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
