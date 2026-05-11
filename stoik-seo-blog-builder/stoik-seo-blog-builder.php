<?php
/**
 * Plugin Name: Stoik SEO Blog Builder
 * Description: Adds a streamlined blog post creation dashboard with SEO guidance and a distinctive blog homepage shortcode.
 * Version: 0.1.0
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Author: Stoik
 * Text Domain: stoik-seo-blog-builder
 */

if (!defined('ABSPATH')) {
    exit;
}

final class Stoik_SEO_Blog_Builder
{
    const VERSION = '0.1.0';
    const SLUG = 'stoik-seo-blog-builder';
    const ADMIN_CAPABILITY = 'manage_options';

    public function __construct()
    {
        add_action('admin_menu', array($this, 'register_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('admin_post_sbb_create_post', array($this, 'handle_create_post'));
        add_action('wp_enqueue_scripts', array($this, 'register_public_assets'));
        add_action('init', array($this, 'register_shortcodes'));
        add_action('wp_head', array($this, 'output_seo_meta'), 2);
        add_filter('the_posts', array($this, 'enqueue_shortcode_assets'));
        add_filter('pre_get_document_title', array($this, 'filter_document_title'));
    }

    public function register_admin_menu()
    {
        add_menu_page(
            __('SEO Blog Builder', 'stoik-seo-blog-builder'),
            __('SEO Blog Builder', 'stoik-seo-blog-builder'),
            self::ADMIN_CAPABILITY,
            self::SLUG,
            array($this, 'render_admin_page'),
            'dashicons-welcome-write-blog',
            25
        );
    }

    public function enqueue_admin_assets($hook)
    {
        if ('toplevel_page_' . self::SLUG !== $hook) {
            return;
        }

        wp_enqueue_media();
        wp_enqueue_style(
            'sbb-admin',
            plugin_dir_url(__FILE__) . 'assets/admin.css',
            array(),
            self::VERSION
        );
        wp_enqueue_script(
            'sbb-admin',
            plugin_dir_url(__FILE__) . 'assets/admin.js',
            array('jquery'),
            self::VERSION,
            true
        );
        wp_localize_script(
            'sbb-admin',
            'sbbAdmin',
            array(
                'chooseFeatured' => __('Choose featured image', 'stoik-seo-blog-builder'),
                'chooseImages' => __('Choose supporting images', 'stoik-seo-blog-builder'),
                'useImage' => __('Use this image', 'stoik-seo-blog-builder'),
                'useImages' => __('Use these images', 'stoik-seo-blog-builder'),
            )
        );
    }

    public function register_public_assets()
    {
        $this->register_public_style();

        if (is_singular('post')) {
            $post_id = get_queried_object_id();
            if ($post_id && get_post_meta($post_id, '_sbb_created', true)) {
                wp_enqueue_style('sbb-public');
            }
        }
    }

    public function register_shortcodes()
    {
        add_shortcode('stoik_blog_home', array($this, 'render_blog_home'));
        add_shortcode('seo_blog_home', array($this, 'render_blog_home'));
    }

    public function enqueue_shortcode_assets($posts)
    {
        if (is_admin() || empty($posts)) {
            return $posts;
        }

        foreach ($posts as $post) {
            if (
                has_shortcode($post->post_content, 'stoik_blog_home')
                || has_shortcode($post->post_content, 'seo_blog_home')
            ) {
                $this->register_public_style();
                wp_enqueue_style('sbb-public');
                break;
            }
        }

        return $posts;
    }

    private function register_public_style()
    {
        if (wp_style_is('sbb-public', 'registered')) {
            return;
        }

        wp_register_style(
            'sbb-public',
            plugin_dir_url(__FILE__) . 'assets/public.css',
            array(),
            self::VERSION
        );
    }

    public function render_admin_page()
    {
        if (!current_user_can(self::ADMIN_CAPABILITY)) {
            wp_die(esc_html__('You do not have permission to create posts.', 'stoik-seo-blog-builder'));
        }

        $created_id = isset($_GET['sbb_created']) ? absint($_GET['sbb_created']) : 0;
        $error = isset($_GET['sbb_error']) ? sanitize_key(wp_unslash($_GET['sbb_error'])) : '';
        ?>
        <div class="wrap sbb-admin-wrap">
            <div class="sbb-admin-hero">
                <div>
                    <span class="sbb-kicker"><?php esc_html_e('Stoik publishing system', 'stoik-seo-blog-builder'); ?></span>
                    <h1><?php esc_html_e('SEO Blog Builder', 'stoik-seo-blog-builder'); ?></h1>
                    <p><?php esc_html_e('Drop in copy, attach images, tune SEO basics, and publish a polished WordPress post from one dashboard.', 'stoik-seo-blog-builder'); ?></p>
                </div>
                <div class="sbb-shortcode-card">
                    <span><?php esc_html_e('Blog homepage shortcode', 'stoik-seo-blog-builder'); ?></span>
                    <code>[stoik_blog_home]</code>
                    <small><?php esc_html_e('Paste this into a WordPress page named Blog.', 'stoik-seo-blog-builder'); ?></small>
                </div>
            </div>

            <?php if ($created_id) : ?>
                <div class="notice notice-success is-dismissible">
                    <p>
                        <?php
                        printf(
                            wp_kses_post(__('Blog post created. <a href="%1$s">Edit it</a> or <a href="%2$s" target="_blank" rel="noopener">view it</a>.', 'stoik-seo-blog-builder')),
                            esc_url(get_edit_post_link($created_id)),
                            esc_url(get_permalink($created_id))
                        );
                        ?>
                    </p>
                </div>
            <?php endif; ?>

            <?php if ($error) : ?>
                <div class="notice notice-error is-dismissible">
                    <p><?php echo esc_html($this->get_error_message($error)); ?></p>
                </div>
            <?php endif; ?>

            <form class="sbb-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field('sbb_create_post', 'sbb_nonce'); ?>
                <input type="hidden" name="action" value="sbb_create_post" />

                <div class="sbb-layout">
                    <main class="sbb-panel sbb-main-panel">
                        <section class="sbb-section">
                            <div class="sbb-section-head">
                                <span>01</span>
                                <div>
                                    <h2><?php esc_html_e('Post copy', 'stoik-seo-blog-builder'); ?></h2>
                                    <p><?php esc_html_e('Paste your post copy here. Plain text will be cleaned into paragraphs, headings, and lists.', 'stoik-seo-blog-builder'); ?></p>
                                </div>
                            </div>

                            <label class="sbb-field">
                                <span><?php esc_html_e('Post title', 'stoik-seo-blog-builder'); ?></span>
                                <input type="text" name="sbb_title" class="sbb-title-input" required placeholder="<?php esc_attr_e('Example: Five Training Lessons That Build Better Consistency', 'stoik-seo-blog-builder'); ?>" />
                            </label>

                            <div class="sbb-editor-wrap">
                                <?php
                                wp_editor(
                                    '',
                                    'sbb_body_editor',
                                    array(
                                        'textarea_name' => 'sbb_body',
                                        'textarea_rows' => 16,
                                        'media_buttons' => true,
                                        'teeny' => false,
                                        'quicktags' => true,
                                    )
                                );
                                ?>
                            </div>

                            <label class="sbb-field">
                                <span><?php esc_html_e('Short excerpt', 'stoik-seo-blog-builder'); ?></span>
                                <textarea name="sbb_excerpt" rows="3" placeholder="<?php esc_attr_e('A concise summary used in the blog homepage and search results.', 'stoik-seo-blog-builder'); ?>"></textarea>
                            </label>
                        </section>

                        <section class="sbb-section">
                            <div class="sbb-section-head">
                                <span>02</span>
                                <div>
                                    <h2><?php esc_html_e('Images', 'stoik-seo-blog-builder'); ?></h2>
                                    <p><?php esc_html_e('Choose a featured image and optional supporting images to append inside the post.', 'stoik-seo-blog-builder'); ?></p>
                                </div>
                            </div>

                            <div class="sbb-image-grid">
                                <div class="sbb-image-picker">
                                    <label><?php esc_html_e('Featured image', 'stoik-seo-blog-builder'); ?></label>
                                    <input type="hidden" name="sbb_featured_image_id" class="sbb-featured-image-id" />
                                    <button type="button" class="button button-secondary sbb-select-featured"><?php esc_html_e('Choose featured image', 'stoik-seo-blog-builder'); ?></button>
                                    <button type="button" class="button-link sbb-clear-featured"><?php esc_html_e('Clear', 'stoik-seo-blog-builder'); ?></button>
                                    <div class="sbb-featured-preview sbb-preview-box"></div>
                                </div>

                                <div class="sbb-image-picker">
                                    <label><?php esc_html_e('Supporting images', 'stoik-seo-blog-builder'); ?></label>
                                    <input type="hidden" name="sbb_image_ids" class="sbb-image-ids" />
                                    <button type="button" class="button button-secondary sbb-select-images"><?php esc_html_e('Choose supporting images', 'stoik-seo-blog-builder'); ?></button>
                                    <button type="button" class="button-link sbb-clear-images"><?php esc_html_e('Clear', 'stoik-seo-blog-builder'); ?></button>
                                    <div class="sbb-images-preview sbb-preview-box sbb-preview-grid"></div>
                                </div>
                            </div>

                            <label class="sbb-field">
                                <span><?php esc_html_e('Default image alt text', 'stoik-seo-blog-builder'); ?></span>
                                <input type="text" name="sbb_image_alt" class="sbb-image-alt" placeholder="<?php esc_attr_e('Describe the image for accessibility and SEO.', 'stoik-seo-blog-builder'); ?>" />
                            </label>
                        </section>
                    </main>

                    <aside class="sbb-panel sbb-side-panel">
                        <section class="sbb-section">
                            <div class="sbb-section-head compact">
                                <span>03</span>
                                <div>
                                    <h2><?php esc_html_e('SEO controls', 'stoik-seo-blog-builder'); ?></h2>
                                    <p><?php esc_html_e('These fields feed this plugin and popular SEO plugin meta keys.', 'stoik-seo-blog-builder'); ?></p>
                                </div>
                            </div>

                            <label class="sbb-field">
                                <span><?php esc_html_e('SEO title', 'stoik-seo-blog-builder'); ?></span>
                                <input type="text" name="sbb_seo_title" class="sbb-seo-title" maxlength="80" placeholder="<?php esc_attr_e('Aim for 50-60 characters.', 'stoik-seo-blog-builder'); ?>" />
                                <small><strong class="sbb-title-count">0</strong>/60 <?php esc_html_e('recommended characters', 'stoik-seo-blog-builder'); ?></small>
                            </label>

                            <label class="sbb-field">
                                <span><?php esc_html_e('Meta description', 'stoik-seo-blog-builder'); ?></span>
                                <textarea name="sbb_meta_description" class="sbb-meta-description" rows="4" maxlength="180" placeholder="<?php esc_attr_e('Aim for 120-160 characters.', 'stoik-seo-blog-builder'); ?>"></textarea>
                                <small><strong class="sbb-description-count">0</strong>/160 <?php esc_html_e('recommended characters', 'stoik-seo-blog-builder'); ?></small>
                            </label>

                            <label class="sbb-field">
                                <span><?php esc_html_e('Focus keyword', 'stoik-seo-blog-builder'); ?></span>
                                <input type="text" name="sbb_focus_keyword" class="sbb-focus-keyword" placeholder="<?php esc_attr_e('Example: strength training consistency', 'stoik-seo-blog-builder'); ?>" />
                            </label>

                            <div class="sbb-checklist">
                                <h3><?php esc_html_e('Publish checklist', 'stoik-seo-blog-builder'); ?></h3>
                                <ul>
                                    <li data-check="title"><?php esc_html_e('SEO title is in the target range.', 'stoik-seo-blog-builder'); ?></li>
                                    <li data-check="description"><?php esc_html_e('Meta description is in the target range.', 'stoik-seo-blog-builder'); ?></li>
                                    <li data-check="keyword"><?php esc_html_e('Focus keyword is present.', 'stoik-seo-blog-builder'); ?></li>
                                    <li data-check="featured"><?php esc_html_e('Featured image selected.', 'stoik-seo-blog-builder'); ?></li>
                                    <li data-check="alt"><?php esc_html_e('Image alt text is ready.', 'stoik-seo-blog-builder'); ?></li>
                                </ul>
                            </div>
                        </section>

                        <section class="sbb-section">
                            <div class="sbb-section-head compact">
                                <span>04</span>
                                <div>
                                    <h2><?php esc_html_e('Publish settings', 'stoik-seo-blog-builder'); ?></h2>
                                </div>
                            </div>

                            <label class="sbb-field">
                                <span><?php esc_html_e('Status', 'stoik-seo-blog-builder'); ?></span>
                                <select name="sbb_status">
                                    <option value="draft"><?php esc_html_e('Save as draft', 'stoik-seo-blog-builder'); ?></option>
                                    <?php if (current_user_can('publish_posts')) : ?>
                                        <option value="publish"><?php esc_html_e('Publish immediately', 'stoik-seo-blog-builder'); ?></option>
                                    <?php endif; ?>
                                    <option value="pending"><?php esc_html_e('Pending review', 'stoik-seo-blog-builder'); ?></option>
                                </select>
                            </label>

                            <div class="sbb-field">
                                <span><?php esc_html_e('Categories', 'stoik-seo-blog-builder'); ?></span>
                                <div class="sbb-category-list">
                                    <?php $this->render_category_checkboxes(); ?>
                                </div>
                            </div>

                            <label class="sbb-field">
                                <span><?php esc_html_e('Tags', 'stoik-seo-blog-builder'); ?></span>
                                <input type="text" name="sbb_tags" placeholder="<?php esc_attr_e('Separate tags with commas.', 'stoik-seo-blog-builder'); ?>" />
                            </label>

                            <?php submit_button(__('Create blog post', 'stoik-seo-blog-builder'), 'primary large', 'submit', false); ?>
                        </section>
                    </aside>
                </div>
            </form>
        </div>
        <?php
    }

    public function handle_create_post()
    {
        if (!current_user_can(self::ADMIN_CAPABILITY)) {
            wp_die(esc_html__('You do not have permission to create posts.', 'stoik-seo-blog-builder'));
        }

        if (
            !isset($_POST['sbb_nonce'])
            || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['sbb_nonce'])), 'sbb_create_post')
        ) {
            $this->redirect_with_error('nonce');
        }

