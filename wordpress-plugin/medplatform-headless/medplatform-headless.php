<?php
/**
 * Plugin Name: Med Platform Headless
 * Description: Registers the headless WordPress schema used by the Astro frontend.
 * Version: 0.1.3
 * Author: Codex
 */

if (! defined('ABSPATH')) {
	exit;
}

function mp_headless_project_stages() {
	return array('Conception', 'Research', 'Dialogue', 'Publication', 'Implemented');
}

function mp_headless_project_focus_area_options() {
	return array(
		array(
			'slug'        => 'governance-processes',
			'title'       => 'Governance Processes',
			'description' => 'Research and dialogue on institutional design, local governance, and state-society relations.',
		),
		array(
			'slug'        => 'national-dialogue',
			'title'       => 'National Dialogue',
			'description' => 'Support for inclusive dialogue formats, peace process reflection, and operational policy pathways.',
		),
		array(
			'slug'        => 'youth-participation',
			'title'       => 'Youth Participation',
			'description' => 'Work on participation, mentorship, and long-term civic inclusion in future-facing policy spaces.',
		),
		array(
			'slug'        => 'knowledge-exchange',
			'title'       => 'Knowledge Exchange',
			'description' => 'Crossroads exchanges between experts, researchers, and policymakers working across African and European contexts.',
		),
		array(
			'slug'        => 'africa-europe-dialogue',
			'title'       => 'Africa-Europe Dialogue',
			'description' => 'Policy-facing conversations on regional priorities, institutions, and transnational cooperation.',
		),
		array(
			'slug'        => 'sustainable-development',
			'title'       => 'Sustainable Development',
			'description' => 'Research on climate, energy transition, and long-term development choices shaping regional futures.',
		),
		array(
			'slug'        => 'europe-gulf-relations',
			'title'       => 'Europe-Gulf Relations',
			'description' => 'Analysis of diplomatic, economic, and strategic ties between Gulf actors and European institutions.',
		),
		array(
			'slug'        => 'regional-security',
			'title'       => 'Regional Security',
			'description' => 'Policy-oriented work on geopolitical developments, maritime issues, and shifting security architectures.',
		),
		array(
			'slug'        => 'strategic-trends',
			'title'       => 'Strategic Trends',
			'description' => 'Long-term research on sectoral cooperation, social change, and regional power dynamics.',
		),
	);
}

function mp_headless_project_focus_areas_by_slug() {
	$options = array();

	foreach (mp_headless_project_focus_area_options() as $option) {
		$options[$option['slug']] = $option;
	}

	return $options;
}

function mp_headless_project_card_icon_options() {
	return array(
		'lucide:network'                => __('Network', 'medplatform-headless'),
		'lucide:globe-2'                => __('Globe', 'medplatform-headless'),
		'lucide:messages-square'        => __('Dialogue', 'medplatform-headless'),
		'lucide:book-open'              => __('Research', 'medplatform-headless'),
		'lucide:shield'                 => __('Security', 'medplatform-headless'),
		'lucide:chart-no-axes-combined' => __('Data', 'medplatform-headless'),
		'lucide:file-text'              => __('Publication', 'medplatform-headless'),
		'lucide:calendar-days'          => __('Convening', 'medplatform-headless'),
	);
}

function mp_headless_sanitize_project_card_icon($value) {
	$value = sanitize_text_field(wp_unslash($value));

	if ($value === '') {
		return '';
	}

	$options = mp_headless_project_card_icon_options();

	return isset($options[$value]) ? $value : '';
}

function mp_headless_sanitize_string_array($value) {
	if (! is_array($value)) {
		return array();
	}

	$clean = array();
	foreach ($value as $item) {
		$item = sanitize_text_field(wp_unslash($item));
		if ($item !== '') {
			$clean[] = $item;
		}
	}

	return array_values($clean);
}

function mp_headless_sanitize_int_array($value) {
	if (! is_array($value)) {
		return array();
	}

	return array_values(array_filter(array_map('intval', $value)));
}

function mp_headless_sanitize_int_value($value) {
	return intval($value);
}

function mp_headless_sanitize_nonnegative_int_value($value) {
	return max(0, intval($value));
}

function mp_headless_sanitize_bool_value($value) {
	return ! empty($value);
}

function mp_headless_sanitize_hex_color_value($value) {
	$sanitized = sanitize_hex_color(wp_unslash($value));

	return is_string($sanitized) ? $sanitized : '';
}

function mp_headless_sanitize_secret_value($value) {
	return trim(preg_replace('/[\r\n]+/', '', (string) wp_unslash($value)));
}

function mp_headless_sanitize_repo_path_value($value) {
	$value = trim((string) wp_unslash($value));
	$value = preg_replace('#/+#', '/', $value);

	return ltrim($value, '/');
}

function mp_headless_set_github_deploy_status($status, $message, $reason = '') {
	update_option(
		'mp_headless_github_last_status',
		array(
			'status'     => sanitize_text_field((string) $status),
			'message'    => sanitize_textarea_field((string) $message),
			'reason'     => sanitize_text_field((string) $reason),
			'timestamp'  => time(),
			'updated_at' => gmdate('c'),
		),
		false
	);
}

function mp_headless_github_auto_push_enabled() {
	return (bool) get_option('mp_headless_github_auto_push_enabled', true);
}

function mp_headless_trim_words($value, $limit) {
	$limit = max(1, intval($limit));
	$value = trim(preg_replace('/\s+/', ' ', wp_strip_all_tags((string) $value)));

	if ($value === '') {
		return '';
	}

	$words = preg_split('/\s+/', $value);

	if (! is_array($words) || count($words) <= $limit) {
		return $value;
	}

	return implode(' ', array_slice($words, 0, $limit));
}

function mp_headless_project_parent_would_create_cycle($project_id, $candidate_parent_id) {
	$project_id          = intval($project_id);
	$candidate_parent_id = intval($candidate_parent_id);

	if ($project_id < 1 || $candidate_parent_id < 1) {
		return false;
	}

	$visited_ids = array();

	while ($candidate_parent_id > 0 && ! in_array($candidate_parent_id, $visited_ids, true)) {
		if ($candidate_parent_id === $project_id) {
			return true;
		}

		$visited_ids[] = $candidate_parent_id;
		$parent_post   = get_post($candidate_parent_id);

		if (! $parent_post instanceof WP_Post || $parent_post->post_type !== 'mp_project') {
			return false;
		}

		$candidate_parent_id = intval(get_post_meta($candidate_parent_id, 'mp_parent_project_id', true));
	}

	return false;
}

function mp_headless_normalize_parent_project_id($project_id, $parent_project_id) {
	$project_id        = intval($project_id);
	$parent_project_id = intval($parent_project_id);

	if ($parent_project_id < 1 || ($project_id > 0 && $parent_project_id === $project_id)) {
		return 0;
	}

	$parent_project = get_post($parent_project_id);
	if (! $parent_project instanceof WP_Post || $parent_project->post_type !== 'mp_project') {
		return 0;
	}

	if ($project_id > 0 && mp_headless_project_parent_would_create_cycle($project_id, $parent_project_id)) {
		return 0;
	}

	return $parent_project_id;
}

function mp_headless_normalize_project_reference_id($project_id, $reference_project_id) {
	$project_id           = intval($project_id);
	$reference_project_id = intval($reference_project_id);

	if ($reference_project_id < 1 || ($project_id > 0 && $reference_project_id === $project_id)) {
		return 0;
	}

	$reference_project = get_post($reference_project_id);
	if (! $reference_project instanceof WP_Post || $reference_project->post_type !== 'mp_project') {
		return 0;
	}

	return $reference_project_id;
}

function mp_headless_normalize_aligned_project_id($project_id, $aligned_project_id) {
	return mp_headless_normalize_project_reference_id($project_id, $aligned_project_id);
}

function mp_headless_sanitize_named_assets($value) {
	if (! is_array($value)) {
		return array();
	}

	$clean = array();
	foreach ($value as $item) {
		if (! is_array($item)) {
			continue;
		}

		$name = isset($item['name']) ? sanitize_text_field(wp_unslash($item['name'])) : '';
		$logo = isset($item['logo']) ? esc_url_raw(wp_unslash($item['logo'])) : '';

		if ($name !== '' || $logo !== '') {
			$clean[] = array(
				'name' => $name,
				'logo' => $logo,
			);
		}
	}

	return array_values($clean);
}

function mp_headless_sanitize_named_links($value) {
	if (! is_array($value)) {
		return array();
	}

	$clean = array();
	foreach ($value as $item) {
		if (! is_array($item)) {
			continue;
		}

		$name = isset($item['name']) ? sanitize_text_field(wp_unslash($item['name'])) : '';
		$url  = isset($item['url']) ? esc_url_raw(wp_unslash($item['url'])) : '';

		if ($name !== '' || $url !== '') {
			$clean[] = array(
				'name' => $name,
				'url'  => $url,
			);
		}
	}

	return array_values($clean);
}

function mp_headless_sanitize_focus_areas($value) {
	if (! is_array($value)) {
		return array();
	}

	$clean = array();
	foreach ($value as $item) {
		if (! is_array($item)) {
			continue;
		}

		$title       = isset($item['title']) ? sanitize_text_field(wp_unslash($item['title'])) : '';
		$description = isset($item['description']) ? sanitize_textarea_field(wp_unslash($item['description'])) : '';

		if ($title !== '' || $description !== '') {
			$clean[] = array(
				'title'       => $title,
				'description' => $description,
			);
		}
	}

	return array_values($clean);
}

function mp_headless_sanitize_custom_focus_areas($value) {
	if (! is_array($value)) {
		return array();
	}

	$clean = array();
	foreach ($value as $item) {
		if (! is_array($item)) {
			continue;
		}

		$title       = isset($item['title']) ? sanitize_text_field(wp_unslash($item['title'])) : '';
		$description = isset($item['description'])
			? mp_headless_trim_words(sanitize_textarea_field(wp_unslash($item['description'])), 25)
			: '';
		$enabled     = array_key_exists('enabled', $item)
			? ! empty($item['enabled'])
			: ($title !== '' || $description !== '');

		if ($title !== '' || $description !== '' || $enabled) {
			$clean[] = array(
				'enabled'     => $enabled,
				'title'       => $title,
				'description' => $description,
			);
		}
	}

	return array_values($clean);
}

function mp_headless_get_frontend_base_url() {
	$configured_url = trim((string) get_option('mp_headless_frontend_url', ''));
	if ($configured_url !== '') {
		return untrailingslashit($configured_url);
	}

	$home_url = home_url('/');
	$host     = wp_parse_url($home_url, PHP_URL_HOST);

	if (in_array($host, array('127.0.0.1', 'localhost'), true)) {
		return 'http://localhost:4321';
	}

	return 'https://mediterranean-platform.org';
}

function mp_headless_get_frontend_url($path = '/') {
	$base_url = mp_headless_get_frontend_base_url();

	if ($path === '' || $path === '/') {
		return $base_url . '/';
	}

	return $base_url . '/' . ltrim($path, '/');
}

function mp_headless_get_site_settings_payload() {
	return array(
		'socialLinks'              => array(
			'linkedin'  => trim((string) get_option('mp_headless_site_linkedin_url', '')),
			'youtube'   => trim((string) get_option('mp_headless_site_youtube_url', '')),
			'instagram' => trim((string) get_option('mp_headless_site_instagram_url', '')),
		),
		'showPublicDownloadCounts' => (bool) get_option('mp_headless_show_public_download_counts', false),
	);
}

function mp_headless_get_publication_download_count($publication_id) {
	return max(0, (int) get_post_meta((int) $publication_id, 'mp_download_count', true));
}

function mp_headless_increment_publication_download_count($publication_id) {
	$publication_id = (int) $publication_id;
	$new_count      = mp_headless_get_publication_download_count($publication_id) + 1;

	update_post_meta($publication_id, 'mp_download_count', $new_count);
	clean_post_cache($publication_id);

	return $new_count;
}

function mp_headless_get_publication_download_stats_payload($publication_id) {
	return array(
		'publicationId' => (int) $publication_id,
		'downloadCount' => mp_headless_get_publication_download_count($publication_id),
	);
}

function mp_headless_get_frontend_post_path($post) {
	$post = get_post($post);
	if (! $post instanceof WP_Post) {
		return '/';
	}

	switch ($post->post_type) {
		case 'mp_publication':
			return '/blog/' . $post->post_name;
		case 'mp_project':
			return '/services/' . $post->post_name;
		case 'mp_person':
			return '/team/' . $post->post_name;
		case 'mp_homepage':
			return '/';
		default:
			return '/';
	}
}

function mp_headless_get_frontend_post_url($post) {
	return mp_headless_get_frontend_url(mp_headless_get_frontend_post_path($post));
}

function mp_headless_get_frontend_term_path($term) {
	$term = get_term($term);
	if (! $term || is_wp_error($term)) {
		return '/blog';
	}

	switch ($term->taxonomy) {
		case 'mp_output_type':
			return '/blog/category/' . $term->slug;
		case 'mp_hashtag':
			return '/blog/tags/' . $term->slug;
		case 'mp_topic':
			return '/blog';
		default:
			return '/';
	}
}

function mp_headless_get_frontend_term_url($term) {
	return mp_headless_get_frontend_url(mp_headless_get_frontend_term_path($term));
}

function mp_headless_is_login_request() {
	$script_name = basename((string) ($_SERVER['PHP_SELF'] ?? ''));
	return in_array($script_name, array('wp-login.php', 'wp-signup.php'), true);
}

function mp_headless_is_rest_request() {
	if ((defined('REST_REQUEST') && REST_REQUEST) || (function_exists('wp_is_json_request') && wp_is_json_request())) {
		return true;
	}

	$request_uri = (string) ($_SERVER['REQUEST_URI'] ?? '');
	return strpos($request_uri, '/wp-json/') !== false || strpos($request_uri, 'rest_route=') !== false;
}

function mp_headless_is_public_page_request() {
	if (is_admin() || wp_doing_ajax() || wp_doing_cron() || mp_headless_is_login_request() || mp_headless_is_rest_request()) {
		return false;
	}

	if (is_preview()) {
		return false;
	}

	if (defined('XMLRPC_REQUEST') && XMLRPC_REQUEST) {
		return false;
	}

	$request_path = wp_parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
	if (is_string($request_path)) {
		$basename = basename($request_path);
		if ($basename !== '' && preg_match('/\.[a-z0-9]+$/i', $basename)) {
			return false;
		}
	}

	return is_front_page() || is_home() || is_archive() || is_singular() || is_search() || is_404();
}

function mp_headless_get_frontend_redirect_url() {
	if (is_singular()) {
		return mp_headless_get_frontend_post_url(get_queried_object_id());
	}

	if (is_post_type_archive('mp_publication')) {
		return mp_headless_get_frontend_url('/blog');
	}

	if (is_post_type_archive('mp_project')) {
		return mp_headless_get_frontend_url('/services');
	}

	if (is_post_type_archive('mp_person')) {
		return mp_headless_get_frontend_url('/team');
	}

	if (is_tax(array('mp_output_type', 'mp_hashtag', 'mp_topic'))) {
		return mp_headless_get_frontend_term_url(get_queried_object());
	}

	if (is_search()) {
		return mp_headless_get_frontend_url('/blog');
	}

	return mp_headless_get_frontend_url('/');
}

function mp_headless_post_type_labels($singular, $plural) {
	return array(
		'name'               => $plural,
		'singular_name'      => $singular,
		'menu_name'          => $plural,
		'name_admin_bar'     => $singular,
		'add_new'            => sprintf(__('Add %s', 'medplatform-headless'), $singular),
		'add_new_item'       => sprintf(__('Add %s', 'medplatform-headless'), $singular),
		'edit_item'          => sprintf(__('Edit %s', 'medplatform-headless'), $singular),
		'new_item'           => sprintf(__('New %s', 'medplatform-headless'), $singular),
		'view_item'          => sprintf(__('View %s', 'medplatform-headless'), $singular),
		'view_items'         => sprintf(__('View %s', 'medplatform-headless'), $plural),
		'search_items'       => sprintf(__('Search %s', 'medplatform-headless'), $plural),
		'not_found'          => sprintf(__('No %s found.', 'medplatform-headless'), strtolower($plural)),
		'not_found_in_trash' => sprintf(__('No %s found in Trash.', 'medplatform-headless'), strtolower($plural)),
		'all_items'          => sprintf(__('All %s', 'medplatform-headless'), $plural),
	);
}

function mp_headless_default_output_types() {
	return array(
		array(
			'label' => 'Policy Paper',
			'slug'  => 'policy-paper',
		),
		array(
			'label' => 'Research Report',
			'slug'  => 'research-report',
		),
		array(
			'label' => 'Policy Brief',
			'slug'  => 'policy-brief',
		),
		array(
			'label' => 'E-Book',
			'slug'  => 'e-book',
		),
		array(
			'label' => 'Insights',
			'slug'  => 'insights',
		),
		array(
			'label' => 'Pod-Cast',
			'slug'  => 'pod-cast',
		),
	);
}

function mp_headless_ensure_output_type_terms() {
	$legacy_insights_term = get_term_by('slug', 'archive-commentary', 'mp_output_type');
	if (! $legacy_insights_term) {
		$legacy_insights_term = get_term_by('name', 'Archive & Commentary', 'mp_output_type');
	}

	if ($legacy_insights_term && ! is_wp_error($legacy_insights_term)) {
		wp_update_term(
			$legacy_insights_term->term_id,
			'mp_output_type',
			array(
				'name' => 'Insights',
				'slug' => 'insights',
			)
		);
	}

	foreach (mp_headless_default_output_types() as $output_type) {
		$existing = get_term_by('slug', $output_type['slug'], 'mp_output_type');

		if (! $existing) {
			$existing = get_term_by('name', $output_type['label'], 'mp_output_type');
		}

		if (! $existing) {
			wp_insert_term(
				$output_type['label'],
				'mp_output_type',
				array(
					'slug' => $output_type['slug'],
				)
			);
		}
	}
}

function mp_headless_get_output_type_options() {
	mp_headless_ensure_output_type_terms();
	$options = array();

	foreach (mp_headless_default_output_types() as $output_type) {
		$term = get_term_by('slug', $output_type['slug'], 'mp_output_type');
		if (! $term) {
			$term = get_term_by('name', $output_type['label'], 'mp_output_type');
		}

		if ($term && ! is_wp_error($term)) {
			$options[] = array(
				'label' => $term->name,
				'slug'  => $term->slug,
			);
		}
	}

	return $options;
}

function mp_headless_get_publication_output_type_slug($post_id) {
	$terms = wp_get_object_terms(
		$post_id,
		'mp_output_type',
		array(
			'fields' => 'slugs',
		)
	);

	if (is_wp_error($terms) || empty($terms)) {
		return '';
	}

	return (string) $terms[0];
}

function mp_headless_publication_is_podcast($post_id) {
	if (! $post_id) {
		return false;
	}

	return mp_headless_get_publication_output_type_slug($post_id) === 'pod-cast';
}

function mp_headless_get_podcast_publications() {
	mp_headless_ensure_output_type_terms();

	return get_posts(
		array(
			'post_type'      => 'mp_publication',
			'posts_per_page' => -1,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'tax_query'      => array(
				array(
					'taxonomy' => 'mp_output_type',
					'field'    => 'slug',
					'terms'    => array('pod-cast'),
				),
			),
		)
	);
}

function mp_headless_register_content_types() {
	register_post_type(
		'mp_publication',
		array(
			'labels'       => mp_headless_post_type_labels(__('Publication', 'medplatform-headless'), __('Publications', 'medplatform-headless')),
			'public'       => true,
			'show_ui'      => true,
			'show_in_rest' => true,
			'has_archive'  => false,
			'rewrite'      => false,
			'menu_icon'    => 'dashicons-media-document',
			'supports'     => array('title', 'editor', 'excerpt', 'revisions', 'custom-fields'),
		)
	);

	register_post_type(
		'mp_project',
		array(
			'labels'       => mp_headless_post_type_labels(__('Project', 'medplatform-headless'), __('Projects', 'medplatform-headless')),
			'public'       => true,
			'show_ui'      => true,
			'show_in_rest' => true,
			'has_archive'  => false,
			'rewrite'      => false,
			'menu_icon'    => 'dashicons-networking',
			'supports'     => array('title', 'editor', 'excerpt', 'revisions', 'custom-fields'),
		)
	);

	register_post_type(
		'mp_person',
		array(
			'labels'       => mp_headless_post_type_labels(__('Person', 'medplatform-headless'), __('People', 'medplatform-headless')),
			'public'       => true,
			'show_ui'      => true,
			'show_in_rest' => true,
			'has_archive'  => false,
			'rewrite'      => false,
			'menu_icon'    => 'dashicons-businessperson',
			'supports'     => array('title', 'editor', 'excerpt', 'revisions', 'custom-fields', 'page-attributes'),
		)
	);

	register_post_type(
		'mp_homepage',
		array(
			'labels'       => array(
				'name'          => __('Homepage Settings', 'medplatform-headless'),
				'singular_name' => __('Homepage Settings', 'medplatform-headless'),
				'menu_name'     => __('Homepage Settings', 'medplatform-headless'),
				'edit_item'     => __('Edit Homepage Settings', 'medplatform-headless'),
			),
			'public'       => false,
			'show_ui'      => false,
			'show_in_menu' => false,
			'show_in_rest' => true,
			'has_archive'  => false,
			'rewrite'      => false,
			'supports'     => array('custom-fields'),
		)
	);

	register_taxonomy(
		'mp_topic',
		array('mp_publication'),
		array(
			'labels'            => array(
				'name'          => __('Topics', 'medplatform-headless'),
				'singular_name' => __('Topic', 'medplatform-headless'),
			),
			'public'            => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'hierarchical'      => false,
		)
	);

	register_taxonomy(
		'mp_hashtag',
		array('mp_publication'),
		array(
			'labels'            => array(
				'name'          => __('Hashtags', 'medplatform-headless'),
				'singular_name' => __('Hashtag', 'medplatform-headless'),
			),
			'public'            => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'hierarchical'      => false,
		)
	);

	register_taxonomy(
		'mp_output_type',
		array('mp_publication'),
		array(
			'labels'            => array(
				'name'          => __('Output Types', 'medplatform-headless'),
				'singular_name' => __('Output Type', 'medplatform-headless'),
			),
			'public'            => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'hierarchical'      => false,
			'meta_box_cb'       => false,
			'show_in_quick_edit'=> false,
		)
	);
}
add_action('init', 'mp_headless_register_content_types');
add_action('init', 'mp_headless_ensure_output_type_terms', 20);

function mp_headless_disable_person_block_editor($use_block_editor, $post_type) {
	if ($post_type === 'mp_person') {
		return false;
	}

	return $use_block_editor;
}
add_filter('use_block_editor_for_post_type', 'mp_headless_disable_person_block_editor', 10, 2);

