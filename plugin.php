<?php
/*
  Plugin Name: Genesis Tabs Extended
  Plugin URI: http://wordpress.org/plugins/genesis-tabs-extended/
  Description: Genesis Tabs extends the Featured Post widget to create a simple tabbed area. Now you can
  also select if the selection is exclusive to the selected category (in this category only) per tab
  and you can add additional categories that always have to be excluded, when doing a query on the
  tabbed category.
  Author: StudioPress / Johan van de Merwe
  Author URI: http://www.enovision.net/genesis-tabs-extended/

  Version: 1.1

  License: GNU General Public License v2.0 (or later)
  License URI: http://www.opensource.org/licenses/gpl-license.php
 */

register_activation_hook(__FILE__, 'genesis_tabs_extended_activation_check');

/**
 * This function runs on plugin activation. It checks to make sure the required
 * minimum Genesis version is installed. If not, it deactivates itself.
 *
 * @since 0.9.0
 */
function genesis_tabs_extended_activation_check()
{

    $latest = '1.7.1';

    $theme_info = get_theme_data(TEMPLATEPATH . '/style.css');

    if ('genesis' != basename(TEMPLATEPATH)) {
        deactivate_plugins(plugin_basename(__FILE__));
        /** Deactivate ourself */
        wp_die(sprintf(__('Sorry, you can\'t activate unless you have installed <a href="%s">Genesis</a>', 'apl'), 'http://www.studiopress.com/themes/genesis'));
    }

    if (version_compare($theme_info['Version'], $latest, '<')) {
        deactivate_plugins(plugin_basename(__FILE__));
        /** Deactivate ourself */
        wp_die(sprintf(__('Sorry, you cannot activate without <a href="%s">Genesis %s</a> or greater', 'apl'), 'http://www.studiopress.com/support/showthread.php?t=19576', $latest));
    }
}

/** Initialize Genesis Tabs */
add_action('after_setup_theme', array('Genesis_Tabs_Extended', 'init'));

/**
 * Simple class to handle all the non-widget aspects of the plugin
 *
 * @package Genesis Tabs
 * @since 0.9.0
 * */
class Genesis_Tabs_Extended
{

    /** Faux Constructor */
    function init()
    {

        add_action('widgets_init', array(__CLASS__, 'register_widget'));

        add_action('wp_print_styles', array(__CLASS__, 'register_styles'));
        add_action('wp_enqueue_scripts', array(__CLASS__, 'register_scripts'));

        add_action('wp_footer', array(__CLASS__, 'footer_js'), 20);
    }

    function register_widget()
    {
        register_widget('Genesis_Tabs_Extended_Widget');
    }

    function register_scripts()
    {
        wp_enqueue_script('jquery-ui-tabs');
    }

    function register_styles()
    {
        wp_enqueue_style('genesis-tabs-stylesheet', plugins_url('style.css', __FILE__), false, '');
    }

    function footer_js()
    {
        echo '<script type="text/javascript">jQuery(document).ready(function($) { $(".ui-tabs").tabs(); });</script>' . "\n";
    }

}

/**
 * Widget Class. Handles the widget form and output.
 *
 * @package Genesis_Tabs_Extended
 * @since 0.9.0
 */
class Genesis_Tabs_Extended_Widget extends WP_Widget
{

    /** Constructor */
    function __construct()
    {
        $widget_ops = array('classname' => 'ui-tabs', 'description' => __('Displays featured posts in Tabs', 'genesis'));
        $control_ops = array('width' => 505, 'height' => 350, 'id_base' => 'tabs');
        $this->WP_Widget('tabs', __('Featured Tabs (Extended)', 'genesis'), $widget_ops, $control_ops);
    }

