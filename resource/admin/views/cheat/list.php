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
                                        <th>分类</th>
                                        <th>备注</th>
                                        <th>状态</th>
                                        <th>链接</th>
                                        <th>创建日期</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th>ID</th>
                                        <th>类型</th>
                                        <th>分类</th>
                                        <th>备注</th>
                                        <th>状态</th>
                                        <th>链接</th>
                                        <th>创建日期</th>
                                        <th>操作</th>
                                    </tr>
                                </tfoot>
                            </table>
                            <!-- /.table-responsive -->
                        </div>
                        <!-- Modal -->
                        <div class="edit modal fade" id="editCheatModal" tabindex="-1" role="dialog" aria-labelledby="editCheatModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                        <h4 class="modal-title" id="editCheatModalLabel">修改链接</h4>
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
                                              <label for="home_url" class="col-sm-2 control-label">链接</label>
                                              <div class="col-sm-10">
                                                <input type="text" class="form-control" name="home_url" id="home_url" placeholder="链接">
                                              </div>
                                            </div>
                                            <input type="hidden" name="id" value="0" />
                                        </form>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                                        <button type="button" class="btn btn-primary submit" data-url="/cheat/url">确定</button>
                                    </div>
                                </div>
                                <!-- /.modal-content -->
                            </div>
                            <!-- /.modal-dialog -->
                        </div>
                        <div class="modal fade" id="CheatModal" tabindex="-1" role="dialog" aria-labelledby="CheatModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                        <h4 class="modal-title" id="CheatModalLabel">确定上线</h4>
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
                                        <button type="button" class="btn btn-primary status-submit" data-url="/cheat" data-method="">确定</button>
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