function mp_headless_register_meta() {
	$rest_string = array(
		'single'        => true,
		'type'          => 'string',
		'show_in_rest'  => true,
		'auth_callback' => function() {
			return current_user_can('edit_posts');
		},
	);

	register_post_meta('mp_publication', 'mp_author_name', $rest_string);
	register_post_meta('mp_publication', 'mp_author_role', $rest_string);
	register_post_meta('mp_publication', 'mp_author_image', $rest_string);
	register_post_meta('mp_publication', 'mp_cover_image', $rest_string);
	register_post_meta('mp_publication', 'mp_audio_url', $rest_string);
	register_post_meta('mp_publication', 'mp_download_url', $rest_string);
	register_post_meta('mp_publication', 'mp_download_label', $rest_string);
	register_post_meta(
		'mp_publication',
		'mp_author_person_id',
		array(
			'single'            => true,
			'type'              => 'integer',
			'default'           => 0,
			'sanitize_callback' => 'mp_headless_sanitize_int_value',
			'show_in_rest'      => true,
			'auth_callback'     => function() {
				return current_user_can('edit_posts');
			},
		)
	);
	register_post_meta(
		'mp_publication',
		'mp_author_person_ids',
		array(
			'single'            => true,
			'type'              => 'array',
			'default'           => array(),
			'sanitize_callback' => 'mp_headless_sanitize_int_array',
			'show_in_rest'      => array(
				'schema' => array(
					'type'  => 'array',
					'items' => array('type' => 'integer'),
				),
			),
			'auth_callback'     => function() {
				return current_user_can('edit_posts');
			},
		)
	);

	register_post_meta(
		'mp_publication',
		'mp_related_project_ids',
		array(
			'single'            => true,
			'type'              => 'array',
			'default'           => array(),
			'sanitize_callback' => 'mp_headless_sanitize_int_array',
			'show_in_rest'      => array(
				'schema' => array(
					'type'  => 'array',
					'items' => array('type' => 'integer'),
				),
			),
			'auth_callback'     => function() {
				return current_user_can('edit_posts');
			},
		)
	);
	register_post_meta(
		'mp_publication',
		'mp_contributor_person_ids',
		array(
			'single'            => true,
			'type'              => 'array',
			'default'           => array(),
			'sanitize_callback' => 'mp_headless_sanitize_int_array',
			'show_in_rest'      => array(
				'schema' => array(
					'type'  => 'array',
					'items' => array('type' => 'integer'),
				),
			),
			'auth_callback'     => function() {
				return current_user_can('edit_posts');
			},
		)
	);
	register_post_meta(
		'mp_publication',
		'mp_contributor_names',
		array(
			'single'            => true,
			'type'              => 'array',
			'default'           => array(),
			'sanitize_callback' => 'mp_headless_sanitize_string_array',
			'show_in_rest'      => array(
				'schema' => array(
					'type'  => 'array',
					'items' => array('type' => 'string'),
				),
			),
			'auth_callback'     => function() {
				return current_user_can('edit_posts');
			},
		)
	);
	register_post_meta(
		'mp_publication',
		'mp_references',
		array(
			'single'            => true,
			'type'              => 'array',
			'default'           => array(),
			'sanitize_callback' => 'mp_headless_sanitize_named_links',
			'show_in_rest'      => array(
				'schema' => array(
					'type'  => 'array',
					'items' => array(
						'type'       => 'object',
						'properties' => array(
							'name' => array('type' => 'string'),
							'url'  => array('type' => 'string'),
						),
					),
				),
			),
			'auth_callback'     => function() {
				return current_user_can('edit_posts');
			},
		)
	);

	register_post_meta('mp_person', 'mp_role', $rest_string);
	register_post_meta('mp_person', 'mp_email', $rest_string);
	register_post_meta('mp_person', 'mp_linkedin_url', $rest_string);
	register_post_meta('mp_person', 'mp_website_url', $rest_string);
	register_post_meta('mp_person', 'mp_photo', $rest_string);
	register_post_meta('mp_person', 'mp_short_bio', $rest_string);
	register_post_meta('mp_person', 'mp_profile_bio', $rest_string);
	register_post_meta(
		'mp_person',
		'mp_show_on_team_page',
		array(
			'single'            => true,
			'type'              => 'boolean',
			'default'           => false,
			'sanitize_callback' => 'mp_headless_sanitize_bool_value',
			'show_in_rest'      => true,
			'auth_callback'     => function() {
				return current_user_can('edit_posts');
			},
		)
	);

	register_post_meta('mp_project', 'mp_color', $rest_string);
	register_post_meta(
		'mp_project',
		'mp_progress_color',
		array(
			'single'            => true,
			'type'              => 'string',
			'default'           => '',
			'sanitize_callback' => 'mp_headless_sanitize_hex_color_value',
			'show_in_rest'      => true,
			'auth_callback'     => function() {
				return current_user_can('edit_posts');
			},
		)
	);
	register_post_meta(
		'mp_project',
		'mp_card_icon',
		array(
			'single'            => true,
			'type'              => 'string',
			'default'           => '',
			'sanitize_callback' => 'mp_headless_sanitize_project_card_icon',
			'show_in_rest'      => true,
			'auth_callback'     => function() {
				return current_user_can('edit_posts');
			},
		)
	);
	register_post_meta('mp_project', 'mp_current_stage', $rest_string);
	register_post_meta(
		'mp_project',
		'mp_parent_project_id',
		array(
			'single'            => true,
			'type'              => 'integer',
			'default'           => 0,
			'sanitize_callback' => 'mp_headless_sanitize_int_value',
			'show_in_rest'      => true,
			'auth_callback'     => function() {
				return current_user_can('edit_posts');
			},
		)
	);
	register_post_meta(
		'mp_project',
		'mp_aligned_project_id',
		array(
			'single'            => true,
			'type'              => 'integer',
			'default'           => 0,
			'sanitize_callback' => 'mp_headless_sanitize_int_value',
			'show_in_rest'      => true,
			'auth_callback'     => function() {
				return current_user_can('edit_posts');
			},
		)
	);
	register_post_meta(
		'mp_project',
		'mp_lead_person_id',
		array(
			'single'            => true,
			'type'              => 'integer',
			'default'           => 0,
			'sanitize_callback' => 'mp_headless_sanitize_int_value',
			'show_in_rest'      => true,
			'auth_callback'     => function() {
				return current_user_can('edit_posts');
			},
		)
	);
	register_post_meta(
		'mp_project',
		'mp_lead_person_ids',
		array(
			'single'            => true,
			'type'              => 'array',
			'default'           => array(),
			'sanitize_callback' => 'mp_headless_sanitize_int_array',
			'show_in_rest'      => array(
				'schema' => array(
					'type'  => 'array',
					'items' => array('type' => 'integer'),
				),
			),
			'auth_callback'     => function() {
				return current_user_can('edit_posts');
			},
		)
	);
	register_post_meta(
		'mp_project',
		'mp_hide_project_bar',
		array(
			'single'            => true,
			'type'              => 'boolean',
			'default'           => false,
			'sanitize_callback' => 'mp_headless_sanitize_bool_value',
			'show_in_rest'      => true,
			'auth_callback'     => function() {
				return current_user_can('edit_posts');
			},
		)
	);
	register_post_meta(
		'mp_project',
		'mp_hide_project_currently',
		array(
			'single'            => true,
			'type'              => 'boolean',
			'default'           => false,
			'sanitize_callback' => 'mp_headless_sanitize_bool_value',
			'show_in_rest'      => true,
			'auth_callback'     => function() {
				return current_user_can('edit_posts');
			},
		)
	);
	register_post_meta(
		'mp_project',
		'mp_stage_points',
		array(
			'single'            => true,
			'type'              => 'array',
			'default'           => array(),
			'sanitize_callback' => 'mp_headless_sanitize_string_array',
			'show_in_rest'      => array(
				'schema' => array(
					'type'  => 'array',
					'items' => array('type' => 'string'),
				),
			),
			'auth_callback'     => function() {
				return current_user_can('edit_posts');
			},
		)
	);
	register_post_meta('mp_project', 'mp_lead_name', $rest_string);
	register_post_meta('mp_project', 'mp_lead_role', $rest_string);
	register_post_meta('mp_project', 'mp_lead_image', $rest_string);

	register_post_meta(
		'mp_project',
		'mp_team_member_ids',
		array(
			'single'            => true,
			'type'              => 'array',
			'default'           => array(),
			'sanitize_callback' => 'mp_headless_sanitize_int_array',
			'show_in_rest'      => array(
				'schema' => array(
					'type'  => 'array',
					'items' => array('type' => 'integer'),
				),
			),
			'auth_callback'     => function() {
				return current_user_can('edit_posts');
			},
		)
	);
	register_post_meta(
		'mp_project',
		'mp_team_members',
		array(
			'single'            => true,
			'type'              => 'array',
			'default'           => array(),
			'sanitize_callback' => 'mp_headless_sanitize_string_array',
			'show_in_rest'      => array(
				'schema' => array(
					'type'  => 'array',
					'items' => array('type' => 'string'),
				),
			),
			'auth_callback'     => function() {
				return current_user_can('edit_posts');
			},
		)
	);

	register_post_meta(
		'mp_project',
		'mp_updates',
		array(
			'single'            => true,
			'type'              => 'array',
			'default'           => array(),
			'sanitize_callback' => 'mp_headless_sanitize_string_array',
			'show_in_rest'      => array(
				'schema' => array(
					'type'  => 'array',
					'items' => array('type' => 'string'),
				),
			),
			'auth_callback'     => function() {
				return current_user_can('edit_posts');
			},
		)
	);

	register_post_meta(
		'mp_project',
		'mp_donors',
		array(
			'single'            => true,
			'type'              => 'array',
			'default'           => array(),
			'sanitize_callback' => 'mp_headless_sanitize_named_assets',
			'show_in_rest'      => array(
				'schema' => array(
					'type'  => 'array',
					'items' => array(
						'type'       => 'object',
						'properties' => array(
							'name' => array('type' => 'string'),
							'logo' => array('type' => 'string'),
						),
					),
				),
			),
			'auth_callback'     => function() {
				return current_user_can('edit_posts');
			},
		)
	);

	register_post_meta(
		'mp_project',
		'mp_focus_area_slugs',
		array(
			'single'            => true,
			'type'              => 'array',
			'default'           => array(),
			'sanitize_callback' => 'mp_headless_sanitize_string_array',
			'show_in_rest'      => array(
				'schema' => array(
					'type'  => 'array',
					'items' => array('type' => 'string'),
				),
			),
			'auth_callback'     => function() {
				return current_user_can('edit_posts');
			},
		)
	);
	register_post_meta(
		'mp_project',
		'mp_focus_areas',
		array(
			'single'            => true,
			'type'              => 'array',
			'default'           => array(),
			'sanitize_callback' => 'mp_headless_sanitize_focus_areas',
			'show_in_rest'      => array(
				'schema' => array(
					'type'  => 'array',
					'items' => array(
						'type'       => 'object',
						'properties' => array(
							'title'       => array('type' => 'string'),
							'description' => array('type' => 'string'),
						),
					),
				),
			),
			'auth_callback'     => function() {
				return current_user_can('edit_posts');
			},
		)
	);
	register_post_meta(
		'mp_project',
		'mp_custom_focus_areas',
		array(
			'single'            => true,
			'type'              => 'array',
			'default'           => array(),
			'sanitize_callback' => 'mp_headless_sanitize_custom_focus_areas',
			'show_in_rest'      => array(
				'schema' => array(
					'type'  => 'array',
					'items' => array(
						'type'       => 'object',
						'properties' => array(
							'enabled'     => array('type' => 'boolean'),
							'title'       => array('type' => 'string'),
							'description' => array('type' => 'string'),
						),
					),
				),
			),
			'auth_callback'     => function() {
				return current_user_can('edit_posts');
			},
		)
	);

	register_post_meta(
		'mp_homepage',
		'mp_featured_podcast_id',
		array(
			'single'            => true,
			'type'              => 'integer',
			'default'           => 0,
			'sanitize_callback' => 'mp_headless_sanitize_int_value',
			'show_in_rest'      => true,
			'auth_callback'     => function() {
				return current_user_can('edit_posts');
			},
		)
	);
	register_post_meta(
		'mp_homepage',
		'mp_featured_article_id',
		array(
			'single'            => true,
			'type'              => 'integer',
			'default'           => 0,
			'sanitize_callback' => 'mp_headless_sanitize_int_value',
			'show_in_rest'      => true,
			'auth_callback'     => function() {
				return current_user_can('edit_posts');
			},
		)
	);
	register_post_meta(
		'mp_homepage',
		'mp_homepage_project_count',
		array(
			'single'            => true,
			'type'              => 'integer',
			'default'           => 0,
			'sanitize_callback' => 'mp_headless_sanitize_nonnegative_int_value',
			'show_in_rest'      => true,
			'auth_callback'     => function() {
				return current_user_can('edit_posts');
			},
		)
	);
	register_post_meta(
		'mp_homepage',
		'mp_homepage_project_ids',
		array(
			'single'            => true,
			'type'              => 'array',
			'default'           => array(),
			'sanitize_callback' => 'mp_headless_sanitize_int_array',
			'show_in_rest'      => array(
				'schema' => array(
					'type'  => 'array',
					'items' => array('type' => 'integer'),
				),
			),
			'auth_callback'     => function() {
				return current_user_can('edit_posts');
			},
		)
	);
	register_post_meta(
		'mp_homepage',
		'mp_slider_publication_ids',
		array(
			'single'            => true,
			'type'              => 'array',
			'default'           => array(),
			'sanitize_callback' => 'mp_headless_sanitize_int_array',
			'show_in_rest'      => array(
				'schema' => array(
					'type'  => 'array',
					'items' => array('type' => 'integer'),
				),
			),
			'auth_callback'     => function() {
				return current_user_can('edit_posts');
			},
		)
	);
	register_post_meta(
		'mp_homepage',
		'mp_latest_publication_ids',
		array(
			'single'            => true,
			'type'              => 'array',
			'default'           => array(),
			'sanitize_callback' => 'mp_headless_sanitize_int_array',
			'show_in_rest'      => array(
				'schema' => array(
					'type'  => 'array',
					'items' => array('type' => 'integer'),
				),
			),
			'auth_callback'     => function() {
				return current_user_can('edit_posts');
			},
		)
	);
	register_post_meta('mp_homepage', 'mp_announcement_text', $rest_string);
	register_post_meta('mp_homepage', 'mp_announcement_link_url', $rest_string);
	register_post_meta('mp_homepage', 'mp_announcement_link_label', $rest_string);
}
add_action('init', 'mp_headless_register_meta');

function mp_headless_get_homepage_post_id() {
	$existing = get_page_by_path('homepage-settings', OBJECT, 'mp_homepage');
	if ($existing) {
		return (int) $existing->ID;
	}

	$result = wp_insert_post(
		array(
			'post_type'   => 'mp_homepage',
			'post_title'  => 'Homepage Settings',
			'post_name'   => 'homepage-settings',
			'post_status' => 'publish',
		),
		true
	);

	if (is_wp_error($result)) {
		return 0;
	}

	return (int) $result;
}

function mp_headless_get_homepage_admin_url() {
	$homepage_id = mp_headless_get_homepage_post_id();
	if (! $homepage_id) {
		return admin_url();
	}

	return admin_url('post.php?post=' . $homepage_id . '&action=edit');
}

function mp_headless_seed_upsert_post($post_type, $slug, $postarr) {
	$existing = get_page_by_path($slug, OBJECT, $post_type);

	if ($existing instanceof WP_Post) {
		$postarr['ID'] = $existing->ID;
		return wp_update_post($postarr, true);
	}

	return wp_insert_post($postarr, true);
}

function mp_headless_seed_normalize_output_type($value) {
	if ('Series' === $value) {
		return 'Insights';
	}

	if ('Podcast' === $value) {
		return 'Pod-Cast';
	}

	return $value ?: 'Insights';
}

function mp_headless_get_bundled_seed_payload() {
	$seed_dir = trailingslashit(plugin_dir_path(__FILE__)) . 'seed/';
	$paths    = array(
		'people'       => $seed_dir . 'people.json',
		'projects'     => $seed_dir . 'projects.json',
		'homepage'     => $seed_dir . 'homepage.json',
		'publications' => $seed_dir . 'generated-publications.json',
	);
	$payloads = array();

	foreach ($paths as $key => $path) {
		if (! file_exists($path)) {
			return new WP_Error('mp_headless_seed_missing', sprintf(__('Seed file missing: %s', 'medplatform-headless'), basename($path)));
		}

		$decoded = json_decode((string) file_get_contents($path), true);
		if (! is_array($decoded)) {
			return new WP_Error('mp_headless_seed_invalid', sprintf(__('Seed file could not be parsed: %s', 'medplatform-headless'), basename($path)));
		}

		$payloads[$key] = $decoded;
	}

	return $payloads;
}

function mp_headless_import_bundled_seed_content() {
	$seed_payload = mp_headless_get_bundled_seed_payload();
	if (is_wp_error($seed_payload)) {
		return $seed_payload;
	}

	$people       = $seed_payload['people'];
	$projects     = $seed_payload['projects'];
	$homepage     = $seed_payload['homepage'];
	$publications = $seed_payload['publications'];

	$output_types = array('Policy Paper', 'Research Report', 'Policy Brief', 'E-Book', 'Insights', 'Pod-Cast');
	foreach ($output_types as $term_name) {
		if (! term_exists($term_name, 'mp_output_type')) {
			wp_insert_term($term_name, 'mp_output_type');
		}
	}

	$person_ids          = array();
	$person_ids_by_title = array();

	foreach ($people as $person) {
		if (! is_array($person) || empty($person['slug']) || empty($person['title'])) {
			continue;
		}

		$result = mp_headless_seed_upsert_post(
			'mp_person',
			(string) $person['slug'],
			array(
				'post_type'    => 'mp_person',
				'post_status'  => 'publish',
				'post_name'    => (string) $person['slug'],
				'post_title'   => (string) $person['title'],
				'post_excerpt' => (string) ($person['excerpt'] ?? ''),
				'post_content' => (string) ($person['contentHtml'] ?? ''),
				'menu_order'   => intval($person['menuOrder'] ?? 0),
			)
		);

		if (is_wp_error($result)) {
			continue;
		}

		$person_id = (int) $result;
		$person_ids[(string) $person['slug']] = $person_id;
		$person_ids_by_title[strtolower((string) $person['title'])] = $person_id;

		update_post_meta($person_id, 'mp_role', (string) ($person['role'] ?? ''));
		update_post_meta($person_id, 'mp_email', (string) ($person['email'] ?? ''));
		update_post_meta($person_id, 'mp_linkedin_url', (string) ($person['linkedinUrl'] ?? ''));
		update_post_meta($person_id, 'mp_website_url', (string) ($person['websiteUrl'] ?? ''));
		update_post_meta($person_id, 'mp_photo', (string) ($person['imageUrl'] ?? ''));
		update_post_meta($person_id, 'mp_show_on_team_page', ! empty($person['showOnTeamPage']));
	}

	$project_ids = array();

	foreach ($projects as $project) {
		if (! is_array($project) || empty($project['slug']) || empty($project['title'])) {
			continue;
		}

		$result = mp_headless_seed_upsert_post(
			'mp_project',
			(string) $project['slug'],
			array(
				'post_type'    => 'mp_project',
				'post_status'  => 'publish',
				'post_name'    => (string) $project['slug'],
				'post_title'   => (string) $project['title'],
				'post_excerpt' => (string) ($project['excerpt'] ?? ''),
				'post_content' => (string) ($project['contentHtml'] ?? ''),
			)
		);

		if (is_wp_error($result)) {
			continue;
		}

		$project_id = (int) $result;
		$project_ids[(string) $project['slug']] = $project_id;

		$lead_person_slugs = ! empty($project['leadPersonSlugs']) && is_array($project['leadPersonSlugs'])
			? $project['leadPersonSlugs']
			: array();
		$lead_person_ids = array();

		foreach ($lead_person_slugs as $lead_person_slug) {
			if (isset($person_ids[$lead_person_slug])) {
				$lead_person_ids[] = (int) $person_ids[$lead_person_slug];
			}
		}

		if (empty($lead_person_ids) && ! empty($project['leadName'])) {
			$lead_name_lookup = strtolower((string) $project['leadName']);
			if (isset($person_ids_by_title[$lead_name_lookup])) {
				$lead_person_ids[] = (int) $person_ids_by_title[$lead_name_lookup];
			}
		}

		$team_member_ids = array();
		foreach ((array) ($project['teamMembers'] ?? array()) as $team_member_name) {
			$team_member_lookup = strtolower((string) $team_member_name);
			if (isset($person_ids_by_title[$team_member_lookup])) {
				$team_member_ids[] = (int) $person_ids_by_title[$team_member_lookup];
			}
		}

		foreach ($lead_person_ids as $lead_person_id) {
			if (! in_array((int) $lead_person_id, $team_member_ids, true)) {
				$team_member_ids[] = (int) $lead_person_id;
			}
		}

		$lead_person_ids = array_values(array_unique(array_map('intval', $lead_person_ids)));
		$team_member_ids = array_values(array_unique(array_map('intval', $team_member_ids)));
		$lead_person_id  = ! empty($lead_person_ids) ? (int) $lead_person_ids[0] : 0;
		$lead_name       = $lead_person_id > 0 ? (string) get_the_title($lead_person_id) : (string) ($project['leadName'] ?? '');
		$lead_image      = $lead_person_id > 0 ? (string) get_post_meta($lead_person_id, 'mp_photo', true) : (string) ($project['leadImage'] ?? '');
		$accent_color    = (string) ($project['color'] ?? '#15243a');
		$progress_color  = (string) ($project['progressColor'] ?? $accent_color);

		update_post_meta($project_id, 'mp_color', $accent_color);
		update_post_meta($project_id, 'mp_progress_color', $progress_color);
		update_post_meta($project_id, 'mp_current_stage', (string) ($project['currentStage'] ?? ''));
		update_post_meta($project_id, 'mp_stage_points', (array) ($project['stagePoints'] ?? array('Conception', 'Research', 'Dialogue', 'Publication', 'Implemented')));
		update_post_meta($project_id, 'mp_hide_project_bar', ! empty($project['hideProjectBar']));
		update_post_meta($project_id, 'mp_hide_project_currently', ! empty($project['hideFromProjectScreens']));
		update_post_meta($project_id, 'mp_lead_person_id', $lead_person_id);
		update_post_meta($project_id, 'mp_lead_person_ids', $lead_person_ids);
		update_post_meta($project_id, 'mp_lead_name', $lead_name);
		update_post_meta($project_id, 'mp_lead_role', (string) ($project['leadRole'] ?? ''));
		update_post_meta($project_id, 'mp_lead_image', $lead_image);
		update_post_meta($project_id, 'mp_team_members', (array) ($project['teamMembers'] ?? array()));
		update_post_meta($project_id, 'mp_team_member_ids', $team_member_ids);
		update_post_meta($project_id, 'mp_donors', (array) ($project['donors'] ?? array()));
		update_post_meta($project_id, 'mp_updates', (array) ($project['updates'] ?? array()));
		update_post_meta($project_id, 'mp_focus_areas', (array) ($project['focusAreas'] ?? array()));
		update_post_meta($project_id, 'mp_card_icon', mp_headless_sanitize_project_card_icon($project['cardIcon'] ?? ''));
	}

	foreach ($projects as $project) {
		if (! is_array($project) || empty($project['slug']) || ! isset($project_ids[(string) $project['slug']])) {
			continue;
		}

		$project_id         = (int) $project_ids[(string) $project['slug']];
		$parent_project_id  = 0;
		$aligned_project_id = 0;

		if (! empty($project['parentProjectSlug']) && isset($project_ids[(string) $project['parentProjectSlug']])) {
			$parent_project_id = (int) $project_ids[(string) $project['parentProjectSlug']];
		}

		if (! empty($project['alignedProjectSlug']) && isset($project_ids[(string) $project['alignedProjectSlug']])) {
			$aligned_project_id = (int) $project_ids[(string) $project['alignedProjectSlug']];
		}

		update_post_meta($project_id, 'mp_parent_project_id', $parent_project_id);
		update_post_meta($project_id, 'mp_aligned_project_id', $aligned_project_id);
	}

	$publication_ids = array();

	foreach ($publications as $publication) {
		if (! is_array($publication) || empty($publication['slug']) || empty($publication['title'])) {
			continue;
		}

		$result = mp_headless_seed_upsert_post(
			'mp_publication',
			(string) $publication['slug'],
			array(
				'post_type'    => 'mp_publication',
				'post_status'  => 'publish',
				'post_name'    => (string) $publication['slug'],
				'post_title'   => (string) $publication['title'],
				'post_excerpt' => (string) ($publication['excerpt'] ?? ''),
				'post_content' => (string) ($publication['contentHtml'] ?? ''),
				'post_date'    => (string) ($publication['date'] ?? current_time('mysql')),
			)
		);

		if (is_wp_error($result)) {
			continue;
		}

		$publication_id = (int) $result;
		$publication_ids[(string) $publication['slug']] = $publication_id;

		wp_set_object_terms($publication_id, (array) ($publication['topics'] ?? array()), 'mp_topic');
		wp_set_object_terms($publication_id, (array) ($publication['hashtags'] ?? array()), 'mp_hashtag');
		wp_set_object_terms($publication_id, array(mp_headless_seed_normalize_output_type((string) ($publication['category'] ?? 'Insights'))), 'mp_output_type');

		$related_project_ids = array();
		foreach ((array) ($publication['projectSlugs'] ?? array()) as $project_slug) {
			if (isset($project_ids[$project_slug])) {
				$related_project_ids[] = (int) $project_ids[$project_slug];
			}
		}

		$author_person_slugs = ! empty($publication['authorPersonSlugs']) && is_array($publication['authorPersonSlugs'])
			? $publication['authorPersonSlugs']
			: array();
		if (empty($author_person_slugs) && ! empty($publication['authorPersonSlug'])) {
			$author_person_slugs[] = (string) $publication['authorPersonSlug'];
		}

		$author_person_ids = array();
		foreach ($author_person_slugs as $author_person_slug) {
			if (isset($person_ids[$author_person_slug])) {
				$author_person_ids[] = (int) $person_ids[$author_person_slug];
			}
		}

		$author_person_ids = array_values(array_unique(array_map('intval', $author_person_ids)));
		$author_person_id  = ! empty($author_person_ids) ? (int) $author_person_ids[0] : 0;
		$author_names      = array();

		foreach ($author_person_ids as $selected_author_person_id) {
			$selected_author_name = get_the_title($selected_author_person_id);
			if ($selected_author_name) {
				$author_names[] = $selected_author_name;
			}
		}

		$is_podcast      = mp_headless_seed_normalize_output_type((string) ($publication['category'] ?? 'Insights')) === 'Pod-Cast';
		$asset_url       = (string) ($publication['downloadUrl'] ?? '');
		$download_url    = $is_podcast ? '' : $asset_url;
		$audio_url       = $is_podcast ? $asset_url : (string) ($publication['audioUrl'] ?? '');
		$download_label  = $is_podcast ? '' : (string) ($publication['downloadLabel'] ?? '');
		$author_name     = ! empty($author_names) ? implode(', ', $author_names) : (string) ($publication['authorName'] ?? '');

		update_post_meta($publication_id, 'mp_author_name', $author_name);
		update_post_meta($publication_id, 'mp_author_person_id', $author_person_id);
		update_post_meta($publication_id, 'mp_author_person_ids', $author_person_ids);
		update_post_meta($publication_id, 'mp_author_role', (string) ($publication['authorRole'] ?? ''));
		update_post_meta($publication_id, 'mp_author_image', $author_person_id ? (string) get_post_meta($author_person_id, 'mp_photo', true) : (string) ($publication['authorImage'] ?? ''));
		update_post_meta($publication_id, 'mp_cover_image', (string) ($publication['imageUrl'] ?? ''));
		update_post_meta($publication_id, 'mp_audio_url', $audio_url);
		update_post_meta($publication_id, 'mp_download_url', $download_url);
		update_post_meta($publication_id, 'mp_download_label', $download_label);
		update_post_meta($publication_id, 'mp_references', (array) ($publication['references'] ?? array()));

		$contributor_person_ids = array();
		foreach ((array) ($publication['contributorPersonSlugs'] ?? array()) as $person_slug) {
			if (isset($person_ids[$person_slug])) {
				$contributor_person_ids[] = (int) $person_ids[$person_slug];
			}
		}
		$contributor_person_ids = array_values(
			array_filter(
				array_map('intval', $contributor_person_ids),
				function($person_id) use ($author_person_ids) {
					return ! in_array((int) $person_id, $author_person_ids, true);
				}
			)
		);

		update_post_meta($publication_id, 'mp_contributor_person_ids', $contributor_person_ids);
		update_post_meta($publication_id, 'mp_contributor_names', (array) ($publication['contributorNames'] ?? array()));
		update_post_meta($publication_id, 'mp_related_project_ids', $related_project_ids);
	}

	$homepage_id = mp_headless_get_homepage_post_id();

	if ($homepage_id > 0) {
		$featured_podcast_slug = (string) ($homepage['featuredPodcastSlug'] ?? '');
		$featured_article_slug = (string) ($homepage['featuredArticleSlug'] ?? '');
		$project_selection_ids = array_values($project_ids);
		$slider_ids            = array();
		$latest_ids            = array();

		foreach ((array) ($homepage['sliderPublicationSlugs'] ?? array()) as $slug) {
			if (isset($publication_ids[$slug])) {
				$slider_ids[] = (int) $publication_ids[$slug];
			}
		}

		foreach ((array) ($homepage['latestPublicationSlugs'] ?? array()) as $slug) {
			if (isset($publication_ids[$slug])) {
				$latest_ids[] = (int) $publication_ids[$slug];
			}
		}

		update_post_meta($homepage_id, 'mp_featured_podcast_id', isset($publication_ids[$featured_podcast_slug]) ? (int) $publication_ids[$featured_podcast_slug] : 0);
		update_post_meta($homepage_id, 'mp_featured_article_id', isset($publication_ids[$featured_article_slug]) ? (int) $publication_ids[$featured_article_slug] : 0);
		update_post_meta($homepage_id, 'mp_homepage_project_count', 0);
		update_post_meta($homepage_id, 'mp_homepage_project_ids', $project_selection_ids);
		update_post_meta($homepage_id, 'mp_slider_publication_ids', $slider_ids);
		update_post_meta($homepage_id, 'mp_latest_publication_ids', $latest_ids);
		update_post_meta($homepage_id, 'mp_announcement_text', (string) ($homepage['announcementText'] ?? ''));
		update_post_meta($homepage_id, 'mp_announcement_link_label', (string) ($homepage['announcementLinkLabel'] ?? ''));
		update_post_meta($homepage_id, 'mp_announcement_link_url', (string) ($homepage['announcementLinkUrl'] ?? ''));
	}

	flush_rewrite_rules();

	return array(
		'people'       => count($person_ids),
		'projects'     => count($project_ids),
		'publications' => count($publication_ids),
		'homepage'     => $homepage_id > 0 ? 1 : 0,
	);
}

