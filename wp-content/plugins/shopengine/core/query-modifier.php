<?php

namespace ShopEngine\Core;

use ShopEngine\Traits\Singleton;

class Query_Modifier
{

    use Singleton;

    private $custom_query = [];

    public function init()
    {

        add_action('pre_get_posts', [$this, 'modify_query']);
    }

    public function modify_query($query)
    {
        if (is_admin() || !$query->is_main_query() || $query->is_single === true) {
            return;
        }

        if (!isset($query->query_vars['wc_query']) || $query->query_vars['wc_query'] != 'product_query') {
            return;
        }

        // query filter begins

        // update query for product per page filter
        //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- It's a fronted user part, not possible to verify nonce here
        if (!empty($_GET['shopengine_products_per_page'])) {
            //phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $query->set('posts_per_page', absint(intval($_GET['shopengine_products_per_page'])));
        }


        $color_prefix = 'shopengine_filter_color_';

        $attribute_prefix = 'shopengine_filter_attribute_';

        $image_prefix = 'shopengine_filter_image_';

        $label_prefix = 'shopengine_filter_label_';

        $shipping_prefix = 'shopengine_filter_shipping_';

        $category_prefix = 'shopengine_filter_category';

        $stock_prefix = 'shopengine_filter_stock';

        $sale_prefix = 'shopengine_filter_onsale';

        $meta_query = ['relation' => 'AND'];

        //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- It's a fronted user part, not possible to verify nonce here
        foreach ($_GET as $key => $value) {

            if ($key === 'rating_filter') {
                $meta_query[] = [
                    'key' => '_wc_average_rating',
                    'value' => explode(',', trim($value)),
                    'type' => 'numeric',
                    'compare' => 'IN'
                ];
            }

            if ($key === $category_prefix) {

                $query->query['product_cat'] = '';
                $query->query_vars['product_cat'] = '';
                $query = $this->query($key . 'product_cat', $category_prefix, $value, $query);

            } elseif (strpos($key, $color_prefix) !== false) {

                $query = $this->query($key, $color_prefix, $value, $query);

            } elseif (strpos($key, $attribute_prefix) !== false) {

                $query = $this->query($key, $attribute_prefix, $value, $query);

            } elseif (strpos($key, $image_prefix) !== false) {

                $query = $this->query($key, $image_prefix, $value, $query);

            } elseif (strpos($key, $label_prefix) !== false) {

                $query = $this->query($key, $label_prefix, $value, $query);

            } elseif (strpos($key, $shipping_prefix) !== false) {

                $query = $this->query($key, $shipping_prefix, $value, $query);

            } elseif ($key === $stock_prefix) {

                $meta_query[] = [
                    'key' => '_stock_status',
                    'value' => $value,
                    'compare' => 'IN'
                ];


            } elseif ($key === $sale_prefix) {

                $s = explode(',', $value);

                foreach ($s as $v) {

                    if ($v === 'on_sale') {

                        $product_ids_on_sale = wc_get_product_ids_on_sale(); // including varriation products
                        $query->set( 'post__in', (array) $product_ids_on_sale );

                    } else {
                        $meta_query[] = [
                            'key' => '_sale_price',
                            'compare' => 'NOT EXISTS',
                            'operator' => 'OR',
                        ];
                    }
                }
            }
        }

        if (!empty($meta_query)) {
            $query->set('meta_query', $meta_query);
        }

        $product_visibility_terms  = wc_get_product_visibility_term_ids();
        $product_visibility_not_in = array( $product_visibility_terms['exclude-from-catalog'] );

		if ( 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) ) {
			$product_visibility_not_in[] = $product_visibility_terms['outofstock'];
		}

        // Get existing tax query
        $this->custom_query= $query->get('tax_query');

        if (!is_array($this->custom_query)) {
            $this->custom_query = [];
        }

        $this->custom_query['tax_query'][] = apply_filters('shopengine-product-visibility-modifier',[
            'taxonomy'  => 'product_visibility',
            'terms'     =>  $product_visibility_not_in,
            'field'     => 'term_taxonomy_id',
            'operator'  => 'NOT IN',
        ]);

        // Add category filter if set in the query parameters
        if (!empty($_GET['shopengine_filter_category'])) {
            $category_filter = [
                'taxonomy' => 'product_cat',
                'terms' => explode(',', $_GET['shopengine_filter_category']),
                'field' => 'slug',
                'operator' => 'IN',
            ];

            $this->custom_query[] = apply_filters('shopengine-category-filter-modifier', $category_filter);
        } 

        $query->set('tax_query', apply_filters('shopengine-tax-query-modifier', $this->custom_query)); 
    }

    public function query($key, $prefix, $values, $query)
    {

        $taxonomy = str_replace($prefix, '', $key);
 
        $values = explode(',', trim($values));
 
        $this->custom_query['relation'] =  'AND';

        $this->custom_query[] = [
            'taxonomy' => $taxonomy,
            'field' => 'slug',
            'terms' => $values,
            'operator' => 'IN',
        ];

        return $query;
    }
}
