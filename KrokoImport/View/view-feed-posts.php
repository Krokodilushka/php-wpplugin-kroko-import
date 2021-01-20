<?php

use KrokoImport\View\Tables\Feeds_Posts_Table;

/** @var string $feed_url */

$table = new Feeds_Posts_Table( $feed_url );
if ( ! empty( $feedData ) ) {
	if ( $feedData->count_posts() > 0 ) {
		foreach ( $feedData->get_posts() as $item ) {
			$table->add_item( $item );
		}
	}
	$table->prepare_items();
}
?>
<div class="feeds-data">
	<?php $table->display(); ?>
</div>