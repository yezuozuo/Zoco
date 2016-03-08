<div>
    <table class="table">
        <tr>
            <td>id</td>
            <td>代言名称</td>
            <td>阅读数</td>
            <td>删除</td>
            <!--<td>用户名</td>-->
            <!--<td>性别</td>-->
            <!--<td>身份证号</td>-->
            <!--<td>学校</td>-->
            <!--<td>是否是黑名单</td>-->
            <!--<td>查看代言</td>-->
        </tr>
        <?php
        $id = 1;
        foreach ($res as $user) {
            if ($id % 2 == 1) {
                echo '<tr class="success">';
            }
            echo '<td>' . $user['id'] . '</td>';
            echo '<td>' . $user['title'] . '</td>';
            echo '<td>' . $user['read_times'] . '</td>';
            //echo '<td>' . $user['wechat_name'] . '</td>';
            //echo '<td>' . $user['username'] . '</td>';
            //echo '<td>' . $user['sex'] . '</td>';
            //echo '<td>' . $user['id_number'] . '</td>';
            //echo '<td>' . $user['school'] . '</td>';
            //echo '<td>' . $user['is_bad_list'] . '</td>';
            echo '<td><a href="?c=page&v=deleteRepresent&id=' . $user['id'] . '">删除</a></td>';
            echo '</tr>';
        }
        ?>
    </table>
</div>
<?php echo $page; ?>