function mp_headless_set_seed_import_status($status, $message, $counts = array()) {
	set_transient(
		'mp_headless_seed_import_status',
		array(
			'status'     => sanitize_key((string) $status),
			'message'    => sanitize_text_field((string) $message),
			'counts'     => is_array($counts) ? $counts : array(),
			'updated_at' => current_time('mysql'),
		),
		10 * MINUTE_IN_SECONDS
	);
}

function mp_headless_get_person_admin_url($person_id = 0) {
	$url = admin_url('admin.php?page=mp-person-profile');

	if ($person_id > 0) {
		$url = add_query_arg('person_id', intval($person_id), $url);
	}

	return $url;
}

function mp_headless_get_project_admin_url($project_id = 0) {
	$url = admin_url('admin.php?page=mp-project-profile');

	if ($project_id > 0) {
		$url = add_query_arg('project_id', intval($project_id), $url);
	}

	return $url;
}

function mp_headless_activate() {
	mp_headless_register_content_types();
	mp_headless_register_meta();
	mp_headless_ensure_output_type_terms();
	flush_rewrite_rules();
	mp_headless_get_homepage_post_id();
}
register_activation_hook(__FILE__, 'mp_headless_activate');

function mp_headless_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'mp_headless_deactivate');

function mp_headless_parse_lines($value) {
	$raw = preg_split('/\r\n|\r|\n/', (string) wp_unslash($value));
	return array_values(
		array_filter(
			array_map('trim', $raw),
			function($item) {
				return $item !== '';
			}
		)
	);
}

function mp_headless_parse_named_assets($value) {
	$lines = mp_headless_parse_lines($value);
	$items = array();

	foreach ($lines as $line) {
		$parts = array_map('trim', explode('|', $line, 2));
		$items[] = array(
			'name' => $parts[0] ?? '',
			'logo' => isset($parts[1]) ? esc_url_raw($parts[1]) : '',
		);
	}

	return $items;
}

function mp_headless_parse_focus_areas($value) {
	$lines = mp_headless_parse_lines($value);
	$items = array();

	foreach ($lines as $line) {
		$parts = array_map('trim', explode('|', $line, 2));
		$items[] = array(
			'title'       => $parts[0] ?? '',
			'description' => $parts[1] ?? '',
		);
	}

	return $items;
}

function mp_headless_parse_named_links($value) {
	$lines = mp_headless_parse_lines($value);
	$items = array();

	foreach ($lines as $line) {
		$parts = array_map('trim', explode('|', $line, 2));
		$items[] = array(
			'name' => $parts[0] ?? '',
			'url'  => isset($parts[1]) ? esc_url_raw($parts[1]) : '',
		);
	}

	return $items;
}

function mp_headless_find_person_id_by_name($name) {
	$name = trim((string) $name);
	if ($name === '') {
		return 0;
	}

	$person = get_page_by_title($name, OBJECT, 'mp_person');
	return $person instanceof WP_Post ? (int) $person->ID : 0;
}

function mp_headless_normalize_selected_person_ids($selected_ids, $fallback_id = 0) {
	$person_ids = is_array($selected_ids) ? mp_headless_sanitize_int_array($selected_ids) : array();
	$fallback_id = intval($fallback_id);

	if ($fallback_id > 0 && ! in_array($fallback_id, $person_ids, true)) {
		$person_ids[] = $fallback_id;
	}

	return array_values(array_unique(array_filter(array_map('intval', $person_ids))));
}

function mp_headless_generate_logo_label_from_url($url, $index = 0) {
	$path = wp_parse_url((string) $url, PHP_URL_PATH);
	$file = $path ? wp_basename($path) : '';
	$name = preg_replace('/\.[^.]+$/', '', $file);
	$name = trim(str_replace(array('-', '_'), ' ', (string) $name));

	if ($name === '') {
		return sprintf(__('Donor %d', 'medplatform-headless'), intval($index) + 1);
	}

	return ucwords($name);
}

function mp_headless_resolve_focus_area_objects($selected_slugs) {
	$selected_slugs = is_array($selected_slugs) ? array_values(array_unique(array_map('sanitize_title', $selected_slugs))) : array();
	$options_by_slug = mp_headless_project_focus_areas_by_slug();
	$resolved = array();

	foreach ($selected_slugs as $slug) {
		if (isset($options_by_slug[$slug])) {
			$resolved[] = array(
				'title'       => $options_by_slug[$slug]['title'],
				'description' => $options_by_slug[$slug]['description'],
			);
		}
	}

	return $resolved;
}

function mp_headless_render_media_field($args) {
	$label         = $args['label'] ?? '';
	$name          = $args['name'] ?? '';
	$value         = $args['value'] ?? '';
	$button_label  = $args['button_label'] ?? __('Select file', 'medplatform-headless');
	$library_type  = $args['library_type'] ?? '';
	$placeholder   = $args['placeholder'] ?? '/Team/example.jpg or https://example.com/file.jpg';
	$description   = $args['description'] ?? __('Use either a WordPress media upload or a local public path like /Team/name.jpg.', 'medplatform-headless');
	$max_size_mb   = isset($args['max_size_mb']) ? (float) $args['max_size_mb'] : 0;
	$max_size_bytes = $max_size_mb > 0 ? (int) round($max_size_mb * 1024 * 1024) : 0;
	?>
	<p>
		<label><strong><?php echo esc_html($label); ?></strong></label><br />
		<div class="mp-media-field">
			<input
				type="text"
				class="widefat mp-media-input"
				name="<?php echo esc_attr($name); ?>"
				value="<?php echo esc_attr($value); ?>"
				placeholder="<?php echo esc_attr($placeholder); ?>"
			/>
			<div style="margin-top:8px; display:flex; gap:8px; flex-wrap:wrap;">
				<button
					type="button"
					class="button mp-media-upload"
					data-library-type="<?php echo esc_attr($library_type); ?>"
					data-max-size-bytes="<?php echo esc_attr($max_size_bytes); ?>"
					data-max-size-label="<?php echo esc_attr($max_size_mb > 0 ? sprintf(__('Max %s MB', 'medplatform-headless'), rtrim(rtrim(number_format($max_size_mb, 2, '.', ''), '0'), '.')) : ''); ?>"
				>
					<?php echo esc_html($button_label); ?>
				</button>
				<button type="button" class="button-link-delete mp-media-clear">
					<?php esc_html_e('Clear', 'medplatform-headless'); ?>
				</button>
			</div>
			<p style="margin:8px 0 0; color:#646970;">
				<?php echo esc_html($description); ?>
			</p>
		</div>
	</p>
	<?php
}

