<?php
/**
 * Plugin Name: Web Scraping
 * Plugin URI: https://attsoftware.com/
 * Description: A Web Scraping plugin.
 * Version: 1.0
 * Author: AT&T Software LLC
 * Author URI: https://attsoftware.com/
*/

class WebScarping 
{
    public $version;
    function __construct()
    {
        $this->register();
        $this->version = time();
    }

    function register(){
        add_action('admin_menu', array($this,'register_admin_menu'));
        add_action('admin_enqueue_scripts', array($this,'load_enqueue_files'));
        add_action('wp_ajax_save_wbs_vendor_details',array($this,'save_wbs_vendor_details'));
        add_action('wp_ajax_nopriv_save_wbs_vendor_details',array($this,'save_wbs_vendor_details'));
    }

    function activate()
    {
       // $this->register_admin_menu();
       $this->register_custom_post_type();
    }


    function register_admin_menu()
    {
        // Add a new top-level menu (ill-advised):
        add_menu_page(__('Web Scraper','webscraper'), __('Web Scraper','webscraper'), 'manage_options', 'web-scraper', array($this,'web_scraper_callback') );

        add_submenu_page('web-scraper', __('Vendors','webscraper'), __('Vendors','webscraper'), 'manage_options', 'wbs-vendors', array($this,'web_scraper_vendors_callback'));
        
        add_submenu_page('web-scraper', __('Product URLs','webscraper'), __('Product URLs','webscraper'), 'manage_options', 'product-urls', array($this,'web_scraper_product_url_callback'));

    }

    function register_custom_post_type()
    {
        $args = array(
            'label' => 'Vendors',
            'public' => true
        );
        register_post_type('wbs_vendor',$args);
    }

    function web_scraper_callback()
    {
        echo '<div class="wrap">';
       
        echo '<h1>'.esc_html( get_admin_page_title() ).'</h1>';
        $this->get_the_tabs();
        require_once plugin_dir_path(__FILE__).'inc/web-scraper.php';
        echo '</div>';
    }

    function web_scraper_vendors_callback(){
        echo '<div class="wrap">';
       
        
        if(isset($_GET['action']) && ($_GET['action'] === 'addnew' || $_GET['action'] === 'edit') ){
            echo '<h1>'.esc_html( get_admin_page_title() ).'</h1>';
            
            require_once plugin_dir_path(__FILE__).'inc/wbs-vendors-form.php';
        }
        elseif(isset($_GET['vendor']) && $_GET['vendor'] !== ''){
            echo '<h1>'.esc_html( 'Vendor Products' ).'</h1>';
            
            require_once plugin_dir_path(__FILE__).'inc/wbs-vendors-products.php';
        }
        else{
            
            echo '<h1>'.esc_html( get_admin_page_title() ).' <a href="admin.php?page=wbs-vendors&action=addnew" class="page-title-action">Add New</a></h1>';
            $this->get_the_tabs();
            require_once plugin_dir_path(__FILE__).'inc/wbs-vendors.php';
        }
        echo '</div>';
    }

    function web_scraper_product_url_callback(){
        echo '<div class="wrap">';
       
        echo '<h1>'.esc_html( get_admin_page_title() ).'</h1>';
        $this->get_the_tabs();
        require_once plugin_dir_path(__FILE__).'inc/product-urls.php';
        echo '</div>';
    }

    public static function get_the_tabs(){
        global $submenu;
        // return $submenu['web-scraper'];
        $tab = isset($_GET['page'])?$_GET['page']:'';
        ob_start();
        ?>
        <!-- Here are our tabs -->
        <nav class="nav-tab-wrapper">
            <?php foreach($submenu['web-scraper'] as $menu):?>
            <a href="?page=<?php echo $menu[2]?>" class="nav-tab <?php if($tab===$menu[2]):?>nav-tab-active<?php endif; ?>"><?php echo esc_html( $menu[0] ); ?></a>
            <?php endforeach;?>            
        </nav>
        <?php
        echo ob_get_clean();

    }

