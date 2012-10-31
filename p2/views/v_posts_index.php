<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dkilleffer
 * Date: 10/24/12
 * Time: 6:06 PM
 * To change this template use File | Settings | File Templates.
 */
?>

<h1>p2.neokobe.com</h1>

<p>All the posts</p>

<?php foreach ($posts as $key => $post): ?>
        <p><b><?=$post['first_name'] ?></b> said <b>"<?=$post['post_content'] ?>"</b> at [<?=date('Y-m-d H:i:s', $post['post_modified']) ?>]</p>
<? endforeach; ?>