function mp_headless_render_publication_meta_box($post) {
	wp_nonce_field('mp_headless_save_meta', 'mp_headless_meta_nonce');
	$projects = get_posts(array('post_type' => 'mp_project', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC'));
	$people   = get_posts(array('post_type' => 'mp_person', 'posts_per_page' => -1, 'orderby' => 'menu_order title', 'order' => 'ASC'));
	$output_type_options = mp_headless_get_output_type_options();
	$author_person_id = (int) get_post_meta($post->ID, 'mp_author_person_id', true);
	$author_person_ids = mp_headless_normalize_selected_person_ids(
		get_post_meta($post->ID, 'mp_author_person_ids', true),
		$author_person_id
	);
	$related_project_ids = get_post_meta($post->ID, 'mp_related_project_ids', true);
	$contributor_person_ids = get_post_meta($post->ID, 'mp_contributor_person_ids', true);
	$contributor_names = get_post_meta($post->ID, 'mp_contributor_names', true);
	$references = get_post_meta($post->ID, 'mp_references', true);
	$current_output_type_slug = mp_headless_get_publication_output_type_slug($post->ID);

	if (! $current_output_type_slug && isset($_GET['mp_publication_mode']) && sanitize_text_field(wp_unslash($_GET['mp_publication_mode'])) === 'podcast') {
		$current_output_type_slug = 'pod-cast';
	}

	if (! $current_output_type_slug) {
			$current_output_type_slug = 'insights';
	}

	$related_project_ids = is_array($related_project_ids) ? $related_project_ids : array();
	$contributor_person_ids = is_array($contributor_person_ids) ? $contributor_person_ids : array();
	$contributor_names = is_array($contributor_names) ? $contributor_names : array();
	$references = is_array($references) ? $references : array();
	?>
	<div style="display:grid; gap:18px;">
	<p><label for="mp_output_type_term"><strong><?php esc_html_e('Output Type', 'medplatform-headless'); ?></strong></label><br />
	<select class="widefat" id="mp_output_type_term" name="mp_output_type_term" data-mp-output-type-select>
		<?php foreach ($output_type_options as $option) : ?>
			<option value="<?php echo esc_attr($option['slug']); ?>" <?php selected($current_output_type_slug, $option['slug']); ?>><?php echo esc_html($option['label']); ?></option>
		<?php endforeach; ?>
	</select>
	<span class="description" style="display:block; margin-top:6px;">
		<?php esc_html_e('Choose the publication type here. Use Pod-Cast for audio releases so the homepage and the public page both use the audio player correctly.', 'medplatform-headless'); ?>
	</span></p>
	<p><label><strong><?php esc_html_e('Main Authors', 'medplatform-headless'); ?></strong></label><br />
	<select class="widefat" name="mp_author_person_ids[]" size="8" multiple style="min-height:220px;">
		<?php foreach ($people as $person) : ?>
			<option value="<?php echo esc_attr($person->ID); ?>" <?php selected(in_array((int) $person->ID, $author_person_ids, true)); ?>><?php echo esc_html($person->post_title); ?></option>
		<?php endforeach; ?>
	</select>
	<span class="description" style="display:block; margin-top:6px;">
		<?php esc_html_e('Selected people are shown as the main authors on the frontend. Hold Command or Ctrl to choose multiple authors.', 'medplatform-headless'); ?>
	</span></p>
	<p><label><strong><?php esc_html_e('Author Role', 'medplatform-headless'); ?></strong></label><br />
	<input type="text" class="widefat" name="mp_author_role" value="<?php echo esc_attr(get_post_meta($post->ID, 'mp_author_role', true)); ?>" /></p>
	<?php
	mp_headless_render_media_field(
		array(
			'label'        => __('Publication Cover Image', 'medplatform-headless'),
			'name'         => 'mp_cover_image',
			'value'        => get_post_meta($post->ID, 'mp_cover_image', true),
			'button_label' => __('Upload cover image', 'medplatform-headless'),
			'library_type' => 'image',
		)
	);
	?>
	<div data-mp-podcast-fields>
		<?php
		mp_headless_render_media_field(
			array(
				'label'        => __('Audio File URL', 'medplatform-headless'),
				'name'         => 'mp_audio_url',
				'value'        => get_post_meta($post->ID, 'mp_audio_url', true),
				'button_label' => __('Upload audio file', 'medplatform-headless'),
				'library_type' => 'audio',
				'description'  => __('Use a WordPress media upload or local public path. MP3 files are limited to 10 MB.', 'medplatform-headless'),
				'max_size_mb'  => 10,
			)
		);
		?>
		<p class="description" style="margin-top:-8px;">
			<?php esc_html_e('This file powers the public audio player on the publication page and the featured podcast player on the homepage.', 'medplatform-headless'); ?>
		</p>
	</div>
	<div data-mp-publication-download-fields>
		<?php
		mp_headless_render_media_field(
				array(
					'label'        => __('Publication File URL', 'medplatform-headless'),
					'name'         => 'mp_download_url',
					'value'        => get_post_meta($post->ID, 'mp_download_url', true),
					'button_label' => __('Upload PDF or document', 'medplatform-headless'),
					'library_type' => 'application/pdf',
					'description'  => __('Leave this empty if the publication should not show a download button on the frontend. Podcast entries do not use this field.', 'medplatform-headless'),
					'max_size_mb'  => 5,
				)
			);
			?>
		<p><label><strong><?php esc_html_e('File Button Label', 'medplatform-headless'); ?></strong></label><br />
		<input type="text" class="widefat" name="mp_download_label" value="<?php echo esc_attr(get_post_meta($post->ID, 'mp_download_label', true)); ?>" /></p>
	</div>
	<p><label><strong><?php esc_html_e('References', 'medplatform-headless'); ?></strong></label><br />
	<textarea class="widefat" rows="5" name="mp_references" placeholder="One reference per line: Reference Name | https://example.com"><?php
		if (is_array($references)) {
			$reference_lines = array_map(
				function($item) {
					return trim(($item['name'] ?? '') . ' | ' . ($item['url'] ?? ''));
				},
				$references
			);
			echo esc_textarea(implode("\n", $reference_lines));
		}
	?></textarea></p>
	<p><strong><?php esc_html_e('Linked People Involved', 'medplatform-headless'); ?></strong></p>
	<div style="max-height: 180px; overflow:auto; border:1px solid #ddd; padding:10px; margin-bottom:14px;">
		<?php foreach ($people as $person) : ?>
			<label style="display:block; margin-bottom:6px;">
				<input type="checkbox" name="mp_contributor_person_ids[]" value="<?php echo esc_attr($person->ID); ?>" <?php checked(in_array((int) $person->ID, array_map('intval', $contributor_person_ids), true)); ?> />
				<?php echo esc_html($person->post_title); ?>
			</label>
		<?php endforeach; ?>
	</div>
	<p class="description" style="margin-top:-8px;">
		<?php esc_html_e('Anyone selected above as a main author is automatically left out of the contributors list on the frontend.', 'medplatform-headless'); ?>
	</p>
	<p><label><strong><?php esc_html_e('Additional People Names', 'medplatform-headless'); ?></strong></label><br />
	<textarea class="widefat" rows="4" name="mp_contributor_names" placeholder="One name per line"><?php echo esc_textarea(implode("\n", $contributor_names)); ?></textarea></p>
	<p><strong><?php esc_html_e('Related Projects', 'medplatform-headless'); ?></strong></p>
	<div style="max-height: 180px; overflow:auto; border:1px solid #ddd; padding:10px;">
		<?php foreach ($projects as $project) : ?>
			<label style="display:block; margin-bottom:6px;">
				<input type="checkbox" name="mp_related_project_ids[]" value="<?php echo esc_attr($project->ID); ?>" <?php checked(in_array((int) $project->ID, array_map('intval', $related_project_ids), true)); ?> />
				<?php echo esc_html($project->post_title); ?>
			</label>
		<?php endforeach; ?>
	</div>
	</div>
	<?php
}

function mp_headless_render_person_meta_box($post) {
	wp_nonce_field('mp_headless_save_meta', 'mp_headless_meta_nonce');
	?>
	<p><label><strong><?php esc_html_e('Role', 'medplatform-headless'); ?></strong></label><br />
	<input type="text" class="widefat" name="mp_role" value="<?php echo esc_attr(get_post_meta($post->ID, 'mp_role', true)); ?>" /></p>
	<p><label><strong><?php esc_html_e('Email', 'medplatform-headless'); ?></strong></label><br />
	<input type="email" class="widefat" name="mp_email" value="<?php echo esc_attr(get_post_meta($post->ID, 'mp_email', true)); ?>" /></p>
	<p><label><strong><?php esc_html_e('LinkedIn URL', 'medplatform-headless'); ?></strong></label><br />
	<input type="url" class="widefat" name="mp_linkedin_url" value="<?php echo esc_attr(get_post_meta($post->ID, 'mp_linkedin_url', true)); ?>" /></p>
	<p><label><strong><?php esc_html_e('Personal Website URL', 'medplatform-headless'); ?></strong></label><br />
	<input type="url" class="widefat" name="mp_website_url" value="<?php echo esc_attr(get_post_meta($post->ID, 'mp_website_url', true)); ?>" /></p>
	<?php
	mp_headless_render_media_field(
		array(
			'label'        => __('Photo URL', 'medplatform-headless'),
			'name'         => 'mp_photo',
			'value'        => get_post_meta($post->ID, 'mp_photo', true),
			'button_label' => __('Upload photo', 'medplatform-headless'),
			'library_type' => 'image',
		)
	);
	?>
	<p><label><strong><?php esc_html_e('Team Page Bio', 'medplatform-headless'); ?></strong></label><br />
	<textarea class="widefat" rows="4" name="mp_short_bio" placeholder="<?php esc_attr_e('Short text used on the About team grid.', 'medplatform-headless'); ?>"><?php echo esc_textarea(get_post_meta($post->ID, 'mp_short_bio', true)); ?></textarea></p>
	<p><label><strong><?php esc_html_e('Profile Page Bio', 'medplatform-headless'); ?></strong></label><br />
	<textarea class="widefat" rows="9" name="mp_profile_bio" placeholder="<?php esc_attr_e('Longer profile text used on the personal profile page. Separate paragraphs with a blank line.', 'medplatform-headless'); ?>"><?php echo esc_textarea(get_post_meta($post->ID, 'mp_profile_bio', true)); ?></textarea></p>
	<p>
		<label>
			<input type="checkbox" name="mp_show_on_team_page" value="1" <?php checked((bool) get_post_meta($post->ID, 'mp_show_on_team_page', true)); ?> />
			<?php esc_html_e('Include on the About team page', 'medplatform-headless'); ?>
		</label>
	</p>
	<p style="margin-top:8px;"><?php esc_html_e('Use Team Page Bio for the shorter About-page text, Profile Page Bio for the longer personal page text, and page order for the team-page ordering.', 'medplatform-headless'); ?></p>
	<?php
}

function mp_headless_render_project_meta_box($post) {
	wp_nonce_field('mp_headless_save_meta', 'mp_headless_meta_nonce');
	$people                    = get_posts(array('post_type' => 'mp_person', 'posts_per_page' => -1, 'orderby' => 'menu_order title', 'order' => 'ASC'));
	$project_card_icon_options = mp_headless_project_card_icon_options();
	$team_members              = get_post_meta($post->ID, 'mp_team_members', true);
	$team_member_ids           = get_post_meta($post->ID, 'mp_team_member_ids', true);
	$updates                   = get_post_meta($post->ID, 'mp_updates', true);
	$donors                    = get_post_meta($post->ID, 'mp_donors', true);
	$focus_areas               = get_post_meta($post->ID, 'mp_focus_areas', true);
	$stage_points              = get_post_meta($post->ID, 'mp_stage_points', true);
	$current_stage             = get_post_meta($post->ID, 'mp_current_stage', true);
	$parent_project_id         = (int) get_post_meta($post->ID, 'mp_parent_project_id', true);
	$lead_person_id            = (int) get_post_meta($post->ID, 'mp_lead_person_id', true);
	$lead_person_ids           = mp_headless_normalize_selected_person_ids(
		get_post_meta($post->ID, 'mp_lead_person_ids', true),
		$lead_person_id
	);
	$lead_role                 = (string) get_post_meta($post->ID, 'mp_lead_role', true);
	$project_card_icon         = (string) get_post_meta($post->ID, 'mp_card_icon', true);
	$hide_project_bar          = (bool) get_post_meta($post->ID, 'mp_hide_project_bar', true);
	$available_parent_projects = get_posts(
		array(
			'post_type'      => 'mp_project',
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'post__not_in'   => array($post->ID),
		)
	);
	$stage_points              = is_array($stage_points) && ! empty($stage_points) ? $stage_points : mp_headless_project_stages();
	$team_member_ids           = is_array($team_member_ids) ? array_values(array_filter(array_map('intval', $team_member_ids))) : array();
	$team_members              = is_array($team_members) ? $team_members : array();

	if (empty($team_member_ids) && ! empty($team_members)) {
		foreach ($team_members as $member_name) {
			$member_id = mp_headless_find_person_id_by_name((string) $member_name);
			if ($member_id > 0) {
				$team_member_ids[] = $member_id;
			}
		}
		$team_member_ids = array_values(array_unique(array_map('intval', $team_member_ids)));
	}

	foreach ($lead_person_ids as $selected_lead_person_id) {
		if (! in_array($selected_lead_person_id, $team_member_ids, true)) {
			$team_member_ids[] = $selected_lead_person_id;
		}
	}

	$team_member_ids = array_values(array_unique(array_map('intval', $team_member_ids)));
	$accent_color = (string) get_post_meta($post->ID, 'mp_color', true);
	$progress_color = (string) get_post_meta($post->ID, 'mp_progress_color', true);
	$resolved_progress_color = $progress_color !== '' ? $progress_color : ($accent_color !== '' ? $accent_color : '#15243a');
	?>
	<p><label><strong><?php esc_html_e('Accent Color', 'medplatform-headless'); ?></strong></label><br />
	<span data-mp-color-field style="display:flex; gap:12px; align-items:center; flex-wrap:wrap; margin-top:8px;">
		<input type="color" value="<?php echo esc_attr($accent_color !== '' ? $accent_color : '#15243a'); ?>" data-mp-color-picker style="inline-size:56px; block-size:40px; padding:0; border:0; background:none;" />
		<input type="text" class="regular-text code" name="mp_color" value="<?php echo esc_attr($accent_color); ?>" data-mp-color-value pattern="^#([A-Fa-f0-9]{6})$" placeholder="#15243a" />
	</span></p>
	<p><label><strong><?php esc_html_e('Progress Bar Color', 'medplatform-headless'); ?></strong></label><br />
	<span data-mp-color-field style="display:flex; gap:12px; align-items:center; flex-wrap:wrap; margin-top:8px;">
		<input type="color" value="<?php echo esc_attr($resolved_progress_color); ?>" data-mp-color-picker style="inline-size:56px; block-size:40px; padding:0; border:0; background:none;" />
		<input type="text" class="regular-text code" name="mp_progress_color" value="<?php echo esc_attr($progress_color); ?>" data-mp-color-value pattern="^#([A-Fa-f0-9]{6})$" placeholder="#15243a" />
	</span></p>
	<p><label><strong><?php esc_html_e('Project Card Icon', 'medplatform-headless'); ?></strong></label><br />
	<select class="widefat" name="mp_card_icon">
		<option value=""><?php esc_html_e('Use the default icon', 'medplatform-headless'); ?></option>
		<?php foreach ($project_card_icon_options as $icon_value => $icon_label) : ?>
			<option value="<?php echo esc_attr($icon_value); ?>" <?php selected($project_card_icon, $icon_value); ?>><?php echo esc_html($icon_label); ?></option>
		<?php endforeach; ?>
	</select></p>
	<p class="description" style="margin-top:-8px;">
		<?php esc_html_e('Used on the /services project cards for newer projects. Libya Platform, Africa Nexus, and Gulf Platform keep their custom artwork.', 'medplatform-headless'); ?>
	</p>
	<p>
		<label>
			<input type="checkbox" name="mp_hide_project_bar" value="1" <?php checked($hide_project_bar); ?> />
			<?php esc_html_e('Hide the project progress bar on the public project page', 'medplatform-headless'); ?>
		</label>
	</p>
	<p><label><strong><?php esc_html_e('Progress Points', 'medplatform-headless'); ?></strong></label><br />
	<textarea class="widefat" rows="5" name="mp_stage_points" placeholder="One progress point per line"><?php echo esc_textarea(implode("\n", $stage_points)); ?></textarea></p>
	<p><label><strong><?php esc_html_e('Current Progress Point', 'medplatform-headless'); ?></strong></label><br />
	<input type="text" class="widefat" name="mp_current_stage" value="<?php echo esc_attr((string) $current_stage); ?>" placeholder="<?php echo esc_attr($stage_points[0] ?? 'Conception'); ?>" /></p>
	<p><label><strong><?php esc_html_e('Team Members', 'medplatform-headless'); ?></strong></label><br />
	<select class="widefat" name="mp_team_member_ids[]" size="8" multiple data-mp-team-select style="min-height:220px;">
		<?php foreach ($people as $person) : ?>
			<option value="<?php echo esc_attr($person->ID); ?>" <?php selected(in_array((int) $person->ID, $team_member_ids, true)); ?>><?php echo esc_html($person->post_title); ?></option>
		<?php endforeach; ?>
	</select></p>
	<p class="description" style="margin-top:-8px;">
		<?php esc_html_e('Hold Command or Ctrl to choose multiple team members.', 'medplatform-headless'); ?>
	</p>
	<p><label><strong><?php esc_html_e('Lead Team Members', 'medplatform-headless'); ?></strong></label><br />
	<select class="widefat" name="mp_lead_person_ids[]" size="8" multiple data-mp-lead-select style="min-height:220px;">
		<?php foreach ($people as $person) : ?>
			<?php $is_in_team = empty($team_member_ids) || in_array((int) $person->ID, $team_member_ids, true); ?>
			<option
				value="<?php echo esc_attr($person->ID); ?>"
				<?php selected(in_array((int) $person->ID, $lead_person_ids, true)); ?>
				<?php disabled(! $is_in_team && ! in_array((int) $person->ID, $lead_person_ids, true)); ?>
			>
				<?php echo esc_html($person->post_title); ?>
			</option>
		<?php endforeach; ?>
	</select></p>
	<p class="description" style="margin-top:-8px;">
		<?php esc_html_e('Choose one or more program leads. Selected leads are kept inside the team list automatically.', 'medplatform-headless'); ?>
	</p>
	<p><label><strong><?php esc_html_e('Lead Role', 'medplatform-headless'); ?></strong></label><br />
	<input type="text" class="widefat" name="mp_lead_role" value="<?php echo esc_attr($lead_role); ?>" /></p>
	<p class="description" style="margin-top:-8px;">
		<?php esc_html_e('This label is reused for each selected program lead on the public project page. Use Progress Points to define the labels shown on the public project page. The Current Progress Point should match one of those labels.', 'medplatform-headless'); ?>
	</p>
	<p>
		<label>
			<input type="checkbox" name="mp_has_parent_project" value="1" <?php checked($parent_project_id > 0); ?> />
			<?php esc_html_e('This project sits under another project', 'medplatform-headless'); ?>
		</label>
	</p>
	<p><label><strong><?php esc_html_e('Parent Project', 'medplatform-headless'); ?></strong></label><br />
	<select class="widefat" name="mp_parent_project_id">
		<option value="0"><?php esc_html_e('Select a parent project', 'medplatform-headless'); ?></option>
		<?php foreach ($available_parent_projects as $parent_project) : ?>
			<option value="<?php echo esc_attr($parent_project->ID); ?>" <?php selected($parent_project_id, (int) $parent_project->ID); ?>><?php echo esc_html($parent_project->post_title); ?></option>
		<?php endforeach; ?>
	</select></p>
	<p><label><strong><?php esc_html_e('Current Updates', 'medplatform-headless'); ?></strong></label><br />
	<textarea class="widefat" rows="5" name="mp_updates" placeholder="One update per line"><?php echo esc_textarea(is_array($updates) ? implode("\n", $updates) : ''); ?></textarea></p>
	<p><label><strong><?php esc_html_e('Donors', 'medplatform-headless'); ?></strong></label><br />
	<textarea class="widefat" rows="5" name="mp_donors" placeholder="One donor per line: Name | https://example.com/logo.png"><?php
		if (is_array($donors)) {
			$donor_lines = array_map(
				function($item) {
					return trim(($item['name'] ?? '') . ' | ' . ($item['logo'] ?? ''));
				},
				$donors
			);
			echo esc_textarea(implode("\n", $donor_lines));
		}
	?></textarea></p>
	<p><label><strong><?php esc_html_e('Focus Areas', 'medplatform-headless'); ?></strong></label><br />
	<textarea class="widefat" rows="5" name="mp_focus_areas" placeholder="One per line: Title | Description"><?php
		if (is_array($focus_areas)) {
			$focus_lines = array_map(
				function($item) {
					return trim(($item['title'] ?? '') . ' | ' . ($item['description'] ?? ''));
				},
				$focus_areas
			);
			echo esc_textarea(implode("\n", $focus_lines));
		}
	?></textarea></p>
	<p style="margin-top:8px;"><?php esc_html_e('Use the main editor for the right-column project body text and the excerpt for the short project description. Project short descriptions are capped at 25 words.', 'medplatform-headless'); ?></p>
	<?php
}

function mp_headless_render_homepage_meta_box($post) {
	wp_nonce_field('mp_headless_save_meta', 'mp_headless_meta_nonce');
	$publications           = get_posts(array('post_type' => 'mp_publication', 'posts_per_page' => -1, 'orderby' => 'date', 'order' => 'DESC'));
	$projects               = get_posts(array('post_type' => 'mp_project', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC'));
	$podcast_publications   = mp_headless_get_podcast_publications();
	$featured_podcast_id    = (int) get_post_meta($post->ID, 'mp_featured_podcast_id', true);
	$featured_article_id    = (int) get_post_meta($post->ID, 'mp_featured_article_id', true);
	$homepage_project_count = max(0, (int) get_post_meta($post->ID, 'mp_homepage_project_count', true));
	$homepage_project_ids   = get_post_meta($post->ID, 'mp_homepage_project_ids', true);
	$slider_publication_ids = get_post_meta($post->ID, 'mp_slider_publication_ids', true);
	$latest_publication_ids = get_post_meta($post->ID, 'mp_latest_publication_ids', true);
	$homepage_project_ids   = is_array($homepage_project_ids) ? array_map('intval', $homepage_project_ids) : array();
	$slider_publication_ids = is_array($slider_publication_ids) ? $slider_publication_ids : array();
	$latest_publication_ids = is_array($latest_publication_ids) ? $latest_publication_ids : array();
	?>
	<p><label><strong><?php esc_html_e('Featured Podcast', 'medplatform-headless'); ?></strong></label><br />
	<select class="widefat" name="mp_featured_podcast_id">
		<option value="0"><?php esc_html_e('Select a podcast', 'medplatform-headless'); ?></option>
		<?php foreach ($podcast_publications as $publication) : ?>
			<option value="<?php echo esc_attr($publication->ID); ?>" <?php selected($featured_podcast_id, (int) $publication->ID); ?>><?php echo esc_html($publication->post_title); ?></option>
		<?php endforeach; ?>
	</select></p>
	<p><label><strong><?php esc_html_e('Featured Article', 'medplatform-headless'); ?></strong></label><br />
	<select class="widefat" name="mp_featured_article_id">
		<option value="0"><?php esc_html_e('Select a publication', 'medplatform-headless'); ?></option>
		<?php foreach ($publications as $publication) : ?>
			<option value="<?php echo esc_attr($publication->ID); ?>" <?php selected($featured_article_id, (int) $publication->ID); ?>><?php echo esc_html($publication->post_title); ?></option>
		<?php endforeach; ?>
	</select></p>
	<p><label><strong><?php esc_html_e('Homepage Project Count', 'medplatform-headless'); ?></strong></label><br />
	<input type="number" class="small-text" min="0" max="24" step="1" name="mp_homepage_project_count" value="<?php echo esc_attr((string) $homepage_project_count); ?>" /></p>
	<p class="description" style="margin-top:-8px;">
		<?php esc_html_e('Sets the maximum number of homepage projects shown from the selected project list below. Use 0 to show all selected projects, or all visible projects if no specific projects are selected.', 'medplatform-headless'); ?>
	</p>
	<p><strong><?php esc_html_e('Homepage Featured Projects', 'medplatform-headless'); ?></strong></p>
	<div style="max-height: 180px; overflow:auto; border:1px solid #ddd; padding:10px; margin-bottom:14px;">
		<?php if (empty($projects)) : ?>
			<p style="margin:0; color:#50575e;"><?php esc_html_e('No projects available yet.', 'medplatform-headless'); ?></p>
		<?php else : ?>
			<?php foreach ($projects as $project) : ?>
				<label style="display:block; margin-bottom:6px;">
					<input type="checkbox" name="mp_homepage_project_ids[]" value="<?php echo esc_attr($project->ID); ?>" <?php checked(in_array((int) $project->ID, $homepage_project_ids, true)); ?> />
					<?php echo esc_html($project->post_title); ?>
				</label>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>
	<p><strong><?php esc_html_e('Slider Publications', 'medplatform-headless'); ?></strong></p>
	<div style="max-height: 180px; overflow:auto; border:1px solid #ddd; padding:10px; margin-bottom:14px;">
		<?php foreach ($publications as $publication) : ?>
			<label style="display:block; margin-bottom:6px;">
				<input type="checkbox" name="mp_slider_publication_ids[]" value="<?php echo esc_attr($publication->ID); ?>" <?php checked(in_array((int) $publication->ID, array_map('intval', $slider_publication_ids), true)); ?> />
				<?php echo esc_html($publication->post_title); ?>
			</label>
		<?php endforeach; ?>
	</div>
	<p><strong><?php esc_html_e('Latest Publications Section', 'medplatform-headless'); ?></strong></p>
	<div style="max-height: 180px; overflow:auto; border:1px solid #ddd; padding:10px;">
		<?php foreach ($publications as $publication) : ?>
			<label style="display:block; margin-bottom:6px;">
				<input type="checkbox" name="mp_latest_publication_ids[]" value="<?php echo esc_attr($publication->ID); ?>" <?php checked(in_array((int) $publication->ID, array_map('intval', $latest_publication_ids), true)); ?> />
				<?php echo esc_html($publication->post_title); ?>
			</label>
		<?php endforeach; ?>
	</div>
	<?php
}

function mp_headless_add_meta_boxes() {
	add_meta_box('mp-headless-publication-meta', __('Publication Details', 'medplatform-headless'), 'mp_headless_render_publication_meta_box', 'mp_publication', 'normal', 'default');
	add_meta_box('mp-headless-person-meta', __('Person Details', 'medplatform-headless'), 'mp_headless_render_person_meta_box', 'mp_person', 'normal', 'default');
	add_meta_box('mp-headless-project-meta', __('Project Details', 'medplatform-headless'), 'mp_headless_render_project_meta_box', 'mp_project', 'normal', 'default');
	add_meta_box('mp-headless-homepage-meta', __('Homepage Controls', 'medplatform-headless'), 'mp_headless_render_homepage_meta_box', 'mp_homepage', 'normal', 'default');
	remove_meta_box('tagsdiv-mp_output_type', 'mp_publication', 'side');
	remove_meta_box('mp_output_typediv', 'mp_publication', 'side');
}
add_action('add_meta_boxes', 'mp_headless_add_meta_boxes');

function mp_headless_enqueue_admin_assets($hook_suffix) {
	$is_post_editor = in_array($hook_suffix, array('post.php', 'post-new.php'), true);
	$is_homepage_settings = $hook_suffix === 'toplevel_page_mp-homepage-settings';
	$is_person_settings = isset($_GET['page']) && sanitize_key(wp_unslash($_GET['page'])) === 'mp-person-profile';
	$is_project_settings = isset($_GET['page']) && sanitize_key(wp_unslash($_GET['page'])) === 'mp-project-profile';
	$screen = null;

	if (! $is_post_editor && ! $is_homepage_settings && ! $is_person_settings && ! $is_project_settings) {
		return;
	}

	if ($is_post_editor) {
		$screen = get_current_screen();
		if (! $screen || ! in_array($screen->post_type, array('mp_publication', 'mp_project', 'mp_person'), true)) {
			return;
		}
	}

	if ($is_post_editor || $is_person_settings || $is_project_settings) {
		wp_enqueue_media();
	}
	wp_enqueue_script(
		'mp-headless-admin-media',
		plugins_url('assets/admin-media.js', __FILE__),
		array('jquery'),
		(string) filemtime(plugin_dir_path(__FILE__) . 'assets/admin-media.js'),
		true
	);

	if ($is_post_editor && $screen && $screen->post_type === 'mp_publication') {
		wp_add_inline_style(
			'wp-edit-blocks',
			'body.post-type-mp_publication [data-panel="taxonomy-panel-mp_output_type"], body.post-type-mp_publication .components-panel__body[data-panel="taxonomy-panel-mp_output_type"], body.post-type-mp_publication button[aria-controls="taxonomy-panel-mp_output_type"] { display: none !important; }'
		);
	}
}
add_action('admin_enqueue_scripts', 'mp_headless_enqueue_admin_assets');

function mp_headless_save_meta_boxes($post_id) {
	if (! isset($_POST['mp_headless_meta_nonce']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['mp_headless_meta_nonce'])), 'mp_headless_save_meta')) {
		return;
	}

	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return;
	}

	if (! current_user_can('edit_post', $post_id)) {
		return;
	}

	$post_type = get_post_type($post_id);

	if ($post_type === 'mp_publication') {
		$selected_output_type = sanitize_title(wp_unslash($_POST['mp_output_type_term'] ?? 'insights'));
		$author_person_ids = mp_headless_normalize_selected_person_ids(
			wp_unslash($_POST['mp_author_person_ids'] ?? array()),
			intval($_POST['mp_author_person_id'] ?? 0)
		);
		$author_people = array();

		foreach ($author_person_ids as $person_id) {
			$author_post = get_post($person_id);
			if ($author_post instanceof WP_Post && $author_post->post_type === 'mp_person') {
				$author_people[] = $author_post;
			}
		}

		$primary_author = ! empty($author_people) ? $author_people[0] : null;
		$author_person_id = $primary_author instanceof WP_Post ? (int) $primary_author->ID : 0;
		$author_name = ! empty($author_people)
			? implode(', ', array_map(
				function($author_post) {
					return $author_post->post_title;
				},
				$author_people
			))
			: '';
		$author_image = $author_person_id > 0 ? (string) get_post_meta($author_person_id, 'mp_photo', true) : '';
		$contributor_person_ids = mp_headless_sanitize_int_array(wp_unslash($_POST['mp_contributor_person_ids'] ?? array()));
		$contributor_person_ids = array_values(
			array_filter(
				$contributor_person_ids,
				function($person_id) use ($author_person_ids) {
					return ! in_array((int) $person_id, $author_person_ids, true);
				}
			)
		);

		if ($selected_output_type === '') {
			$selected_output_type = 'insights';
		}

		$download_url = $selected_output_type === 'pod-cast'
			? ''
			: esc_url_raw(wp_unslash($_POST['mp_download_url'] ?? ''));
		$download_label = $selected_output_type === 'pod-cast'
			? ''
			: sanitize_text_field(wp_unslash($_POST['mp_download_label'] ?? ''));

		wp_set_object_terms($post_id, array($selected_output_type), 'mp_output_type', false);
		update_post_meta($post_id, 'mp_author_name', sanitize_text_field($author_name));
		update_post_meta($post_id, 'mp_author_person_id', $author_person_id);
		update_post_meta($post_id, 'mp_author_person_ids', $author_person_ids);
		update_post_meta($post_id, 'mp_author_role', sanitize_text_field(wp_unslash($_POST['mp_author_role'] ?? '')));
		update_post_meta($post_id, 'mp_author_image', esc_url_raw($author_image));
		update_post_meta($post_id, 'mp_cover_image', esc_url_raw(wp_unslash($_POST['mp_cover_image'] ?? '')));
		update_post_meta($post_id, 'mp_audio_url', esc_url_raw(wp_unslash($_POST['mp_audio_url'] ?? '')));
		update_post_meta($post_id, 'mp_download_url', $download_url);
		update_post_meta($post_id, 'mp_download_label', $download_label);
		update_post_meta($post_id, 'mp_contributor_person_ids', $contributor_person_ids);
		update_post_meta($post_id, 'mp_contributor_names', mp_headless_parse_lines($_POST['mp_contributor_names'] ?? ''));
		update_post_meta($post_id, 'mp_related_project_ids', mp_headless_sanitize_int_array(wp_unslash($_POST['mp_related_project_ids'] ?? array())));
		update_post_meta($post_id, 'mp_references', mp_headless_parse_named_links($_POST['mp_references'] ?? ''));
	}

	if ($post_type === 'mp_person') {
		update_post_meta($post_id, 'mp_role', sanitize_text_field(wp_unslash($_POST['mp_role'] ?? '')));
		update_post_meta($post_id, 'mp_email', sanitize_email(wp_unslash($_POST['mp_email'] ?? '')));
		update_post_meta($post_id, 'mp_linkedin_url', esc_url_raw(wp_unslash($_POST['mp_linkedin_url'] ?? '')));
		update_post_meta($post_id, 'mp_website_url', esc_url_raw(wp_unslash($_POST['mp_website_url'] ?? '')));
		update_post_meta($post_id, 'mp_photo', esc_url_raw(wp_unslash($_POST['mp_photo'] ?? '')));
		update_post_meta($post_id, 'mp_short_bio', sanitize_textarea_field(wp_unslash($_POST['mp_short_bio'] ?? '')));
		update_post_meta($post_id, 'mp_profile_bio', sanitize_textarea_field(wp_unslash($_POST['mp_profile_bio'] ?? '')));
		update_post_meta($post_id, 'mp_show_on_team_page', ! empty($_POST['mp_show_on_team_page']));
	}

	if ($post_type === 'mp_project') {
		if (array_key_exists('mp_parent_project_id', $_POST) || array_key_exists('mp_has_parent_project', $_POST)) {
			$parent_project_id = ! empty($_POST['mp_has_parent_project'])
				? mp_headless_normalize_parent_project_id($post_id, intval($_POST['mp_parent_project_id'] ?? 0))
				: 0;

			update_post_meta($post_id, 'mp_parent_project_id', $parent_project_id);
		}

		$team_member_ids = mp_headless_sanitize_int_array(wp_unslash($_POST['mp_team_member_ids'] ?? array()));
		$lead_person_ids = mp_headless_normalize_selected_person_ids(
			wp_unslash($_POST['mp_lead_person_ids'] ?? array()),
			intval($_POST['mp_lead_person_id'] ?? 0)
		);

		foreach ($lead_person_ids as $selected_lead_person_id) {
			if ($selected_lead_person_id > 0 && ! in_array($selected_lead_person_id, $team_member_ids, true)) {
				$team_member_ids[] = $selected_lead_person_id;
			}
		}

		$team_member_ids = array_values(array_unique(array_map('intval', $team_member_ids)));
		$team_member_names = array();
		foreach ($team_member_ids as $person_id) {
			$person = get_post($person_id);
			if ($person instanceof WP_Post && $person->post_type === 'mp_person') {
				$team_member_names[] = $person->post_title;
			}
		}

		$primary_lead_post = ! empty($lead_person_ids) ? get_post($lead_person_ids[0]) : null;
		$lead_person_id = $primary_lead_post instanceof WP_Post ? (int) $primary_lead_post->ID : 0;
		$lead_name = $primary_lead_post instanceof WP_Post ? $primary_lead_post->post_title : sanitize_text_field(wp_unslash($_POST['mp_lead_name'] ?? ''));
		$lead_image = $lead_person_id > 0
			? (string) get_post_meta($lead_person_id, 'mp_photo', true)
			: esc_url_raw(wp_unslash($_POST['mp_lead_image'] ?? ''));
		$accent_color = mp_headless_sanitize_hex_color_value($_POST['mp_color'] ?? '');
		$progress_color = mp_headless_sanitize_hex_color_value($_POST['mp_progress_color'] ?? '');

		if ($accent_color === '') {
			$accent_color = '#15243a';
		}

		if ($progress_color === '') {
			$progress_color = $accent_color;
		}

		update_post_meta($post_id, 'mp_color', $accent_color);
		update_post_meta($post_id, 'mp_progress_color', $progress_color);
		update_post_meta($post_id, 'mp_card_icon', mp_headless_sanitize_project_card_icon(wp_unslash($_POST['mp_card_icon'] ?? '')));
		update_post_meta($post_id, 'mp_hide_project_bar', ! empty($_POST['mp_hide_project_bar']));
		update_post_meta($post_id, 'mp_stage_points', mp_headless_parse_lines($_POST['mp_stage_points'] ?? ''));
		update_post_meta($post_id, 'mp_current_stage', sanitize_text_field(wp_unslash($_POST['mp_current_stage'] ?? '')));
		update_post_meta($post_id, 'mp_lead_person_id', $lead_person_id);
		update_post_meta($post_id, 'mp_lead_person_ids', $lead_person_ids);
		update_post_meta($post_id, 'mp_lead_name', $lead_name);
		update_post_meta($post_id, 'mp_lead_role', sanitize_text_field(wp_unslash($_POST['mp_lead_role'] ?? '')));
		update_post_meta($post_id, 'mp_lead_image', esc_url_raw($lead_image));
		update_post_meta($post_id, 'mp_team_member_ids', $team_member_ids);
		update_post_meta($post_id, 'mp_team_members', $team_member_names);
		update_post_meta($post_id, 'mp_updates', mp_headless_parse_lines($_POST['mp_updates'] ?? ''));
		update_post_meta($post_id, 'mp_donors', mp_headless_parse_named_assets($_POST['mp_donors'] ?? ''));
		update_post_meta($post_id, 'mp_focus_areas', mp_headless_parse_focus_areas($_POST['mp_focus_areas'] ?? ''));
	}

	if ($post_type === 'mp_homepage') {
		$featured_podcast_id = intval($_POST['mp_featured_podcast_id'] ?? 0);
		update_post_meta($post_id, 'mp_featured_podcast_id', mp_headless_publication_is_podcast($featured_podcast_id) ? $featured_podcast_id : 0);
		update_post_meta($post_id, 'mp_featured_article_id', intval($_POST['mp_featured_article_id'] ?? 0));
		update_post_meta($post_id, 'mp_homepage_project_count', mp_headless_sanitize_nonnegative_int_value($_POST['mp_homepage_project_count'] ?? 0));
		update_post_meta($post_id, 'mp_homepage_project_ids', mp_headless_sanitize_int_array(wp_unslash($_POST['mp_homepage_project_ids'] ?? array())));
		update_post_meta($post_id, 'mp_slider_publication_ids', mp_headless_sanitize_int_array(wp_unslash($_POST['mp_slider_publication_ids'] ?? array())));
		update_post_meta($post_id, 'mp_latest_publication_ids', array_slice(mp_headless_sanitize_int_array(wp_unslash($_POST['mp_latest_publication_ids'] ?? array())), 0, 5));
	}
}
add_action('save_post', 'mp_headless_save_meta_boxes');

function mp_headless_limit_project_excerpt_length($data, $postarr) {
	if (($data['post_type'] ?? '') !== 'mp_project') {
		return $data;
	}

	$data['post_excerpt'] = mp_headless_trim_words($data['post_excerpt'] ?? '', 25);

	return $data;
}
add_filter('wp_insert_post_data', 'mp_headless_limit_project_excerpt_length', 10, 2);

function mp_headless_get_selection_count_text($count, $empty_label, $singular_label, $plural_label) {
	$count = (int) $count;

	if ($count < 1) {
		return (string) $empty_label;
	}

	return number_format_i18n($count) . ' ' . ($count === 1 ? (string) $singular_label : (string) $plural_label);
}

function mp_headless_get_selection_count_label($count) {
	return mp_headless_get_selection_count_text(
		$count,
		__('No publications selected', 'medplatform-headless'),
		__('publication selected', 'medplatform-headless'),
		__('publications selected', 'medplatform-headless')
	);
}

function mp_headless_get_project_selection_count_label($count) {
	return mp_headless_get_selection_count_text(
		$count,
		__('No projects selected', 'medplatform-headless'),
		__('project selected', 'medplatform-headless'),
		__('projects selected', 'medplatform-headless')
	);
}

function mp_headless_render_post_selection_list($args) {
	$section_id          = isset($args['section_id']) ? sanitize_html_class((string) $args['section_id']) : wp_unique_id('mp-selection-list-');
	$field_name          = isset($args['field_name']) ? (string) $args['field_name'] : '';
	$title               = isset($args['title']) ? (string) $args['title'] : '';
	$description         = isset($args['description']) ? (string) $args['description'] : '';
	$search_placeholder  = isset($args['search_placeholder']) ? (string) $args['search_placeholder'] : __('Search items...', 'medplatform-headless');
	$empty_search_label  = isset($args['empty_search_label']) ? (string) $args['empty_search_label'] : __('No items match this search.', 'medplatform-headless');
	$empty_list_label    = isset($args['empty_list_label']) ? (string) $args['empty_list_label'] : __('No items available yet.', 'medplatform-headless');
	$posts               = isset($args['posts']) && is_array($args['posts']) ? $args['posts'] : array();
	$selected_ids        = isset($args['selected_ids']) && is_array($args['selected_ids']) ? array_map('intval', $args['selected_ids']) : array();
	$selected_count_text = isset($args['selected_count_text']) ? (string) $args['selected_count_text'] : '';
	$empty_label         = isset($args['empty_selection_label']) ? (string) $args['empty_selection_label'] : __('No items selected', 'medplatform-headless');
	$singular_label      = isset($args['singular_selection_label']) ? (string) $args['singular_selection_label'] : __('item selected', 'medplatform-headless');
	$plural_label        = isset($args['plural_selection_label']) ? (string) $args['plural_selection_label'] : __('items selected', 'medplatform-headless');
	$item_meta_callback  = isset($args['item_meta_callback']) && is_callable($args['item_meta_callback']) ? $args['item_meta_callback'] : null;

	if ($selected_count_text === '') {
		$selected_count_text = mp_headless_get_selection_count_text(count($selected_ids), $empty_label, $singular_label, $plural_label);
	}
	?>
	<div class="mp-selection-card" style="background:#fff; border:1px solid #dcdcde; padding:20px;">
		<div style="display:flex; gap:12px; align-items:flex-start; justify-content:space-between; flex-wrap:wrap;">
			<div>
				<h2 style="margin:0;"><?php echo esc_html($title); ?></h2>
				<p style="margin:8px 0 0; color:#50575e; max-width:72ch;"><?php echo esc_html($description); ?></p>
			</div>
			<span
				class="mp-selection-count"
				data-empty-label="<?php echo esc_attr($empty_label); ?>"
				data-singular-label="<?php echo esc_attr($singular_label); ?>"
				data-plural-label="<?php echo esc_attr($plural_label); ?>"
				style="display:inline-flex; align-items:center; min-height:32px; padding:0 12px; border:1px solid #dcdcde; background:#f6f7f7; color:#1d2327; font-weight:600;"
			><?php echo esc_html($selected_count_text); ?></span>
		</div>
		<div class="mp-selection-panel" style="margin-top:16px;">
			<input
				type="search"
				class="widefat mp-selection-search"
				data-target="#<?php echo esc_attr($section_id); ?>"
				placeholder="<?php echo esc_attr($search_placeholder); ?>"
				aria-label="<?php echo esc_attr($search_placeholder); ?>"
			/>
			<div
				id="<?php echo esc_attr($section_id); ?>"
				class="mp-selection-list"
				style="margin-top:12px; max-height:320px; overflow:auto; border:1px solid #dcdcde; background:#fff;"
			>
				<?php if (empty($posts)) : ?>
					<p style="margin:0; padding:14px 16px; color:#50575e;"><?php echo esc_html($empty_list_label); ?></p>
				<?php else : ?>
					<?php foreach ($posts as $post_item) : ?>
						<?php
						$item_meta_text = $item_meta_callback ? call_user_func($item_meta_callback, $post_item) : '';
						?>
						<label
							class="mp-selection-item"
							data-selection-label="<?php echo esc_attr(function_exists('mb_strtolower') ? mb_strtolower($post_item->post_title) : strtolower($post_item->post_title)); ?>"
							style="display:flex; gap:12px; align-items:flex-start; padding:12px 14px; border-top:1px solid #f0f0f1;"
						>
							<input
								class="mp-selection-checkbox"
								type="checkbox"
								name="<?php echo esc_attr($field_name); ?>[]"
								value="<?php echo esc_attr($post_item->ID); ?>"
								<?php checked(in_array((int) $post_item->ID, $selected_ids, true)); ?>
							/>
							<span style="display:block; line-height:1.35;">
								<strong style="display:block; font-weight:600;"><?php echo esc_html($post_item->post_title); ?></strong>
								<?php if ($item_meta_text !== '') : ?>
									<span style="display:block; margin-top:2px; color:#646970; font-size:12px;"><?php echo esc_html($item_meta_text); ?></span>
								<?php endif; ?>
							</span>
						</label>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
			<p class="mp-selection-empty" hidden style="margin:10px 0 0; color:#646970;"><?php echo esc_html($empty_search_label); ?></p>
		</div>
	</div>
	<?php
}

function mp_headless_render_publication_selection_list($args) {
	$args['posts']                     = isset($args['publications']) && is_array($args['publications']) ? $args['publications'] : array();
	$args['search_placeholder']        = isset($args['search_placeholder']) ? (string) $args['search_placeholder'] : __('Search publications...', 'medplatform-headless');
	$args['empty_search_label']        = isset($args['empty_search_label']) ? (string) $args['empty_search_label'] : __('No publications match this search.', 'medplatform-headless');
	$args['empty_list_label']          = isset($args['empty_list_label']) ? (string) $args['empty_list_label'] : __('No publications available yet.', 'medplatform-headless');
	$args['empty_selection_label']     = isset($args['empty_selection_label']) ? (string) $args['empty_selection_label'] : __('No publications selected', 'medplatform-headless');
	$args['singular_selection_label']  = isset($args['singular_selection_label']) ? (string) $args['singular_selection_label'] : __('publication selected', 'medplatform-headless');
	$args['plural_selection_label']    = isset($args['plural_selection_label']) ? (string) $args['plural_selection_label'] : __('publications selected', 'medplatform-headless');
	$args['selected_count_text']       = isset($args['selected_count_text']) ? (string) $args['selected_count_text'] : mp_headless_get_selection_count_label(count($args['selected_ids'] ?? array()));
	$args['item_meta_callback']        = isset($args['item_meta_callback']) && is_callable($args['item_meta_callback'])
		? $args['item_meta_callback']
		: function($publication) {
			return get_the_date(get_option('date_format'), $publication);
		};

	mp_headless_render_post_selection_list($args);
}

function mp_headless_render_project_selection_list($args) {
	$args['posts']                     = isset($args['projects']) && is_array($args['projects']) ? $args['projects'] : array();
	$args['search_placeholder']        = isset($args['search_placeholder']) ? (string) $args['search_placeholder'] : __('Search projects...', 'medplatform-headless');
	$args['empty_search_label']        = isset($args['empty_search_label']) ? (string) $args['empty_search_label'] : __('No projects match this search.', 'medplatform-headless');
	$args['empty_list_label']          = isset($args['empty_list_label']) ? (string) $args['empty_list_label'] : __('No projects available yet.', 'medplatform-headless');
	$args['empty_selection_label']     = isset($args['empty_selection_label']) ? (string) $args['empty_selection_label'] : __('No projects selected', 'medplatform-headless');
	$args['singular_selection_label']  = isset($args['singular_selection_label']) ? (string) $args['singular_selection_label'] : __('project selected', 'medplatform-headless');
	$args['plural_selection_label']    = isset($args['plural_selection_label']) ? (string) $args['plural_selection_label'] : __('projects selected', 'medplatform-headless');
	$args['selected_count_text']       = isset($args['selected_count_text']) ? (string) $args['selected_count_text'] : mp_headless_get_project_selection_count_label(count($args['selected_ids'] ?? array()));
	$args['item_meta_callback']        = isset($args['item_meta_callback']) && is_callable($args['item_meta_callback'])
		? $args['item_meta_callback']
		: function($project) {
			$meta = array();

			if (! empty(get_post_meta($project->ID, 'mp_hide_project_currently', true))) {
				$meta[] = __('Currently hidden from project screens', 'medplatform-headless');
			}

			$lead_name = trim((string) get_post_meta($project->ID, 'mp_lead_name', true));
			if ($lead_name !== '') {
				$meta[] = sprintf(__('Lead: %s', 'medplatform-headless'), $lead_name);
			}

			return implode(' · ', $meta);
		};

	mp_headless_render_post_selection_list($args);
}

function mp_headless_render_project_donor_logo_row($logo = '') {
	$logo = (string) $logo;
	?>
	<div class="mp-donor-row" style="display:grid; gap:16px; align-items:start; grid-template-columns:84px minmax(0,1fr); padding:14px; border:1px solid #dcdcde; background:#fff;">
		<div class="mp-donor-preview-shell" style="display:flex; align-items:center; justify-content:center; min-height:72px; border:1px solid #dcdcde; background:#f6f7f7; padding:10px;">
			<img
				class="mp-donor-preview"
				src="<?php echo esc_url($logo); ?>"
				alt=""
				style="<?php echo $logo !== '' ? 'max-width:100%; max-height:48px; object-fit:contain;' : 'display:none;'; ?>"
			/>
			<span class="mp-donor-preview-empty" style="<?php echo $logo !== '' ? 'display:none;' : 'color:#646970; font-size:12px; text-align:center;'; ?>">
				<?php esc_html_e('Logo preview', 'medplatform-headless'); ?>
			</span>
		</div>
		<div class="mp-media-field">
			<input
				type="text"
				class="widefat mp-media-input"
				name="mp_donor_logos[]"
				value="<?php echo esc_attr($logo); ?>"
				placeholder="/Logos/example.png or https://example.com/logo.png"
			/>
			<div style="margin-top:8px; display:flex; gap:8px; flex-wrap:wrap;">
				<button type="button" class="button mp-media-upload" data-library-type="image">
					<?php esc_html_e('Upload donor logo', 'medplatform-headless'); ?>
				</button>
				<button type="button" class="button-link-delete mp-media-clear">
					<?php esc_html_e('Clear', 'medplatform-headless'); ?>
				</button>
				<button type="button" class="button-link-delete mp-donor-remove">
					<?php esc_html_e('Remove row', 'medplatform-headless'); ?>
				</button>
			</div>
			<p style="margin:8px 0 0; color:#646970;">
				<?php esc_html_e('Upload only the donor logo image. The frontend handles layout, sizing, and fit automatically.', 'medplatform-headless'); ?>
			</p>
		</div>
	</div>
	<?php
}

function mp_headless_render_person_settings_page() {
	if (! current_user_can('edit_posts')) {
		wp_die(esc_html__('You do not have permission to manage people.', 'medplatform-headless'));
	}

	$person_id = isset($_GET['person_id']) ? intval($_GET['person_id']) : 0;
	$person    = $person_id > 0 ? get_post($person_id) : null;

	if ($person_id > 0 && (! $person || $person->post_type !== 'mp_person')) {
		wp_die(esc_html__('Person profile could not be loaded.', 'medplatform-headless'));
	}

	$is_editing      = $person instanceof WP_Post;
	$updated         = isset($_GET['updated']) ? sanitize_text_field(wp_unslash($_GET['updated'])) : '';
	$person_name     = $is_editing ? $person->post_title : '';
	$person_slug     = $is_editing ? $person->post_name : '';
	$role            = $is_editing ? (string) get_post_meta($person->ID, 'mp_role', true) : '';
	$email           = $is_editing ? (string) get_post_meta($person->ID, 'mp_email', true) : '';
	$linkedin_url    = $is_editing ? (string) get_post_meta($person->ID, 'mp_linkedin_url', true) : '';
	$website_url     = $is_editing ? (string) get_post_meta($person->ID, 'mp_website_url', true) : '';
	$photo           = $is_editing ? (string) get_post_meta($person->ID, 'mp_photo', true) : '';
	$short_bio       = $is_editing ? (string) get_post_meta($person->ID, 'mp_short_bio', true) : '';
	$profile_bio     = $is_editing ? (string) get_post_meta($person->ID, 'mp_profile_bio', true) : '';
	$show_on_team    = $is_editing ? (bool) get_post_meta($person->ID, 'mp_show_on_team_page', true) : false;
	$team_order      = $is_editing ? intval($person->menu_order) : 0;
	$people_list_url = admin_url('edit.php?post_type=mp_person');

	if ($is_editing && $short_bio === '') {
		$short_bio = (string) $person->post_excerpt;
	}

	if ($is_editing && $profile_bio === '') {
		$profile_bio = trim(wp_strip_all_tags((string) $person->post_content));
	}
	?>
	<div class="wrap">
		<h1><?php echo esc_html($is_editing ? __('Edit Person', 'medplatform-headless') : __('Add Person', 'medplatform-headless')); ?></h1>
		<p>
			<?php esc_html_e('Manage the profile settings that power both the About team grid and the direct profile page. This replaces the normal post editor for people.', 'medplatform-headless'); ?>
		</p>
		<p style="margin-top:-4px;">
			<a href="<?php echo esc_url($people_list_url); ?>">&larr; <?php esc_html_e('Back to People', 'medplatform-headless'); ?></a>
		</p>

		<?php if ($updated === '1') : ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e('Person profile updated.', 'medplatform-headless'); ?></p></div>
		<?php endif; ?>

		<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
			<?php wp_nonce_field('mp_headless_save_person_settings', 'mp_headless_person_nonce'); ?>
			<input type="hidden" name="action" value="mp_headless_save_person_settings" />
			<input type="hidden" name="person_id" value="<?php echo esc_attr($is_editing ? (string) $person->ID : '0'); ?>" />

			<div style="display:grid; gap:20px; margin-top:20px; max-width:1100px;">
				<div style="background:#fff; border:1px solid #dcdcde; padding:20px;">
					<h2 style="margin-top:0;"><?php esc_html_e('Profile Basics', 'medplatform-headless'); ?></h2>
					<div style="display:grid; gap:18px; grid-template-columns:repeat(auto-fit,minmax(280px,1fr));">
						<div>
							<label for="mp_person_name"><strong><?php esc_html_e('Name', 'medplatform-headless'); ?></strong></label>
							<input type="text" id="mp_person_name" name="mp_person_name" class="widefat" value="<?php echo esc_attr($person_name); ?>" style="margin-top:8px;" required />
						</div>
						<div>
							<label for="mp_person_slug"><strong><?php esc_html_e('Profile Slug', 'medplatform-headless'); ?></strong></label>
							<input type="text" id="mp_person_slug" name="mp_person_slug" class="widefat" value="<?php echo esc_attr($person_slug); ?>" style="margin-top:8px;" />
							<p style="margin:8px 0 0; color:#646970;"><?php esc_html_e('Used for the public profile URL, for example /team/luigi-narbone.', 'medplatform-headless'); ?></p>
						</div>
						<div>
							<label for="mp_role"><strong><?php esc_html_e('Role', 'medplatform-headless'); ?></strong></label>
							<input type="text" id="mp_role" name="mp_role" class="widefat" value="<?php echo esc_attr($role); ?>" style="margin-top:8px;" />
						</div>
						<div>
							<label for="mp_person_menu_order"><strong><?php esc_html_e('Team Order', 'medplatform-headless'); ?></strong></label>
							<input type="number" id="mp_person_menu_order" name="mp_person_menu_order" class="widefat" value="<?php echo esc_attr((string) $team_order); ?>" style="margin-top:8px;" />
							<p style="margin:8px 0 0; color:#646970;"><?php esc_html_e('Lower numbers appear earlier on the team page.', 'medplatform-headless'); ?></p>
						</div>
					</div>
				</div>

				<div style="background:#fff; border:1px solid #dcdcde; padding:20px;">
					<h2 style="margin-top:0;"><?php esc_html_e('Links and Media', 'medplatform-headless'); ?></h2>
					<div style="display:grid; gap:18px; grid-template-columns:repeat(auto-fit,minmax(280px,1fr));">
						<div>
							<label for="mp_email"><strong><?php esc_html_e('Email', 'medplatform-headless'); ?></strong></label>
							<input type="email" id="mp_email" name="mp_email" class="widefat" value="<?php echo esc_attr($email); ?>" style="margin-top:8px;" />
						</div>
						<div>
							<label for="mp_linkedin_url"><strong><?php esc_html_e('LinkedIn URL', 'medplatform-headless'); ?></strong></label>
							<input type="url" id="mp_linkedin_url" name="mp_linkedin_url" class="widefat" value="<?php echo esc_attr($linkedin_url); ?>" style="margin-top:8px;" />
						</div>
						<div>
							<label for="mp_website_url"><strong><?php esc_html_e('Personal Website URL', 'medplatform-headless'); ?></strong></label>
							<input type="url" id="mp_website_url" name="mp_website_url" class="widefat" value="<?php echo esc_attr($website_url); ?>" style="margin-top:8px;" />
						</div>
					</div>
					<div style="margin-top:18px; max-width:520px;">
						<?php
						mp_headless_render_media_field(
							array(
								'label'        => __('Photo URL', 'medplatform-headless'),
								'name'         => 'mp_photo',
								'value'        => $photo,
								'button_label' => __('Upload photo', 'medplatform-headless'),
								'library_type' => 'image',
							)
						);
						?>
					</div>
				</div>

				<div style="background:#fff; border:1px solid #dcdcde; padding:20px;">
					<h2 style="margin-top:0;"><?php esc_html_e('Profile Text', 'medplatform-headless'); ?></h2>
					<p style="margin-top:0; color:#50575e;"><?php esc_html_e('Use the short bio for the team page and the long bio for the individual profile page.', 'medplatform-headless'); ?></p>
					<p>
						<label for="mp_short_bio"><strong><?php esc_html_e('Short Bio', 'medplatform-headless'); ?></strong></label>
						<textarea id="mp_short_bio" class="widefat" rows="4" name="mp_short_bio" style="margin-top:8px;" placeholder="<?php esc_attr_e('Short text used on the About team grid.', 'medplatform-headless'); ?>"><?php echo esc_textarea($short_bio); ?></textarea>
					</p>
					<p>
						<label for="mp_profile_bio"><strong><?php esc_html_e('Long Bio', 'medplatform-headless'); ?></strong></label>
						<textarea id="mp_profile_bio" class="widefat" rows="10" name="mp_profile_bio" style="margin-top:8px;" placeholder="<?php esc_attr_e('Longer profile text used on the personal profile page. Separate paragraphs with a blank line.', 'medplatform-headless'); ?>"><?php echo esc_textarea($profile_bio); ?></textarea>
					</p>
					<p style="margin-bottom:0;">
						<label>
							<input type="checkbox" name="mp_show_on_team_page" value="1" <?php checked($show_on_team); ?> />
							<?php esc_html_e('Include this person on the About team page', 'medplatform-headless'); ?>
						</label>
					</p>
				</div>
			</div>

			<?php submit_button($is_editing ? __('Save Person', 'medplatform-headless') : __('Create Person', 'medplatform-headless')); ?>
		</form>
	</div>
	<?php
}

function mp_headless_save_person_settings() {
	if (! current_user_can('edit_posts')) {
		wp_die(esc_html__('You do not have permission to manage people.', 'medplatform-headless'));
	}

	check_admin_referer('mp_headless_save_person_settings', 'mp_headless_person_nonce');

	$person_id       = intval($_POST['person_id'] ?? 0);
	$existing_person = $person_id > 0 ? get_post($person_id) : null;

	if ($person_id > 0 && (! $existing_person || $existing_person->post_type !== 'mp_person')) {
		wp_die(esc_html__('Person profile could not be loaded.', 'medplatform-headless'));
	}

	$name        = sanitize_text_field(wp_unslash($_POST['mp_person_name'] ?? ''));
	$slug        = sanitize_title(wp_unslash($_POST['mp_person_slug'] ?? ''));
	$role        = sanitize_text_field(wp_unslash($_POST['mp_role'] ?? ''));
	$email       = sanitize_email(wp_unslash($_POST['mp_email'] ?? ''));
	$linkedin    = esc_url_raw(wp_unslash($_POST['mp_linkedin_url'] ?? ''));
	$website     = esc_url_raw(wp_unslash($_POST['mp_website_url'] ?? ''));
	$photo       = esc_url_raw(wp_unslash($_POST['mp_photo'] ?? ''));
	$short_bio   = sanitize_textarea_field(wp_unslash($_POST['mp_short_bio'] ?? ''));
	$profile_bio = sanitize_textarea_field(wp_unslash($_POST['mp_profile_bio'] ?? ''));
	$team_order  = intval($_POST['mp_person_menu_order'] ?? 0);

	if ($name === '') {
		wp_die(esc_html__('Name is required.', 'medplatform-headless'));
	}

	if ($slug === '') {
		$slug = $existing_person instanceof WP_Post && $existing_person->post_name
			? $existing_person->post_name
			: sanitize_title($name);
	}

	$GLOBALS['mp_headless_suspend_build_trigger'] = true;

	$result = wp_insert_post(
		array(
			'ID'           => $existing_person instanceof WP_Post ? $existing_person->ID : 0,
			'post_type'    => 'mp_person',
			'post_status'  => 'publish',
			'post_title'   => $name,
			'post_name'    => $slug,
			'post_excerpt' => $short_bio,
			'post_content' => $profile_bio,
			'menu_order'   => $team_order,
		),
		true
	);

	$GLOBALS['mp_headless_suspend_build_trigger'] = false;

	if (is_wp_error($result)) {
		wp_die(esc_html($result->get_error_message()));
	}

	$person_id = (int) $result;

	update_post_meta($person_id, 'mp_role', $role);
	update_post_meta($person_id, 'mp_email', $email);
	update_post_meta($person_id, 'mp_linkedin_url', $linkedin);
	update_post_meta($person_id, 'mp_website_url', $website);
	update_post_meta($person_id, 'mp_photo', $photo);
	update_post_meta($person_id, 'mp_short_bio', $short_bio);
	update_post_meta($person_id, 'mp_profile_bio', $profile_bio);
	update_post_meta($person_id, 'mp_show_on_team_page', ! empty($_POST['mp_show_on_team_page']));

	clean_post_cache($person_id);
	mp_headless_trigger_build('person_settings_update:' . $person_id);

	wp_safe_redirect(
		add_query_arg(
			array(
				'page'     => 'mp-person-profile',
				'person_id' => $person_id,
				'updated'  => '1',
			),
			admin_url('admin.php')
		)
	);
	exit;
}
add_action('admin_post_mp_headless_save_person_settings', 'mp_headless_save_person_settings');

function mp_headless_render_project_settings_page() {
	if (! current_user_can('edit_posts')) {
		wp_die(esc_html__('You do not have permission to manage projects.', 'medplatform-headless'));
	}

	$project_id = isset($_GET['project_id']) ? intval($_GET['project_id']) : 0;
	$project    = $project_id > 0 ? get_post($project_id) : null;

	if ($project_id > 0 && (! $project || $project->post_type !== 'mp_project')) {
		wp_die(esc_html__('Project settings could not be loaded.', 'medplatform-headless'));
	}

	$is_editing            = $project instanceof WP_Post;
	$updated               = isset($_GET['updated']) ? sanitize_text_field(wp_unslash($_GET['updated'])) : '';
	$people                = get_posts(array('post_type' => 'mp_person', 'posts_per_page' => -1, 'orderby' => 'menu_order title', 'order' => 'ASC'));
	$available_parent_projects = get_posts(
		array(
			'post_type'      => 'mp_project',
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'post__not_in'   => $is_editing ? array($project->ID) : array(),
		)
	);
	$projects_list_url     = admin_url('edit.php?post_type=mp_project');
	$focus_area_options    = mp_headless_project_focus_area_options();
	$project_card_icon_options = mp_headless_project_card_icon_options();
	$focus_area_titles     = array();
	$project_name          = $is_editing ? $project->post_title : '';
	$project_slug          = $is_editing ? $project->post_name : '';
	$project_description   = $is_editing ? (string) $project->post_excerpt : '';
	$raw_project_content   = $is_editing ? (string) $project->post_content : '';
	$project_content       = $raw_project_content !== ''
		? trim(
			preg_replace(
				"/\n{3,}/",
				"\n\n",
				wp_strip_all_tags(
					str_replace(
						array('</p>', '</div>', '<br>', '<br />', '<br/>'),
						array("</p>\n\n", "</div>\n\n", "\n", "\n", "\n"),
						$raw_project_content
					)
				)
			)
		)
		: '';
	$project_color         = $is_editing ? (string) get_post_meta($project->ID, 'mp_color', true) : '#15243a';
	$project_progress_color = $is_editing ? (string) get_post_meta($project->ID, 'mp_progress_color', true) : '';
	$project_card_icon     = $is_editing ? (string) get_post_meta($project->ID, 'mp_card_icon', true) : '';
	$hide_project_bar      = $is_editing ? (bool) get_post_meta($project->ID, 'mp_hide_project_bar', true) : false;
	$hide_project_currently = $is_editing ? (bool) get_post_meta($project->ID, 'mp_hide_project_currently', true) : false;
	$stage_points          = $is_editing ? get_post_meta($project->ID, 'mp_stage_points', true) : array();
	$current_stage         = $is_editing ? (string) get_post_meta($project->ID, 'mp_current_stage', true) : '';
	$parent_project_id     = $is_editing ? intval(get_post_meta($project->ID, 'mp_parent_project_id', true)) : 0;
	$aligned_project_id    = $is_editing ? intval(get_post_meta($project->ID, 'mp_aligned_project_id', true)) : 0;
	$lead_person_id        = $is_editing ? intval(get_post_meta($project->ID, 'mp_lead_person_id', true)) : 0;
	$lead_person_ids       = $is_editing
		? mp_headless_normalize_selected_person_ids(get_post_meta($project->ID, 'mp_lead_person_ids', true), $lead_person_id)
		: array();
	$lead_name             = $is_editing ? (string) get_post_meta($project->ID, 'mp_lead_name', true) : '';
	$lead_role             = $is_editing ? (string) get_post_meta($project->ID, 'mp_lead_role', true) : 'Program Lead';
	$team_member_ids       = $is_editing ? get_post_meta($project->ID, 'mp_team_member_ids', true) : array();
	$legacy_team_members   = $is_editing ? get_post_meta($project->ID, 'mp_team_members', true) : array();
	$updates               = $is_editing ? get_post_meta($project->ID, 'mp_updates', true) : array();
	$donors                = $is_editing ? get_post_meta($project->ID, 'mp_donors', true) : array();
	$focus_area_slugs      = $is_editing ? get_post_meta($project->ID, 'mp_focus_area_slugs', true) : array();
	$legacy_focus_areas    = $is_editing ? get_post_meta($project->ID, 'mp_focus_areas', true) : array();
	$custom_focus_areas    = $is_editing ? get_post_meta($project->ID, 'mp_custom_focus_areas', true) : array();
	$stage_points          = is_array($stage_points) && ! empty($stage_points) ? array_values(array_filter(array_map('strval', $stage_points))) : mp_headless_project_stages();
	$team_member_ids       = is_array($team_member_ids) ? array_values(array_filter(array_map('intval', $team_member_ids))) : array();
	$legacy_team_members   = is_array($legacy_team_members) ? $legacy_team_members : array();
	$updates               = is_array($updates) ? $updates : array();
	$donors                = is_array($donors) ? $donors : array();
	$focus_area_slugs      = is_array($focus_area_slugs) ? array_values(array_filter(array_map('sanitize_title', $focus_area_slugs))) : array();
	$custom_focus_areas    = is_array($custom_focus_areas) ? mp_headless_sanitize_custom_focus_areas($custom_focus_areas) : array();
	$project_progress_color = $project_progress_color !== '' ? $project_progress_color : ($project_color !== '' ? $project_color : '#15243a');

	foreach ($focus_area_options as $option) {
		$focus_area_titles[strtolower($option['title'])] = $option['slug'];
	}

	if (empty($team_member_ids) && ! empty($legacy_team_members)) {
		foreach ($legacy_team_members as $member_name) {
			$member_id = mp_headless_find_person_id_by_name((string) $member_name);
			if ($member_id > 0) {
				$team_member_ids[] = $member_id;
			}
		}
		$team_member_ids = array_values(array_unique(array_map('intval', $team_member_ids)));
	}

	if (empty($lead_person_ids) && $lead_person_id < 1 && $lead_name !== '') {
		$lead_person_id = mp_headless_find_person_id_by_name($lead_name);
	}

	if ($lead_person_id > 0 && empty($lead_person_ids)) {
		$lead_person_ids[] = $lead_person_id;
	}

	foreach ($lead_person_ids as $selected_lead_person_id) {
		if ($selected_lead_person_id > 0 && ! in_array($selected_lead_person_id, $team_member_ids, true)) {
			$team_member_ids[] = $selected_lead_person_id;
		}
	}

	if (empty($focus_area_slugs) && is_array($legacy_focus_areas)) {
		foreach ($legacy_focus_areas as $focus_area) {
			$title = strtolower(trim((string) ($focus_area['title'] ?? '')));
			if ($title !== '' && isset($focus_area_titles[$title])) {
				$focus_area_slugs[] = $focus_area_titles[$title];
			}
		}
		$focus_area_slugs = array_values(array_unique($focus_area_slugs));
	}

	if (empty($custom_focus_areas) && is_array($legacy_focus_areas)) {
		foreach ($legacy_focus_areas as $focus_area) {
			$title = trim((string) ($focus_area['title'] ?? ''));
			$description = trim((string) ($focus_area['description'] ?? ''));

			if ($title === '' && $description === '') {
				continue;
			}

			if (isset($focus_area_titles[strtolower($title)])) {
				continue;
			}

			$custom_focus_areas[] = array(
				'enabled'     => true,
				'title'       => $title,
				'description' => $description,
			);
		}
	}

	$custom_focus_areas = array_slice($custom_focus_areas, 0, 2);

	$donor_logos = array();
	foreach ($donors as $donor) {
		$logo = is_array($donor) ? (string) ($donor['logo'] ?? '') : '';
		if ($logo !== '') {
			$donor_logos[] = $logo;
		}
	}

	if (empty($donor_logos)) {
		$donor_logos[] = '';
	}
	?>
	<div class="wrap">
		<style>
			.mp-project-focus-grid {
				display: grid;
				gap: 14px;
				grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
			}

			.mp-project-focus-card {
				display: block;
				border: 1px solid #dcdcde;
				background: #fff;
				padding: 14px;
			}

			.mp-project-focus-card:hover {
				border-color: #b7bdc3;
			}
		</style>
		<h1><?php echo esc_html($is_editing ? __('Edit Project', 'medplatform-headless') : __('Add Project', 'medplatform-headless')); ?></h1>
		<p><?php esc_html_e('Manage project settings from one structured form. This replaces the normal post editor for projects.', 'medplatform-headless'); ?></p>
		<p style="margin-top:-4px;">
			<a href="<?php echo esc_url($projects_list_url); ?>">&larr; <?php esc_html_e('Back to Projects', 'medplatform-headless'); ?></a>
		</p>

		<?php if ($updated === '1') : ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e('Project updated.', 'medplatform-headless'); ?></p></div>
		<?php endif; ?>

		<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
			<?php wp_nonce_field('mp_headless_save_project_settings', 'mp_headless_project_nonce'); ?>
			<input type="hidden" name="action" value="mp_headless_save_project_settings" />
			<input type="hidden" name="project_id" value="<?php echo esc_attr($is_editing ? (string) $project->ID : '0'); ?>" />

			<div style="display:grid; gap:20px; margin-top:20px; max-width:1200px;">
				<div style="background:#fff; border:1px solid #dcdcde; padding:20px;">
					<h2 style="margin-top:0;"><?php esc_html_e('Project Basics', 'medplatform-headless'); ?></h2>
					<div style="display:grid; gap:18px; grid-template-columns:repeat(auto-fit,minmax(280px,1fr));">
						<div>
							<label for="mp_project_name"><strong><?php esc_html_e('Project Name', 'medplatform-headless'); ?></strong></label>
							<input type="text" id="mp_project_name" name="mp_project_name" class="widefat" value="<?php echo esc_attr($project_name); ?>" style="margin-top:8px;" required />
						</div>
						<div>
							<label for="mp_project_slug"><strong><?php esc_html_e('Project Slug', 'medplatform-headless'); ?></strong></label>
							<input type="text" id="mp_project_slug" name="mp_project_slug" class="widefat" value="<?php echo esc_attr($project_slug); ?>" style="margin-top:8px;" />
							<p style="margin:8px 0 0; color:#646970;"><?php esc_html_e('Used in the public project URL, for example /services/gulf-platform.', 'medplatform-headless'); ?></p>
						</div>
						<div style="display:grid; gap:10px;">
							<label for="mp_color"><strong><?php esc_html_e('Accent Color', 'medplatform-headless'); ?></strong></label>
							<div data-mp-color-field style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
								<input type="color" value="<?php echo esc_attr($project_color ?: '#15243a'); ?>" data-mp-color-picker style="inline-size:56px; block-size:40px; padding:0; border:0; background:none;" />
								<input type="text" id="mp_color" name="mp_color" class="regular-text code" value="<?php echo esc_attr($project_color ?: '#15243a'); ?>" data-mp-color-value pattern="^#([A-Fa-f0-9]{6})$" />
							</div>
							<p style="margin:0; color:#646970;"><?php esc_html_e('Pick the project accent color. It is reused on the public project page and on related publication project buttons.', 'medplatform-headless'); ?></p>
						</div>
						<div style="display:grid; gap:10px;">
							<label for="mp_progress_color"><strong><?php esc_html_e('Progress Bar Color', 'medplatform-headless'); ?></strong></label>
							<div data-mp-color-field style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
								<input type="color" value="<?php echo esc_attr($project_progress_color ?: '#15243a'); ?>" data-mp-color-picker style="inline-size:56px; block-size:40px; padding:0; border:0; background:none;" />
								<input type="text" id="mp_progress_color" name="mp_progress_color" class="regular-text code" value="<?php echo esc_attr($project_progress_color ?: '#15243a'); ?>" data-mp-color-value pattern="^#([A-Fa-f0-9]{6})$" />
							</div>
							<p style="margin:0; color:#646970;"><?php esc_html_e('Used only for the project progress bar. Leave it aligned with the accent color or pick a separate timeline color.', 'medplatform-headless'); ?></p>
						</div>
						<div>
							<label for="mp_project_description"><strong><?php esc_html_e('Short Description', 'medplatform-headless'); ?></strong></label>
							<textarea id="mp_project_description" class="widefat" rows="4" name="mp_project_description" style="margin-top:8px;"><?php echo esc_textarea($project_description); ?></textarea>
							<p style="margin:8px 0 0; color:#646970;"><?php esc_html_e('Shown on project cards. Anything beyond 25 words is trimmed automatically.', 'medplatform-headless'); ?></p>
						</div>
						<div>
							<label for="mp_card_icon"><strong><?php esc_html_e('Project Card Icon', 'medplatform-headless'); ?></strong></label>
							<select id="mp_card_icon" name="mp_card_icon" class="widefat" style="margin-top:8px;">
								<option value=""><?php esc_html_e('Use the default icon', 'medplatform-headless'); ?></option>
								<?php foreach ($project_card_icon_options as $icon_value => $icon_label) : ?>
									<option value="<?php echo esc_attr($icon_value); ?>" <?php selected($project_card_icon, $icon_value); ?>><?php echo esc_html($icon_label); ?></option>
								<?php endforeach; ?>
							</select>
							<p style="margin:8px 0 0; color:#646970;"><?php esc_html_e('Used on the /services project cards for newer projects. Libya Platform, Africa Nexus, and Gulf Platform keep their custom artwork.', 'medplatform-headless'); ?></p>
						</div>
						<div style="grid-column:1 / -1;">
							<label>
								<input type="checkbox" name="mp_hide_project_currently" value="1" <?php checked($hide_project_currently); ?> />
								<?php esc_html_e('Hide this project and its related posts from project screens for now', 'medplatform-headless'); ?>
							</label>
							<p style="margin:8px 0 0; color:#646970;"><?php esc_html_e('This removes the project from frontend project listings and hides its project-linked publications from project screens, while keeping the content in WordPress.', 'medplatform-headless'); ?></p>
						</div>
					</div>
				</div>

				<div style="background:#fff; border:1px solid #dcdcde; padding:20px;">
					<h2 style="margin-top:0;"><?php esc_html_e('Project Structure', 'medplatform-headless'); ?></h2>
					<div style="display:grid; gap:18px; grid-template-columns:repeat(auto-fit,minmax(280px,1fr));">
						<div>
							<label><strong><?php esc_html_e('Progress Points', 'medplatform-headless'); ?></strong></label>
							<textarea class="widefat" rows="6" name="mp_stage_points" data-mp-stage-points-input style="margin-top:8px;" placeholder="One progress point per line"><?php echo esc_textarea(implode("\n", $stage_points)); ?></textarea>
							<p style="margin:8px 0 0; color:#646970;"><?php esc_html_e('Each line becomes one step on the public project progress bar.', 'medplatform-headless'); ?></p>
						</div>
						<div>
							<label for="mp_current_stage"><strong><?php esc_html_e('Current Progress Point', 'medplatform-headless'); ?></strong></label>
							<select id="mp_current_stage" name="mp_current_stage" class="widefat" data-mp-current-stage-select style="margin-top:8px;">
								<?php foreach ($stage_points as $stage_point) : ?>
									<option value="<?php echo esc_attr($stage_point); ?>" <?php selected($current_stage, $stage_point); ?>><?php echo esc_html($stage_point); ?></option>
								<?php endforeach; ?>
							</select>
							<p style="margin:8px 0 0; color:#646970;"><?php esc_html_e('Choose the live status from the progress points above.', 'medplatform-headless'); ?></p>
							<p style="margin:14px 0 0;">
								<label>
									<input type="checkbox" name="mp_hide_project_bar" value="1" <?php checked($hide_project_bar); ?> />
									<?php esc_html_e('Hide the project progress bar on the public project page', 'medplatform-headless'); ?>
								</label>
							</p>
						</div>
						<div>
							<p style="margin:0;">
								<label>
									<input type="checkbox" name="mp_has_parent_project" value="1" data-mp-parent-project-toggle <?php checked($parent_project_id > 0); ?> />
									<?php esc_html_e('This project sits under another project', 'medplatform-headless'); ?>
								</label>
							</p>
							<div data-mp-parent-project-fields style="margin-top:14px;">
								<label for="mp_parent_project_id"><strong><?php esc_html_e('Parent Project', 'medplatform-headless'); ?></strong></label>
								<select id="mp_parent_project_id" name="mp_parent_project_id" class="widefat" data-mp-parent-project-select style="margin-top:8px;">
									<option value="0"><?php esc_html_e('Select a parent project', 'medplatform-headless'); ?></option>
									<?php foreach ($available_parent_projects as $parent_project) : ?>
										<option value="<?php echo esc_attr($parent_project->ID); ?>" <?php selected($parent_project_id, (int) $parent_project->ID); ?>><?php echo esc_html($parent_project->post_title); ?></option>
									<?php endforeach; ?>
								</select>
								<p style="margin:8px 0 0; color:#646970;"><?php esc_html_e('Optional. When selected, the frontend project page will show that this project sits under the chosen parent project.', 'medplatform-headless'); ?></p>
							</div>
						</div>
						<div>
							<p style="margin:0;">
								<label>
									<input type="checkbox" name="mp_has_aligned_project" value="1" data-mp-aligned-project-toggle <?php checked($aligned_project_id > 0); ?> />
									<?php esc_html_e('This project is aligned with another project', 'medplatform-headless'); ?>
								</label>
							</p>
							<div data-mp-aligned-project-fields style="margin-top:14px;">
								<label for="mp_aligned_project_id"><strong><?php esc_html_e('Aligned Project', 'medplatform-headless'); ?></strong></label>
								<select id="mp_aligned_project_id" name="mp_aligned_project_id" class="widefat" data-mp-aligned-project-select style="margin-top:8px;">
									<option value="0"><?php esc_html_e('Select an aligned project', 'medplatform-headless'); ?></option>
									<?php foreach ($available_parent_projects as $aligned_project) : ?>
										<option value="<?php echo esc_attr($aligned_project->ID); ?>" <?php selected($aligned_project_id, (int) $aligned_project->ID); ?>><?php echo esc_html($aligned_project->post_title); ?></option>
									<?php endforeach; ?>
								</select>
								<p style="margin:8px 0 0; color:#646970;"><?php esc_html_e('Optional. When selected, the frontend project page will show this as an aligned project connection.', 'medplatform-headless'); ?></p>
							</div>
						</div>
					</div>
				</div>

				<div style="background:#fff; border:1px solid #dcdcde; padding:20px;">
					<h2 style="margin-top:0;"><?php esc_html_e('Team and Lead', 'medplatform-headless'); ?></h2>
					<div style="display:grid; gap:18px; grid-template-columns:repeat(auto-fit,minmax(280px,1fr));">
						<div>
							<label for="mp_team_member_ids"><strong><?php esc_html_e('Team Members', 'medplatform-headless'); ?></strong></label>
							<select id="mp_team_member_ids" name="mp_team_member_ids[]" class="widefat" size="8" multiple data-mp-team-select style="margin-top:8px; min-height:220px;">
								<?php foreach ($people as $person) : ?>
									<option value="<?php echo esc_attr($person->ID); ?>" <?php selected(in_array((int) $person->ID, $team_member_ids, true)); ?>><?php echo esc_html($person->post_title); ?></option>
								<?php endforeach; ?>
							</select>
							<p style="margin:8px 0 0; color:#646970;"><?php esc_html_e('Select the people involved in this project. Hold Command or Ctrl to choose multiple team members.', 'medplatform-headless'); ?></p>
						</div>
						<div>
							<label for="mp_lead_person_ids"><strong><?php esc_html_e('Lead Team Members', 'medplatform-headless'); ?></strong></label>
							<select id="mp_lead_person_ids" name="mp_lead_person_ids[]" class="widefat" size="8" multiple data-mp-lead-select style="margin-top:8px; min-height:220px;">
								<?php foreach ($people as $person) : ?>
									<?php $is_in_team = empty($team_member_ids) || in_array((int) $person->ID, $team_member_ids, true); ?>
									<option
										value="<?php echo esc_attr($person->ID); ?>"
										data-person-id="<?php echo esc_attr($person->ID); ?>"
										<?php selected(in_array((int) $person->ID, $lead_person_ids, true)); ?>
										<?php disabled(! $is_in_team && ! in_array((int) $person->ID, $lead_person_ids, true)); ?>
									>
										<?php echo esc_html($person->post_title); ?>
									</option>
								<?php endforeach; ?>
							</select>
							<p style="margin:8px 0 0; color:#646970;"><?php esc_html_e('Choose one or more program leads. Hold Command or Ctrl to select multiple people. Lead photos are pulled automatically from the selected team member profiles.', 'medplatform-headless'); ?></p>
							<p style="margin:16px 0 0;">
								<label for="mp_lead_role"><strong><?php esc_html_e('Lead Label', 'medplatform-headless'); ?></strong></label>
								<input type="text" id="mp_lead_role" name="mp_lead_role" class="widefat" value="<?php echo esc_attr($lead_role); ?>" style="margin-top:8px;" />
							</p>
						</div>
					</div>
				</div>

				<div style="background:#fff; border:1px solid #dcdcde; padding:20px;">
					<h2 style="margin-top:0;"><?php esc_html_e('Donor Logos', 'medplatform-headless'); ?></h2>
					<p style="margin-top:0; color:#50575e;"><?php esc_html_e('Upload donor logos only. The frontend automatically organizes and resizes them inside the donor grid.', 'medplatform-headless'); ?></p>
					<div id="mp-project-donor-list" data-mp-donor-list style="display:grid; gap:12px;">
						<?php foreach ($donor_logos as $logo) : ?>
							<?php mp_headless_render_project_donor_logo_row($logo); ?>
						<?php endforeach; ?>
					</div>
					<p style="margin:14px 0 0;">
						<button type="button" class="button" data-mp-donor-add data-target="#mp-project-donor-list"><?php esc_html_e('Add donor logo', 'medplatform-headless'); ?></button>
					</p>
					<template id="mp-project-donor-template">
						<?php mp_headless_render_project_donor_logo_row(''); ?>
					</template>
				</div>

				<div style="background:#fff; border:1px solid #dcdcde; padding:20px;">
					<h2 style="margin-top:0;"><?php esc_html_e('Focus Areas', 'medplatform-headless'); ?></h2>
					<p style="margin-top:0; color:#50575e;"><?php esc_html_e('Choose from the predefined focus areas, then optionally add up to two custom ones for project-specific workstreams.', 'medplatform-headless'); ?></p>
					<div class="mp-project-focus-grid">
						<?php foreach ($focus_area_options as $option) : ?>
							<label class="mp-project-focus-card">
								<input type="checkbox" name="mp_focus_area_slugs[]" value="<?php echo esc_attr($option['slug']); ?>" <?php checked(in_array($option['slug'], $focus_area_slugs, true)); ?> />
								<strong style="display:block; margin-top:8px;"><?php echo esc_html($option['title']); ?></strong>
								<span style="display:block; margin-top:6px; color:#646970;"><?php echo esc_html($option['description']); ?></span>
							</label>
						<?php endforeach; ?>
					</div>
					<div style="margin-top:20px; display:grid; gap:18px; grid-template-columns:repeat(auto-fit,minmax(280px,1fr));">
						<?php for ($custom_focus_area_index = 0; $custom_focus_area_index < 2; $custom_focus_area_index++) : ?>
							<?php $custom_focus_area = $custom_focus_areas[$custom_focus_area_index] ?? array(); ?>
							<?php $custom_focus_area_enabled = ! empty($custom_focus_area['enabled']); ?>
							<div data-mp-custom-focus-area-card style="border:1px solid #dcdcde; background:#fff; padding:16px;">
								<h3 style="margin:0 0 12px;"><?php echo esc_html(sprintf(__('Custom Focus Area %d', 'medplatform-headless'), $custom_focus_area_index + 1)); ?></h3>
								<p style="margin:0 0 14px;">
									<label>
										<input
											type="checkbox"
											name="<?php echo esc_attr('mp_custom_focus_areas[' . $custom_focus_area_index . '][enabled]'); ?>"
											value="1"
											data-mp-custom-focus-area-toggle
											<?php checked($custom_focus_area_enabled); ?>
										/>
										<?php esc_html_e('Show this custom focus area on the project page', 'medplatform-headless'); ?>
									</label>
								</p>
								<div data-mp-custom-focus-area-fields <?php echo $custom_focus_area_enabled ? '' : 'hidden'; ?>>
								<label for="<?php echo esc_attr('mp_custom_focus_area_title_' . $custom_focus_area_index); ?>"><strong><?php esc_html_e('Title', 'medplatform-headless'); ?></strong></label>
								<input
									type="text"
									id="<?php echo esc_attr('mp_custom_focus_area_title_' . $custom_focus_area_index); ?>"
									name="<?php echo esc_attr('mp_custom_focus_areas[' . $custom_focus_area_index . '][title]'); ?>"
									class="widefat"
									value="<?php echo esc_attr((string) ($custom_focus_area['title'] ?? '')); ?>"
									style="margin-top:8px;"
								/>
								<label for="<?php echo esc_attr('mp_custom_focus_area_description_' . $custom_focus_area_index); ?>" style="display:block; margin-top:14px;"><strong><?php esc_html_e('Description', 'medplatform-headless'); ?></strong></label>
								<textarea
									id="<?php echo esc_attr('mp_custom_focus_area_description_' . $custom_focus_area_index); ?>"
									name="<?php echo esc_attr('mp_custom_focus_areas[' . $custom_focus_area_index . '][description]'); ?>"
									class="widefat"
									rows="4"
									style="margin-top:8px;"
								><?php echo esc_textarea((string) ($custom_focus_area['description'] ?? '')); ?></textarea>
								<p style="margin:8px 0 0; color:#646970;"><?php esc_html_e('Maximum 25 words. Anything longer is trimmed automatically when you save.', 'medplatform-headless'); ?></p>
								</div>
							</div>
						<?php endfor; ?>
					</div>
				</div>

				<div style="background:#fff; border:1px solid #dcdcde; padding:20px;">
					<h2 style="margin-top:0;"><?php esc_html_e('Project Text', 'medplatform-headless'); ?></h2>
					<p style="margin-top:0; color:#50575e;"><?php esc_html_e('Use blank lines to separate paragraphs in the main project text.', 'medplatform-headless'); ?></p>
					<p>
						<label for="mp_project_content"><strong><?php esc_html_e('Main Project Text', 'medplatform-headless'); ?></strong></label>
						<textarea id="mp_project_content" class="widefat" rows="10" name="mp_project_content" style="margin-top:8px;"><?php echo esc_textarea($project_content); ?></textarea>
					</p>
					<p style="margin-bottom:0;">
						<label for="mp_updates"><strong><?php esc_html_e('Current Updates', 'medplatform-headless'); ?></strong></label>
						<textarea id="mp_updates" class="widefat" rows="5" name="mp_updates" style="margin-top:8px;" placeholder="One update per line"><?php echo esc_textarea(implode("\n", $updates)); ?></textarea>
					</p>
				</div>
			</div>

			<?php submit_button($is_editing ? __('Save Project', 'medplatform-headless') : __('Create Project', 'medplatform-headless')); ?>
		</form>
	</div>
	<?php
}

function mp_headless_save_project_settings() {
	if (! current_user_can('edit_posts')) {
		wp_die(esc_html__('You do not have permission to manage projects.', 'medplatform-headless'));
	}

	check_admin_referer('mp_headless_save_project_settings', 'mp_headless_project_nonce');

	$project_id       = intval($_POST['project_id'] ?? 0);
	$existing_project = $project_id > 0 ? get_post($project_id) : null;

	if ($project_id > 0 && (! $existing_project || $existing_project->post_type !== 'mp_project')) {
		wp_die(esc_html__('Project settings could not be loaded.', 'medplatform-headless'));
	}

	$name              = sanitize_text_field(wp_unslash($_POST['mp_project_name'] ?? ''));
	$slug              = sanitize_title(wp_unslash($_POST['mp_project_slug'] ?? ''));
	$description       = mp_headless_trim_words(sanitize_textarea_field(wp_unslash($_POST['mp_project_description'] ?? '')), 25);
	$content           = sanitize_textarea_field(wp_unslash($_POST['mp_project_content'] ?? ''));
	$color             = sanitize_hex_color(wp_unslash($_POST['mp_color'] ?? ''));
	$progress_color    = sanitize_hex_color(wp_unslash($_POST['mp_progress_color'] ?? ''));
	$project_card_icon = mp_headless_sanitize_project_card_icon(wp_unslash($_POST['mp_card_icon'] ?? ''));
	$stage_points      = mp_headless_parse_lines($_POST['mp_stage_points'] ?? '');
	$current_stage     = sanitize_text_field(wp_unslash($_POST['mp_current_stage'] ?? ''));
	$parent_project_id = ! empty($_POST['mp_has_parent_project'])
		? mp_headless_normalize_parent_project_id($project_id, intval($_POST['mp_parent_project_id'] ?? 0))
		: 0;
	$aligned_project_id = ! empty($_POST['mp_has_aligned_project'])
		? mp_headless_normalize_aligned_project_id($project_id, intval($_POST['mp_aligned_project_id'] ?? 0))
		: 0;
	$team_member_ids   = mp_headless_sanitize_int_array(wp_unslash($_POST['mp_team_member_ids'] ?? array()));
	$lead_person_ids   = mp_headless_normalize_selected_person_ids(
		wp_unslash($_POST['mp_lead_person_ids'] ?? array()),
		intval($_POST['mp_lead_person_id'] ?? 0)
	);
	$lead_role         = sanitize_text_field(wp_unslash($_POST['mp_lead_role'] ?? ''));
	$updates           = mp_headless_parse_lines($_POST['mp_updates'] ?? '');
	$focus_area_slugs  = mp_headless_sanitize_string_array(wp_unslash($_POST['mp_focus_area_slugs'] ?? array()));
	$custom_focus_areas = array_slice(
		mp_headless_sanitize_custom_focus_areas(wp_unslash($_POST['mp_custom_focus_areas'] ?? array())),
		0,
		2
	);
	$donor_logo_values = wp_unslash($_POST['mp_donor_logos'] ?? array());

	if ($name === '') {
		wp_die(esc_html__('Project name is required.', 'medplatform-headless'));
	}

	if ($slug === '') {
		$slug = $existing_project instanceof WP_Post && $existing_project->post_name
			? $existing_project->post_name
			: sanitize_title($name);
	}

	if ($color === '') {
		$color = '#15243a';
	}

	if ($progress_color === '') {
		$progress_color = $color;
	}

	if (empty($stage_points)) {
		$stage_points = mp_headless_project_stages();
	}

	if ($current_stage === '' || ! in_array($current_stage, $stage_points, true)) {
		$current_stage = $stage_points[0];
	}

	foreach ($lead_person_ids as $selected_lead_person_id) {
		if ($selected_lead_person_id > 0 && ! in_array($selected_lead_person_id, $team_member_ids, true)) {
			$team_member_ids[] = $selected_lead_person_id;
		}
	}

	$team_member_ids = array_values(array_unique(array_map('intval', $team_member_ids)));
	$team_member_names = array();
	foreach ($team_member_ids as $person_id) {
		$person = get_post($person_id);
		if ($person instanceof WP_Post && $person->post_type === 'mp_person') {
			$team_member_names[] = $person->post_title;
		}
	}

	$lead_person_id = ! empty($lead_person_ids) ? (int) $lead_person_ids[0] : 0;
	$lead_post  = $lead_person_id > 0 ? get_post($lead_person_id) : null;
	$lead_name  = ($lead_post instanceof WP_Post && $lead_post->post_type === 'mp_person') ? $lead_post->post_title : '';
	$lead_image = $lead_person_id > 0 ? (string) get_post_meta($lead_person_id, 'mp_photo', true) : '';
	if ($lead_role === '' && $lead_person_id > 0) {
		$lead_role = (string) get_post_meta($lead_person_id, 'mp_role', true);
	}

	$focus_area_slugs = array_values(array_unique(array_map('sanitize_title', $focus_area_slugs)));
	$focus_area_objects = mp_headless_resolve_focus_area_objects($focus_area_slugs);

	foreach ($custom_focus_areas as $custom_focus_area) {
		if (empty($custom_focus_area['enabled'])) {
			continue;
		}

		$title       = (string) ($custom_focus_area['title'] ?? '');
		$description = (string) ($custom_focus_area['description'] ?? '');

		if ($title === '' && $description === '') {
			continue;
		}

		$focus_area_objects[] = array(
			'title'       => $title,
			'description' => $description,
		);
	}

	$donors = array();
	if (is_array($donor_logo_values)) {
		foreach (array_values($donor_logo_values) as $index => $logo_value) {
			$logo = esc_url_raw((string) $logo_value);
			if ($logo === '') {
				continue;
			}

			$donors[] = array(
				'name' => mp_headless_generate_logo_label_from_url($logo, $index),
				'logo' => $logo,
			);
		}
	}

	$GLOBALS['mp_headless_suspend_build_trigger'] = true;

	$result = wp_insert_post(
		array(
			'ID'           => $existing_project instanceof WP_Post ? $existing_project->ID : 0,
			'post_type'    => 'mp_project',
			'post_status'  => 'publish',
			'post_title'   => $name,
			'post_name'    => $slug,
			'post_excerpt' => $description,
			'post_content' => $content,
		),
		true
	);

	$GLOBALS['mp_headless_suspend_build_trigger'] = false;

	if (is_wp_error($result)) {
		wp_die(esc_html($result->get_error_message()));
	}

	$project_id = (int) $result;

	update_post_meta($project_id, 'mp_color', $color);
	update_post_meta($project_id, 'mp_progress_color', $progress_color);
	update_post_meta($project_id, 'mp_card_icon', $project_card_icon);
	update_post_meta($project_id, 'mp_hide_project_bar', ! empty($_POST['mp_hide_project_bar']));
	update_post_meta($project_id, 'mp_hide_project_currently', ! empty($_POST['mp_hide_project_currently']));
	update_post_meta($project_id, 'mp_stage_points', $stage_points);
	update_post_meta($project_id, 'mp_current_stage', $current_stage);
	update_post_meta($project_id, 'mp_parent_project_id', $parent_project_id);
	update_post_meta($project_id, 'mp_aligned_project_id', $aligned_project_id);
	update_post_meta($project_id, 'mp_lead_person_id', $lead_person_id);
	update_post_meta($project_id, 'mp_lead_person_ids', $lead_person_ids);
	update_post_meta($project_id, 'mp_lead_name', $lead_name);
	update_post_meta($project_id, 'mp_lead_role', $lead_role);
	update_post_meta($project_id, 'mp_lead_image', esc_url_raw($lead_image));
	update_post_meta($project_id, 'mp_team_member_ids', $team_member_ids);
	update_post_meta($project_id, 'mp_team_members', $team_member_names);
	update_post_meta($project_id, 'mp_donors', $donors);
	update_post_meta($project_id, 'mp_updates', $updates);
	update_post_meta($project_id, 'mp_focus_area_slugs', $focus_area_slugs);
	update_post_meta($project_id, 'mp_custom_focus_areas', $custom_focus_areas);
	update_post_meta($project_id, 'mp_focus_areas', $focus_area_objects);

	clean_post_cache($project_id);
	mp_headless_trigger_build('project_settings_update:' . $project_id);

	wp_safe_redirect(
		add_query_arg(
			array(
				'page'       => 'mp-project-profile',
				'project_id' => $project_id,
				'updated'    => '1',
			),
			admin_url('admin.php')
		)
	);
	exit;
}
add_action('admin_post_mp_headless_save_project_settings', 'mp_headless_save_project_settings');

function mp_headless_render_homepage_settings_page() {
	if (! current_user_can('edit_posts')) {
		wp_die(esc_html__('You do not have permission to manage homepage settings.', 'medplatform-headless'));
	}

	$homepage_id              = mp_headless_get_homepage_post_id();
	$publications             = get_posts(array('post_type' => 'mp_publication', 'posts_per_page' => -1, 'orderby' => 'date', 'order' => 'DESC'));
	$projects                 = get_posts(array('post_type' => 'mp_project', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC'));
	$podcast_publications     = mp_headless_get_podcast_publications();
	$featured_podcast_id      = (int) get_post_meta($homepage_id, 'mp_featured_podcast_id', true);
	$featured_article_id      = (int) get_post_meta($homepage_id, 'mp_featured_article_id', true);
	$homepage_project_count   = max(0, (int) get_post_meta($homepage_id, 'mp_homepage_project_count', true));
	$homepage_project_ids     = get_post_meta($homepage_id, 'mp_homepage_project_ids', true);
	$slider_publication_ids   = get_post_meta($homepage_id, 'mp_slider_publication_ids', true);
	$latest_publication_ids   = get_post_meta($homepage_id, 'mp_latest_publication_ids', true);
	$announcement_text        = (string) get_post_meta($homepage_id, 'mp_announcement_text', true);
	$announcement_link_url    = (string) get_post_meta($homepage_id, 'mp_announcement_link_url', true);
	$announcement_link_label  = (string) get_post_meta($homepage_id, 'mp_announcement_link_label', true);
	$homepage_project_ids     = is_array($homepage_project_ids) ? array_map('intval', $homepage_project_ids) : array();
	$slider_publication_ids   = is_array($slider_publication_ids) ? array_map('intval', $slider_publication_ids) : array();
	$latest_publication_ids   = is_array($latest_publication_ids) ? array_map('intval', $latest_publication_ids) : array();
	$updated                  = isset($_GET['updated']) ? sanitize_text_field(wp_unslash($_GET['updated'])) : '';
	?>
	<div class="wrap">
		<style>
			.mp-selection-list .mp-selection-item:first-child {
				border-top: 0;
			}

			.mp-selection-list .mp-selection-item:hover {
				background: #f6f7f7;
			}

			.mp-selection-list .mp-selection-item:focus-within {
				outline: 2px solid #2271b1;
				outline-offset: -2px;
				background: #f6f7f7;
			}
		</style>
		<h1><?php esc_html_e('Homepage Settings', 'medplatform-headless'); ?></h1>
		<p><?php esc_html_e('Control the homepage editorial selections from one place. Changes save directly into the live homepage configuration.', 'medplatform-headless'); ?></p>

		<?php if ($updated === '1') : ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e('Homepage settings updated.', 'medplatform-headless'); ?></p></div>
		<?php endif; ?>

		<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
			<?php wp_nonce_field('mp_headless_save_homepage_settings', 'mp_headless_homepage_nonce'); ?>
			<input type="hidden" name="action" value="mp_headless_save_homepage_settings" />

			<div style="display:grid; gap:20px; margin-top:20px; max-width:1200px;">
				<div style="background:#fff; border:1px solid #dcdcde; padding:20px;">
					<h2 style="margin-top:0;"><?php esc_html_e('Feature Slots', 'medplatform-headless'); ?></h2>
					<div style="display:grid; gap:18px; grid-template-columns:repeat(auto-fit,minmax(280px,1fr));">
						<div>
							<label for="mp_featured_podcast_id"><strong><?php esc_html_e('Featured Podcast', 'medplatform-headless'); ?></strong></label>
							<select class="widefat" id="mp_featured_podcast_id" name="mp_featured_podcast_id" style="margin-top:8px;">
								<option value="0"><?php esc_html_e('Select a podcast', 'medplatform-headless'); ?></option>
								<?php foreach ($podcast_publications as $publication) : ?>
									<option value="<?php echo esc_attr($publication->ID); ?>" <?php selected($featured_podcast_id, (int) $publication->ID); ?>><?php echo esc_html($publication->post_title); ?></option>
								<?php endforeach; ?>
							</select>
							<p style="margin:8px 0 0; color:#646970;"><?php esc_html_e('Only publications tagged with the Pod-Cast output type appear here.', 'medplatform-headless'); ?></p>
						</div>
						<div>
							<label for="mp_featured_article_id"><strong><?php esc_html_e('Featured Article', 'medplatform-headless'); ?></strong></label>
							<select class="widefat" id="mp_featured_article_id" name="mp_featured_article_id" style="margin-top:8px;">
								<option value="0"><?php esc_html_e('Select a publication', 'medplatform-headless'); ?></option>
								<?php foreach ($publications as $publication) : ?>
									<option value="<?php echo esc_attr($publication->ID); ?>" <?php selected($featured_article_id, (int) $publication->ID); ?>><?php echo esc_html($publication->post_title); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
						<div>
							<label for="mp_homepage_project_count"><strong><?php esc_html_e('Homepage Project Count', 'medplatform-headless'); ?></strong></label>
							<input
								type="number"
								id="mp_homepage_project_count"
								name="mp_homepage_project_count"
								class="small-text"
								min="0"
								max="24"
								step="1"
								value="<?php echo esc_attr((string) $homepage_project_count); ?>"
								style="margin-top:8px;"
							/>
							<p style="margin:8px 0 0; color:#646970;"><?php esc_html_e('Sets the maximum number of homepage projects shown from the project selection list below. Use 0 to show all selected projects, or all visible projects if you leave the project list empty.', 'medplatform-headless'); ?></p>
						</div>
					</div>
				</div>

				<?php
				mp_headless_render_project_selection_list(
					array(
						'section_id'         => 'mp-homepage-projects-list',
						'field_name'         => 'mp_homepage_project_ids',
						'title'              => __('Homepage Projects', 'medplatform-headless'),
						'description'        => __('Search the project list and tick the exact projects that should appear in the homepage Projects section. Projects appear in the same order as this list, and the project count setting above acts as the display limit.', 'medplatform-headless'),
						'search_placeholder' => __('Search homepage projects...', 'medplatform-headless'),
						'projects'           => $projects,
						'selected_ids'       => $homepage_project_ids,
					)
				);
				?>

				<div style="background:#fff; border:1px solid #dcdcde; padding:20px;">
					<h2 style="margin-top:0;"><?php esc_html_e('Bottom Announcement Bar', 'medplatform-headless'); ?></h2>
					<p style="margin-top:0; color:#50575e;"><?php esc_html_e('This controls the sticky bottom announcement shown across the website. Keep the text short so it stays readable on smaller screens. Leave all three fields empty to hide the bar completely.', 'medplatform-headless'); ?></p>
					<div style="display:grid; gap:18px; grid-template-columns:repeat(auto-fit,minmax(280px,1fr));">
						<div style="grid-column:1 / -1;">
							<label for="mp_announcement_text"><strong><?php esc_html_e('Announcement Text', 'medplatform-headless'); ?></strong></label>
							<input
								type="text"
								id="mp_announcement_text"
								name="mp_announcement_text"
								class="widefat"
								maxlength="120"
								value="<?php echo esc_attr($announcement_text); ?>"
								style="margin-top:8px;"
								placeholder="<?php esc_attr_e('May 20: African Talks - South African Foreign Policy', 'medplatform-headless'); ?>"
							/>
						</div>
						<div>
							<label for="mp_announcement_link_label"><strong><?php esc_html_e('Link Label', 'medplatform-headless'); ?></strong></label>
							<input
								type="text"
								id="mp_announcement_link_label"
								name="mp_announcement_link_label"
								class="widefat"
								maxlength="28"
								value="<?php echo esc_attr($announcement_link_label); ?>"
								style="margin-top:8px;"
								placeholder="<?php esc_attr_e('Register', 'medplatform-headless'); ?>"
							/>
						</div>
						<div>
							<label for="mp_announcement_link_url"><strong><?php esc_html_e('Link URL', 'medplatform-headless'); ?></strong></label>
							<input
								type="url"
								id="mp_announcement_link_url"
								name="mp_announcement_link_url"
								class="widefat"
								value="<?php echo esc_attr($announcement_link_url); ?>"
								style="margin-top:8px;"
								placeholder="/widgets"
							/>
						</div>
					</div>
				</div>

				<?php
				mp_headless_render_publication_selection_list(
					array(
						'section_id'         => 'mp-slider-publications-list',
						'field_name'         => 'mp_slider_publication_ids',
						'title'              => __('Homepage Slider', 'medplatform-headless'),
						'description'        => __('Search the publication list and tick only the items that should appear in the homepage article slider.', 'medplatform-headless'),
						'search_placeholder' => __('Search slider publications...', 'medplatform-headless'),
						'publications'       => $publications,
						'selected_ids'       => $slider_publication_ids,
					)
				);

				mp_headless_render_publication_selection_list(
					array(
						'section_id'         => 'mp-latest-publications-list',
						'field_name'         => 'mp_latest_publication_ids',
						'title'              => __('Latest Publications Section', 'medplatform-headless'),
						'description'        => __('Search the publication list and tick the items that should appear in the homepage latest publications area. Up to 5 selected publications will be shown on the frontend.', 'medplatform-headless'),
						'search_placeholder' => __('Search latest publications...', 'medplatform-headless'),
						'publications'       => $publications,
						'selected_ids'       => $latest_publication_ids,
					)
				);
				?>
			</div>

			<?php submit_button(__('Save Homepage Settings', 'medplatform-headless')); ?>
		</form>
	</div>
	<?php
}

function mp_headless_render_site_settings_page() {
	if (! current_user_can('edit_posts')) {
		wp_die(esc_html__('You do not have permission to manage site settings.', 'medplatform-headless'));
	}

	$site_settings                = mp_headless_get_site_settings_payload();
	$linkedin_url                 = (string) ($site_settings['socialLinks']['linkedin'] ?? '');
	$youtube_url                  = (string) ($site_settings['socialLinks']['youtube'] ?? '');
	$instagram_url                = (string) ($site_settings['socialLinks']['instagram'] ?? '');
	$show_public_download_counts  = ! empty($site_settings['showPublicDownloadCounts']);
	$updated                      = isset($_GET['updated']) ? sanitize_text_field(wp_unslash($_GET['updated'])) : '';
	?>
	<div class="wrap">
		<h1><?php esc_html_e('Site Settings', 'medplatform-headless'); ?></h1>
		<p><?php esc_html_e('Control the shared frontend settings used across navigation, footer links, and publication download displays.', 'medplatform-headless'); ?></p>

		<?php if ($updated === '1') : ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e('Site settings updated.', 'medplatform-headless'); ?></p></div>
		<?php endif; ?>

		<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
			<?php wp_nonce_field('mp_headless_save_site_settings', 'mp_headless_site_settings_nonce'); ?>
			<input type="hidden" name="action" value="mp_headless_save_site_settings" />

			<div style="display:grid; gap:20px; margin-top:20px; max-width:1100px;">
				<div style="background:#fff; border:1px solid #dcdcde; padding:20px;">
					<h2 style="margin-top:0;"><?php esc_html_e('Social Links', 'medplatform-headless'); ?></h2>
					<p style="margin-top:0; color:#50575e;"><?php esc_html_e('These links control the social icons in the top-right main menu and the social links in the footer. Leave any field empty to hide that platform across the frontend.', 'medplatform-headless'); ?></p>
					<div style="display:grid; gap:18px; grid-template-columns:repeat(auto-fit,minmax(280px,1fr));">
						<div>
							<label for="mp_site_linkedin_url"><strong><?php esc_html_e('LinkedIn URL', 'medplatform-headless'); ?></strong></label>
							<input type="url" id="mp_site_linkedin_url" name="mp_site_linkedin_url" class="widefat" value="<?php echo esc_attr($linkedin_url); ?>" style="margin-top:8px;" placeholder="https://www.linkedin.com/company/..." />
						</div>
						<div>
							<label for="mp_site_youtube_url"><strong><?php esc_html_e('YouTube URL', 'medplatform-headless'); ?></strong></label>
							<input type="url" id="mp_site_youtube_url" name="mp_site_youtube_url" class="widefat" value="<?php echo esc_attr($youtube_url); ?>" style="margin-top:8px;" placeholder="https://www.youtube.com/..." />
						</div>
						<div>
							<label for="mp_site_instagram_url"><strong><?php esc_html_e('Instagram URL', 'medplatform-headless'); ?></strong></label>
							<input type="url" id="mp_site_instagram_url" name="mp_site_instagram_url" class="widefat" value="<?php echo esc_attr($instagram_url); ?>" style="margin-top:8px;" placeholder="https://www.instagram.com/..." />
						</div>
					</div>
				</div>

				<div style="background:#fff; border:1px solid #dcdcde; padding:20px;">
					<h2 style="margin-top:0;"><?php esc_html_e('Download Visibility', 'medplatform-headless'); ?></h2>
					<p style="margin-top:0; color:#50575e;"><?php esc_html_e('Control whether publication download counts are shown publicly next to downloadable files on the frontend.', 'medplatform-headless'); ?></p>
					<p style="margin-bottom:0;">
						<label>
							<input type="checkbox" name="mp_show_public_download_counts" value="1" <?php checked($show_public_download_counts); ?> />
							<?php esc_html_e('Show download counts publicly across the website', 'medplatform-headless'); ?>
						</label>
					</p>
					<p style="margin:8px 0 0; color:#646970;"><?php esc_html_e('WordPress will keep tracking downloads either way. This setting only controls whether the count is visible to visitors.', 'medplatform-headless'); ?></p>
				</div>
			</div>

			<?php submit_button(__('Save Site Settings', 'medplatform-headless')); ?>
		</form>
	</div>
	<?php
}

function mp_headless_register_settings_page() {
	add_submenu_page(
		'edit.php?post_type=mp_publication',
		__('Add Pod-Cast', 'medplatform-headless'),
		__('Add Pod-Cast', 'medplatform-headless'),
		'edit_posts',
		'mp-add-podcast',
		'__return_null'
	);

	add_submenu_page(
		'edit.php?post_type=mp_person',
		__('Add Person', 'medplatform-headless'),
		__('Add Person', 'medplatform-headless'),
		'edit_posts',
		'mp-person-profile',
		'mp_headless_render_person_settings_page'
	);

	add_submenu_page(
		'edit.php?post_type=mp_project',
		__('Add Project', 'medplatform-headless'),
		__('Add Project', 'medplatform-headless'),
		'edit_posts',
		'mp-project-profile',
		'mp_headless_render_project_settings_page'
	);

	add_menu_page(
		__('Homepage Settings', 'medplatform-headless'),
		__('Homepage Settings', 'medplatform-headless'),
		'edit_posts',
		'mp-homepage-settings',
		'mp_headless_render_homepage_settings_page',
		'dashicons-admin-home',
		21
	);

	add_menu_page(
		__('Site Settings', 'medplatform-headless'),
		__('Site Settings', 'medplatform-headless'),
		'edit_posts',
		'mp-site-settings',
		'mp_headless_render_site_settings_page',
		'dashicons-admin-generic',
		22
	);

	add_options_page(
		__('Med Platform Headless', 'medplatform-headless'),
		__('Med Platform Headless', 'medplatform-headless'),
		'manage_options',
		'medplatform-headless',
		'mp_headless_render_settings_page'
	);
}
add_action('admin_menu', 'mp_headless_register_settings_page');

function mp_headless_customize_people_menu() {
	remove_submenu_page('edit.php?post_type=mp_person', 'post-new.php?post_type=mp_person');
	remove_submenu_page('edit.php?post_type=mp_project', 'post-new.php?post_type=mp_project');
}
add_action('admin_menu', 'mp_headless_customize_people_menu', 30);

function mp_headless_customize_admin_bar($wp_admin_bar) {
	if (! is_admin_bar_showing()) {
		return;
	}

	$frontend_url = mp_headless_get_frontend_url('/');

	foreach (array('site-name', 'view-site') as $node_id) {
		$node = $wp_admin_bar->get_node($node_id);
		if (! $node) {
			continue;
		}

		$node->href = $frontend_url;
		$wp_admin_bar->add_node((array) $node);
	}
}
add_action('admin_bar_menu', 'mp_headless_customize_admin_bar', 90);

function mp_headless_save_homepage_settings() {
	if (! current_user_can('edit_posts')) {
		wp_die(esc_html__('You do not have permission to manage homepage settings.', 'medplatform-headless'));
	}

	check_admin_referer('mp_headless_save_homepage_settings', 'mp_headless_homepage_nonce');

	$homepage_id = mp_headless_get_homepage_post_id();
	if (! $homepage_id) {
		wp_die(esc_html__('Homepage Settings could not be loaded.', 'medplatform-headless'));
	}

	$featured_podcast_id = intval($_POST['mp_featured_podcast_id'] ?? 0);
	update_post_meta($homepage_id, 'mp_featured_podcast_id', mp_headless_publication_is_podcast($featured_podcast_id) ? $featured_podcast_id : 0);
	update_post_meta($homepage_id, 'mp_featured_article_id', intval($_POST['mp_featured_article_id'] ?? 0));
	update_post_meta($homepage_id, 'mp_homepage_project_count', mp_headless_sanitize_nonnegative_int_value($_POST['mp_homepage_project_count'] ?? 0));
	update_post_meta($homepage_id, 'mp_homepage_project_ids', mp_headless_sanitize_int_array(wp_unslash($_POST['mp_homepage_project_ids'] ?? array())));
	update_post_meta($homepage_id, 'mp_slider_publication_ids', mp_headless_sanitize_int_array(wp_unslash($_POST['mp_slider_publication_ids'] ?? array())));
	update_post_meta($homepage_id, 'mp_latest_publication_ids', array_slice(mp_headless_sanitize_int_array(wp_unslash($_POST['mp_latest_publication_ids'] ?? array())), 0, 5));
	update_post_meta($homepage_id, 'mp_announcement_text', sanitize_text_field(wp_unslash($_POST['mp_announcement_text'] ?? '')));
	update_post_meta($homepage_id, 'mp_announcement_link_url', esc_url_raw(wp_unslash($_POST['mp_announcement_link_url'] ?? '')));
	update_post_meta($homepage_id, 'mp_announcement_link_label', sanitize_text_field(wp_unslash($_POST['mp_announcement_link_label'] ?? '')));

	clean_post_cache($homepage_id);
	mp_headless_trigger_build('homepage_settings_update:' . $homepage_id);

	wp_safe_redirect(admin_url('admin.php?page=mp-homepage-settings&updated=1'));
	exit;
}
add_action('admin_post_mp_headless_save_homepage_settings', 'mp_headless_save_homepage_settings');

function mp_headless_save_site_settings() {
	if (! current_user_can('edit_posts')) {
		wp_die(esc_html__('You do not have permission to manage site settings.', 'medplatform-headless'));
	}

	check_admin_referer('mp_headless_save_site_settings', 'mp_headless_site_settings_nonce');

	update_option('mp_headless_site_linkedin_url', esc_url_raw(wp_unslash($_POST['mp_site_linkedin_url'] ?? '')));
	update_option('mp_headless_site_youtube_url', esc_url_raw(wp_unslash($_POST['mp_site_youtube_url'] ?? '')));
	update_option('mp_headless_site_instagram_url', esc_url_raw(wp_unslash($_POST['mp_site_instagram_url'] ?? '')));
	update_option('mp_headless_show_public_download_counts', ! empty($_POST['mp_show_public_download_counts']));

	mp_headless_trigger_build('site_settings_update');

	wp_safe_redirect(admin_url('admin.php?page=mp-site-settings&updated=1'));
	exit;
}
add_action('admin_post_mp_headless_save_site_settings', 'mp_headless_save_site_settings');

function mp_headless_import_seed_content() {
	if (! current_user_can('manage_options')) {
		wp_die(esc_html__('You do not have permission to import the bundled seed content.', 'medplatform-headless'));
	}

	check_admin_referer('mp_headless_import_seed_content', 'mp_headless_seed_import_nonce');

	$GLOBALS['mp_headless_suspend_build_trigger'] = true;
	$result = mp_headless_import_bundled_seed_content();
	$GLOBALS['mp_headless_suspend_build_trigger'] = false;

	if (is_wp_error($result)) {
		mp_headless_set_seed_import_status('error', $result->get_error_message());
		wp_safe_redirect(admin_url('options-general.php?page=medplatform-headless'));
		exit;
	}

	$counts = is_array($result) ? $result : array();
	mp_headless_set_seed_import_status('success', __('Bundled seed content imported successfully. A frontend rebuild has been triggered.', 'medplatform-headless'), $counts);
	mp_headless_trigger_build('seed_import');

	wp_safe_redirect(admin_url('options-general.php?page=medplatform-headless'));
	exit;
}
add_action('admin_post_mp_headless_import_seed_content', 'mp_headless_import_seed_content');

function mp_headless_manual_push_update() {
	if (! current_user_can('manage_options')) {
		wp_die(esc_html__('You do not have permission to push a manual frontend update.', 'medplatform-headless'));
	}

	check_admin_referer('mp_headless_manual_push_update', 'mp_headless_manual_push_nonce');

	mp_headless_trigger_build('manual_push_update', true);

	wp_safe_redirect(admin_url('options-general.php?page=medplatform-headless'));
	exit;
}
add_action('admin_post_mp_headless_manual_push_update', 'mp_headless_manual_push_update');

function mp_headless_redirect_homepage_admin_views() {
	if (! is_admin()) {
		return;
	}

	$homepage_id = mp_headless_get_homepage_post_id();
	if (! $homepage_id) {
		return;
	}

	$post_type = isset($_GET['post_type']) ? sanitize_key(wp_unslash($_GET['post_type'])) : '';
	$page      = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';

	if ($page === 'mp-homepage-settings' || $page === 'mp-person-profile' || $page === 'mp-project-profile') {
		return;
	}

	if ($page === 'mp-add-podcast') {
		wp_safe_redirect(admin_url('post-new.php?post_type=mp_publication&mp_publication_mode=podcast'));
		exit;
	}

	if (strpos($_SERVER['REQUEST_URI'] ?? '', 'post-new.php') !== false && $post_type === 'mp_homepage') {
		wp_safe_redirect(admin_url('admin.php?page=mp-homepage-settings'));
		exit;
	}

	if (strpos($_SERVER['REQUEST_URI'] ?? '', 'post-new.php') !== false && $post_type === 'mp_person') {
		wp_safe_redirect(mp_headless_get_person_admin_url());
		exit;
	}

	if (strpos($_SERVER['REQUEST_URI'] ?? '', 'post-new.php') !== false && $post_type === 'mp_project') {
		wp_safe_redirect(mp_headless_get_project_admin_url());
		exit;
	}

	if (strpos($_SERVER['REQUEST_URI'] ?? '', 'edit.php') !== false && $post_type === 'mp_homepage') {
		wp_safe_redirect(admin_url('admin.php?page=mp-homepage-settings'));
		exit;
	}

	$post_id = isset($_GET['post']) ? intval($_GET['post']) : 0;
	if ($post_id > 0 && get_post_type($post_id) === 'mp_person') {
		wp_safe_redirect(mp_headless_get_person_admin_url($post_id));
		exit;
	}

	if ($post_id > 0 && get_post_type($post_id) === 'mp_project') {
		wp_safe_redirect(mp_headless_get_project_admin_url($post_id));
		exit;
	}

	if ($post_id > 0 && get_post_type($post_id) === 'mp_homepage') {
		wp_safe_redirect(admin_url('admin.php?page=mp-homepage-settings'));
		exit;
	}
}
add_action('admin_init', 'mp_headless_redirect_homepage_admin_views');

function mp_headless_render_add_podcast_action_button() {
	$screen = get_current_screen();

	if (! $screen || $screen->id !== 'edit-mp_publication') {
		return;
	}

	$podcast_url   = admin_url('edit.php?post_type=mp_publication&page=mp-add-podcast');
	$podcast_label = __('Add Pod-Cast', 'medplatform-headless');
	?>
	<script>
		document.addEventListener("DOMContentLoaded", () => {
			const addAction = document.querySelector(".page-title-action");
			if (!(addAction instanceof HTMLElement) || document.querySelector(".mp-add-podcast-action")) {
				return;
			}

			const button = document.createElement("a");
			button.href = <?php echo wp_json_encode($podcast_url); ?>;
			button.className = "page-title-action mp-add-podcast-action";
			button.textContent = <?php echo wp_json_encode($podcast_label); ?>;
			addAction.insertAdjacentElement("afterend", button);
		});
	</script>
	<?php
}
add_action('admin_print_footer_scripts-edit.php', 'mp_headless_render_add_podcast_action_button');

function mp_headless_render_people_action_button() {
	$screen = get_current_screen();

	if (! $screen || $screen->id !== 'edit-mp_person') {
		return;
	}

	$person_url   = mp_headless_get_person_admin_url();
	$person_label = __('Add Person', 'medplatform-headless');
	?>
	<script>
		document.addEventListener("DOMContentLoaded", () => {
			const addAction = document.querySelector(".page-title-action");
			if (!(addAction instanceof HTMLAnchorElement)) {
				return;
			}

			addAction.href = <?php echo wp_json_encode($person_url); ?>;
			addAction.textContent = <?php echo wp_json_encode($person_label); ?>;
		});
	</script>
	<?php
}
add_action('admin_print_footer_scripts-edit.php', 'mp_headless_render_people_action_button');

function mp_headless_render_projects_action_button() {
	$screen = get_current_screen();

	if (! $screen || $screen->id !== 'edit-mp_project') {
		return;
	}

	$project_url   = mp_headless_get_project_admin_url();
	$project_label = __('Add Project', 'medplatform-headless');
	?>
	<script>
		document.addEventListener("DOMContentLoaded", () => {
			const addAction = document.querySelector(".page-title-action");
			if (!(addAction instanceof HTMLAnchorElement)) {
				return;
			}

			addAction.href = <?php echo wp_json_encode($project_url); ?>;
			addAction.textContent = <?php echo wp_json_encode($project_label); ?>;
		});
	</script>
	<?php
}
add_action('admin_print_footer_scripts-edit.php', 'mp_headless_render_projects_action_button');

function mp_headless_customize_person_row_actions($actions, $post) {
	if (! in_array($post->post_type, array('mp_publication', 'mp_person', 'mp_project'), true)) {
		return $actions;
	}

	if (isset($actions['edit'])) {
		if ($post->post_type === 'mp_person' || $post->post_type === 'mp_project') {
			$edit_url = $post->post_type === 'mp_person'
				? mp_headless_get_person_admin_url($post->ID)
				: mp_headless_get_project_admin_url($post->ID);
			$actions['edit'] = sprintf(
				'<a href="%s">%s</a>',
				esc_url($edit_url),
				esc_html__('Edit', 'medplatform-headless')
			);
		}
	}

	$frontend_url = mp_headless_get_frontend_post_url($post);
	if (isset($actions['view'])) {
		$actions['view'] = sprintf(
			'<a href="%s" rel="bookmark">%s</a>',
			esc_url($frontend_url),
			esc_html__('View', 'medplatform-headless')
		);
	}

	if (isset($actions['preview']) && $post->post_status === 'publish') {
		$actions['preview'] = sprintf(
			'<a href="%s" rel="bookmark">%s</a>',
			esc_url($frontend_url),
			esc_html__('Preview', 'medplatform-headless')
		);
	}

	unset($actions['inline hide-if-no-js']);

	return $actions;
}
add_filter('post_row_actions', 'mp_headless_customize_person_row_actions', 10, 2);

function mp_headless_register_settings() {
	register_setting(
		'mp_headless_settings',
		'mp_headless_frontend_url',
		array(
			'type'              => 'string',
			'sanitize_callback' => 'esc_url_raw',
			'default'           => '',
		)
	);

	register_setting(
		'mp_headless_settings',
		'mp_headless_build_hook_url',
		array(
			'type'              => 'string',
			'sanitize_callback' => 'esc_url_raw',
			'default'           => '',
		)
	);

	register_setting(
		'mp_headless_settings',
		'mp_headless_github_repo_owner',
		array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'default'           => '',
		)
	);

	register_setting(
		'mp_headless_settings',
		'mp_headless_github_repo_name',
		array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'default'           => '',
		)
	);

	register_setting(
		'mp_headless_settings',
		'mp_headless_github_branch',
		array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'default'           => 'main',
		)
	);

	register_setting(
		'mp_headless_settings',
		'mp_headless_github_token',
		array(
			'type'              => 'string',
			'sanitize_callback' => 'mp_headless_sanitize_secret_value',
			'default'           => '',
		)
	);

	register_setting(
		'mp_headless_settings',
		'mp_headless_github_trigger_path',
		array(
			'type'              => 'string',
			'sanitize_callback' => 'mp_headless_sanitize_repo_path_value',
			'default'           => '.hostinger/deploy-trigger.json',
		)
	);

	register_setting(
		'mp_headless_settings',
		'mp_headless_github_auto_push_enabled',
		array(
			'type'              => 'boolean',
			'sanitize_callback' => 'mp_headless_sanitize_bool_value',
			'default'           => true,
		)
	);
}
add_action('admin_init', 'mp_headless_register_settings');

