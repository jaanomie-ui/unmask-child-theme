<?php
/**
 * UNMASK Visitor Grid Widget
 *
 * Displays visitors in a grid format with designation and scene name.
 * "any time another user appears on the site, you should be able to see them"
 *
 * @package UNMASK
 * @since 1.0.0
 */

if (!defined('ABSPATH')) exit;

class UNMASK_Visitor_Grid_Widget extends WP_Widget {

    /**
     * Register widget with WordPress.
     */
    public function __construct() {
        parent::__construct(
            'unmask_visitor_grid',
            'UNMASK: Visitor Grid',
            [
                'description' => 'Displays visitors in a grid with designation + scene name.',
                'classname' => 'widget-visitor-grid',
            ]
        );
    }

    /**
     * Front-end display of widget.
     */
    public function widget($args, $instance) {
        $title = !empty($instance['title']) ? $instance['title'] : 'visitors';
        $count = !empty($instance['count']) ? absint($instance['count']) : 12;
        $order = !empty($instance['order']) ? $instance['order'] : 'active';
        $columns = !empty($instance['columns']) ? absint($instance['columns']) : 3;

        echo $args['before_widget'];

        if ($title) {
            echo $args['before_title'] . esc_html($title) . $args['after_title'];
        }

        // Get visitors based on order
        $visitors = $this->get_visitors($count, $order);

        if (!empty($visitors)) {
            echo '<div class="visitor-grid visitor-grid--cols-' . esc_attr($columns) . '">';
            foreach ($visitors as $visitor) {
                $this->render_visitor_cell($visitor);
            }
            echo '</div>';

            // Link to member directory
            if (function_exists('bp_get_members_directory_permalink')) {
                echo '<div class="visitor-grid__footer">';
                echo '<a href="' . esc_url(bp_get_members_directory_permalink()) . '" class="visitor-grid__link">view all</a>';
                echo '</div>';
            }
        } else {
            echo '<p class="visitor-grid__empty">no visitors yet</p>';
        }

        echo $args['after_widget'];
    }

    /**
     * Render a single visitor cell
     */
    private function render_visitor_cell($visitor) {
        $user_id = $visitor->ID;
        $scene_name = '';
        $designation = '';

        // Get scene name from xProfile
        if (function_exists('xprofile_get_field_data')) {
            $scene_name = xprofile_get_field_data(3, $user_id);
        }
        if (empty($scene_name)) {
            $scene_name = $visitor->display_name;
        }

        // Get designation
        if (function_exists('unmask_get_designation')) {
            $designation = unmask_get_designation($user_id);
        } else {
            $designation = 'V-' . str_pad($user_id, 3, '0', STR_PAD_LEFT);
        }

        // Get designation color based on viewer relationship
        $designation_color = function_exists('unmask_get_designation_color_class')
            ? unmask_get_designation_color_class($user_id)
            : 'designation--yellow';

        // Get avatar URL
        $avatar_url = '';
        if (function_exists('bp_core_fetch_avatar')) {
            $avatar_url = bp_core_fetch_avatar([
                'item_id' => $user_id,
                'type' => 'thumb',
                'width' => 48,
                'height' => 48,
                'html' => false
            ]);
        } else {
            $avatar_url = get_avatar_url($user_id, ['size' => 48]);
        }

        // Get profile URL
        $profile_url = function_exists('bp_core_get_user_domain')
            ? bp_core_get_user_domain($user_id)
            : get_author_posts_url($user_id);

        // Is drone?
        $is_drone = function_exists('unmask_get_member_type')
            ? (unmask_get_member_type($user_id) === 'drone')
            : false;
        ?>
        <a href="<?php echo esc_url($profile_url); ?>" class="visitor-cell <?php echo $is_drone ? 'visitor-cell--drone' : ''; ?>" title="<?php echo esc_attr($scene_name); ?>">
            <div class="visitor-cell__avatar">
                <?php if ($avatar_url) : ?>
                    <img src="<?php echo esc_url($avatar_url); ?>" alt="">
                <?php else : ?>
                    <span class="visitor-cell__placeholder">&#9670;</span>
                <?php endif; ?>
            </div>
            <span class="visitor-cell__designation designation <?php echo esc_attr($designation_color); ?> <?php echo $is_drone ? 'designation--drone' : ''; ?>"><?php echo esc_html($designation); ?></span>
            <span class="visitor-cell__name"><?php echo esc_html($scene_name); ?></span>
        </a>
        <?php
    }