    /** Widget Output */
    function widget($args, $instance)
    {

        global $wp_query, $_genesis_displayed_ids;

        extract($args);

        // defaults
        $instance = wp_parse_args((array)$instance, array(
            'title' => '',
            'posts_cat_1' => '',
            'posts_cat_2' => '',
            'posts_cat_3' => '',
            'posts_cat_4' => '',
            'posts_cat_5' => '',
            'posts_cat_6' => '',
            'posts_cat_7' => '',
            'posts_cat_8' => '',
            'exclude_displayed' => 0,
            'excluded_categories' => '',
            'first_tab_recent' => 0,
            'first_tab_recent_header' => '',
            'show_image' => 0,
            'image_alignment' => '',
            'image_size' => '',
            'show_title' => 0,
            'show_byline' => 0,
            'post_info' => '[post_date] ' . __('By', 'genesis') . ' [post_author_posts_link] [post_comments]',
            'show_content' => 'excerpt',
            'content_limit' => '',
            'more_text' => __('[Read More...]', 'genesis')
        ));

        echo $before_widget;

        // check if the first tab has to be the most recent contribution
        $cat0 = $instance['first_tab_recent'] ? 'most-recent-tab' : false;

        // Pull the chosen categories into an array
        $cats = array($cat0, $instance['posts_cat_1'], $instance['posts_cat_2'], $instance['posts_cat_3'], $instance['posts_cat_4'], $instance['posts_cat_5'], $instance['posts_cat_6'], $instance['posts_cat_7'], $instance['posts_cat_8']);
        $exclude = $instance['excluded_categories'];

        // Display the tab links
        echo '<ul class="ui-tabs-nav">';

        foreach ((array)$cats as $cat) {
            if ($cat) {
                if ($cat !== 'most-recent-tab') {
                    echo '<li><a href="#cat-' . $cat . '">' . get_cat_name($cat) . '</a></li>';
                } else {
                    $header = $args['first_tab_recent_header'] !== '' ? $instance['first_tab_recent_header'] : '&nbsp;';
                    echo '<li><a href="#cat-most-recent-tab">' . $header . '</a></li>';
                }
            }
        }
        echo '</ul>';

        // Loop through all chosen categories
        foreach ((array)$cats as $idx => $cat) :

            if (!$cat)
                continue; // skip iteration if $cat is empty

// Custom loop

            $args = array('showposts' => 1, 'orderby' => 'date', 'order' => 'DESC');

            if ($cat !== 'most-recent-tab') {

                $args['cat'] = $cat;

                $exclude_cp = $exclude; // make a copy first

                while ($idx !== false) {
                    $idx = array_search($cat, $exclude_cp);
                    if ($idx !== false)
                        unset($exclude_cp[$idx]);
                }

                if (count($exclude_cp) > 0) {
                    $args['category__not_in'] = $exclude_cp;
                }

                if ($instance['exclude_displayed']) {
                    $args['post__not_in'] = (array)$_genesis_displayed_ids;
                }

            }

            $tabbed_posts = new WP_Query($args);

            if ($tabbed_posts->have_posts()) : while ($tabbed_posts->have_posts()) : $tabbed_posts->the_post();

                $_genesis_displayed_ids[] = get_the_ID();

                echo ' <div id="cat-' . $cat . '" ';
                post_class('ui-tabs-hide');
                echo '>';

                if (!empty($instance['show_image'])) :
                    printf(' <a href="%s" title="%s" class="%s" >%s </a> ', get_permalink(), the_title_attribute('echo=0'), esc_attr($instance['image_alignment']), genesis_get_image(array('format' => 'html', 'size' => $instance['image_size'])));
                endif;

                if (!empty($instance['show_title'])) :
                    printf(' <h2><a href="%s" title = "%s" >%s </a></h2> ', get_permalink(), the_title_attribute('echo=0'), get_the_title());
                endif;

                if (!empty($instance['show_byline']) && !empty($instance['post_info'])) :
                    printf(' <p class="byline post-info">%s </p> ', do_shortcode(esc_html($instance['post_info'])));
                endif;

                if (!empty($instance['show_content'])) :

                    if ('excerpt' == $instance['show_content']) :
                        the_excerpt();
                    elseif ('content - limit' == $instance['show_content']) :
                        the_content_limit((int)$instance['content_limit'], esc_html($instance['more_text']));
                    else :
                        the_content(esc_html($instance['more_text']));
                    endif;

                endif;

                echo ' </div ><!--end post_class()-->' . "\n\n";

            endwhile;
            endif;

        endforeach;

        echo $after_widget;
        wp_reset_query();
    }

/** Update hook */
function update($new_instance, $old_instance)
{
    return $new_instance;
}

/** Form output */
function form($instance)
{

    // ensure value exists
    $instance = wp_parse_args((array)$instance, array(
        'title' => '',
        'posts_cat_1' => '',
        'posts_cat_2' => '',
        'posts_cat_3' => '',
        'posts_cat_4' => '',
        'posts_cat_5' => '',
        'posts_cat_6' => '',
        'posts_cat_7' => '',
        'posts_cat_8' => '',
        'exclude_displayed' => '',
        'excluded_categories' => '', // excluded categories
        'first_tab_recent' => '',
        'first_tab_recent_header' => '',
        'show_image' => 0,
        'image_alignment' => '',
        'image_size' => '',
        'show_title' => 0,
        'show_byline' => 0,
        'post_info' => '[post_date] ' . __('By', 'genesis') . ' [post_author_posts_link] [post_comments]',
        'show_content' => 'excerpt',
        'content_limit' => '',
        'more_text' => __('[Read More...]', 'genesis')
    ));
    ?>

    <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'genesis'); ?>:</label>
        <input type="text" id="<?php echo $this->get_field_id('title'); ?>"
               name="<?php echo $this->get_field_name('title'); ?>"
               value="<?php echo esc_attr($instance['title']); ?>" style="width:95%;"/></p>

