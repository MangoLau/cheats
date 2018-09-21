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
                            <table width="100%" class="table table-striped table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <!-- <th>ID</th> -->
                                        <th>UID</th>
                                        <th>QQ</th>
                                        <th>说说id</th>
                                        <th>快手用户ID</th>
                                        <th>快手作品ID</th>
                                        <th>K歌歌曲ID</th>
                                        <th>类型</th>
                                        <th>数量</th>
                                        <th>消费积分</th>
                                        <!-- <th>所使用的卡密</th> -->
                                        <th>自动下单失败原因</th>
                                        <th>创建日期</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($orders as $k => $v): ?>
                                        <tr>
                                            <!-- <td><?= $v->id ?></td> -->
                                            <td><?= $v->uid ?></td>
                                            <td><?= $v->qq ?></td>
                                            <td><?= $v->ssid ?></td>
                                            <td><?= $v->ksid ?></td>
                                            <td><?= $v->zpid ?></td>
                                            <td><?= $v->qmkg_gqid ?></td>
                                            <td><?= $v->cid ?></td>
                                            <td><?= $v->amount ?></td>
                                            <td><?= $v->scores ?></td>
                                            <!-- <td><?= $v->identify ?></td> -->
                                            <td><?= $v->errmsg ?: '无'?></td>
                                            <td><?= $v->created_at ?></td>
                                            <td><button class='btn btn-xs btn-info order-by-hand' attr-id='<?= $v->id ?>'>手动下单</button></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <!-- /.table-responsive -->
                        </div>
                    <!-- /.panel -->
                    </div>
                <!-- /.col-lg-12 -->
                </div>
            </div>
    </div>

</div>

<!-- Metis Menu Plugin JavaScript -->
<script src="../assets/vendor/metisMenu/metisMenu.min.js"></script>
<!-- Custom Theme JavaScript -->
<script src="../assets/js/sb-admin-2.js"></script>

<script type="text/javascript">
    $('button.order-by-hand').click(function(){
        var order_id = $(this).attr('attr-id')
        $.post(
            '/order/createByHand',
            {
                order_id: order_id
            },
            function (response) {
                if (response.error_code > 0) {
                    alert(response.error)
                } else {
                    alert('已将该订单从队列中删除，现在可以去网站手动下单了, 订单id=' + order_id)
                    window.location.reload()
                }
            }
        )
    })
</script>
</body>
</html>