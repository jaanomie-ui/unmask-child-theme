<?php
/**
 * Dossier Data Queries
 *
 * @package UNMASK
 */

if (!defined('ABSPATH')) exit;

/**
 * Get user's active ISOs
 */
function unmask_get_user_isos($user_id, $limit = 10) {
    return new WP_Query([
        'post_type' => 'iso',
        'author' => $user_id,
        'posts_per_page' => $limit,
        'post_status' => 'publish',
        'meta_query' => [
            [
                'key' => 'iso_expiration',
                'value' => date('Ymd'),
                'compare' => '>=',
                'type' => 'DATE'
            ]
        ],
        'orderby' => 'date',
        'order' => 'DESC'
    ]);
}

/**
 * Get user's next Factory booking
 */
function unmask_get_next_factory_booking($user_id) {
    if (!class_exists('Factory_Booking_Bookings')) {
        return null;
    }

    $bookings = Factory_Booking_Bookings::get_all([
        'user_id' => $user_id,
        'date_from' => date('Y-m-d'),
        'status' => 'approved',
        'orderby' => 'booking_date',
        'order' => 'ASC',
        'limit' => 1
    ]);

    return !empty($bookings) ? $bookings[0] : null;
}

/**
 * Get user's records read count
 */
function unmask_get_records_read_count($user_id) {
    $read = get_user_meta($user_id, 'unmask_records_read', true);
    return is_array($read) ? count($read) : 0;
}

/**
 * Mark record as read
 */
function unmask_mark_record_read($user_id, $record_id) {
    $read = get_user_meta($user_id, 'unmask_records_read', true);
    if (!is_array($read)) {
        $read = [];
    }

    if (!in_array($record_id, $read)) {
        $read[] = (int) $record_id;
        update_user_meta($user_id, 'unmask_records_read', $read);
    }
    return true;
}

/**
 * Check if user has read a record
 */
function unmask_has_read_record($user_id, $record_id) {
    $read = get_user_meta($user_id, 'unmask_records_read', true);
    if (!is_array($read)) {
        return false;
    }
    return in_array((int) $record_id, $read);
}

/**
 * Get user's magazine credits
 * Assumes ACF repeater 'record_contributors' on posts with:
 *   - contributor_user (user ID)
 *   - contributor_role (select)
 */
function unmask_get_user_credits($user_id, $limit = 10) {
    if (!function_exists('get_field')) {
        return [];
    }

    // Query posts where user might be in contributors
    // This is a broad query - we filter in PHP
    $posts = get_posts([
        'post_type' => 'post',
        'posts_per_page' => 100, // Get more, filter down
        'post_status' => 'publish',
        'orderby' => 'date',
        'order' => 'DESC'
    ]);

    $credits = [];
    foreach ($posts as $post) {
        $contributors = get_field('record_contributors', $post->ID);
        if (!$contributors || !is_array($contributors)) {
            continue;
        }

        foreach ($contributors as $contributor) {
            $contributor_id = isset($contributor['contributor_user'])
                ? (is_array($contributor['contributor_user'])
                    ? $contributor['contributor_user']['ID']
                    : $contributor['contributor_user'])
                : null;

            if ($contributor_id == $user_id) {
                $credits[] = [
                    'post_id' => $post->ID,
                    'title' => $post->post_title,
                    'url' => get_permalink($post->ID),
                    'role' => isset($contributor['contributor_role']) ? $contributor['contributor_role'] : 'featured',
                    'issue' => get_field('issue_number', $post->ID) ?: '001'
                ];

                if (count($credits) >= $limit) {
                    break 2;
                }
            }
        }
    }

    return $credits;
}

/**
 * Get dashboard data aggregate
 */
function unmask_get_dashboard_data($user_id) {
    // ISOs
    $isos = unmask_get_user_isos($user_id);

    // Factory
    $factory = unmask_get_next_factory_booking($user_id);

    // BuddyBoss data
    $connections = 0;
    $pending = 0;
    $messages = 0;

    if (function_exists('bp_get_total_friend_count')) {
        $connections = bp_get_total_friend_count($user_id);
    }

    if (function_exists('friends_get_friendship_request_user_ids')) {
        $pending = count(friends_get_friendship_request_user_ids($user_id));
    }

    if (function_exists('bp_get_total_unread_message_count')) {
        $messages = bp_get_total_unread_message_count($user_id);
    }

    // Records & Credits
    $records_read = unmask_get_records_read_count($user_id);
    $credits = unmask_get_user_credits($user_id);

    return [
        'isos_active' => $isos->found_posts,
        'factory_next' => $factory,
        'connections' => $connections,
        'connections_pending' => $pending,
        'messages_unread' => $messages,
        'records_read' => $records_read,
        'credits' => count($credits),
        // Placeholder for Pink Panthers
        'pp_next' => null
    ];
}

/**
 * Get user's recent activity for dashboard
 */
function unmask_get_user_activity($user_id, $limit = 5) {
    if (!function_exists('bp_activity_get')) {
        return [];
    }

    $activities = bp_activity_get([
        'user_id' => $user_id,
        'per_page' => $limit,
        'max' => $limit,
        'display_comments' => false
    ]);

    $formatted = [];
    if (!empty($activities['activities'])) {
        foreach ($activities['activities'] as $activity) {
            $formatted[] = [
                'id' => $activity->id,
                'type' => $activity->type,
                'component' => $activity->component,
                'action' => wp_strip_all_tags($activity->action),
                'content' => wp_trim_words(wp_strip_all_tags($activity->content), 10),
                'date' => $activity->date_recorded,
                'date_formatted' => unmask_format_date($activity->date_recorded)
            ];
        }
    }

    return $formatted;
}