        $title = isset($_POST['sbb_title']) ? sanitize_text_field(wp_unslash($_POST['sbb_title'])) : '';
        $raw_body = isset($_POST['sbb_body']) ? wp_kses_post(wp_unslash($_POST['sbb_body'])) : '';
        $excerpt = isset($_POST['sbb_excerpt']) ? sanitize_textarea_field(wp_unslash($_POST['sbb_excerpt'])) : '';
        $status = isset($_POST['sbb_status']) ? sanitize_key(wp_unslash($_POST['sbb_status'])) : 'draft';
        $status = in_array($status, array('draft', 'publish', 'pending'), true) ? $status : 'draft';
        if ('publish' === $status && !current_user_can('publish_posts')) {
            $status = 'pending';
        }
        $seo_title = isset($_POST['sbb_seo_title']) ? sanitize_text_field(wp_unslash($_POST['sbb_seo_title'])) : '';
        $meta_description = isset($_POST['sbb_meta_description']) ? sanitize_textarea_field(wp_unslash($_POST['sbb_meta_description'])) : '';
        $focus_keyword = isset($_POST['sbb_focus_keyword']) ? sanitize_text_field(wp_unslash($_POST['sbb_focus_keyword'])) : '';
        $image_alt = isset($_POST['sbb_image_alt']) ? sanitize_text_field(wp_unslash($_POST['sbb_image_alt'])) : '';
        $submitted_featured_image_id = isset($_POST['sbb_featured_image_id']) ? absint($_POST['sbb_featured_image_id']) : 0;
        $submitted_image_ids = $this->parse_id_list(isset($_POST['sbb_image_ids']) ? wp_unslash($_POST['sbb_image_ids']) : '');
        $featured_image_id = $this->validate_image_id($submitted_featured_image_id);
        $image_ids = $this->validate_image_ids($submitted_image_ids);
        $category_ids = $this->parse_category_ids(isset($_POST['sbb_categories']) ? (array) $_POST['sbb_categories'] : array());
        $tags = $this->sanitize_tags_input(isset($_POST['sbb_tags']) ? wp_unslash($_POST['sbb_tags']) : '');

