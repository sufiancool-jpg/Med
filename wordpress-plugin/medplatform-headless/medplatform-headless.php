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
	register_post_meta('mp_project', 'mp_current_stage', $rest_string);
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
	<p><label><strong><?php esc_html_e('Displayed Author', 'medplatform-headless'); ?></strong></label><br />
	<select class="widefat" name="mp_author_person_id">
		<option value="0"><?php esc_html_e('Select a person', 'medplatform-headless'); ?></option>
		<?php foreach ($people as $person) : ?>
			<option value="<?php echo esc_attr($person->ID); ?>" <?php selected($author_person_id, (int) $person->ID); ?>><?php echo esc_html($person->post_title); ?></option>
		<?php endforeach; ?>
	</select>
	<span class="description" style="display:block; margin-top:6px;">
		<?php esc_html_e('The publication author name and profile photo are pulled automatically from the selected person profile.', 'medplatform-headless'); ?>
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
	<?php
	mp_headless_render_media_field(
			array(
				'label'        => __('Publication File URL', 'medplatform-headless'),
				'name'         => 'mp_download_url',
				'value'        => get_post_meta($post->ID, 'mp_download_url', true),
				'button_label' => __('Upload PDF or document', 'medplatform-headless'),
				'library_type' => 'application/pdf',
				'description'  => __('Use a WordPress media upload or local public path. PDF and document files are limited to 5 MB.', 'medplatform-headless'),
				'max_size_mb'  => 5,
			)
		);
		?>
	<p><label><strong><?php esc_html_e('File Button Label', 'medplatform-headless'); ?></strong></label><br />
	<input type="text" class="widefat" name="mp_download_label" value="<?php echo esc_attr(get_post_meta($post->ID, 'mp_download_label', true)); ?>" /></p>
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
	$stage_points = get_post_meta($post->ID, 'mp_stage_points', true);
	$current_stage = get_post_meta($post->ID, 'mp_current_stage', true);
	$hide_project_bar = (bool) get_post_meta($post->ID, 'mp_hide_project_bar', true);
	$stage_points = is_array($stage_points) && ! empty($stage_points) ? $stage_points : mp_headless_project_stages();
	?>
	<p><label><strong><?php esc_html_e('Accent Color', 'medplatform-headless'); ?></strong></label><br />
	<input type="text" class="widefat" name="mp_color" value="<?php echo esc_attr(get_post_meta($post->ID, 'mp_color', true)); ?>" placeholder="#15243a" /></p>
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
	<p class="description" style="margin-top:-8px;">
		<?php esc_html_e('Use Progress Points to define the labels shown on the public project page. The Current Progress Point should match one of those labels.', 'medplatform-headless'); ?>
	</p>
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
		$author_person_id = intval($_POST['mp_author_person_id'] ?? 0);
		$author_post = $author_person_id ? get_post($author_person_id) : null;
		$author_name = $author_post ? $author_post->post_title : '';
		$author_image = $author_person_id ? (string) get_post_meta($author_person_id, 'mp_photo', true) : '';

		if ($selected_output_type === '') {
			$selected_output_type = 'insights';
		}

		wp_set_object_terms($post_id, array($selected_output_type), 'mp_output_type', false);
		update_post_meta($post_id, 'mp_author_name', sanitize_text_field($author_name));
		update_post_meta($post_id, 'mp_author_person_id', $author_person_id);
		update_post_meta($post_id, 'mp_author_role', sanitize_text_field(wp_unslash($_POST['mp_author_role'] ?? '')));
		update_post_meta($post_id, 'mp_author_image', esc_url_raw($author_image));
		update_post_meta($post_id, 'mp_cover_image', esc_url_raw(wp_unslash($_POST['mp_cover_image'] ?? '')));
		update_post_meta($post_id, 'mp_audio_url', esc_url_raw(wp_unslash($_POST['mp_audio_url'] ?? '')));
		update_post_meta($post_id, 'mp_download_url', esc_url_raw(wp_unslash($_POST['mp_download_url'] ?? '')));
		update_post_meta($post_id, 'mp_download_label', sanitize_text_field(wp_unslash($_POST['mp_download_label'] ?? '')));
		update_post_meta($post_id, 'mp_contributor_person_ids', mp_headless_sanitize_int_array(wp_unslash($_POST['mp_contributor_person_ids'] ?? array())));
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
		update_post_meta($post_id, 'mp_color', sanitize_text_field(wp_unslash($_POST['mp_color'] ?? '')));
		update_post_meta($post_id, 'mp_hide_project_bar', ! empty($_POST['mp_hide_project_bar']));
		update_post_meta($post_id, 'mp_stage_points', mp_headless_parse_lines($_POST['mp_stage_points'] ?? ''));
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
		update_post_meta($post_id, 'mp_latest_publication_ids', array_slice(mp_headless_sanitize_int_array(wp_unslash($_POST['mp_latest_publication_ids'] ?? array())), 0, 5));
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
	$projects_list_url     = admin_url('edit.php?post_type=mp_project');
	$focus_area_options    = mp_headless_project_focus_area_options();
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
	$hide_project_bar      = $is_editing ? (bool) get_post_meta($project->ID, 'mp_hide_project_bar', true) : false;
	$stage_points          = $is_editing ? get_post_meta($project->ID, 'mp_stage_points', true) : array();
	$current_stage         = $is_editing ? (string) get_post_meta($project->ID, 'mp_current_stage', true) : '';
	$lead_person_id        = $is_editing ? intval(get_post_meta($project->ID, 'mp_lead_person_id', true)) : 0;
	$lead_name             = $is_editing ? (string) get_post_meta($project->ID, 'mp_lead_name', true) : '';
	$lead_role             = $is_editing ? (string) get_post_meta($project->ID, 'mp_lead_role', true) : 'Program Lead';
	$team_member_ids       = $is_editing ? get_post_meta($project->ID, 'mp_team_member_ids', true) : array();
	$legacy_team_members   = $is_editing ? get_post_meta($project->ID, 'mp_team_members', true) : array();
	$updates               = $is_editing ? get_post_meta($project->ID, 'mp_updates', true) : array();
	$donors                = $is_editing ? get_post_meta($project->ID, 'mp_donors', true) : array();
	$focus_area_slugs      = $is_editing ? get_post_meta($project->ID, 'mp_focus_area_slugs', true) : array();
	$legacy_focus_areas    = $is_editing ? get_post_meta($project->ID, 'mp_focus_areas', true) : array();
	$stage_points          = is_array($stage_points) && ! empty($stage_points) ? array_values(array_filter(array_map('strval', $stage_points))) : mp_headless_project_stages();
	$team_member_ids       = is_array($team_member_ids) ? array_values(array_filter(array_map('intval', $team_member_ids))) : array();
	$legacy_team_members   = is_array($legacy_team_members) ? $legacy_team_members : array();
	$updates               = is_array($updates) ? $updates : array();
	$donors                = is_array($donors) ? $donors : array();
	$focus_area_slugs      = is_array($focus_area_slugs) ? array_values(array_filter(array_map('sanitize_title', $focus_area_slugs))) : array();

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

	if ($lead_person_id < 1 && $lead_name !== '') {
		$lead_person_id = mp_headless_find_person_id_by_name($lead_name);
	}

	if ($lead_person_id > 0 && ! in_array($lead_person_id, $team_member_ids, true)) {
		$team_member_ids[] = $lead_person_id;
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
							<div style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
								<input type="color" value="<?php echo esc_attr($project_color ?: '#15243a'); ?>" data-mp-color-picker style="inline-size:56px; block-size:40px; padding:0; border:0; background:none;" />
								<input type="text" id="mp_color" name="mp_color" class="regular-text code" value="<?php echo esc_attr($project_color ?: '#15243a'); ?>" data-mp-color-value pattern="^#([A-Fa-f0-9]{6})$" />
							</div>
							<p style="margin:0; color:#646970;"><?php esc_html_e('Pick the project accent color. It is reused on the public project page and on related publication project buttons.', 'medplatform-headless'); ?></p>
						</div>
						<div>
							<label for="mp_project_description"><strong><?php esc_html_e('Short Description', 'medplatform-headless'); ?></strong></label>
							<textarea id="mp_project_description" class="widefat" rows="4" name="mp_project_description" style="margin-top:8px;"><?php echo esc_textarea($project_description); ?></textarea>
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
							<label for="mp_lead_person_id"><strong><?php esc_html_e('Lead Team Member', 'medplatform-headless'); ?></strong></label>
							<select id="mp_lead_person_id" name="mp_lead_person_id" class="widefat" data-mp-lead-select style="margin-top:8px;">
								<option value="0"><?php esc_html_e('Select a lead', 'medplatform-headless'); ?></option>
								<?php foreach ($people as $person) : ?>
									<?php $is_in_team = empty($team_member_ids) || in_array((int) $person->ID, $team_member_ids, true); ?>
									<option
										value="<?php echo esc_attr($person->ID); ?>"
										data-person-id="<?php echo esc_attr($person->ID); ?>"
										<?php selected($lead_person_id, (int) $person->ID); ?>
										<?php disabled(! $is_in_team && $lead_person_id !== (int) $person->ID); ?>
									>
										<?php echo esc_html($person->post_title); ?>
									</option>
								<?php endforeach; ?>
							</select>
							<p style="margin:8px 0 0; color:#646970;"><?php esc_html_e('The lead photo is pulled automatically from the selected team member profile.', 'medplatform-headless'); ?></p>
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
					<p style="margin-top:0; color:#50575e;"><?php esc_html_e('Choose from the predefined focus areas so the public project page stays consistent and mistake-proof.', 'medplatform-headless'); ?></p>
					<div class="mp-project-focus-grid">
						<?php foreach ($focus_area_options as $option) : ?>
							<label class="mp-project-focus-card">
								<input type="checkbox" name="mp_focus_area_slugs[]" value="<?php echo esc_attr($option['slug']); ?>" <?php checked(in_array($option['slug'], $focus_area_slugs, true)); ?> />
								<strong style="display:block; margin-top:8px;"><?php echo esc_html($option['title']); ?></strong>
								<span style="display:block; margin-top:6px; color:#646970;"><?php echo esc_html($option['description']); ?></span>
							</label>
						<?php endforeach; ?>
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
	$description       = sanitize_textarea_field(wp_unslash($_POST['mp_project_description'] ?? ''));
	$content           = sanitize_textarea_field(wp_unslash($_POST['mp_project_content'] ?? ''));
	$color             = sanitize_hex_color(wp_unslash($_POST['mp_color'] ?? ''));
	$stage_points      = mp_headless_parse_lines($_POST['mp_stage_points'] ?? '');
	$current_stage     = sanitize_text_field(wp_unslash($_POST['mp_current_stage'] ?? ''));
	$team_member_ids   = mp_headless_sanitize_int_array(wp_unslash($_POST['mp_team_member_ids'] ?? array()));
	$lead_person_id    = intval($_POST['mp_lead_person_id'] ?? 0);
	$lead_role         = sanitize_text_field(wp_unslash($_POST['mp_lead_role'] ?? ''));
	$updates           = mp_headless_parse_lines($_POST['mp_updates'] ?? '');
	$focus_area_slugs  = mp_headless_sanitize_string_array(wp_unslash($_POST['mp_focus_area_slugs'] ?? array()));
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

	if (empty($stage_points)) {
		$stage_points = mp_headless_project_stages();
	}

	if ($current_stage === '' || ! in_array($current_stage, $stage_points, true)) {
		$current_stage = $stage_points[0];
	}

	if ($lead_person_id > 0 && ! in_array($lead_person_id, $team_member_ids, true)) {
		$team_member_ids[] = $lead_person_id;
	}

	$team_member_ids = array_values(array_unique(array_map('intval', $team_member_ids)));
	$team_member_names = array();
	foreach ($team_member_ids as $person_id) {
		$person = get_post($person_id);
		if ($person instanceof WP_Post && $person->post_type === 'mp_person') {
			$team_member_names[] = $person->post_title;
		}
	}

	$lead_post  = $lead_person_id > 0 ? get_post($lead_person_id) : null;
	$lead_name  = ($lead_post instanceof WP_Post && $lead_post->post_type === 'mp_person') ? $lead_post->post_title : '';
	$lead_image = $lead_person_id > 0 ? (string) get_post_meta($lead_person_id, 'mp_photo', true) : '';
	if ($lead_role === '' && $lead_person_id > 0) {
		$lead_role = (string) get_post_meta($lead_person_id, 'mp_role', true);
	}

	$focus_area_slugs = array_values(array_unique(array_map('sanitize_title', $focus_area_slugs)));
	$focus_area_objects = mp_headless_resolve_focus_area_objects($focus_area_slugs);

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
	update_post_meta($project_id, 'mp_hide_project_bar', ! empty($_POST['mp_hide_project_bar']));
	update_post_meta($project_id, 'mp_stage_points', $stage_points);
	update_post_meta($project_id, 'mp_current_stage', $current_stage);
	update_post_meta($project_id, 'mp_lead_person_id', $lead_person_id);
	update_post_meta($project_id, 'mp_lead_name', $lead_name);
	update_post_meta($project_id, 'mp_lead_role', $lead_role);
	update_post_meta($project_id, 'mp_lead_image', esc_url_raw($lead_image));
	update_post_meta($project_id, 'mp_team_member_ids', $team_member_ids);
	update_post_meta($project_id, 'mp_team_members', $team_member_names);
	update_post_meta($project_id, 'mp_donors', $donors);
	update_post_meta($project_id, 'mp_updates', $updates);
	update_post_meta($project_id, 'mp_focus_area_slugs', $focus_area_slugs);
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
	if ($post->post_type !== 'mp_person' && $post->post_type !== 'mp_project') {
		return $actions;
	}

	if (isset($actions['edit'])) {
		$edit_url = $post->post_type === 'mp_person'
			? mp_headless_get_person_admin_url($post->ID)
			: mp_headless_get_project_admin_url($post->ID);
		$actions['edit'] = sprintf(
			'<a href="%s">%s</a>',
			esc_url($edit_url),
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