    /**
     * Get visitors based on order preference
     */
    private function get_visitors($count, $order) {
        global $wpdb;

        $args = [
            'number' => $count,
            'fields' => ['ID', 'display_name'],
        ];

        switch ($order) {
            case 'newest':
                $args['orderby'] = 'registered';
                $args['order'] = 'DESC';
                break;

            case 'active':
            default:
                // BuddyBoss stores last activity in bp_activity or user meta
                if (function_exists('bp_core_get_users')) {
                    $bp_users = bp_core_get_users([
                        'type' => 'active',
                        'per_page' => $count,
                    ]);
                    if (!empty($bp_users['users'])) {
                        return $bp_users['users'];
                    }
                }
                // Fallback to newest
                $args['orderby'] = 'registered';
                $args['order'] = 'DESC';
                break;

            case 'random':
                $args['orderby'] = 'rand';
                break;
        }

        $query = new WP_User_Query($args);
        return $query->get_results();
    }

    /**
     * Back-end widget form.
     */
    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : 'visitors';
        $count = !empty($instance['count']) ? absint($instance['count']) : 12;
        $order = !empty($instance['order']) ? $instance['order'] : 'active';
        $columns = !empty($instance['columns']) ? absint($instance['columns']) : 3;
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">Title:</label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text"
                   value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('count')); ?>">Number of visitors:</label>
            <input class="tiny-text" id="<?php echo esc_attr($this->get_field_id('count')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('count')); ?>" type="number"
                   step="1" min="3" max="24" value="<?php echo esc_attr($count); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('columns')); ?>">Columns:</label>
            <select class="widefat" id="<?php echo esc_attr($this->get_field_id('columns')); ?>"
                    name="<?php echo esc_attr($this->get_field_name('columns')); ?>">
                <option value="2" <?php selected($columns, 2); ?>>2 columns</option>
                <option value="3" <?php selected($columns, 3); ?>>3 columns</option>
                <option value="4" <?php selected($columns, 4); ?>>4 columns</option>
            </select>
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('order')); ?>">Order by:</label>
            <select class="widefat" id="<?php echo esc_attr($this->get_field_id('order')); ?>"
                    name="<?php echo esc_attr($this->get_field_name('order')); ?>">
                <option value="active" <?php selected($order, 'active'); ?>>Recently active</option>
                <option value="newest" <?php selected($order, 'newest'); ?>>Newest members</option>
                <option value="random" <?php selected($order, 'random'); ?>>Random</option>
            </select>
        </p>
        <?php
    }

    /**
     * Sanitize widget form values as they are saved.
     */
    public function update($new_instance, $old_instance) {
        $instance = [];
        $instance['title'] = !empty($new_instance['title']) ? sanitize_text_field($new_instance['title']) : '';
        $instance['count'] = !empty($new_instance['count']) ? absint($new_instance['count']) : 12;
        $instance['columns'] = !empty($new_instance['columns']) ? absint($new_instance['columns']) : 3;
        $instance['order'] = !empty($new_instance['order']) ? sanitize_key($new_instance['order']) : 'active';
        return $instance;
    }
}

/**
 * Register the widget
 */
function unmask_register_visitor_grid_widget() {
    register_widget('UNMASK_Visitor_Grid_Widget');
}
add_action('widgets_init', 'unmask_register_visitor_grid_widget');
