<?php
/**
 * Plugin Name: Med Platform Headless
 * Description: Registers the headless WordPress schema used by the Astro frontend.
 * Version: 0.1.0
 * Author: Codex
 */

if (! defined('ABSPATH')) {
	exit;
}

function mp_headless_project_stages() {
	return array('Conception', 'Research', 'Dialogue', 'Publication', 'Implemented');
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

function mp_headless_sanitize_bool_value($value) {
	return ! empty($value);
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
	register_post_meta('mp_project', 'mp_current_stage', $rest_string);
	register_post_meta('mp_project', 'mp_lead_name', $rest_string);
	register_post_meta('mp_project', 'mp_lead_role', $rest_string);
	register_post_meta('mp_project', 'mp_lead_image', $rest_string);

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

function mp_headless_get_person_admin_url($person_id = 0) {
	$url = admin_url('admin.php?page=mp-person-profile');

	if ($person_id > 0) {
		$url = add_query_arg('person_id', intval($person_id), $url);
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

function mp_headless_render_media_field($args) {
	$label         = $args['label'] ?? '';
	$name          = $args['name'] ?? '';
	$value         = $args['value'] ?? '';
	$button_label  = $args['button_label'] ?? __('Select file', 'medplatform-headless');
	$library_type  = $args['library_type'] ?? '';
	$placeholder   = $args['placeholder'] ?? '/Team/example.jpg or https://example.com/file.jpg';
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
				<button type="button" class="button mp-media-upload" data-library-type="<?php echo esc_attr($library_type); ?>">
					<?php echo esc_html($button_label); ?>
				</button>
				<button type="button" class="button-link-delete mp-media-clear">
					<?php esc_html_e('Clear', 'medplatform-headless'); ?>
				</button>
			</div>
			<p style="margin:8px 0 0; color:#646970;">
				<?php esc_html_e('Use either a WordPress media upload or a local public path like /Team/name.jpg.', 'medplatform-headless'); ?>
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
	$related_project_ids = get_post_meta($post->ID, 'mp_related_project_ids', true);
	$contributor_person_ids = get_post_meta($post->ID, 'mp_contributor_person_ids', true);
	$contributor_names = get_post_meta($post->ID, 'mp_contributor_names', true);
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
	<p><label><strong><?php esc_html_e('Displayed Author Name', 'medplatform-headless'); ?></strong></label><br />
	<input type="text" class="widefat" name="mp_author_name" value="<?php echo esc_attr(get_post_meta($post->ID, 'mp_author_name', true)); ?>" /></p>
	<p><label><strong><?php esc_html_e('Linked Author Profile', 'medplatform-headless'); ?></strong></label><br />
	<select class="widefat" name="mp_author_person_id">
		<option value="0"><?php esc_html_e('No linked profile', 'medplatform-headless'); ?></option>
		<?php foreach ($people as $person) : ?>
			<option value="<?php echo esc_attr($person->ID); ?>" <?php selected($author_person_id, (int) $person->ID); ?>><?php echo esc_html($person->post_title); ?></option>
		<?php endforeach; ?>
	</select></p>
	<p><label><strong><?php esc_html_e('Author Role', 'medplatform-headless'); ?></strong></label><br />
	<input type="text" class="widefat" name="mp_author_role" value="<?php echo esc_attr(get_post_meta($post->ID, 'mp_author_role', true)); ?>" /></p>
	<?php
	mp_headless_render_media_field(
		array(
			'label'        => __('Author Image URL', 'medplatform-headless'),
			'name'         => 'mp_author_image',
			'value'        => get_post_meta($post->ID, 'mp_author_image', true),
			'button_label' => __('Upload author image', 'medplatform-headless'),
			'library_type' => 'image',
		)
	);
	mp_headless_render_media_field(
		array(
			'label'        => __('Cover Image URL', 'medplatform-headless'),
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
			)
		);
		?>
		<p class="description" style="margin-top:-8px;">
			<?php esc_html_e('This file powers the public audio player on the publication page and the featured podcast player on the homepage.', 'medplatform-headless'); ?>
		</p>
	</div>
	<?php
	mp_headless_render_media_field(
		array(
			'label'        => __('Publication File URL', 'medplatform-headless'),
			'name'         => 'mp_download_url',
			'value'        => get_post_meta($post->ID, 'mp_download_url', true),
			'button_label' => __('Upload PDF or document', 'medplatform-headless'),
			'library_type' => 'application/pdf',
		)
	);
	?>
	<p><label><strong><?php esc_html_e('File Button Label', 'medplatform-headless'); ?></strong></label><br />
	<input type="text" class="widefat" name="mp_download_label" value="<?php echo esc_attr(get_post_meta($post->ID, 'mp_download_label', true)); ?>" /></p>
	<p><strong><?php esc_html_e('Linked People Involved', 'medplatform-headless'); ?></strong></p>
	<div style="max-height: 180px; overflow:auto; border:1px solid #ddd; padding:10px; margin-bottom:14px;">
		<?php foreach ($people as $person) : ?>
			<label style="display:block; margin-bottom:6px;">
				<input type="checkbox" name="mp_contributor_person_ids[]" value="<?php echo esc_attr($person->ID); ?>" <?php checked(in_array((int) $person->ID, array_map('intval', $contributor_person_ids), true)); ?> />
				<?php echo esc_html($person->post_title); ?>
			</label>
		<?php endforeach; ?>
	</div>
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
	$team_members = get_post_meta($post->ID, 'mp_team_members', true);
	$updates      = get_post_meta($post->ID, 'mp_updates', true);
	$donors       = get_post_meta($post->ID, 'mp_donors', true);
	$focus_areas  = get_post_meta($post->ID, 'mp_focus_areas', true);
	?>
	<p><label><strong><?php esc_html_e('Accent Color', 'medplatform-headless'); ?></strong></label><br />
	<input type="text" class="widefat" name="mp_color" value="<?php echo esc_attr(get_post_meta($post->ID, 'mp_color', true)); ?>" placeholder="#15243a" /></p>
	<p><label><strong><?php esc_html_e('Current Stage', 'medplatform-headless'); ?></strong></label><br />
	<select class="widefat" name="mp_current_stage">
		<?php foreach (mp_headless_project_stages() as $stage) : ?>
			<option value="<?php echo esc_attr($stage); ?>" <?php selected(get_post_meta($post->ID, 'mp_current_stage', true), $stage); ?>><?php echo esc_html($stage); ?></option>
		<?php endforeach; ?>
	</select></p>
	<p><label><strong><?php esc_html_e('Lead Name', 'medplatform-headless'); ?></strong></label><br />
	<input type="text" class="widefat" name="mp_lead_name" value="<?php echo esc_attr(get_post_meta($post->ID, 'mp_lead_name', true)); ?>" /></p>
	<p><label><strong><?php esc_html_e('Lead Role', 'medplatform-headless'); ?></strong></label><br />
	<input type="text" class="widefat" name="mp_lead_role" value="<?php echo esc_attr(get_post_meta($post->ID, 'mp_lead_role', true)); ?>" /></p>
	<?php
	mp_headless_render_media_field(
		array(
			'label'        => __('Lead Image URL', 'medplatform-headless'),
			'name'         => 'mp_lead_image',
			'value'        => get_post_meta($post->ID, 'mp_lead_image', true),
			'button_label' => __('Upload lead image', 'medplatform-headless'),
			'library_type' => 'image',
		)
	);
	?>
	<p><label><strong><?php esc_html_e('Team Members', 'medplatform-headless'); ?></strong></label><br />
	<textarea class="widefat" rows="5" name="mp_team_members" placeholder="One member per line"><?php echo esc_textarea(is_array($team_members) ? implode("\n", $team_members) : ''); ?></textarea></p>
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
	<p style="margin-top:8px;"><?php esc_html_e('Use the main editor for the right-column project body text and the excerpt for the short project description.', 'medplatform-headless'); ?></p>
	<?php
}

function mp_headless_render_homepage_meta_box($post) {
	wp_nonce_field('mp_headless_save_meta', 'mp_headless_meta_nonce');
	$publications           = get_posts(array('post_type' => 'mp_publication', 'posts_per_page' => -1, 'orderby' => 'date', 'order' => 'DESC'));
	$podcast_publications   = mp_headless_get_podcast_publications();
	$featured_podcast_id    = (int) get_post_meta($post->ID, 'mp_featured_podcast_id', true);
	$featured_article_id    = (int) get_post_meta($post->ID, 'mp_featured_article_id', true);
	$slider_publication_ids = get_post_meta($post->ID, 'mp_slider_publication_ids', true);
	$latest_publication_ids = get_post_meta($post->ID, 'mp_latest_publication_ids', true);
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
}
add_action('add_meta_boxes', 'mp_headless_add_meta_boxes');

function mp_headless_enqueue_admin_assets($hook_suffix) {
	$is_post_editor = in_array($hook_suffix, array('post.php', 'post-new.php'), true);
	$is_homepage_settings = $hook_suffix === 'toplevel_page_mp-homepage-settings';
	$is_person_settings = isset($_GET['page']) && sanitize_key(wp_unslash($_GET['page'])) === 'mp-person-profile';

	if (! $is_post_editor && ! $is_homepage_settings && ! $is_person_settings) {
		return;
	}

	if ($is_post_editor) {
		$screen = get_current_screen();
		if (! $screen || ! in_array($screen->post_type, array('mp_publication', 'mp_project', 'mp_person'), true)) {
			return;
		}
	}

	if ($is_post_editor || $is_person_settings) {
		wp_enqueue_media();
	}
	wp_enqueue_script(
		'mp-headless-admin-media',
		plugins_url('assets/admin-media.js', __FILE__),
		array('jquery'),
		(string) filemtime(plugin_dir_path(__FILE__) . 'assets/admin-media.js'),
		true
	);
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

		if ($selected_output_type === '') {
			$selected_output_type = 'insights';
		}

		wp_set_object_terms($post_id, array($selected_output_type), 'mp_output_type', false);
		update_post_meta($post_id, 'mp_author_name', sanitize_text_field(wp_unslash($_POST['mp_author_name'] ?? '')));
		update_post_meta($post_id, 'mp_author_person_id', intval($_POST['mp_author_person_id'] ?? 0));
		update_post_meta($post_id, 'mp_author_role', sanitize_text_field(wp_unslash($_POST['mp_author_role'] ?? '')));
		update_post_meta($post_id, 'mp_author_image', esc_url_raw(wp_unslash($_POST['mp_author_image'] ?? '')));
		update_post_meta($post_id, 'mp_cover_image', esc_url_raw(wp_unslash($_POST['mp_cover_image'] ?? '')));
		update_post_meta($post_id, 'mp_audio_url', esc_url_raw(wp_unslash($_POST['mp_audio_url'] ?? '')));
		update_post_meta($post_id, 'mp_download_url', esc_url_raw(wp_unslash($_POST['mp_download_url'] ?? '')));
		update_post_meta($post_id, 'mp_download_label', sanitize_text_field(wp_unslash($_POST['mp_download_label'] ?? '')));
		update_post_meta($post_id, 'mp_contributor_person_ids', mp_headless_sanitize_int_array(wp_unslash($_POST['mp_contributor_person_ids'] ?? array())));
		update_post_meta($post_id, 'mp_contributor_names', mp_headless_parse_lines($_POST['mp_contributor_names'] ?? ''));
		update_post_meta($post_id, 'mp_related_project_ids', mp_headless_sanitize_int_array(wp_unslash($_POST['mp_related_project_ids'] ?? array())));
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
		update_post_meta($post_id, 'mp_color', sanitize_text_field(wp_unslash($_POST['mp_color'] ?? '')));
		update_post_meta($post_id, 'mp_current_stage', sanitize_text_field(wp_unslash($_POST['mp_current_stage'] ?? '')));
		update_post_meta($post_id, 'mp_lead_name', sanitize_text_field(wp_unslash($_POST['mp_lead_name'] ?? '')));
		update_post_meta($post_id, 'mp_lead_role', sanitize_text_field(wp_unslash($_POST['mp_lead_role'] ?? '')));
		update_post_meta($post_id, 'mp_lead_image', esc_url_raw(wp_unslash($_POST['mp_lead_image'] ?? '')));
		update_post_meta($post_id, 'mp_team_members', mp_headless_parse_lines($_POST['mp_team_members'] ?? ''));
		update_post_meta($post_id, 'mp_updates', mp_headless_parse_lines($_POST['mp_updates'] ?? ''));
		update_post_meta($post_id, 'mp_donors', mp_headless_parse_named_assets($_POST['mp_donors'] ?? ''));
		update_post_meta($post_id, 'mp_focus_areas', mp_headless_parse_focus_areas($_POST['mp_focus_areas'] ?? ''));
	}

	if ($post_type === 'mp_homepage') {
		$featured_podcast_id = intval($_POST['mp_featured_podcast_id'] ?? 0);
		update_post_meta($post_id, 'mp_featured_podcast_id', mp_headless_publication_is_podcast($featured_podcast_id) ? $featured_podcast_id : 0);
		update_post_meta($post_id, 'mp_featured_article_id', intval($_POST['mp_featured_article_id'] ?? 0));
		update_post_meta($post_id, 'mp_slider_publication_ids', mp_headless_sanitize_int_array(wp_unslash($_POST['mp_slider_publication_ids'] ?? array())));
		update_post_meta($post_id, 'mp_latest_publication_ids', mp_headless_sanitize_int_array(wp_unslash($_POST['mp_latest_publication_ids'] ?? array())));
	}
}
add_action('save_post', 'mp_headless_save_meta_boxes');

function mp_headless_get_selection_count_label($count) {
	$count = (int) $count;

	if ($count < 1) {
		return __('No publications selected', 'medplatform-headless');
	}

	return sprintf(
		_n('%s publication selected', '%s publications selected', $count, 'medplatform-headless'),
		number_format_i18n($count)
	);
}

function mp_headless_render_publication_selection_list($args) {
	$section_id          = isset($args['section_id']) ? sanitize_html_class((string) $args['section_id']) : wp_unique_id('mp-selection-list-');
	$field_name          = isset($args['field_name']) ? (string) $args['field_name'] : '';
	$title               = isset($args['title']) ? (string) $args['title'] : '';
	$description         = isset($args['description']) ? (string) $args['description'] : '';
	$search_placeholder  = isset($args['search_placeholder']) ? (string) $args['search_placeholder'] : __('Search publications...', 'medplatform-headless');
	$empty_search_label  = isset($args['empty_search_label']) ? (string) $args['empty_search_label'] : __('No publications match this search.', 'medplatform-headless');
	$publications        = isset($args['publications']) && is_array($args['publications']) ? $args['publications'] : array();
	$selected_ids        = isset($args['selected_ids']) && is_array($args['selected_ids']) ? array_map('intval', $args['selected_ids']) : array();
	$selected_count_text = mp_headless_get_selection_count_label(count($selected_ids));
	?>
	<div class="mp-selection-card" style="background:#fff; border:1px solid #dcdcde; padding:20px;">
		<div style="display:flex; gap:12px; align-items:flex-start; justify-content:space-between; flex-wrap:wrap;">
			<div>
				<h2 style="margin:0;"><?php echo esc_html($title); ?></h2>
				<p style="margin:8px 0 0; color:#50575e; max-width:72ch;"><?php echo esc_html($description); ?></p>
			</div>
			<span
				class="mp-selection-count"
				data-empty-label="<?php echo esc_attr(__('No publications selected', 'medplatform-headless')); ?>"
				data-singular-label="<?php echo esc_attr(__('publication selected', 'medplatform-headless')); ?>"
				data-plural-label="<?php echo esc_attr(__('publications selected', 'medplatform-headless')); ?>"
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
				<?php if (empty($publications)) : ?>
					<p style="margin:0; padding:14px 16px; color:#50575e;"><?php esc_html_e('No publications available yet.', 'medplatform-headless'); ?></p>
				<?php else : ?>
					<?php foreach ($publications as $publication) : ?>
						<label
							class="mp-selection-item"
							data-selection-label="<?php echo esc_attr(function_exists('mb_strtolower') ? mb_strtolower($publication->post_title) : strtolower($publication->post_title)); ?>"
							style="display:flex; gap:12px; align-items:flex-start; padding:12px 14px; border-top:1px solid #f0f0f1;"
						>
							<input
								class="mp-selection-checkbox"
								type="checkbox"
								name="<?php echo esc_attr($field_name); ?>[]"
								value="<?php echo esc_attr($publication->ID); ?>"
								<?php checked(in_array((int) $publication->ID, $selected_ids, true)); ?>
							/>
							<span style="display:block; line-height:1.35;">
								<strong style="display:block; font-weight:600;"><?php echo esc_html($publication->post_title); ?></strong>
								<span style="display:block; margin-top:2px; color:#646970; font-size:12px;"><?php echo esc_html(get_the_date(get_option('date_format'), $publication)); ?></span>
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

function mp_headless_render_homepage_settings_page() {
	if (! current_user_can('edit_posts')) {
		wp_die(esc_html__('You do not have permission to manage homepage settings.', 'medplatform-headless'));
	}

	$homepage_id              = mp_headless_get_homepage_post_id();
	$publications             = get_posts(array('post_type' => 'mp_publication', 'posts_per_page' => -1, 'orderby' => 'date', 'order' => 'DESC'));
	$podcast_publications     = mp_headless_get_podcast_publications();
	$featured_podcast_id      = (int) get_post_meta($homepage_id, 'mp_featured_podcast_id', true);
	$featured_article_id      = (int) get_post_meta($homepage_id, 'mp_featured_article_id', true);
	$slider_publication_ids   = get_post_meta($homepage_id, 'mp_slider_publication_ids', true);
	$latest_publication_ids   = get_post_meta($homepage_id, 'mp_latest_publication_ids', true);
	$announcement_text        = (string) get_post_meta($homepage_id, 'mp_announcement_text', true);
	$announcement_link_url    = (string) get_post_meta($homepage_id, 'mp_announcement_link_url', true);
	$announcement_link_label  = (string) get_post_meta($homepage_id, 'mp_announcement_link_label', true);
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
					</div>
				</div>

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
						'description'        => __('Search the publication list and tick the items that should appear in the homepage latest publications area.', 'medplatform-headless'),
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

	add_menu_page(
		__('Homepage Settings', 'medplatform-headless'),
		__('Homepage Settings', 'medplatform-headless'),
		'edit_posts',
		'mp-homepage-settings',
		'mp_headless_render_homepage_settings_page',
		'dashicons-admin-home',
		21
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
}
add_action('admin_menu', 'mp_headless_customize_people_menu', 30);

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
	update_post_meta($homepage_id, 'mp_slider_publication_ids', mp_headless_sanitize_int_array(wp_unslash($_POST['mp_slider_publication_ids'] ?? array())));
	update_post_meta($homepage_id, 'mp_latest_publication_ids', mp_headless_sanitize_int_array(wp_unslash($_POST['mp_latest_publication_ids'] ?? array())));
	update_post_meta($homepage_id, 'mp_announcement_text', sanitize_text_field(wp_unslash($_POST['mp_announcement_text'] ?? '')));
	update_post_meta($homepage_id, 'mp_announcement_link_url', esc_url_raw(wp_unslash($_POST['mp_announcement_link_url'] ?? '')));
	update_post_meta($homepage_id, 'mp_announcement_link_label', sanitize_text_field(wp_unslash($_POST['mp_announcement_link_label'] ?? '')));

	clean_post_cache($homepage_id);
	mp_headless_trigger_build('homepage_settings_update:' . $homepage_id);

	wp_safe_redirect(admin_url('admin.php?page=mp-homepage-settings&updated=1'));
	exit;
}
add_action('admin_post_mp_headless_save_homepage_settings', 'mp_headless_save_homepage_settings');

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

	if ($page === 'mp-homepage-settings' || $page === 'mp-person-profile') {
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

	if (strpos($_SERVER['REQUEST_URI'] ?? '', 'edit.php') !== false && $post_type === 'mp_homepage') {
		wp_safe_redirect(admin_url('admin.php?page=mp-homepage-settings'));
		exit;
	}

	$post_id = isset($_GET['post']) ? intval($_GET['post']) : 0;
	if ($post_id > 0 && get_post_type($post_id) === 'mp_person') {
		wp_safe_redirect(mp_headless_get_person_admin_url($post_id));
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

function mp_headless_customize_person_row_actions($actions, $post) {
	if ($post->post_type !== 'mp_person') {
		return $actions;
	}

	if (isset($actions['edit'])) {
		$actions['edit'] = sprintf(
			'<a href="%s">%s</a>',
			esc_url(mp_headless_get_person_admin_url($post->ID)),
			esc_html__('Edit', 'medplatform-headless')
		);
	}

	unset($actions['inline hide-if-no-js']);

	return $actions;
}
add_filter('post_row_actions', 'mp_headless_customize_person_row_actions', 10, 2);

function mp_headless_register_settings() {
	register_setting(
		'mp_headless_settings',
		'mp_headless_build_hook_url',
		array(
			'type'              => 'string',
			'sanitize_callback' => 'esc_url_raw',
			'default'           => '',
		)
	);
}
add_action('admin_init', 'mp_headless_register_settings');

function mp_headless_render_settings_page() {
	?>
	<div class="wrap">
		<h1><?php esc_html_e('Med Platform Headless', 'medplatform-headless'); ?></h1>
		<form action="options.php" method="post">
			<?php settings_fields('mp_headless_settings'); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="mp_headless_build_hook_url"><?php esc_html_e('Build Hook URL', 'medplatform-headless'); ?></label></th>
					<td>
						<input id="mp_headless_build_hook_url" type="url" class="regular-text code" name="mp_headless_build_hook_url" value="<?php echo esc_attr(get_option('mp_headless_build_hook_url', '')); ?>" />
						<p class="description"><?php esc_html_e('Optional. When set, WordPress will POST to this URL after people, project, publication, homepage, or taxonomy changes.', 'medplatform-headless'); ?></p>
					</td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

function mp_headless_trigger_build($reason) {
	$hook = get_option('mp_headless_build_hook_url', '');
	if (! $hook) {
		return;
	}

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
					'site'      => home_url('/'),
					'timestamp' => time(),
				)
			),
		)
	);
}

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
