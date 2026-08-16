<?php
// Theme support loader
if (file_exists(__DIR__ . '/class-wp-theme-support.php')) {
    require_once __DIR__ . '/class-wp-theme-support.php';
}


function custom_partner_price_display() {
    global $product;
    if ( is_user_partner() ) {
        $partner_price = get_post_meta( $product->get_id(), 'partner_price', true );
        if ( ! empty( $partner_price ) ) {
            echo '<span class="partner-price">' . __( 'Partner Ár:', 'your-textdomain' ) . ' ' . wc_price( $partner_price ) . '</span>';
        }
    } else {
        echo $product->get_price_html();
    }
}







add_action('woocommerce_single_product_summary', 'display_sku_below_price', 11);

function display_sku_below_price() {
    global $product;
    if ($product->get_sku()) {
        echo '<div class="product-sku">Cikkszám: ' . esc_html($product->get_sku()) . '</div>';
    }
}


// Termékkategóriák megjelenítése a termék neve alatt a kategória nézetben egyedi stílusosztállyal
add_action('woocommerce_after_shop_loop_item_title', 'display_product_category_under_name_with_class', 5);

function display_product_category_under_name_with_class() {
    global $product;
    
    // Lekéri a termék kategóriáit
    $categories = wp_get_post_terms( $product->get_id(), 'product_cat' );
    
    // Ellenőrizzük, hogy vannak-e kategóriák a termékhez
    if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) {
        echo '<div class="product-category-label">'; // Egyedi osztály hozzáadása

        foreach ( $categories as $category ) {
            // Kategória linkek megjelenítése
            echo '<a href="' . get_term_link( $category->term_id ) . '" class="category-link">' . esc_html( $category->name ) . '</a>';
        }

        echo '</div>';
    }
}


function register_mobile_menu() {
    register_nav_menu('mobile', __('Mobile Menu', 'your-textdomain'));
}
add_action('init', 'register_mobile_menu');


add_filter( 'woocommerce_get_price_html', 'add_custom_price_labels', 100, 2 );
function add_custom_price_labels( $price, $product ) {
    $gross_price_text = __("Bruttó ár: ", 'your-textdomain'); // Bruttó ár felirat
	$regular_price_text = __("Nettó ár: ", 'your-textdomain');
    
    $sale_price_text = __("Nettó akciós ár: ", 'your-textdomain');
    $partner_price = get_post_meta($product->get_id(), 'partner_price', true);
    $featured_partner_price = get_post_meta($product->get_id(), 'featured_partner_price', true);

    if (is_user_logged_in()) {
        $user = wp_get_current_user();

        if (in_array('kiemelt_partner', $user->roles) && !empty($featured_partner_price)) {
            // Kiemelt partner ár (nettó és bruttó ár megjelenítése)
            $gross_price = wc_get_price_including_tax($product, ['price' => $featured_partner_price]);
            return '<span class="price-label">' . __('Kiemelt Partner Ár (nettó): ', 'your-textdomain') . '</span>' . wc_price($featured_partner_price) .
                   '<br><span class="price-label">' . $gross_price_text . '</span>' . wc_price($gross_price);
        } elseif (in_array('partner', $user->roles) && !empty($partner_price)) {
            // Partner ár (nettó és bruttó ár megjelenítése)
            $gross_price = wc_get_price_including_tax($product, ['price' => $partner_price]);
            return '<span class="price-label">' . __('Partner Ár (nettó): ', 'your-textdomain') . '</span>' . wc_price($partner_price) .
                   '<br><span class="price-label">' . $gross_price_text . '</span>' . wc_price($gross_price);
        } elseif ($product->is_on_sale()) {
            // Normál felhasználók esetén akciós ár (nettó és bruttó)
            $regular_price_html = '<del>' . $regular_price_text . wc_price($product->get_regular_price()) . '</del>';
            $sale_price_html = '<ins>' . $sale_price_text . wc_price($product->get_sale_price()) . '</ins>';
            $gross_sale_price = wc_get_price_including_tax($product, ['price' => $product->get_sale_price()]);
            return $regular_price_html . '<br>' . $sale_price_html .
                   '<br><span class="price-label">' . $gross_price_text . '</span>' . wc_price($gross_sale_price);
        }
    }

    // Vendégek és normál felhasználók esetén (nettó és bruttó ár)
    $gross_price = wc_get_price_including_tax($product);
    return $regular_price_text . wc_price($product->get_price()) . 
           '<br><span class="price-label">' . $gross_price_text . '</span>' . wc_price($gross_price);
}