    <div class="genesis-widget-column-right">

        <div class="genesis-widget-column-box">

            <p><span class="description">Recent post in first Tab?</em></span></p>

            <p>
                <input id="<?php echo $this->get_field_id('first_tab_recent'); ?>" type="checkbox"
                       name="<?php echo $this->get_field_name('first_tab_recent'); ?>"
                       value="1" <?php checked($instance['first_tab_recent']); ?>/>
                <label
                    for="<?php echo $this->get_field_id('first_tab_recent'); ?>"><?php _e('Show most recent post in first tab ? ', 'genesis'); ?></label>
            </p>

            <p><label
                    for="<?php echo $this->get_field_id('first_tab_recent_header'); ?>"><?php _e('First Tab Header', 'genesis'); ?>
                    :</label>
                <input type="text" id="<?php echo $this->get_field_id('first_tab_recent_header'); ?>"
                       name="<?php echo $this->get_field_name('first_tab_recent_header'); ?>"
                       value="<?php echo esc_attr($instance['first_tab_recent_header']); ?>" style="width:95%;"/>
            </p>

            <p><span class="description">Choose up to 8 categories to pull posts from.<br/> Each category you choose will represent a single tab.</span>
            </p>

            <p><?php wp_dropdown_categories(array('name' => $this->get_field_name('posts_cat_1'), 'selected' => $instance['posts_cat_1'], 'orderby' => 'Name', 'hierarchical' => 1, 'show_option_all' => __("- None Selected -", 'genesis'), 'hide_empty' => '0')); ?>
            </p>

            <p><?php wp_dropdown_categories(array('name' => $this->get_field_name('posts_cat_2'), 'selected' => $instance['posts_cat_2'], 'orderby' => 'Name', 'hierarchical' => 1, 'show_option_all' => __("- None Selected -", 'genesis'), 'hide_empty' => '0')); ?>
            </p>

            <p><?php wp_dropdown_categories(array('name' => $this->get_field_name('posts_cat_3'), 'selected' => $instance['posts_cat_3'], 'orderby' => 'Name', 'hierarchical' => 1, 'show_option_all' => __("- None Selected -", 'genesis'), 'hide_empty' => '0')); ?>
            </p>

            <p><?php wp_dropdown_categories(array('name' => $this->get_field_name('posts_cat_4'), 'selected' => $instance['posts_cat_4'], 'orderby' => 'Name', 'hierarchical' => 1, 'show_option_all' => __("- None Selected -", 'genesis'), 'hide_empty' => '0')); ?>
            </p>

