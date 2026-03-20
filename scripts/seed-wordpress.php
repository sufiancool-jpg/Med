<?php

if (! defined('ABSPATH')) {
	exit("This script must be run with wp eval-file.\n");
}

$root_dir          = dirname(__DIR__);
$people_path       = $root_dir . '/wordpress/seed/people.json';
$projects_path     = $root_dir . '/wordpress/seed/projects.json';
$homepage_path     = $root_dir . '/wordpress/seed/homepage.json';
$publications_path = $root_dir . '/wordpress/seed/generated-publications.json';

if (! file_exists($people_path) || ! file_exists($projects_path) || ! file_exists($homepage_path) || ! file_exists($publications_path)) {
	exit("Seed files are missing. Run the generator first.\n");
}

$people       = json_decode(file_get_contents($people_path), true);
$projects     = json_decode(file_get_contents($projects_path), true);
$homepage     = json_decode(file_get_contents($homepage_path), true);
$publications = json_decode(file_get_contents($publications_path), true);

if (! is_array($people) || ! is_array($projects) || ! is_array($publications) || ! is_array($homepage)) {
	exit("Seed files could not be parsed.\n");
}

function mp_seed_log($message) {
	if (class_exists('WP_CLI')) {
		WP_CLI::log($message);
		return;
	}

	echo $message . PHP_EOL;
}

function mp_seed_upsert_post($post_type, $slug, $postarr) {
	$existing = get_page_by_path($slug, OBJECT, $post_type);

	if ($existing) {
		$postarr['ID'] = $existing->ID;
		return wp_update_post($postarr, true);
	}

	return wp_insert_post($postarr, true);
}

function mp_seed_normalize_output_type($value) {
	if ('Series' === $value) {
		return 'Insights';
	}

	if ('Podcast' === $value) {
		return 'Pod-Cast';
	}

	return $value ?: 'Insights';
}

$output_types = array('Policy Paper', 'Research Report', 'Policy Brief', 'E-Book', 'Insights', 'Pod-Cast');
foreach ($output_types as $term_name) {
	if (! term_exists($term_name, 'mp_output_type')) {
		wp_insert_term($term_name, 'mp_output_type');
	}
}

$person_ids = array();
$person_ids_by_title = array();
foreach ($people as $person) {
	$result = mp_seed_upsert_post(
		'mp_person',
		$person['slug'],
		array(
			'post_type'    => 'mp_person',
			'post_status'  => 'publish',
			'post_name'    => $person['slug'],
			'post_title'   => $person['title'],
			'post_excerpt' => $person['excerpt'] ?? '',
			'post_content' => $person['contentHtml'] ?? '',
			'menu_order'   => intval($person['menuOrder'] ?? 0),
		)
	);

	if (is_wp_error($result)) {
		mp_seed_log('Failed to save person ' . $person['slug'] . ': ' . $result->get_error_message());
		continue;
	}

	$person_id = (int) $result;
	$person_ids[$person['slug']] = $person_id;
	$person_title = isset($person['title']) ? (string) $person['title'] : '';
	if ($person_title !== '') {
		$person_ids_by_title[strtolower($person_title)] = $person_id;
	}

	update_post_meta($person_id, 'mp_role', $person['role'] ?? '');
	update_post_meta($person_id, 'mp_email', $person['email'] ?? '');
	update_post_meta($person_id, 'mp_linkedin_url', $person['linkedinUrl'] ?? '');
	update_post_meta($person_id, 'mp_website_url', $person['websiteUrl'] ?? '');
	update_post_meta($person_id, 'mp_photo', $person['imageUrl'] ?? '');
	update_post_meta($person_id, 'mp_show_on_team_page', ! empty($person['showOnTeamPage']));

	mp_seed_log('Saved person: ' . $person['title']);
}