add_action( 'woocommerce_after_shop_loop_item_title', 'show_sale_percentage_loop', 25 );
function show_sale_percentage_loop() {
    global $product;
    if ( ! $product->is_on_sale() ) return;

    $regular_price = (float) $product->get_regular_price();
    $sale_price = (float) $product->get_sale_price();

    if ( $regular_price === 0 ) return; // Avoid division by zero

    $percentage = round(100 - ($sale_price / $regular_price * 100));
    echo '<div class="sale-percentage">-' . $percentage . '%</div>';
}

add_action( 'woocommerce_single_product_summary', 'check_sale_price_display_single_product', 20 );

function check_sale_price_display_single_product() {
    global $product;
    if ( $product->is_on_sale() ) {
        add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
    }
}

add_action( 'woocommerce_before_shop_loop_item_title', 'check_sale_price_display' );

function check_sale_price_display() {
    global $product;
    if ( $product->is_on_sale() ) {
        add_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );
    }
}


function display_sku_in_product_category() {
    global $product;
    if ( $product->get_sku() ) {
        echo '<span class="category-sku">Cikkszám: ' . $product->get_sku() . '</span>';
    }
}

add_action( 'woocommerce_after_shop_loop_item_title', 'display_sku_in_product_category', 20 );


if ( ! defined( 'ABSPATH' ) ) {
	die();
}


  // Exit if accessed directly
  if ( !defined( 'ABSPATH' ) )exit;


  function beauty_shop_settings( $values ) {

    $values[ 'primary_color' ] = '#E80505';
    $values[ 'secondary_color' ] = '#ffdc00';
    $values[ 'heading_font' ] = 'Jost';
    $values[ 'body_font' ] = 'Poppins';

    $values[ 'woo_bar_color' ] = '#000';
    $values[ 'woo_bar_bg_color' ] = '#ffffff';
    $values[ 'woo_category_title' ] = esc_html__( 'Termékeink', "beauty-shop" );

    $values[ 'preloader_enabled' ] = false;

    $values[ 'logo_width' ] = 130;
    $values[ 'layout_width' ] = 1280;

    $values[ 'header_layout' ] = 'woocommerce-bar';
    $values[ 'menu_layout' ] = 'default';
    $values[ 'enable_search' ] = true;
    $values[ 'ed_social_links' ] = true;

    $values[ 'subscription_shortcode' ] = '';

    $values[ 'enable_top_bar' ] = true;
    $values[ 'top_bar_left_content' ] = 'menu';
    $values[ 'top_bar_left_text' ] = esc_html__( 'edit top bar text', "beauty-shop" );
    $values[ 'top_bar_right_content' ] = 'menu_social';
    $values[ 'enable_top_bar' ] = true;
    $values[ 'topbar_bg_color' ] = '#dc0404';
    $values[ 'topbar_text_color' ] = '#e7e7e7';


    //$values[ 'footer_text_color' ] = '#000000';
   // $values[ 'footer_color' ] = '#F4F4F4';
   // $values[ 'footer_link' ] = 'https://sp-wordpress-weboldal-keszites.hu';
   // $values[ 'footer_copyright' ] = esc_html__( 'A theme by GradientThemes', "beauty-shop" );

    $values[ 'page_sidebar_layout' ] = 'right-sidebar';
    $values[ 'post_sidebar_layout' ] = 'right-sidebar';
    $values[ 'layout_style' ] = 'right-sidebar';
    $values[ 'woo_sidebar_layout' ] = 'left-sidebar';

    return $values;

  }


  add_filter( 'best_shop_settings', 'beauty_shop_settings' );


  /*
   * Add default header image
   */

  function beauty_shop_header_style() {
    add_theme_support(
      'custom-header',
      apply_filters(
        'beauty_shop_custom_header_args',
        array(
          'default-text-color' => '#000000',
          'width' => 1920,
          'height' => 760,
          'flex-height' => true,
          'video' => true,
          'wp-head-callback' => 'beauty_shop_header_style',
        )
      )
    );
    add_theme_support( 'automatic-feed-links' );
  }

  add_action( 'after_setup_theme', 'beauty_shop_header_style' );


  //  PARENT ACTION

  if ( !function_exists( 'beauty_shop_cfg_locale_css' ) ):
    function beauty_shop_cfg_locale_css( $uri ) {
      if ( empty( $uri ) && is_rtl() && file_exists( get_template_directory() . '/rtl.css' ) )
        $uri = get_template_directory_uri() . '/rtl.css';
      return $uri;
    }
  endif;

  add_filter( 'locale_stylesheet_uri', 'beauty_shop_cfg_locale_css' );

  if ( !function_exists( 'beauty_shop_cfg_parent_css' ) ):
    function beauty_shop_cfg_parent_css() {
      wp_enqueue_style( 'beauty_shop_cfg_parent', trailingslashit( get_template_directory_uri() ) . 'style.css', array() );
    }
  endif;

  add_action( 'wp_enqueue_scripts', 'beauty_shop_cfg_parent_css', 10 );

  // Add prealoder js
  function beauty_shop_custom_scripts() {
    wp_enqueue_script( "beauty-shop", get_stylesheet_directory_uri() . '/assests/preloader.js', array( 'jquery' ), '', true );
  }

  add_action( 'wp_enqueue_scripts', 'beauty_shop_custom_scripts' );

  // END ENQUEUE PARENT ACTION

  if ( !function_exists( 'beauty_shop_customize_register' ) ):
    /**
     * Add postMessage support for site title and description for the Theme Customizer.
     *
     * @param WP_Customize_Manager $wp_customize Theme Customizer object.
     */
    function beauty_shop_customize_register( $wp_customize ) {

      $wp_customize->add_section(
        'subscription_settings',
        array(
          'title' => esc_html__( 'Email Subscription', "beauty-shop" ),
          'priority' => 199,
          'capability' => 'edit_theme_options',
          'panel' => 'theme_options',
          'description' => __( 'Add email subscription plugin shortcode.', "beauty-shop" ),

        )
      );

      /** Footer Copyright */
      $wp_customize->add_setting(
        'subscription_shortcode',
        array(
          'default' => best_shop_default_settings( 'subscription_shortcode' ),
          'sanitize_callback' => 'sanitize_text_field',
          'transport' => 'postMessage'
        )
      );

      $wp_customize->add_control(
        'subscription_shortcode',
        array(
          'label' => esc_html__( 'Subscription Plugin Shortcode', "beauty-shop" ),
          'section' => 'subscription_settings',
          'type' => 'text',
        )
      );

      //preloader
      $wp_customize->add_section(
        'preloader_settings',
        array(
          'title' => esc_html__( 'Preloader', "beauty-shop" ),
          'priority' => 200,
          'capability' => 'edit_theme_options',
          'panel' => 'theme_options',

        )
      );

      $wp_customize->add_setting(
        'preloader_enabled',
        array(
          'default' => best_shop_default_settings( 'preloader_enabled' ),
          'sanitize_callback' => 'best_shop_sanitize_checkbox',
          'transport' => 'refresh'
        )
      );

      $wp_customize->add_control(
        'preloader_enabled',
        array(
          'label' => esc_html__( 'Enable Preloader', "beauty-shop" ),
          'section' => 'preloader_settings',
          'type' => 'checkbox',
        )
      );


    }
  endif;
  add_action( 'customize_register', 'beauty_shop_customize_register' );