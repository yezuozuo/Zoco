<div class="content">
    <div class="container">
        <div class="row">
            <div class="span6 offset3">
                <h4 class="widget-header"><i class="icon-lock"></i>登陆</h4>

                <div class="widget-body">
                    <div class="center-align">
                        <form class="form-horizontal form-signin-signup" id="loginForm" action="?c=visitor&v=doLogin"
                              method="post">
                            <input type="text" name="username" id="username" placeholder="用户名">
                            <input type="password" name="password" id="password" placeholder="密码">

                            <div class="remember-me">
                                <div class="pull-right">
                                    <a href="#">忘记密码？</a>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                            <input type="submit" value="登陆" class="btn btn-primary btn-large">
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