function mp_headless_render_settings_page() {
	$github_repo_owner  = (string) get_option('mp_headless_github_repo_owner', '');
	$github_repo_name   = (string) get_option('mp_headless_github_repo_name', '');
	$github_branch      = (string) get_option('mp_headless_github_branch', 'main');
	$github_token       = (string) get_option('mp_headless_github_token', '');
	$github_trigger_path = (string) get_option('mp_headless_github_trigger_path', '.hostinger/deploy-trigger.json');
	$github_auto_push_enabled = mp_headless_github_auto_push_enabled();
	$github_last_status = get_option('mp_headless_github_last_status', array());
	$seed_import_status = get_transient('mp_headless_seed_import_status');
	if ($seed_import_status) {
		delete_transient('mp_headless_seed_import_status');
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e('Med Platform Headless', 'medplatform-headless'); ?></h1>
		<?php if (is_array($seed_import_status) && ! empty($seed_import_status['message'])) : ?>
			<?php $seed_notice_class = (($seed_import_status['status'] ?? '') === 'success') ? 'notice notice-success is-dismissible' : 'notice notice-error'; ?>
			<div class="<?php echo esc_attr($seed_notice_class); ?>">
				<p><?php echo esc_html((string) $seed_import_status['message']); ?></p>
				<?php if (! empty($seed_import_status['counts']) && is_array($seed_import_status['counts'])) : ?>
					<p style="margin-top:0;">
						<?php
						echo esc_html(
							sprintf(
								/* translators: 1: people count, 2: project count, 3: publication count */
								__('Imported %1$s people, %2$s projects, and %3$s publications.', 'medplatform-headless'),
								number_format_i18n((int) ($seed_import_status['counts']['people'] ?? 0)),
								number_format_i18n((int) ($seed_import_status['counts']['projects'] ?? 0)),
								number_format_i18n((int) ($seed_import_status['counts']['publications'] ?? 0))
							)
						);
						?>
					</p>
				<?php endif; ?>
			</div>
		<?php endif; ?>
		<form action="options.php" method="post">
			<?php settings_fields('mp_headless_settings'); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="mp_headless_frontend_url"><?php esc_html_e('Frontend Site URL', 'medplatform-headless'); ?></label></th>
					<td>
						<input id="mp_headless_frontend_url" type="url" class="regular-text code" name="mp_headless_frontend_url" value="<?php echo esc_attr(get_option('mp_headless_frontend_url', '')); ?>" />
						<p class="description"><?php esc_html_e('Optional. When set, admin "Visit site" links and public WordPress redirects will use this Astro frontend URL. Leave empty to use the local Astro URL on localhost and the production frontend URL elsewhere.', 'medplatform-headless'); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="mp_headless_build_hook_url"><?php esc_html_e('Build Hook URL', 'medplatform-headless'); ?></label></th>
					<td>
						<input id="mp_headless_build_hook_url" type="url" class="regular-text code" name="mp_headless_build_hook_url" value="<?php echo esc_attr(get_option('mp_headless_build_hook_url', '')); ?>" />
						<p class="description"><?php esc_html_e('Optional. When set, WordPress will POST to this URL after people, project, publication, homepage, or taxonomy changes.', 'medplatform-headless'); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row" colspan="2" style="padding-bottom:0;">
						<h2 style="margin:0;"><?php esc_html_e('GitHub Auto Deploy', 'medplatform-headless'); ?></h2>
						<p class="description" style="margin:6px 0 0;"><?php esc_html_e('Optional. When configured, WordPress will commit a tiny deploy-trigger file to GitHub after CMS changes. Hostinger auto-deployment will then redeploy the frontend from the updated branch.', 'medplatform-headless'); ?></p>
					</th>
				</tr>
				<tr>
					<th scope="row"><label for="mp_headless_github_repo_owner"><?php esc_html_e('GitHub Repo Owner', 'medplatform-headless'); ?></label></th>
					<td>
						<input id="mp_headless_github_repo_owner" type="text" class="regular-text code" name="mp_headless_github_repo_owner" value="<?php echo esc_attr($github_repo_owner); ?>" placeholder="sufiancool-jpg" />
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="mp_headless_github_repo_name"><?php esc_html_e('GitHub Repo Name', 'medplatform-headless'); ?></label></th>
					<td>
						<input id="mp_headless_github_repo_name" type="text" class="regular-text code" name="mp_headless_github_repo_name" value="<?php echo esc_attr($github_repo_name); ?>" placeholder="Med" />
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="mp_headless_github_branch"><?php esc_html_e('GitHub Branch', 'medplatform-headless'); ?></label></th>
					<td>
						<input id="mp_headless_github_branch" type="text" class="regular-text code" name="mp_headless_github_branch" value="<?php echo esc_attr($github_branch); ?>" placeholder="main" />
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="mp_headless_github_token"><?php esc_html_e('GitHub Token', 'medplatform-headless'); ?></label></th>
					<td>
						<input id="mp_headless_github_token" type="password" class="regular-text code" name="mp_headless_github_token" value="<?php echo esc_attr($github_token); ?>" autocomplete="new-password" />
						<p class="description"><?php esc_html_e('Use a GitHub fine-grained personal access token with Contents read/write access to this repository.', 'medplatform-headless'); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="mp_headless_github_trigger_path"><?php esc_html_e('Trigger File Path', 'medplatform-headless'); ?></label></th>
					<td>
						<input id="mp_headless_github_trigger_path" type="text" class="regular-text code" name="mp_headless_github_trigger_path" value="<?php echo esc_attr($github_trigger_path); ?>" placeholder=".hostinger/deploy-trigger.json" />
						<p class="description"><?php esc_html_e('WordPress updates this file in GitHub to force a Hostinger redeploy. Leave the default unless you have a reason to change it.', 'medplatform-headless'); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e('Automatic GitHub Pushes', 'medplatform-headless'); ?></th>
					<td>
						<input type="hidden" name="mp_headless_github_auto_push_enabled" value="0" />
						<label>
							<input type="checkbox" name="mp_headless_github_auto_push_enabled" value="1" <?php checked($github_auto_push_enabled); ?> />
							<?php esc_html_e('Automatically push GitHub deploy updates after CMS changes', 'medplatform-headless'); ?>
						</label>
						<p class="description"><?php esc_html_e('Turn this off if you want WordPress content changes to wait until you manually click Push Update.', 'medplatform-headless'); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e('Last GitHub Trigger', 'medplatform-headless'); ?></th>
					<td>
						<?php if (is_array($github_last_status) && ! empty($github_last_status['timestamp'])) : ?>
							<p style="margin:0 0 6px;"><strong><?php echo esc_html(ucfirst((string) ($github_last_status['status'] ?? 'unknown'))); ?></strong></p>
							<p style="margin:0 0 6px;"><?php echo esc_html((string) ($github_last_status['message'] ?? '')); ?></p>
							<p style="margin:0; color:#646970;">
								<?php
								echo esc_html(
									sprintf(
										/* translators: 1: reason, 2: datetime */
										__('Reason: %1$s | Time: %2$s', 'medplatform-headless'),
										(string) ($github_last_status['reason'] ?? 'n/a'),
										(string) ($github_last_status['updated_at'] ?? '')
									)
								);
								?>
							</p>
						<?php else : ?>
							<p style="margin:0; color:#646970;"><?php esc_html_e('No GitHub auto-deploy attempt has been recorded yet.', 'medplatform-headless'); ?></p>
						<?php endif; ?>
					</td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>

		<div style="margin-top:28px; max-width:900px; background:#fff; border:1px solid #dcdcde; padding:24px;">
			<h2 style="margin-top:0;"><?php esc_html_e('Manual Push Update', 'medplatform-headless'); ?></h2>
			<p style="margin-top:0; color:#50575e;"><?php esc_html_e('Use this to push a fresh deploy-trigger commit to GitHub whenever you want to redeploy the frontend manually. This works even when automatic GitHub pushes are turned off.', 'medplatform-headless'); ?></p>
			<form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
				<?php wp_nonce_field('mp_headless_manual_push_update', 'mp_headless_manual_push_nonce'); ?>
				<input type="hidden" name="action" value="mp_headless_manual_push_update" />
				<?php submit_button(__('Push Update', 'medplatform-headless'), 'secondary', 'submit', false); ?>
			</form>
		</div>

		<div style="margin-top:28px; max-width:900px; background:#fff; border:1px solid #dcdcde; padding:24px;">
			<h2 style="margin-top:0;"><?php esc_html_e('Bundled Seed Import', 'medplatform-headless'); ?></h2>
			<p style="margin-top:0; color:#50575e;"><?php esc_html_e('Import the existing team, project, publication, and homepage content that already ships with this repo. Records are matched by slug, so existing matching entries are updated and any extra CMS content stays untouched.', 'medplatform-headless'); ?></p>
			<p style="margin:0 0 18px; color:#50575e;"><?php esc_html_e('Use this once on a fresh CMS to bring over the current website data, then keep editing from WordPress afterward.', 'medplatform-headless'); ?></p>
			<form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
				<?php wp_nonce_field('mp_headless_import_seed_content', 'mp_headless_seed_import_nonce'); ?>
				<input type="hidden" name="action" value="mp_headless_import_seed_content" />
				<?php submit_button(__('Import Bundled Seed Content', 'medplatform-headless'), 'primary', 'submit', false); ?>
			</form>
		</div>
	</div>
	<?php
}

function mp_headless_register_rest_routes() {
	register_rest_route(
		'mp-headless/v1',
		'/site-settings',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => function() {
				return rest_ensure_response(mp_headless_get_site_settings_payload());
			},
			'permission_callback' => '__return_true',
		)
	);

	register_rest_route(
		'mp-headless/v1',
		'/publications/(?P<id>\d+)/download-stats',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => function(WP_REST_Request $request) {
				$publication_id = (int) $request->get_param('id');
				$publication    = get_post($publication_id);

				if (! $publication instanceof WP_Post || $publication->post_type !== 'mp_publication' || $publication->post_status !== 'publish') {
					return new WP_Error('mp_headless_not_found', __('Publication not found.', 'medplatform-headless'), array('status' => 404));
				}

				return rest_ensure_response(mp_headless_get_publication_download_stats_payload($publication_id));
			},
			'permission_callback' => '__return_true',
		)
	);
}
add_action('rest_api_init', 'mp_headless_register_rest_routes');