        if ('' === $title || '' === trim(wp_strip_all_tags($raw_body))) {
            $this->redirect_with_error('missing_content');
        }

        if (($submitted_featured_image_id && !$featured_image_id) || count($submitted_image_ids) !== count($image_ids)) {
            $this->redirect_with_error('invalid_image');
        }

        $all_image_ids = $image_ids;
        if ($featured_image_id) {
            array_unshift($all_image_ids, $featured_image_id);
        }
        $all_image_ids = array_values(array_unique(array_filter(array_map('absint', $all_image_ids))));

        if ($image_alt) {
            foreach ($all_image_ids as $attachment_id) {
                if ('attachment' === get_post_type($attachment_id) && !get_post_meta($attachment_id, '_wp_attachment_image_alt', true)) {
                    update_post_meta($attachment_id, '_wp_attachment_image_alt', $image_alt);
                }
            }
        }

        $content = $this->format_post_content($raw_body, $image_ids);
        $reading_time = $this->calculate_reading_time($content);

        $post_id = wp_insert_post(
            array(
                'post_title' => $title,
                'post_content' => $content,
                'post_excerpt' => $excerpt,
                'post_status' => $status,
                'post_type' => 'post',
                'post_author' => get_current_user_id(),
                'post_category' => $category_ids,
                'tags_input' => $tags,
            ),
            true
        );

