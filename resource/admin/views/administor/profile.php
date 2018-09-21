<?php $this->render('common/header') ?>

<?php $this->render('common/nav'); ?>

        <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header"><?=$title ?: '设置'?></h1>
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->
            <div class="row">
                <div class="col-lg-8 col-lg-offset-2">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <?=$title ?: '设置'?>
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <!-- /.col-lg-6 (nested) -->
                                <div class="col-lg-6 col-lg-offset-3">
                                    <h2 align="center">修改密码</h2>
                                    <?php if (!empty($notifications)): ?>
                                        <div id="notifications">
                                            <div class="alert <?= $notifications['error'] ? 'alert-danger' : 'alert-success' ?> alert-dismissable">
                                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                                <?= $notifications['message'] ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <form role="form" method="post" action="">
                                        <div class="form-group">
                                            <label>用户名</label>
                                            <input class="form-control" name="username" type="text" value="<?=$_SESSION['userinfo']['username']?>" placeholder="Username" disabled>
                                        </div>
                                        <div class="form-group">
                                            <label>原密码</label>
                                            <input class="form-control" type="password" name="old_password" placeholder="Enter old password">
                                        </div>
                                        <div class="form-group">
                                            <label>新密码</label>
                                            <input class="form-control" type="password" name="new_password" placeholder="Enter new password">
                                        </div>
                                        <div class="form-group">
                                            <label>确认新密码</label>
                                            <input class="form-control" type="password" name="confirm_new_password" placeholder="Confirm new password">
                                        </div>

                                        <button type="submit" class="btn btn-primary">重置密码</button>
                                    </form>
                                </div>
                                <!-- /.col-lg-6 (nested) -->
                            </div>
                            <!-- /.row (nested) -->
                        </div>
                        <!-- /.panel-body -->
                    </div>
                    <!-- /.panel -->
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->
        </div>
        <!-- /#page-wrapper -->

<?php $this->render('common/footer'); ?>
