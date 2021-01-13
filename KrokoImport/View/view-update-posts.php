<?php
/** @var $feedID string */
/** @var $feedURL string */
/** @var $response */
?>
<div class="wrap">
    <p>feedID: <?= $feedID ?> url: <?= $feedURL ?></p>
    <p>ответ:</p>
    <pre><?php print_r($response); ?></pre>
</div>