function mp_headless_handle_publication_download() {
	$publication_id = intval($_GET['publication_id'] ?? 0);
	$fallback_url   = mp_headless_get_frontend_url('/blog');

	if ($publication_id < 1) {
		wp_safe_redirect($fallback_url);
		exit;
	}

	$publication = get_post($publication_id);
	if (! $publication instanceof WP_Post || $publication->post_type !== 'mp_publication' || $publication->post_status !== 'publish') {
		wp_safe_redirect($fallback_url);
		exit;
	}

	if (mp_headless_publication_is_podcast($publication_id)) {
		wp_safe_redirect(mp_headless_get_frontend_post_url($publication));
		exit;
	}

	$download_url = trim((string) get_post_meta($publication_id, 'mp_download_url', true));
	if ($download_url === '') {
		wp_safe_redirect(mp_headless_get_frontend_post_url($publication));
		exit;
	}

	if (! preg_match('#^https?://#i', $download_url)) {
		$download_url = home_url('/' . ltrim($download_url, '/'));
	}

	mp_headless_increment_publication_download_count($publication_id);
	wp_redirect($download_url);
	exit;
}
add_action('admin_post_nopriv_mp_headless_track_publication_download', 'mp_headless_handle_publication_download');
add_action('admin_post_mp_headless_track_publication_download', 'mp_headless_handle_publication_download');