    function load_enqueue_files(){

        $version = 1.0;
        wp_enqueue_style( 'wbs-styles', plugin_dir_url( __FILE__ ) . 'assets/css/style.css', array('dataTables','bootstrap-grid'), $version );
        wp_enqueue_style( 'dataTables', plugin_dir_url( __FILE__ ) . 'assets/css/jquery.dataTables.min.css', array(), $version );
        wp_enqueue_style( 'bootstrap-grid', plugin_dir_url( __FILE__ ) . 'assets/css/bootstrap-grid.min.css', array(), $version );



        wp_enqueue_script( 'wbs-scripts', plugin_dir_url( __FILE__ ) . 'assets/js/scripts.js', array('jquery'), $version );

        wp_enqueue_script( 'dataTables', plugin_dir_url( __FILE__ ) . 'assets/js/jquery.dataTables.min.js', array('wbs-scripts'), $version );

        wp_localize_script( 'wbs-scripts', 'ajax_object',
            array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'we_value' => 1234 ) );
    }

    function save_wbs_vendor_details()
    {
        $output =[];
        parse_str($_REQUEST['data'], $output);

        $vendorName = $output['post']['vendor_name'];
        $vendor_id = $output['vendor'];
        if($vendor_id)
        {
            $vendor_id = wp_update_post(array('ID'=> $vendor_id,'post_title'=>$vendorName,'post_type'=>'wbs_vendor','post_status'=>'publish'));
        }
        else{
            $vendor_id = wp_insert_post(array('post_title'=>$vendorName,'post_type'=>'wbs_vendor','post_status'=>'publish'));
        }

        update_post_meta($vendor_id, 'post_data', $output['postmeta']);
        wp_send_json_success($vendor_id);die;
    }

    function get_form_fiels($args = array()){
        $args = array(
            'post' => array(
                    array (
                        'title' => 'Vendor Name',
                        'name' => 'vendor_name',
                        'type' => 'text',
                        'required' => true,
                        'validation' => true,
                    )
                ),
            'postmeta' => array(
                array(
                    'title' => 'Product Name',
                    'name' => 'product_name',
                    'type' => 'text',
                    'required' => true,
                    'validation' => true
                ),
                array(
                    'title' => 'Product Price',
                    'name' => 'product_price',
                    'type' => 'text',
                    'required' => true,
                    'validation' => true
                ),
                array(
                    'title' => 'Product Images',
                    'name' => 'product_images',
                    'type' => 'text',
                    'required' => true,
                    'validation' => true
                ),
                array(
                    'title' => 'Product Description',
                    'name' => 'product_description',
                    'type' => 'text',
                    'required' => true,
                    'validation' => true
                ),
                array(
                    'title' => 'Product SKU',
                    'name' => 'product_sku',
                    'type' => 'text',
                    'required' => true,
                    'validation' => true
                ),
                array(
                    'title' => 'Product Categories',
                    'name' => 'product_categories',
                    'type' => 'text',
                    'required' => true,
                    'validation' => true
                ),
                array(
                    'title' => 'Product Tags',
                    'name' => 'product_tags',
                    'type' => 'text',
                    'required' => true,
                    'validation' => true
                )
            )
        );
       
        return apply_filters('get_form_fiels',$args);
    }

    function print_pre($data){
        echo '<pre>';
        print_r($data);
        echo '</pre>';
    }
}

if(class_exists('WebScarping'))
{
    $webscraper = new WebScarping();
}
 
register_activation_hook(__FILE__, array($webscraper,'activate'));

// add_filter('get_form_fiels','custom_fields');

function custom_fields($args){
    $args['postmeta'][] = array(
            'title' => 'URLs',
            'name' => 'urls',
            'type' => 'text',
            'required' => true,
            'validation' => true
        );
    return $args;
}