$project_ids = array();
foreach ($projects as $project) {
	$result = mp_seed_upsert_post(
		'mp_project',
		$project['slug'],
		array(
			'post_type'    => 'mp_project',
			'post_status'  => 'publish',
			'post_name'    => $project['slug'],
			'post_title'   => $project['title'],
			'post_excerpt' => $project['excerpt'],
			'post_content' => $project['contentHtml'],
		)
	);

	if (is_wp_error($result)) {
		mp_seed_log('Failed to save project ' . $project['slug'] . ': ' . $result->get_error_message());
		continue;
	}

	$project_id = (int) $result;
	$project_ids[$project['slug']] = $project_id;
	$lead_person_slugs = isset($project['leadPersonSlugs']) && is_array($project['leadPersonSlugs'])
		? $project['leadPersonSlugs']
		: array();
	$lead_person_ids = array();

	foreach ($lead_person_slugs as $lead_person_slug) {
		if (isset($person_ids[$lead_person_slug])) {
			$lead_person_ids[] = $person_ids[$lead_person_slug];
		}
	}

	if (empty($lead_person_ids) && ! empty($project['leadName'])) {
		$lead_name_lookup = strtolower((string) $project['leadName']);
		if (isset($person_ids_by_title[$lead_name_lookup])) {
			$lead_person_ids[] = $person_ids_by_title[$lead_name_lookup];
		}
	}

	$lead_person_ids = array_values(array_unique(array_map('intval', $lead_person_ids)));
	$lead_person_id = ! empty($lead_person_ids) ? (int) $lead_person_ids[0] : 0;
	$lead_name = $lead_person_id > 0 ? get_the_title($lead_person_id) : ($project['leadName'] ?? '');
	$lead_image = $lead_person_id > 0
		? get_post_meta($lead_person_id, 'mp_photo', true)
		: ($project['leadImage'] ?? '');

	update_post_meta($project_id, 'mp_color', $project['color'] ?? '');
	update_post_meta($project_id, 'mp_current_stage', $project['currentStage'] ?? '');
	update_post_meta($project_id, 'mp_stage_points', $project['stagePoints'] ?? array('Conception', 'Research', 'Dialogue', 'Publication', 'Implemented'));
	update_post_meta($project_id, 'mp_hide_project_bar', ! empty($project['hideProjectBar']));
	update_post_meta($project_id, 'mp_lead_person_id', $lead_person_id);
	update_post_meta($project_id, 'mp_lead_person_ids', $lead_person_ids);
	update_post_meta($project_id, 'mp_lead_name', $lead_name);
	update_post_meta($project_id, 'mp_lead_role', $project['leadRole'] ?? '');
	update_post_meta($project_id, 'mp_lead_image', $lead_image);
	update_post_meta($project_id, 'mp_team_members', $project['teamMembers'] ?? array());
	update_post_meta($project_id, 'mp_donors', $project['donors'] ?? array());
	update_post_meta($project_id, 'mp_updates', $project['updates'] ?? array());
	update_post_meta($project_id, 'mp_focus_areas', $project['focusAreas'] ?? array());

	mp_seed_log('Saved project: ' . $project['title']);
}

foreach ($projects as $project) {
	if (! isset($project_ids[$project['slug']])) {
		continue;
	}

	$project_id           = $project_ids[$project['slug']];
	$parent_project_id    = 0;
	$aligned_project_id   = 0;
	$parent_project_slug  = isset($project['parentProjectSlug']) ? (string) $project['parentProjectSlug'] : '';
	$aligned_project_slug = isset($project['alignedProjectSlug']) ? (string) $project['alignedProjectSlug'] : '';

	if ($parent_project_slug !== '' && isset($project_ids[$parent_project_slug])) {
		$parent_project_id = (int) $project_ids[$parent_project_slug];
	}

	if ($aligned_project_slug !== '' && isset($project_ids[$aligned_project_slug])) {
		$aligned_project_id = (int) $project_ids[$aligned_project_slug];
	}

	update_post_meta($project_id, 'mp_parent_project_id', $parent_project_id);
	update_post_meta($project_id, 'mp_aligned_project_id', $aligned_project_id);
	update_post_meta($project_id, 'mp_hide_project_currently', ! empty($project['hideFromProjectScreens']));
}