function mp_headless_get_download_dashboard_stats() {
	$publication_ids = get_posts(
		array(
			'post_type'      => 'mp_publication',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		)
	);

	$total_downloads    = 0;
	$top_publication_id = 0;
	$top_download_count = 0;

	foreach ($publication_ids as $publication_id) {
		$count            = mp_headless_get_publication_download_count($publication_id);
		$total_downloads += $count;

		if ($count > $top_download_count) {
			$top_download_count = $count;
			$top_publication_id = (int) $publication_id;
		}
	}

	return array(
		'total_downloads'    => $total_downloads,
		'top_publication_id' => $top_publication_id,
		'top_download_count' => $top_download_count,
	);
}

function mp_headless_render_download_dashboard_widget() {
	$stats              = mp_headless_get_download_dashboard_stats();
	$total_downloads    = (int) ($stats['total_downloads'] ?? 0);
	$top_publication_id = (int) ($stats['top_publication_id'] ?? 0);
	$top_download_count = (int) ($stats['top_download_count'] ?? 0);
	$top_publication    = $top_publication_id > 0 ? get_post($top_publication_id) : null;
	?>
	<div style="display:grid; gap:14px;">
		<div style="border:1px solid #dcdcde; background:#fff; padding:14px;">
			<p style="margin:0; font-size:12px; font-weight:600; letter-spacing:.08em; text-transform:uppercase; color:#50575e;"><?php esc_html_e('Total Downloaded Files', 'medplatform-headless'); ?></p>
			<p style="margin:8px 0 0; font-size:28px; font-weight:700; line-height:1;"><?php echo esc_html(number_format_i18n($total_downloads)); ?></p>
		</div>
		<div style="border:1px solid #dcdcde; background:#fff; padding:14px;">
			<p style="margin:0; font-size:12px; font-weight:600; letter-spacing:.08em; text-transform:uppercase; color:#50575e;"><?php esc_html_e('Highest Downloaded Publication', 'medplatform-headless'); ?></p>
			<?php if ($top_publication instanceof WP_Post && $top_download_count > 0) : ?>
				<p style="margin:8px 0 0; font-size:18px; font-weight:600; line-height:1.35;">
					<a href="<?php echo esc_url(get_edit_post_link($top_publication->ID)); ?>"><?php echo esc_html($top_publication->post_title); ?></a>
				</p>
				<p style="margin:6px 0 0; color:#50575e;"><?php echo esc_html(sprintf(_n('%s download', '%s downloads', $top_download_count, 'medplatform-headless'), number_format_i18n($top_download_count))); ?></p>
			<?php else : ?>
				<p style="margin:8px 0 0; color:#50575e;"><?php esc_html_e('No publication downloads recorded yet.', 'medplatform-headless'); ?></p>
			<?php endif; ?>
		</div>
	</div>
	<?php
}

