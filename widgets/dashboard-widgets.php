<?php
function stdi_add_blog_post_widget() {
    wp_add_dashboard_widget(
        'stdi_blog_post_widget',
        'STDI Blog Post',
        'stdi_blog_post_widget_display'
    );
}
add_action('wp_dashboard_setup', 'stdi_add_blog_post_widget');

function stdi_get_latest_blogs_from_api() {
    $response = wp_remote_get(STDI_ADMIN_CUSTOM_ENDPOINT . 'get_latest_blogs');

    if (is_wp_error($response)) {
        return array('code' => 500, 'data' => array(), 'error' => $response->get_error_message());
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (isset($data['code']) && $data['code'] == 100) {
        return array('code' => 100, 'data' => $data['data']);
    } else {
        return array('code' => 500, 'data' => array(), 'error' => 'Unexpected API response');
    }
}

function stdi_blog_post_widget_display() {
    $response = stdi_get_latest_blogs_from_api();

    if ($response['code'] == 100 && !empty($response['data'])) { ?>
        <ul>
            <?php foreach ($response['data'] as $post) : ?>
                <li>
                    <a href="<?php echo esc_url($post['post_permalink']); ?>" target="_blank">
                        <?php echo esc_html($post['post_title']); ?>
                    </a>
                    <p><?php echo esc_html($post['post_content']); ?></p>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php } else { ?>
        <p>No recent posts available.</p>
    <?php }
}