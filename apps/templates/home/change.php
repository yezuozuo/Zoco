<div class="content">
    <div class="container">
        <div class="row">
            <div class="span6 offset3">
                <h4 class="widget-header"><i class="icon-lock"></i>修改信息</h4>

                <div class="widget-body">
                    <div class="center-align">
                        <form class="form-horizontal form-signin-signup" id="loginForm"
                              action="?c=page&v=doUserInfoChange" method="post">
                            <span>openid</span>
                            <input type="text" name="openid" id="openid" value="<?php echo $res['openid']; ?>" readonly>
                            <br>
                            <span>注册时间</span>
                            <input type="text" name="register_time" id="register_time"
                                   value="<?php echo $res['register_time']; ?>" readonly>
                            <br>
                            <span>微信昵称</span>
                            <input type="text" name="wechat_name" id="wechat_name"
                                   value="<?php echo $res['wechat_name']; ?>" readonly>
                            <br>
                            <span>用户名</span>
                            <input type="text" name="username" id="username" value="<?php echo $res['username']; ?>">
                            <br>
                            <span>性别</span>
                            <span>男</span><input type="radio" name="sex" id="sex_M"
                                                 value="M" <?php if ($res['sex'] == 'M') echo "checked" ?>>
                            <span>女</span><input type="radio" name="sex" id="sex_F"
                                                 value="F" <?php if ($res['sex'] == 'F') echo "checked" ?>>
                            <br>
                            <span>身份证号</span>
                            <input type="text" name="id_number" id="id_number" value="<?php echo $res['id_number'] ?>">
                            <br>
                            <span>学校</span>
                            <input type="text" name="school" id="school" value="<?php echo $res['school'] ?>">
                            <br>
                            <span>屏蔽</span>
                            <span>是</span><input type="radio" name="is_bad_list" id="bad_yes"
                                                 value="1" <?php if ($res['is_bad_list'] == '1') echo "checked" ?>>
                            <span>否</span><input type="radio" name="is_bad_list" id="bad_no"
                                                 value="0" <?php if ($res['is_bad_list'] == '0') echo "checked" ?>>
                            <br>
                            <input type="submit" value="提交" class="btn btn-primary btn-large">
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $('#loginForm').submit(function () {
        if ($('#username').val() == '') {
            alert('请输入用户名！');
            return false;
        }
        if ($('#password').val() == '') {
            alert('请输入密码！');
            return false;
        }
    });
</script>