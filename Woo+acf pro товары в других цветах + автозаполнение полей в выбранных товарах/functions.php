<?php
//woocommerce connected products (color-link)
    //frontend logic
function woo_connect_products_frontend(){
    function check_current_product($id, $array) {
        foreach ($array as $key => $val) {
            if ($val['product'] === $id) {
                return $key;
            }
        }
        return null;
    }
    if ( is_product() ) {
        if ( function_exists('get_field') && function_exists('wc_get_product') ){
            $connected_products = get_field('connected_products');
            $current_product = wc_get_product()->get_id();
            if ( isset($connected_products) && is_array($connected_products)  ) {
                $result = '<p><strong>Этот товар в других цветах:</strong></p>';
                $result .= '<div class="other_colors_list">';
                foreach ( $connected_products as $product ){
                    if ( $product['product'] == $current_product ){
                        $delete_current_product = check_current_product($product['product'], $connected_products);
                        unset($connected_products[$delete_current_product]);
                    }
                }
                foreach ($connected_products as $product) {
                    $result .= '<div class="color">';
                    $result .= '<a href="'. get_post_permalink($product['product']) .'">';
                    $result .= '<img src="'. wp_get_attachment_image_url($product['color']) .'" />';
                    $result .= '</a>';
                    $result .= '</div>';
                }
                $result .= '</div>';
            }
            return $result;
        }
    }
}
add_shortcode('connected_products', 'woo_connect_products_frontend');
    //backend logic
function woo_connect_products_backend( $post_id ){
    if ( ! wp_is_post_revision( $post_id ) ){
        // удаляем этот хук, чтобы он не создавал бесконечного цикла
        remove_action('save_post_product', 'woo_connect_products_backend');
        // обновляем пост, когда снова вызовется хук save_post
        if ( function_exists('get_field') ){
            $connected_products = get_field('connected_products', $post_id);
            if ( isset($connected_products) && is_array($connected_products)  ) {
                $product_ids = [];
                foreach ( $connected_products as $product ) {
                    array_push($product_ids, $product['product']);
                }
                foreach ( $product_ids as $id ){
                    update_field('connected_products', $connected_products, $id);
                }
            }
        }
        // снова вешаем хук
        add_action('save_post_product', 'woo_connect_products_backend');
    }
}
add_action( 'save_post_product', 'woo_connect_products_backend' );
?>