function mp_headless_register_dashboard_widgets() {
	if (! current_user_can('edit_posts')) {
		return;
	}

	wp_add_dashboard_widget(
		'mp_headless_download_stats',
		__('Publication Download Stats', 'medplatform-headless'),
		'mp_headless_render_download_dashboard_widget'
	);
}
add_action('wp_dashboard_setup', 'mp_headless_register_dashboard_widgets');

function mp_headless_trigger_build($reason, $force_github_push = false) {
	if (! empty($GLOBALS['mp_headless_suspend_build_trigger'])) {
		return;
	}

	$hook = get_option('mp_headless_build_hook_url', '');
	if ($hook) {
		wp_remote_post(
			$hook,
			array(
				'timeout' => 5,
				'headers' => array(
					'Content-Type' => 'application/json',
				),
				'body'    => wp_json_encode(
					array(
						'reason'    => $reason,
						'site'      => mp_headless_get_frontend_url('/'),
						'timestamp' => time(),
					)
				),
			)
		);
	}

	mp_headless_trigger_github_deploy($reason, $force_github_push);
}

function mp_headless_get_github_deploy_settings() {
	return array(
		'owner'        => trim((string) get_option('mp_headless_github_repo_owner', '')),
		'repo'         => trim((string) get_option('mp_headless_github_repo_name', '')),
		'branch'       => trim((string) get_option('mp_headless_github_branch', 'main')) ?: 'main',
		'token'        => trim((string) get_option('mp_headless_github_token', '')),
		'trigger_path' => trim((string) get_option('mp_headless_github_trigger_path', '.hostinger/deploy-trigger.json')) ?: '.hostinger/deploy-trigger.json',
	);
}

function mp_headless_trigger_github_deploy($reason, $force = false) {
	$settings = mp_headless_get_github_deploy_settings();

	if (! $force && ! mp_headless_github_auto_push_enabled()) {
		mp_headless_set_github_deploy_status('skipped', 'Automatic GitHub pushes are disabled. Use Push Update to redeploy manually.', $reason);
		return;
	}

	if ($settings['owner'] === '' || $settings['repo'] === '' || $settings['token'] === '') {
		mp_headless_set_github_deploy_status('skipped', 'GitHub auto deploy is not fully configured yet.', $reason);
		return;
	}

	$last_trigger_at = (int) get_option('mp_headless_github_last_trigger_at', 0);
	if ($last_trigger_at > 0 && (time() - $last_trigger_at) < 30) {
		mp_headless_set_github_deploy_status('skipped', 'GitHub auto deploy was throttled to avoid duplicate commits within 30 seconds.', $reason);
		return;
	}

	$encoded_path_parts = array_map('rawurlencode', explode('/', $settings['trigger_path']));
	$contents_url       = sprintf(
		'https://api.github.com/repos/%s/%s/contents/%s',
		rawurlencode($settings['owner']),
		rawurlencode($settings['repo']),
		implode('/', $encoded_path_parts)
	);
	$headers            = array(
		'Accept'               => 'application/vnd.github+json',
		'Authorization'        => 'Bearer ' . $settings['token'],
		'X-GitHub-Api-Version' => '2022-11-28',
		'User-Agent'           => 'Med-Platform-Headless/0.1.3',
	);
	$existing_sha       = '';
	$lookup_response    = wp_remote_get(
		add_query_arg('ref', $settings['branch'], $contents_url),
		array(
			'timeout' => 15,
			'headers' => $headers,
		)
	);

	if (is_wp_error($lookup_response)) {
		mp_headless_set_github_deploy_status('error', 'GitHub lookup failed: ' . $lookup_response->get_error_message(), $reason);
		error_log('Med Platform Headless GitHub deploy lookup failed: ' . $lookup_response->get_error_message());
		return;
	}

	$lookup_status = (int) wp_remote_retrieve_response_code($lookup_response);
	if ($lookup_status === 200) {
		$lookup_body = json_decode(wp_remote_retrieve_body($lookup_response), true);
		$existing_sha = isset($lookup_body['sha']) ? sanitize_text_field((string) $lookup_body['sha']) : '';
	} elseif ($lookup_status !== 404) {
		mp_headless_set_github_deploy_status('error', 'GitHub lookup returned unexpected status ' . $lookup_status . '.', $reason);
		error_log('Med Platform Headless GitHub deploy lookup returned status ' . $lookup_status);
		return;
	}

	$payload = array(
		'reason'     => $reason,
		'site'       => mp_headless_get_frontend_url('/'),
		'cms'        => home_url('/'),
		'timestamp'  => time(),
		'updated_at' => gmdate('c'),
		'nonce'      => wp_generate_uuid4(),
	);
	$request_body = array(
		'message' => sprintf('Trigger Hostinger deploy: %s', substr($reason, 0, 120)),
		'content' => base64_encode(wp_json_encode($payload)),
		'branch'  => $settings['branch'],
	);

	if ($existing_sha !== '') {
		$request_body['sha'] = $existing_sha;
	}

	$commit_response = wp_remote_request(
		$contents_url,
		array(
			'method'  => 'PUT',
			'timeout' => 20,
			'headers' => $headers,
			'data_format' => 'body',
			'body'    => wp_json_encode($request_body),
		)
	);

	if (is_wp_error($commit_response)) {
		mp_headless_set_github_deploy_status('error', 'GitHub commit failed: ' . $commit_response->get_error_message(), $reason);
		error_log('Med Platform Headless GitHub deploy commit failed: ' . $commit_response->get_error_message());
		return;
	}

	$commit_status = (int) wp_remote_retrieve_response_code($commit_response);
	if (! in_array($commit_status, array(200, 201), true)) {
		mp_headless_set_github_deploy_status('error', 'GitHub commit returned status ' . $commit_status . '.', $reason);
		error_log('Med Platform Headless GitHub deploy commit returned status ' . $commit_status . ' with body: ' . wp_remote_retrieve_body($commit_response));
		return;
	}

	update_option('mp_headless_github_last_trigger_at', time(), false);
	mp_headless_set_github_deploy_status('success', 'GitHub deploy trigger commit created successfully.', $reason);
}

function mp_headless_filter_post_type_link($post_link, $post) {
	if (! $post instanceof WP_Post) {
		return $post_link;
	}

	if (! in_array($post->post_type, array('mp_publication', 'mp_person', 'mp_project', 'mp_homepage'), true)) {
		return $post_link;
	}

	return mp_headless_get_frontend_post_url($post);
}
add_filter('post_type_link', 'mp_headless_filter_post_type_link', 10, 2);

function mp_headless_filter_term_link($term_link, $term, $taxonomy) {
	if (! in_array($taxonomy, array('mp_output_type', 'mp_hashtag', 'mp_topic'), true)) {
		return $term_link;
	}

	return mp_headless_get_frontend_term_url($term);
}
add_filter('term_link', 'mp_headless_filter_term_link', 10, 3);

function mp_headless_allowed_redirect_hosts($hosts) {
	$frontend_host = wp_parse_url(mp_headless_get_frontend_base_url(), PHP_URL_HOST);
	if ($frontend_host && ! in_array($frontend_host, $hosts, true)) {
		$hosts[] = $frontend_host;
	}

	return $hosts;
}
add_filter('allowed_redirect_hosts', 'mp_headless_allowed_redirect_hosts');

add_filter('wp_sitemaps_enabled', '__return_false');

function mp_headless_filter_wp_robots($robots) {
	if (! mp_headless_is_public_page_request()) {
		return $robots;
	}

	return array(
		'noindex'   => true,
		'nofollow'  => true,
		'noarchive' => true,
	);
}
add_filter('wp_robots', 'mp_headless_filter_wp_robots');

function mp_headless_send_noindex_headers() {
	if (! mp_headless_is_public_page_request()) {
		return;
	}

	header('X-Robots-Tag: noindex, nofollow, noarchive', true);
}
add_action('send_headers', 'mp_headless_send_noindex_headers');

function mp_headless_redirect_public_requests() {
	if (! mp_headless_is_public_page_request()) {
		return;
	}

	$method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
	if (! in_array($method, array('GET', 'HEAD'), true)) {
		return;
	}

	$frontend_base = untrailingslashit(mp_headless_get_frontend_base_url());
	$wp_base       = untrailingslashit(home_url('/'));
	if ($frontend_base === $wp_base) {
		return;
	}

	wp_safe_redirect(mp_headless_get_frontend_redirect_url(), 301, 'Med Platform Headless');
	exit;
}
add_action('template_redirect', 'mp_headless_redirect_public_requests', 1);

function mp_headless_maybe_trigger_build_on_post_save($post_id, $post) {
	if (wp_is_post_revision($post_id) || (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)) {
		return;
	}

	if (! empty($GLOBALS['mp_headless_suspend_build_trigger'])) {
		return;
	}

	if (! in_array($post->post_type, array('mp_publication', 'mp_person', 'mp_project', 'mp_homepage'), true)) {
		return;
	}

	mp_headless_trigger_build('save_post:' . $post->post_type . ':' . $post_id);
}
add_action('save_post', 'mp_headless_maybe_trigger_build_on_post_save', 20, 2);

function mp_headless_after_term_change($term_id) {
	mp_headless_trigger_build('term_change:' . intval($term_id));
}
add_action('created_mp_topic', 'mp_headless_after_term_change');
add_action('edited_mp_topic', 'mp_headless_after_term_change');
add_action('created_mp_hashtag', 'mp_headless_after_term_change');
add_action('edited_mp_hashtag', 'mp_headless_after_term_change');
add_action('created_mp_output_type', 'mp_headless_after_term_change');
add_action('edited_mp_output_type', 'mp_headless_after_term_change');
