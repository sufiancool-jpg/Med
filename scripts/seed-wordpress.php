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

	update_post_meta($project_id, 'mp_color', $project['color'] ?? '');
	update_post_meta($project_id, 'mp_current_stage', $project['currentStage'] ?? '');
	update_post_meta($project_id, 'mp_lead_name', $project['leadName'] ?? '');
	update_post_meta($project_id, 'mp_lead_role', $project['leadRole'] ?? '');
	update_post_meta($project_id, 'mp_lead_image', $project['leadImage'] ?? '');
	update_post_meta($project_id, 'mp_team_members', $project['teamMembers'] ?? array());
	update_post_meta($project_id, 'mp_donors', $project['donors'] ?? array());
	update_post_meta($project_id, 'mp_updates', $project['updates'] ?? array());
	update_post_meta($project_id, 'mp_focus_areas', $project['focusAreas'] ?? array());

	mp_seed_log('Saved project: ' . $project['title']);
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

	update_post_meta($publication_id, 'mp_author_name', $publication['authorName'] ?? '');
	update_post_meta($publication_id, 'mp_author_person_id', isset($person_ids[$publication['authorPersonSlug'] ?? '']) ? $person_ids[$publication['authorPersonSlug']] : 0);
	update_post_meta($publication_id, 'mp_author_role', $publication['authorRole'] ?? '');
	update_post_meta($publication_id, 'mp_author_image', $publication['authorImage'] ?? '');
	update_post_meta($publication_id, 'mp_cover_image', $publication['imageUrl'] ?? '');
	update_post_meta($publication_id, 'mp_download_url', $publication['downloadUrl'] ?? '');
	update_post_meta($publication_id, 'mp_download_label', $publication['downloadLabel'] ?? '');
	$contributor_person_ids = array();
	foreach (($publication['contributorPersonSlugs'] ?? array()) as $person_slug) {
		if (isset($person_ids[$person_slug])) {
			$contributor_person_ids[] = $person_ids[$person_slug];
		}
	}
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
