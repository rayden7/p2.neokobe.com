
<h1>All Users' Posts</h1>

<?php foreach ($posts as $key => $post): ?>
        <p><b><?=$post['first_name'] ?></b> said <b>"<?=$post['post_content'] ?>"</b> at [<?=date('Y-m-d H:i:s', $post['post_modified']) ?>]</p>
<? endforeach; ?>
