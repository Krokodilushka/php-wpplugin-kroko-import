<?php

use KrokoImport\View\Tables\Feeds_Posts_Table;

$table = new Feeds_Posts_Table();
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