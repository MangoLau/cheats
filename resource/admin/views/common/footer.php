</div>
    <!-- Metis Menu Plugin JavaScript -->
    <script src="../assets/vendor/metisMenu/metisMenu.min.js"></script>

    <!-- DataTables JavaScript -->
    <script src="../assets/vendor/datatables/js/jquery.dataTables.min.js"></script>
    <script src="../assets/vendor/datatables-plugins/dataTables.bootstrap.min.js"></script>
    <script src="../assets/vendor/datatables-responsive/dataTables.responsive.js"></script>

    <!-- Custom Theme JavaScript -->
    <script src="../assets/js/sb-admin-2.js"></script>

    <!-- Page-Level Demo Scripts - Tables - Use for reference -->
    <script>

    $(document).ready(function() {
        $('#dataTables-example').DataTable({
            "responsive": true,
            "order": [[ 0, "desc" ]],     // 第一个字段倒叙排列
            "processing": true,
            "serverSide": true,
            "ajax": "<?= $this->ajax_api ?>",
            "pageLength": 25,
            "language": {
                searchPlaceholder: "<?= $this->search_desc ?>"
            },
            <?php if ($this->ajax_api == '/banners'): ?>
                "columns": [
                    { data: 'id' },
                    { data: 'pic', orderable: false, },
                    { data: 'link', orderable: false, },
                    { data: 'remark', orderable: false, },
                    { data: 'status' },
                    { data: 'created_at' },
                    {
                        data: null, orderable: false
                    },
                ],
                "columnDefs": [
                    {
                        // 图片
                        targets: 1,
                        render: function( data, type, row ) {
                            return '<a target="_blank" href="' + data + '"><img width="80" height="80" src="' + data + '" /></a>';
                        }
                    },
                    {
                        // 备注
                        targets: 3,
                        render: function( data, type, row ) {
                            return data ? data : '无';
                        }
                    },
                    {
                        // 操作
                        targets: 6,
                        render: function( data, type, row ) {
                            return row.status == '停用' ? '<button class="btn btn-info btn-sm" onclick=\'pushOnline("#BannerModal", ' + row.id + ')\'>上线</button>' : '<button class="btn btn-danger btn-sm" onclick=\'pushOffline("#BannerModal", ' + row.id + ')\'>下线</button>';
                        }
                    },
                ],
            <?php elseif ( $this->ajax_api == '/users'): ?>
                "columns": [
                    { data: 'id' },
                    { data: 'qq' },
                    { data: 'nickname', orderable: false, },
                    { data: 'avatar', orderable: false, },
                    { data: 'openid', orderable: false, },
                    { data: 'total_scores' },
                    { data: 'remaining_scores' },
                    { data: 'vip_deadline' },
                    // { data: 'platform', orderable: false, },
                    { data: 'created_at' },
                    // {
                    //     data: null,
                    // }
                ],
                "columnDefs": [ 
                    {
                        // 头像
                        targets: 3,
                        render: function( data, type, row ) {
                            if( data == "" ) {
                                return '无';
                            }
                            
                            return '<a target="_blank" href="'+ data +'"><img width="60" height="60" src="'+ data +'" /></a>';
                            
                        }
                    },
                    // {
                    //     // 平台
                    //     targets: 8,
                    //     render: function( data, type, row ) {
                    //         if( data == "ifanr" ) {
                    //             return '爱范儿';
                    //         } else if ( data == 'qq' ) {
                    //             return 'QQ';
                    //         } else if ( data == 'weibo' ) {
                    //             return '微博';
                    //         } else if ( data == 'wechat' ) {
                    //             return '微信';
                    //         } else {
                    //             return '其他';
                    //         }
                    //     }
                    // },
                    // {
                    //     // 操作
                    //     targets: 6,
                    //     render: function( data, type, row ) {
                    //         return "<a class='btn btn-info btn-sm' href='/user/qrcodes?uid=" + row.id + "'>详情</a>";
                    //     }
                    // },
                ],
            <?php elseif ( $this->ajax_api == '/complaints'): ?>
                "columns": [
                    { data: 'id' },
                    { data: 'qq' },
                    { data: 'mobile'},
                    { data: 'created_at' },
                ],
            <?php elseif ( $this->ajax_api == '/cards'): ?>
                "columns": [
                    { data: 'id' },
                    { data: 'title', orderable: false, },
                    { data: 'identify', orderable: false, },
                    { data: 'password', orderable: false, },
                    { data: 'total' },
                    { data: 'remaining' },
                    { data: 'status' },
                    { data: 'created_at' },
                    {
                        data: null,
                    }
                ],
                "columnDefs": [ 
                    {
                        // 操作
                        targets: 8,
                        render: function( data, type, row ) {
                            return (row.status == '停用' ? '<button class="btn btn-info btn-sm" onclick=\'pushOnline("#CardModal", ' + row.id + ')\'>上线</button>' : '<button class="btn btn-danger btn-sm" onclick=\'pushOffline("#CardModal", ' + row.id + ')\'>下线</button>') + "&nbsp;&nbsp;<button class='btn btn-success btn-sm' onclick='editCard(" + JSON.stringify(row) + ")'>编辑</button>";;
                        }
                    },
                ],
            <?php elseif ( $this->ajax_api == '/cheatproducts'): ?>
                "columns": [
                    { data: 'id' },
                    { data: 'title', orderable: false, },
                    { data: 'amount' },
                    { data: 'scores' },
                    { data: 'status' },
                    { data: 'created_at' },
                    {
                        data: null,
                    }
                ],
                "columnDefs": [ 
                    {
                        // 操作
                        targets: 6,
                        render: function( data, type, row ) {
                            return (row.status == '停用' ? '<button class="btn btn-info btn-sm" onclick=\'pushOnline("#CheatProductModal", ' + row.id + ')\'>上线</button>' : '<button class="btn btn-danger btn-sm" onclick=\'pushOffline("#CheatProductModal", ' + row.id + ')\'>下线</button>') + "&nbsp;&nbsp;<button class='btn btn-success btn-sm' onclick='editCheatProduct(" + JSON.stringify(row) + ")'>编辑</button>";
                        }
                    },
                ],
            <?php elseif ( $this->ajax_api == '/cheats'): ?>
                "columns": [
                    { data: 'id' },
                    { data: 'title', orderable: false, },
                    { data: 'category' },
                    { data: 'remark' },
                    { data: 'status' },
                    { data: 'home_url' },
                    { data: 'created_at' },
                    {
                        data: null,
                    }
                ],
                "columnDefs": [
                    {
                        // 操作
                        targets: 5,
                        render: function( data, type, row ) {
                            return '<a target="_blank" href='+ data + '>' + data + '</a>';
                        }
                    },
                    {
                        // 操作
                        targets: 7,
                        render: function( data, type, row ) {
                            return "<button class='btn btn-warning btn-sm' onclick=\"changeCheatUrl(" + row.id + ", '" + row.home_url + "')\">修改链接</button>&nbsp;&nbsp;" + (row.status == '停用' ? '<button class="btn btn-info btn-sm" onclick=\'pushOnline("#CheatModal", ' + row.id + ', "' + row.title + '")\'>上线</button>' : '<button class="btn btn-danger btn-sm" onclick=\'pushOffline("#CheatModal", ' + row.id + ', "' + row.title + '")\'>下线</button>')
                        }
                    }
                ],
            <?php elseif ( $this->ajax_api == '/configs'): ?>
                "columns": [
                    { data: 'id' },
                    { data: 'key', orderable: false, },
                    { data: 'value' },
                    { data: 'platform' },
                    { data: 'remark', orderable: false, },
                    { data: 'status' },
                    { data: 'created_at' },
                    {
                        data: null,
                    }
                ],
                "columnDefs": [ 
                    {
                        // 操作
                        targets: 7,
                        render: function( data, type, row ) {
                            return (row.status == '停用' ? '<button class="btn btn-info btn-sm" onclick=\'pushOnline("#ConfigModal", ' + row.id + ')\'>上线</button>' : '<button class="btn btn-danger btn-sm" onclick=\'pushOffline("#ConfigModal", ' + row.id + ')\'>下线</button>') + "&nbsp;&nbsp;<button class='btn btn-success btn-sm' onclick='editConfig(" + JSON.stringify(row) + ")'>编辑</button>";
                        }
                    },
                ],
            <?php elseif ( $this->ajax_api == '/channels'): ?>
                "columns": [
                    { data: 'id' },
                    { data: 'name', orderable: false, },
                    { data: 'remark', orderable: false, },
                    { data: 'status' },
                    { data: 'cheats', orderable: false },
                    { data: 'created_at' },
                    {
                        data: null,
                    }
                ],
                "columnDefs": [
                    {
                        // 开启的业务
                        targets: 4,
                        render: function( data, type, row ) {
                            var length = data.length
                            var ret = ''
                            for (var i = 0; i < length; i ++) {
                                ret = ret + '<code>' + data[i] + '</code>'
                            }

                            return ret ? ret : '无'
                        }
                    },
                    {
                        // 操作
                        targets: 6,
                        render: function( data, type, row ) {
                            return (row.status == '停用' ? '<button class="btn btn-info btn-sm" onclick=\'pushOnline("#ChannelModal", ' + row.id + ')\'>上线</button>' : '<button class="btn btn-danger btn-sm" onclick=\'pushOffline("#ChannelModal", ' + row.id + ')\'>下线</button>') + "&nbsp;&nbsp;<button class='btn btn-success btn-sm' onclick='editChannel(" + JSON.stringify(row) + ")'>编辑</button>";
                        }
                    },
                ],
            <?php elseif ( $this->ajax_api == '/hotpeoples'): ?>
                "columns": [
                    { data: 'id' },
                    { data: 'qq' },
                    { data: 'avatar', orderable: false },
                    { data: 'scores' },
                    { data: 'status' },
                    { data: 'created_at' },
                    {
                        data: null,
                    }
                ],
                "columnDefs": [ 
                    {
                        targets: 2,
                        render: function (data, type, row) {
                            return '<a target="_blank" href="'+ data +'"><img width="60" height="60" src="'+ data +'" /></a>';
                        }
                    },
                    {
                        // 操作
                        targets: 6,
                        render: function( data, type, row ) {
                            return row.status == '停用' ? '<button class="btn btn-info btn-sm" onclick=\'pushOnline("#HotPeopleModal", ' + row.id + ')\'>上线</button>' : '<button class="btn btn-danger btn-sm" onclick=\'pushOffline("#HotPeopleModal", ' + row.id + ')\'>下线</button>';
                        }
                    },
                ],
            <?php elseif ( $this->ajax_api == '/orders' || $this->ajax_api == '/orders/laquanquan'): ?>
                "columns": [
                    { data: 'id' },
                    { data: 'uid' },
                    { data: 'qq', orderable: false },
                    { data: 'title' },
                    { data: 'amount' },
                    { data: 'real_amount' },
                    { data: 'scores' },
                    { data: 'identify' },
                    { data: 'channel' },
                    { data: 'platform' },
                    { data: 'status' },
                    { data: 'created_at' },
                    // { data: 'updated_at' },
                    {
                        data: null,
                    }
                ],
                "columnDefs": [ 
                    {
                        // 操作
                        targets: 12,
                        render: function( data, type, row ) {
                            return row.status != '失败' ? '<button class="btn btn-info btn-sm" onclick=\'returnScores(' + row.id + ')\'>退积分</button>' : '无';
                        }
                    },
                ],
            <?php elseif ( $this->ajax_api == '/products'): ?>
                "columns": [
                    { data: 'id' },
                    { data: 'money' },
                    { data: 'type', orderable: false },
                    { data: 'amount' },
                    { data: 'status' },
                    { data: 'created_at' },
                    {
                        data: null,
                    }
                ],
                "columnDefs": [
                    {
                        // 操作
                        targets: 6,
                        render: function( data, type, row ) {
                            return (row.status == '停用' ? '<button class="btn btn-info btn-sm" onclick=\'pushOnline("#ProductModal", ' + row.id + ')\'>上线</button>' : '<button class="btn btn-danger btn-sm" onclick=\'pushOffline("#ProductModal", ' + row.id + ')\'>下线</button>') + "&nbsp;&nbsp;<button class='btn btn-success btn-sm' onclick='editProduct(" + JSON.stringify(row) + ")'>编辑</button>";
                        }
                    },
                ],
            <?php elseif ( $this->ajax_api == '/recharges'): ?>
                "columns": [
                    { data: 'id' },
                    { data: 'uid' },
                    // { data: 'bmob_order_id' },
                    { data: 'money' },
                    { data: 'type', orderable: false },
                    { data: 'amount' },
                    { data: 'pay_type' },
                    { data: 'platform' },
                    { data: 'channel' },
                    { data: 'status' },
                    { data: 'created_at' },
                    // { data: 'updated_at' },
                    // {
                    //     data: null,
                    // }
                ],
                "columnDefs" : [
                    {
                        targets: 7,
                        render: function( data, type, row ) {
                            return data == '' ? '无' : data;
                        }

                    },
                    // {
                    //     targets: 10,
                    //     render: function( data, type, row ) {
                    //         var node = '无'
                    //         if (row.origin_status == 0) {
                    //             if (row.bmob_order_id == '') {
                    //                 node = "<button class='btn btn-info btn-sm' onclick='editRecharge(" + JSON.stringify(row) + ")'>更新bmob订单号</button>"
                    //             } else {
                    //                 node = "<button class='btn btn-success btn-sm' onclick='updateState(" + JSON.stringify(row) + ")'>手动同步支付状态</button>"
                    //             }
                    //         }

                    //         return node
                    //     }
                    // }
                ],
            <?php elseif ( $this->ajax_api == '/administors' ): ?>
                "columns": [
                    { data: 'id' },
                    { data: 'username' },
                    { data: 'created_at' },
                    { data: 'last_login_at' },
                ],
                "columnDefs": [
                    {
                        // 操作
                        targets: 2,
                        render: function( data, type, row ) {
                            // return data;
                            return new Date(parseInt(data) * 1000).toLocaleString().replace(/:\d{1,2}$/,' ');
                        }
                    },
                    {
                        // 操作
                        targets: 3,
                        render: function( data, type, row ) {
                            // return data;
                            return data ? new Date(parseInt(data) * 1000).toLocaleString().replace(/:\d{1,2}$/,' ') : '未登录过';
                        }
                    },
                ],
            <?php elseif ( $this->ajax_api == '/attendances' ): ?>
                "columns": [
                    { data: 'id' },
                    { data: 'uid' },
                    { data: 'created_day' },
                ],
            <?php elseif ( $this->ajax_api == '/orders'): ?>
                "columns": [
                    { data: 'id' },
                    { data: 'uid' },
                    { data: 'qq', orderable: false },
                    { data: 'cid' },
                    { data: 'amount' },
                    { data: 'real_amount' },
                    { data: 'scores' },
                    { data: 'channel', orderable: false },
                    { data: 'platform' },
                    { data: 'status' },
                    { data: 'created_at' },
                    {
                        data: null,
                    }
                ],
                "columnDefs": [ 
                    {
                        // 头像
                        targets: 9,
                        render: function( data, type, row ) {
                            if( data == "" ) {
                                return '无';
                            }
                            
                            return '<a target="_blank" href="'+ data +'"><img width="60" height="60" src="'+ data +'" /></a>';
                            
                        }
                    },
                    {
                        // 平台
                        targets: 4,
                        render: function( data, type, row ) {
                            if( data == "ifanr" ) {
                                return '爱范儿';
                            } else if ( data == 'qq' ) {
                                return 'QQ';
                            } else if ( data == 'weibo' ) {
                                return '微博';
                            } else if ( data == 'wechat' ) {
                                return '微信';
                            } else {
                                return '其他';
                            }
                        }
                    },
                    {
                        // 操作
                        targets: 6,
                        render: function( data, type, row ) {
                            return "<a class='btn btn-info btn-sm' href='/user/qrcodes?uid=" + row.id + "'>详情</a>";
                        }
                    },
                ],
            <?php elseif ( $this->ajax_api == '/feedbacks'): ?>
                "columns": [
                    { data: 'id' },
                    { data: 'reply_id' },
                    { data: 'uid' },
                    { data: 'text_content', orderable: false },
                    { data: 'status' },
                    { data: 'created_at' },
                    {
                        data: null,
                    }
                ],
                "columnDefs" : [
                    {
                        targets: 6,
                        render: function( data, type, row ) {
                            var node = '无'
                            if (row.origin_status == 0) {
                                node = '<button class="btn btn-info btn-sm" onclick=\'pushOnline("#FeedbackModal", ' + row.id + ',"", "确定审核通过?")\'>通过</button>' + '&nbsp;<button class="btn btn-danger btn-sm" onclick=\'pushOffline("#FeedbackModal", ' + row.id + ',"", "确定拒绝通过?")\'>拒绝</button>'
                            } else if (row.origin_status == 1 && row.reply_id == 0) {
                                node = "<button class='btn btn-success btn-sm' onclick='feedbackReply(" + data.id + ")'>回复</button>"
                            } else if (row.origin_status == 2) {
                                node = "审核失败"
                            } else if (row.origin_status == 3) {
                                node = "已回复"
                            }

                            return node
                        }
                    }
                ],
            <?php endif; ?>
        });

        // 添加产品类型
        $('#addProductModal #type').change(function(){
            var type = $(this).val()
            var amount_palceholder;
            if (type == 1) {
                amount_palceholder = '积分数量'
            } else if (type == 2) {
                amount_palceholder = 'VIP月数'
            }

            $('#addProductModal #amount').attr('placeholder', amount_palceholder);
        })

        // 新增
        $('div.add button.submit').click(function(){
            ajaxFunc('div.add', 'button.submit')
        })

        // 编辑
        $('div.edit button.submit').click(function(){
            ajaxFunc('div.edit', 'button.submit')
        })

        //
        $('div.reply button.submit').click(function(){
            ajaxFunc('div.reply', 'button.submit')
        })

        // 通用上线／下线方法
        $('button.status-submit').click(function(){
            // var id = $.trim($('input[name="id"]').val())
            // if (id == 0) {
            //     $('span.blockRetMsg').text('请先选择一个')
            //     $('div.notifications').children('div.alert').removeClass('alert-success').addClass('alert-danger');
            //     $('div.notifications').removeClass('hidden');

            //     return
            // }

            var url = $(this).attr('data-url')
            var method = $(this).attr('data-method')

            $.ajax({
                url: url,
                type: method,
                success: function(res) {
                    // console.log(res)
                    if (res.error_code == 0) {
                        $('div.notifications').removeClass('hidden').children('div.alert').removeClass('alert-danger').addClass('alert-success');
                        $('span.blockRetMsg').text('操作成功')
                        $('div.notifications').removeClass('hide');

                        window.setTimeout(window.location.reload(), 2000);
                    } else {
                        $('div.notifications').children('div.alert').removeClass('alert-success').addClass('alert-danger');
                        $('span.blockRetMsg').text(res.error)
                        $('div.notifications').removeClass('hide');
                    }
                }
            });
        })

        // 渠道页面(取消)全选操作
        $('#addChannelModal input.check-all').click(function(){
            effectAll(this.checked, $('#addChannelModal input.check-single'))
        })
        $('#addChannelModal input.check-single').click(function(){
            if (isAllChecked($('#addChannelModal input.check-single'))) {
                $('#addChannelModal input.check-all').prop('checked', true)
            } else {
                $('#addChannelModal input.check-all').prop('checked', false)
            }
        })
        $('#editChannelModal input.check-all').click(function(){
            effectAll(this.checked, $('#editChannelModal input.check-single'))
        })
        $('#editChannelModal input.check-single').click(function(){
            if (isAllChecked($('#editChannelModal input.check-single'))) {
                $('#editChannelModal input.check-all').prop('checked', true)
            } else {
                $('#editChannelModal input.check-all').prop('checked', false)
            }
        })
    });

    // 通用ajax请求 新增/编辑
    function ajaxFunc(modal, button) {
        // console.log(modal, button)
        var valid = 1
        $(modal).find('input').each(function(index, element) {
            // 卡密模块的密码可以为空
            if ($(element).attr('name') != 'password' && $(element).attr('name') != 'edit-password' && ($(element).val() == 0 || $(element).val() == '')) {
                $(modal + ' span.blockRetMsg').text('所有选项都不能为空或0')
                $(modal +' div.notifications').children('div.alert').removeClass('alert-success').addClass('alert-danger');
                $(modal +' div.notifications').removeClass('hide');
                
                valid = 0
                return
            }
        })

        $(modal).find('textarea').each(function(index, element) {
            if ($.trim($(element).val()) == '') {
                $(modal + ' span.blockRetMsg').text('所有选项都不能为空或0')
                $(modal +' div.notifications').children('div.alert').removeClass('alert-success').addClass('alert-danger');
                $(modal +' div.notifications').removeClass('hide');
                
                valid = 0
                return
            }
        })

        if (valid) {
            var url = $(modal + ' ' + button).attr('data-url')
            $.post(
                url,
                $(modal + ' form').serialize(),
                function (res) {
                    if (res.error_code == 0) {
                        $(modal + ' div.notifications').children('div.alert').removeClass('alert-danger').addClass('alert-success');
                        $(modal + ' span.blockRetMsg').text('操作成功')
                        $(modal + ' div.notifications').removeClass('hide');

                        window.setTimeout(window.location.reload(), 2000);
                    } else {
                        $(modal + ' div.notifications').children('div.alert').removeClass('alert-success').addClass('alert-danger');
                        $(modal + ' span.blockRetMsg').text(res.error)
                        $(modal + ' div.notifications').removeClass('hide');
                    }
                }
            )
        }
    }

    // 上线
    function pushOnline(node, id, name = '', title = '确定上线?') {
        $(node+'Label').text(title)
        $(node).find('input[name="id"]').val(id)
        var old_url = $(node).find('button.status-submit').attr('data-url')
        if (old_url.lastIndexOf('/') != 0) {
            var prefix_url = old_url.substr(0, old_url.lastIndexOf('/'))
        } else {
            var prefix_url = old_url
        }
        
        $(node).find('button.status-submit').attr('data-url', prefix_url + '/' + id)
        $(node).find('button.status-submit').attr('data-method', 'PUT')
        name = name == '' ? id : name
        $(node).find('p.content code').text(name)
        $(node).modal('toggle')
    }

    // 下线
    function pushOffline(node, id, name = '', title = '确定下线?') {
        $(node+'Label').text(title)
        $(node).find('input[name="id"]').val(id)
        var old_url = $(node).find('button.status-submit').attr('data-url')
        if (old_url.lastIndexOf('/') != 0) {
            var prefix_url = old_url.substr(0, old_url.lastIndexOf('/'))
        } else {
            var prefix_url = old_url
        }
        
        $(node).find('button.status-submit').attr('data-url', prefix_url + '/' + id)
        $(node).find('button.status-submit').attr('data-method', 'DELETE')
        name = name == '' ? id : name
        $(node).find('p.content code').text(name)
        $(node).modal('toggle')
    }

    // 更新配置
    function editConfig(row) {
        $('div.edit #value_input').addClass('hidden')
        $('div.edit #value_radio').addClass('hidden')
        $('#value_radio div.col-sm-10').html('')

        $('div.edit input[name="id"]').val(row.id)
        $('div.edit select#edit-platform').val(row.origin_platform)
        $('div.edit input[name="edit-key"]').val(row.key)

        if (row.options == '') {
            $('div.edit #value_input').removeClass('hidden')
        } else {
            var node = '';
            var options = eval('(' + row.options + ')')
            $.each(options, function(index, val){
                node += '<label class="radio-inline"><input type="radio" name="choice" value="' + index + '"' + (row.value == index ? ' checked' : '') + '>' + val + '</label>'
            })

            // 组装节点
            $('#value_radio div.col-sm-10').html(node)
            $('div.edit #value_radio').removeClass('hidden')
        }
        
        $('div.edit input[name="edit-value"]').val(row.value)
        $('div.edit input[name="edit-remark"]').val(row.remark)

        $('div.edit').modal('toggle')
    }

    // 更新渠道设置
    function editChannel(row) {
        $('#editChannelModal .check-single').each(function(index) {
            $(this).attr('checked', false)
        })

        $('div.edit input[name="id"]').val(row.id)
        $('div.edit input[name="edit-name"]').val(row.name)
        $('div.edit input[name="edit-remark"]').val(row.remark)

        var length = row.cheat_ids.length
        for (var i = 0; i < length; i ++) {
            node = $('#editChannelModal .check-single[value="' + row.cheat_ids[i] + '"]')
            node.attr('checked', true)
        }

        $('div.edit').modal('toggle')
    }

    // 更新充值产品
    function editProduct(row) {
        $('div.edit input[name="id"]').val(row.id)
        $('div.edit select#edit-type').val(row.origin_type)
        $('div.edit input[name="edit-money"]').val(row.money)
        $('div.edit input[name="edit-amount"]').val(row.amount)

        $('div.edit').modal('toggle')
    }

    // 更新刷赞产品
    function editCheatProduct(row) {
        $('div.edit input[name="id"]').val(row.id)
        $('div.edit select#edit-type').val(row.cid)
        $('div.edit input[name="edit-scores"]').val(row.scores)
        $('div.edit input[name="edit-amount"]').val(row.amount)

        $('div.edit').modal('toggle')
    }

    // 更新卡密
    function editCard(row) {
        $('div.edit input[name="id"]').val(row.id)
        $('div.edit select#edit-type').val(row.type)
        $('div.edit input[name="edit-identify"]').val(row.identify)
        $('div.edit input[name="edit-password"]').val(row.origin_password)
        $('div.edit input[name="edit-total"]').val(row.total)
        $('div.edit input[name="edit-remaining"]').val(row.remaining)

        $('div.edit').modal('toggle')
    }

    // 更新刷赞业务的URL
    function changeCheatUrl(id, url) {
        // console.log(id, url)
        $('div.edit input[name="id"]').val(id)
        $('div.edit input[name="home_url"]').val(url)

        $('div.edit').modal('toggle')
    }

    function feedbackReply(id) {
        $('div.reply input[name="id"]').val(id)

        $('div.reply').modal('toggle')
    }

    // 更新比目订单号
    function editRecharge(row) {
        $('div.edit input[name="id"]').val(row.id)
        $('div.edit input[name="edit-id"]').val(row.id)

        $('div.edit').modal('toggle')
    }

    // 退回积分
    function returnScores(id) {
        $('#returnScoresModal p.content code').text(id)

        $('#returnScoresModal').find('button.status-submit').attr('data-url', '/order/' + id).attr('data-method', 'DELETE')

        $('#returnScoresModal').modal('toggle')
    }

    // 重新调用回调接口
    function updateState(row) {
        if (row.bmob_order_id == '') {
            alert('没有比目订单号')
            return
        }

        $.post(
            'recharge/rsyncStatus',
            {
                id: row.id,
            },
            function (res) {
                if (res.error_code == 0) {
                    alert('同步成功')
                    window.location.reload()
                } else {
                    alert('同步失败')
                }
            }
        )
    }

    // 是否已全选
    function isAllChecked(nodes)
    {
        var length = nodes.length
        for (var i = 0; i < length; i++) {
            if (!nodes[i].checked) {
                return false
            }
        }

        return true
    }

    // 全选／全不选
    function effectAll(checked, nodes)
    {
        length = nodes.length
        for (var i = 0; i < length; i++) {
            nodes[i].checked = checked
        }
    }

    // js-htmlspecialchars
    function escapeHtml(text) {
      var map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
      };
    
      return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    </script>

</body>

</html>