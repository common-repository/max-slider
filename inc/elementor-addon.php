<?php
//Elementor Editor view


if (!function_exists('max_slide_choice')) {

	function max_slide_choice()
	{
		static $list = [];

		if (!count($list)) {
			$posts = get_posts(
				[
					'numberposts' => -1,
					'post_status' => 'publish',
					'post_type' => 'max_slides'
				]
			);

			foreach ($posts as $post) {
				$list[$post->ID] = $post->post_title;
			}
		}

		return $list;
	}
}