$publication_ids = array();
foreach ($publications as $publication) {
	$result = mp_seed_upsert_post(
		'mp_publication',
		$publication['slug'],
		array(
			'post_type'    => 'mp_publication',
			'post_status'  => 'publish',
			'post_name'    => $publication['slug'],
			'post_title'   => $publication['title'],
			'post_excerpt' => $publication['excerpt'] ?? '',
			'post_content' => $publication['contentHtml'] ?? '',
			'post_date'    => $publication['date'],
		)
	);

	if (is_wp_error($result)) {
		mp_seed_log('Failed to save publication ' . $publication['slug'] . ': ' . $result->get_error_message());
		continue;
	}

	$publication_id = (int) $result;
	$publication_ids[$publication['slug']] = $publication_id;

	wp_set_object_terms($publication_id, $publication['topics'] ?? array(), 'mp_topic');
	wp_set_object_terms($publication_id, $publication['hashtags'] ?? array(), 'mp_hashtag');
	wp_set_object_terms($publication_id, array(mp_seed_normalize_output_type($publication['category'] ?? 'Insights')), 'mp_output_type');

	$related_project_ids = array();
	foreach (($publication['projectSlugs'] ?? array()) as $project_slug) {
		if (isset($project_ids[$project_slug])) {
			$related_project_ids[] = $project_ids[$project_slug];
		}
	}

	$author_person_slugs = isset($publication['authorPersonSlugs']) && is_array($publication['authorPersonSlugs'])
		? $publication['authorPersonSlugs']
		: array();
	if (empty($author_person_slugs) && ! empty($publication['authorPersonSlug'])) {
		$author_person_slugs[] = $publication['authorPersonSlug'];
	}

	$author_person_ids = array();
	foreach ($author_person_slugs as $author_person_slug) {
		if (isset($person_ids[$author_person_slug])) {
			$author_person_ids[] = $person_ids[$author_person_slug];
		}
	}
	$author_person_ids = array_values(array_unique(array_map('intval', $author_person_ids)));
	$author_person_id = ! empty($author_person_ids) ? (int) $author_person_ids[0] : 0;
	$author_names = array();
	foreach ($author_person_ids as $selected_author_person_id) {
		$selected_author_name = get_the_title($selected_author_person_id);
		if ($selected_author_name) {
			$author_names[] = $selected_author_name;
		}
	}
	$author_name = ! empty($author_names)
		? implode(', ', $author_names)
		: ($publication['authorName'] ?? '');
	update_post_meta($publication_id, 'mp_author_name', $author_name);
	update_post_meta($publication_id, 'mp_author_person_id', $author_person_id);
	update_post_meta($publication_id, 'mp_author_person_ids', $author_person_ids);
	update_post_meta($publication_id, 'mp_author_role', $publication['authorRole'] ?? '');
	update_post_meta($publication_id, 'mp_author_image', $author_person_id ? get_post_meta($author_person_id, 'mp_photo', true) : ($publication['authorImage'] ?? ''));
	update_post_meta($publication_id, 'mp_cover_image', $publication['imageUrl'] ?? '');
	update_post_meta($publication_id, 'mp_download_url', $publication['downloadUrl'] ?? '');
	update_post_meta($publication_id, 'mp_download_label', $publication['downloadLabel'] ?? '');
	update_post_meta($publication_id, 'mp_references', $publication['references'] ?? array());
	$contributor_person_ids = array();
	foreach (($publication['contributorPersonSlugs'] ?? array()) as $person_slug) {
		if (isset($person_ids[$person_slug])) {
			$contributor_person_ids[] = $person_ids[$person_slug];
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
	update_post_meta($publication_id, 'mp_contributor_names', $publication['contributorNames'] ?? array());
	update_post_meta($publication_id, 'mp_related_project_ids', $related_project_ids);

	mp_seed_log('Saved publication: ' . $publication['title']);
}

$homepage_post = get_page_by_path('homepage-settings', OBJECT, 'mp_homepage');
if (! $homepage_post) {
	$homepage_id = wp_insert_post(
		array(
			'post_type'   => 'mp_homepage',
			'post_status' => 'publish',
			'post_name'   => 'homepage-settings',
			'post_title'  => 'Homepage Settings',
		)
	);
} else {
	$homepage_id = $homepage_post->ID;
}

if (! is_wp_error($homepage_id)) {
	update_post_meta($homepage_id, 'mp_featured_podcast_id', isset($publication_ids[$homepage['featuredPodcastSlug']]) ? $publication_ids[$homepage['featuredPodcastSlug']] : 0);
	update_post_meta($homepage_id, 'mp_featured_article_id', isset($publication_ids[$homepage['featuredArticleSlug']]) ? $publication_ids[$homepage['featuredArticleSlug']] : 0);

	$slider_ids = array();
	foreach (($homepage['sliderPublicationSlugs'] ?? array()) as $slug) {
		if (isset($publication_ids[$slug])) {
			$slider_ids[] = $publication_ids[$slug];
		}
	}

	$latest_ids = array();
	foreach (($homepage['latestPublicationSlugs'] ?? array()) as $slug) {
		if (isset($publication_ids[$slug])) {
			$latest_ids[] = $publication_ids[$slug];
		}
	}

	update_post_meta($homepage_id, 'mp_slider_publication_ids', $slider_ids);
	update_post_meta($homepage_id, 'mp_latest_publication_ids', $latest_ids);
	update_post_meta($homepage_id, 'mp_announcement_text', $homepage['announcementText'] ?? '');
	update_post_meta($homepage_id, 'mp_announcement_link_label', $homepage['announcementLinkLabel'] ?? '');
	update_post_meta($homepage_id, 'mp_announcement_link_url', $homepage['announcementLinkUrl'] ?? '');
	mp_seed_log('Saved homepage settings.');
}

flush_rewrite_rules();
mp_seed_log('WordPress seed complete.');