            <p><?php wp_dropdown_categories(array('name' => $this->get_field_name('posts_cat_5'), 'selected' => $instance['posts_cat_5'], 'orderby' => 'Name', 'hierarchical' => 1, 'show_option_all' => __("- None Selected -", 'genesis'), 'hide_empty' => '0')); ?>
            </p>

            <p><?php wp_dropdown_categories(array('name' => $this->get_field_name('posts_cat_6'), 'selected' => $instance['posts_cat_6'], 'orderby' => 'Name', 'hierarchical' => 1, 'show_option_all' => __("- None Selected -", 'genesis'), 'hide_empty' => '0')); ?>
            </p>

            <p><?php wp_dropdown_categories(array('name' => $this->get_field_name('posts_cat_7'), 'selected' => $instance['posts_cat_7'], 'orderby' => 'Name', 'hierarchical' => 1, 'show_option_all' => __("- None Selected -", 'genesis'), 'hide_empty' => '0')); ?>
            </p>

            <p><?php wp_dropdown_categories(array('name' => $this->get_field_name('posts_cat_8'), 'selected' => $instance['posts_cat_8'], 'orderby' => 'Name', 'hierarchical' => 1, 'show_option_all' => __("- None Selected -", 'genesis'), 'hide_empty' => '0')); ?>
            </p>

            <p><span class="description">Don't include posts from these categories < br /><em>if post is in more
                        than
                        one category, it is not used when it is in one of the selected categories below
                        .</em></span>
            </p>

            <ul>
                <?php
                $excluded_categories = $instance['excluded_categories'];

                $wp_categories = get_categories();

                foreach ($wp_categories as $idx => $cat) {

                    $field = $this->get_field_name('excluded_categories');
                    $id = $this->get_field_id('excluded_categories');

                    $checked = in_array($cat->term_id, $excluded_categories) ? ' checked="1"' : '';

                    echo '<li id="category-"' . $cat->term_id . '>';
                    echo '<input id="' . $id . '"' .
                        ' type="checkbox" name="' . $field . '[' . $idx . ']"' .
                        ' value="' . $cat->term_id . '"' . $checked . '/>';
                    echo '<label for="' . $id . '">' . $cat->name . '</label>';
                    echo '</li>';
                }
                ?>
            </ul>

            <p><span class="description">Don't show the same post more than once.</em></span></p>

            <p>
                <input id="<?php echo $this->get_field_id('exclude_displayed'); ?>" type="checkbox"
                       name="<?php echo $this->get_field_name('exclude_displayed'); ?>"
                       value="1" <?php checked($instance['exclude_displayed']); ?>/>
                <label
                    for="<?php echo $this->get_field_id('exclude_displayed'); ?>"><?php _e('Exclude Previously Displayed Posts?', 'genesis'); ?></label>
            </p>

        </div>

    </div>

    <div class="genesis-widget-column genesis-widget-column-right">

        <div class="genesis-widget-column-box genesis-widget-column-box-top">

            <p><input id="<?php echo $this->get_field_id('show_image'); ?>" type="checkbox"
                      name="<?php echo $this->get_field_name('show_image'); ?>"
                      value="1" <?php checked(1, $instance['show_image']); ?>/> <label
                    for="<?php echo $this->get_field_id('show_image'); ?>"><?php _e('Show Featured Image', 'genesis'); ?></label>
            </p>

            <p><label for="<?php echo $this->get_field_id('image_size'); ?>"><?php _e('Image Size', 'genesis'); ?>
                    :</label>
                <?php $sizes = genesis_get_additional_image_sizes(); ?>
                <select id="<?php echo $this->get_field_id('image_size'); ?>"
                        name="<?php echo $this->get_field_name('image_size'); ?>">
                    <option value="thumbnail">thumbnail (<?php echo get_option('thumbnail_size_w'); ?>
                        x<?php echo get_option('thumbnail_size_h'); ?>)
                    </option>
                    <?php
                    foreach ((array)$sizes as $name => $size) :
                        echo '<option value="' . esc_attr($name) . '" ' . selected($name, $instance['image_size'], FALSE) . '>' . esc_html($name) . ' (' . $size['width'] . 'x' . $size['height'] . ')</option>';
                    endforeach;
                    ?>
                </select></p>