        if (is_wp_error($post_id)) {
            $this->redirect_with_error('insert_failed');
        }

        if ($featured_image_id && 'attachment' === get_post_type($featured_image_id)) {
            set_post_thumbnail($post_id, $featured_image_id);
        }

        foreach ($all_image_ids as $attachment_id) {
            if ('attachment' === get_post_type($attachment_id)) {
                wp_update_post(
                    array(
                        'ID' => $attachment_id,
                        'post_parent' => $post_id,
                    )
                );
            }
        }

        update_post_meta($post_id, '_sbb_created', '1');
        update_post_meta($post_id, '_sbb_created_by', get_current_user_id());
        update_post_meta($post_id, '_sbb_created_at', gmdate('c'));
        update_post_meta($post_id, '_sbb_reading_time', $reading_time);
        update_post_meta($post_id, '_sbb_focus_keyword', $focus_keyword);

        if ($seo_title) {
            update_post_meta($post_id, '_sbb_seo_title', $seo_title);
        }
        if ($meta_description) {
            update_post_meta($post_id, '_sbb_meta_description', $meta_description);
        }

        $this->sync_common_seo_plugin_meta($post_id, $seo_title, $meta_description, $focus_keyword);

        wp_safe_redirect(
            add_query_arg(
                array(
                    'page' => self::SLUG,
                    'sbb_created' => $post_id,
                ),
                admin_url('admin.php')
            )
        );
        exit;
    }

    public function render_blog_home($atts)
    {
        $atts = shortcode_atts(
            array(
                'posts_per_page' => 9,
                'category' => '',
                'tag' => '',
                'title' => __('Field Notes', 'stoik-seo-blog-builder'),
                'eyebrow' => __('Stoik Journal', 'stoik-seo-blog-builder'),
                'intro' => __('Practical essays, updates, and guides built for focused readers.', 'stoik-seo-blog-builder'),
            ),
            $atts,
            'stoik_blog_home'
        );

        $this->register_public_style();
        wp_enqueue_style('sbb-public');

        $query_args = array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => absint($atts['posts_per_page']),
            'ignore_sticky_posts' => false,
        );

        if ($atts['category']) {
            $query_args['category_name'] = sanitize_title($atts['category']);
        }
        if ($atts['tag']) {
            $query_args['tag'] = sanitize_title($atts['tag']);
        }

        $query = new WP_Query($query_args);

        ob_start();
        ?>
        <section class="sbb-blog-home" aria-labelledby="sbb-blog-home-title">
            <div class="sbb-blog-home__intro">
                <span class="sbb-blog-home__eyebrow"><?php echo esc_html($atts['eyebrow']); ?></span>
                <h2 id="sbb-blog-home-title"><?php echo esc_html($atts['title']); ?></h2>
                <p><?php echo esc_html($atts['intro']); ?></p>
            </div>

            <?php if ($query->have_posts()) : ?>
                <?php
                $query->the_post();
                $featured_id = get_the_ID();
                ?>
                <article class="sbb-feature-story">
                    <a class="sbb-feature-story__media" href="<?php the_permalink(); ?>" aria-label="<?php echo esc_attr(get_the_title()); ?>">
                        <?php if (has_post_thumbnail()) : ?>
                            <?php the_post_thumbnail('large'); ?>
                        <?php else : ?>
                            <span class="sbb-feature-story__placeholder"><?php echo esc_html($this->get_post_initials(get_the_title())); ?></span>
                        <?php endif; ?>
                    </a>
                    <div class="sbb-feature-story__content">
                        <span class="sbb-story-label"><?php esc_html_e('Featured dispatch', 'stoik-seo-blog-builder'); ?></span>
                        <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                        <p><?php echo esc_html($this->get_archive_excerpt(get_the_ID())); ?></p>
                        <div class="sbb-story-meta">
                            <span><?php echo esc_html(get_the_date()); ?></span>
                            <span><?php echo esc_html($this->get_reading_time_label(get_the_ID())); ?></span>
                        </div>
                    </div>
                </article>

                <div class="sbb-dispatch-board">
                    <div class="sbb-dispatch-board__rail" aria-label="<?php esc_attr_e('Latest posts', 'stoik-seo-blog-builder'); ?>">
                        <?php
                        $rail_index = 1;
                        while ($query->have_posts() && $rail_index <= 3) :
                            $query->the_post();
                            ?>
                            <a class="sbb-rail-item" href="<?php the_permalink(); ?>">
                                <span><?php echo esc_html(str_pad((string) $rail_index, 2, '0', STR_PAD_LEFT)); ?></span>
                                <strong><?php the_title(); ?></strong>
                                <small><?php echo esc_html(get_the_date('M j')); ?></small>
                            </a>
                            <?php
                            $rail_index++;
                        endwhile;
                        ?>
                    </div>

                    <div class="sbb-story-timeline">
                        <?php
                        $timeline_index = 1;
                        while ($query->have_posts()) :
                            $query->the_post();
                            ?>
                            <article class="sbb-timeline-card">
                                <div class="sbb-timeline-card__marker"><?php echo esc_html(str_pad((string) $timeline_index, 2, '0', STR_PAD_LEFT)); ?></div>
                                <div class="sbb-timeline-card__body">
                                    <div class="sbb-timeline-card__topline">
                                        <span><?php echo esc_html(get_the_date('M j, Y')); ?></span>
                                        <span><?php echo esc_html($this->get_reading_time_label(get_the_ID())); ?></span>
                                    </div>
                                    <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                    <p><?php echo esc_html($this->get_archive_excerpt(get_the_ID())); ?></p>
                                    <a class="sbb-read-link" href="<?php the_permalink(); ?>"><?php esc_html_e('Read entry', 'stoik-seo-blog-builder'); ?></a>
                                </div>
                            </article>
                            <?php
                            $timeline_index++;
                        endwhile;
                        ?>
                    </div>
                </div>
            <?php else : ?>
                <div class="sbb-empty-state">
                    <h3><?php esc_html_e('No posts yet.', 'stoik-seo-blog-builder'); ?></h3>
                    <p><?php esc_html_e('Create your first post from SEO Blog Builder, then it will appear here automatically.', 'stoik-seo-blog-builder'); ?></p>
                </div>
            <?php endif; ?>
        </section>
        <?php
        wp_reset_postdata();

        return ob_get_clean();
    }

    public function output_seo_meta()
    {
        if (!is_singular('post') || $this->external_seo_plugin_active()) {
            return;
        }

        $post_id = get_queried_object_id();
        if (!$post_id || !get_post_meta($post_id, '_sbb_created', true)) {
            return;
        }

        $title = $this->get_seo_title($post_id);
        $description = $this->get_meta_description($post_id);
        $url = get_permalink($post_id);
        $image = get_the_post_thumbnail_url($post_id, 'large');
        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => $title,
            'description' => $description,
            'datePublished' => get_the_date('c', $post_id),
            'dateModified' => get_the_modified_date('c', $post_id),
            'author' => array(
                '@type' => 'Person',
                'name' => get_the_author_meta('display_name', (int) get_post_field('post_author', $post_id)),
            ),
            'publisher' => array(
                '@type' => 'Organization',
                'name' => get_bloginfo('name'),
            ),
            'mainEntityOfPage' => $url,
        );

        if ($image) {
            $schema['image'] = array($image);
        }

        echo "\n" . '<meta name="description" content="' . esc_attr($description) . '" />' . "\n";
        echo '<meta property="og:type" content="article" />' . "\n";
        echo '<meta property="og:title" content="' . esc_attr($title) . '" />' . "\n";
        echo '<meta property="og:description" content="' . esc_attr($description) . '" />' . "\n";
        echo '<meta property="og:url" content="' . esc_url($url) . '" />' . "\n";
        if ($image) {
            echo '<meta property="og:image" content="' . esc_url($image) . '" />' . "\n";
            echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
        } else {
            echo '<meta name="twitter:card" content="summary" />' . "\n";
        }
        echo '<meta name="twitter:title" content="' . esc_attr($title) . '" />' . "\n";
        echo '<meta name="twitter:description" content="' . esc_attr($description) . '" />' . "\n";
        echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
    }

    public function filter_document_title($title)
    {
        if (!is_singular('post') || $this->external_seo_plugin_active()) {
            return $title;
        }

        $post_id = get_queried_object_id();
        if (!$post_id || !get_post_meta($post_id, '_sbb_created', true)) {
            return $title;
        }

        $seo_title = get_post_meta($post_id, '_sbb_seo_title', true);

        return $seo_title ? $seo_title : $title;
    }

    private function render_category_checkboxes()
    {
        $categories = get_categories(
            array(
                'hide_empty' => false,
                'orderby' => 'name',
                'order' => 'ASC',
            )
        );

        if (empty($categories)) {
            echo '<p>' . esc_html__('No categories found yet.', 'stoik-seo-blog-builder') . '</p>';
            return;
        }

        foreach ($categories as $category) {
            printf(
                '<label><input type="checkbox" name="sbb_categories[]" value="%1$d" /> %2$s</label>',
                absint($category->term_id),
                esc_html($category->name)
            );
        }
    }

    private function format_post_content($raw_body, $image_ids)
    {
        $body = trim($raw_body);
        $content = $this->body_has_html($body) ? wp_kses_post($body) : $this->format_plain_text($body);
        $gallery = $this->build_image_gallery($image_ids);

        return $content . $gallery;
    }

    private function body_has_html($body)
    {
        return $body !== wp_strip_all_tags($body);
    }

    private function format_plain_text($body)
    {
        $lines = preg_split('/\r\n|\r|\n/', $body);
        $html = '';
        $paragraph = array();
        $list_items = array();

        foreach ($lines as $line) {
            $line = trim($line);

            if ('' === $line) {
                $html .= $this->flush_paragraph($paragraph);
                $html .= $this->flush_list($list_items);
                $paragraph = array();
                $list_items = array();
                continue;
            }

            if (preg_match('/^(#{2,3})\s+(.+)$/', $line, $matches)) {
                $html .= $this->flush_paragraph($paragraph);
                $html .= $this->flush_list($list_items);
                $paragraph = array();
                $list_items = array();
                $tag = '###' === $matches[1] ? 'h3' : 'h2';
                $html .= sprintf('<%1$s>%2$s</%1$s>', $tag, esc_html($matches[2]));
                continue;
            }

            if (preg_match('/^[-*]\s+(.+)$/', $line, $matches)) {
                $html .= $this->flush_paragraph($paragraph);
                $paragraph = array();
                $list_items[] = $matches[1];
                continue;
            }

            $html .= $this->flush_list($list_items);
            $list_items = array();
            $paragraph[] = $line;
        }

        $html .= $this->flush_paragraph($paragraph);
        $html .= $this->flush_list($list_items);

        return wp_kses_post($html);
    }

    private function flush_paragraph($paragraph)
    {
        if (empty($paragraph)) {
            return '';
        }

        return '<p>' . esc_html(implode(' ', $paragraph)) . '</p>';
    }

    private function flush_list($list_items)
    {
        if (empty($list_items)) {
            return '';
        }

        $html = '<ul>';
        foreach ($list_items as $item) {
            $html .= '<li>' . esc_html($item) . '</li>';
        }
        $html .= '</ul>';

        return $html;
    }

    private function build_image_gallery($image_ids)
    {
        $image_ids = array_values(array_filter(array_map('absint', $image_ids)));

        if (empty($image_ids)) {
            return '';
        }

        $html = '<div class="sbb-post-image-gallery">';

        foreach ($image_ids as $image_id) {
            if ('attachment' !== get_post_type($image_id)) {
                continue;
            }

            $image_html = wp_get_attachment_image(
                $image_id,
                'large',
                false,
                array(
                    'class' => 'sbb-post-image',
                    'loading' => 'lazy',
                )
            );

            if (!$image_html) {
                continue;
            }

            $caption = wp_get_attachment_caption($image_id);
            $html .= '<figure class="sbb-post-figure">' . $image_html;
            if ($caption) {
                $html .= '<figcaption>' . esc_html($caption) . '</figcaption>';
            }
            $html .= '</figure>';
        }

        $html .= '</div>';

        return $html;
    }

    private function parse_id_list($ids)
    {
        if (empty($ids)) {
            return array();
        }

        $ids = explode(',', sanitize_text_field($ids));

        return array_values(array_unique(array_filter(array_map('absint', $ids))));
    }

    private function validate_image_ids($image_ids)
    {
        $valid_ids = array();

        foreach ($image_ids as $image_id) {
            $valid_id = $this->validate_image_id($image_id);
            if ($valid_id) {
                $valid_ids[] = $valid_id;
            }
        }

        return array_values(array_unique($valid_ids));
    }

    private function validate_image_id($image_id)
    {
        $image_id = absint($image_id);

        if (!$image_id) {
            return 0;
        }

        if ('attachment' !== get_post_type($image_id)) {
            return 0;
        }

        $mime_type = get_post_mime_type($image_id);
        if (!$mime_type || 0 !== strpos($mime_type, 'image/')) {
            return 0;
        }

        if (!current_user_can('edit_post', $image_id)) {
            return 0;
        }

        return $image_id;
    }

    private function parse_category_ids($categories)
    {
        $categories = array_values(array_filter(array_map('absint', $categories)));
        $valid_categories = array();

        if (current_user_can('assign_categories')) {
            foreach ($categories as $category_id) {
                $term = get_term($category_id, 'category');
                if ($term && !is_wp_error($term)) {
                    $valid_categories[] = $category_id;
                }
            }
        }

        return $valid_categories ? $valid_categories : array((int) get_option('default_category'));
    }

    private function sanitize_tags_input($tags)
    {
        if (!current_user_can('assign_post_tags')) {
            return '';
        }

        return sanitize_text_field($tags);
    }

    private function calculate_reading_time($content)
    {
        $word_count = str_word_count(wp_strip_all_tags($content));

        return max(1, (int) ceil($word_count / 200));
    }

    private function sync_common_seo_plugin_meta($post_id, $seo_title, $meta_description, $focus_keyword)
    {
        if ($seo_title) {
            update_post_meta($post_id, '_yoast_wpseo_title', $seo_title);
            update_post_meta($post_id, 'rank_math_title', $seo_title);
        }

        if ($meta_description) {
            update_post_meta($post_id, '_yoast_wpseo_metadesc', $meta_description);
            update_post_meta($post_id, 'rank_math_description', $meta_description);
        }

        if ($focus_keyword) {
            update_post_meta($post_id, '_yoast_wpseo_focuskw', $focus_keyword);
            update_post_meta($post_id, 'rank_math_focus_keyword', $focus_keyword);
        }
    }

    private function external_seo_plugin_active()
    {
        return defined('WPSEO_VERSION')
            || defined('RANK_MATH_VERSION')
            || defined('AIOSEO_VERSION')
            || class_exists('All_in_One_SEO_Pack');
    }

    private function get_seo_title($post_id)
    {
        $seo_title = get_post_meta($post_id, '_sbb_seo_title', true);

        return $seo_title ? $seo_title : get_the_title($post_id);
    }

    private function get_meta_description($post_id)
    {
        $description = get_post_meta($post_id, '_sbb_meta_description', true);
        if ($description) {
            return $description;
        }

        $excerpt = get_the_excerpt($post_id);
        if ($excerpt) {
            return wp_trim_words($excerpt, 28, '');
        }

        return wp_trim_words(wp_strip_all_tags(get_post_field('post_content', $post_id)), 28, '');
    }

    private function get_archive_excerpt($post_id)
    {
        $excerpt = get_the_excerpt($post_id);
        if ($excerpt) {
            return wp_trim_words($excerpt, 24, '...');
        }

        return wp_trim_words(wp_strip_all_tags(get_post_field('post_content', $post_id)), 24, '...');
    }

    private function get_reading_time_label($post_id)
    {
        $reading_time = absint(get_post_meta($post_id, '_sbb_reading_time', true));
        if (!$reading_time) {
            $reading_time = $this->calculate_reading_time(get_post_field('post_content', $post_id));
        }

        return sprintf(
            _n('%d min read', '%d min read', $reading_time, 'stoik-seo-blog-builder'),
            $reading_time
        );
    }

    private function get_post_initials($title)
    {
        $words = preg_split('/\s+/', trim(wp_strip_all_tags($title)));
        $initials = '';

        foreach ($words as $word) {
            if ('' === $word) {
                continue;
            }
            $initials .= strtoupper(substr($word, 0, 1));
            if (strlen($initials) >= 2) {
                break;
            }
        }

        return $initials ? $initials : 'SB';
    }

    private function get_error_message($error)
    {
        $messages = array(
            'nonce' => __('Security check failed. Please try again.', 'stoik-seo-blog-builder'),
            'missing_content' => __('Please add both a post title and body copy.', 'stoik-seo-blog-builder'),
            'invalid_image' => __('One or more selected files are not valid image attachments you can use. Please reselect images from the media library.', 'stoik-seo-blog-builder'),
            'insert_failed' => __('WordPress could not create the post. Please try again.', 'stoik-seo-blog-builder'),
        );

        return isset($messages[$error]) ? $messages[$error] : __('Something went wrong. Please try again.', 'stoik-seo-blog-builder');
    }

    private function redirect_with_error($error)
    {
        wp_safe_redirect(
            add_query_arg(
                array(
                    'page' => self::SLUG,
                    'sbb_error' => sanitize_key($error),
                ),
                admin_url('admin.php')
            )
        );
        exit;
    }
}

new Stoik_SEO_Blog_Builder();
