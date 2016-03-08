<div class="container">
    <div class="row-fluid">
        <div class="span4 offset1">
            <div class="page-header">
                <h2>快速反馈</h2>
            </div>
            <form class="form-contact-us" action="?c=tool&v=sendEmail" method="post" id="contactFormSubmit">
                <div class="control-group">
                    <div class="controls">
                        <input type="text" id="inputName" placeholder="Name" name="name">
                    </div>
                </div>
                <div class="control-group">
                    <div class="controls">
                        <input type="text" id="inputEmail" placeholder="Email" name="email">
                    </div>
                </div>
                <div class="control-group">
                    <div class="controls">
                        <textarea id="inputMessage" placeholder="Message" name="message"></textarea>
                    </div>
                </div>
                <div class="control-group">
                    <div class="controls">
                        <input type="submit" class="btn btn-primary btn-large" value="Send" style="margin-left: 25%;">
                    </div>
                </div>
            </form>
        </div>

        <div class="span5 offset1">
            <div class="page-header">
                <h2>联系人</h2>
            </div>
            <div>
                <?php
                foreach ($contact as $person) {
                    ?>
                    <h3><?php echo $person['name']; ?></h3>
                    <a href="mailto:<?php echo $person['email']; ?>" class="contact-mail">
                        <strong><span><?php echo $person['email']; ?></span></strong>
                    </a>
                    <hr>
                    <?php
                }
                ?>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    var reg = /^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+((\.[a-zA-Z0-9_-]{2,3}){1,2})$/;
    $('#contactFormSubmit').submit(function () {
        if ($('#inputName').val() == '') {
            alert('请输入名字！');
            return false;
        }
        if ($('#inputEmail').val() == '') {
            alert('请输入邮箱！');
            return false;
        }
        if (!reg.test($('#inputEmail').val())) {
            alert('您填写的邮箱格式不正确,请重新填写！');
            return false;
        }
        if ($('#inputMessage').val() == '') {
            alert('请输入反馈信息！');
            return false;
        }
    });
</script>