            <p><label
                    for="<?php echo $this->get_field_id('image_alignment'); ?>"><?php _e('Image Alignment', 'genesis'); ?>
                    :</label>
                <select id="<?php echo $this->get_field_id('image_alignment'); ?>"
                        name="<?php echo $this->get_field_name('image_alignment'); ?>">
                    <option value="">- <?php _e('None', 'genesis'); ?> -</option>
                    <option
                        value="alignleft" <?php selected('alignleft', $instance['image_alignment']); ?>><?php _e('Left', 'genesis'); ?></option>
                    <option
                        value="alignright" <?php selected('alignright', $instance['image_alignment']); ?>><?php _e('Right', 'genesis'); ?></option>
                </select></p>

        </div>

        <div class="genesis-widget-column-box">

            <p><input id="<?php echo $this->get_field_id('show_title'); ?>" type="checkbox"
                      name="<?php echo $this->get_field_name('show_title'); ?>"
                      value="1" <?php checked(1, $instance['show_title']); ?>/> <label
                    for="<?php echo $this->get_field_id('show_title'); ?>"><?php _e('Show Post Title', 'genesis'); ?></label>
            </p>

            <p><input id="<?php echo $this->get_field_id('show_byline'); ?>" type="checkbox"
                      name="<?php echo $this->get_field_name('show_byline'); ?>"
                      value="1" <?php checked(1, $instance['show_byline']); ?>/> <label
                    for="<?php echo $this->get_field_id('show_byline'); ?>"><?php _e('Show Post Info', 'genesis'); ?></label>

                <input type="text" id="<?php echo $this->get_field_id('post_info'); ?>"
                       name="<?php echo $this->get_field_name('post_info'); ?>"
                       value="<?php echo esc_attr($instance['post_info']); ?>" class="widefat"/>

            </p>

            <p><label
                    for="<?php echo $this->get_field_id('show_content'); ?>"><?php _e('Content Type', 'genesis'); ?>
                    :</label>
                <select id="<?php echo $this->get_field_id('show_content'); ?>"
                        name="<?php echo $this->get_field_name('show_content'); ?>">
                    <option
                        value="content" <?php selected('content', $instance['show_content']); ?>><?php _e('Show Content', 'genesis'); ?></option>
                    <option
                        value="excerpt" <?php selected('excerpt', $instance['show_content']); ?>><?php _e('Show Excerpt', 'genesis'); ?></option>
                    <option
                        value="content-limit" <?php selected('content-limit', $instance['show_content']); ?>><?php _e('Show Content Limit', 'genesis'); ?></option>
                    <option
                        value="" <?php selected('', $instance['show_content']); ?>><?php _e('No Content', 'genesis'); ?></option>
                </select>

                <br/><label
                    for="<?php echo $this->get_field_id('content_limit'); ?>"><?php _e('Limit content to', 'genesis'); ?></label>
                <input type="text" id="<?php echo $this->get_field_id('image_alignment'); ?>"
                       name="<?php echo $this->get_field_name('content_limit'); ?>"
                       value="<?php echo esc_attr(intval($instance['content_limit'])); ?>"
                       size="3"/> <?php _e('characters', 'genesis'); ?></p>

            <p><label
                    for="<?php echo $this->get_field_id('more_text'); ?>"><?php _e('More Text (if applicable)', 'genesis'); ?>
                    :</label>
                <input type="text" id="<?php echo $this->get_field_id('more_text'); ?>"
                       name="<?php echo $this->get_field_name('more_text'); ?>"
                       value="<?php echo esc_attr($instance['more_text']); ?>"/></p>

        </div>

        <br/>

    </div>

<?php
}

}