<?php if(!defined('CONTACTXP_VERSION')) die('Fatal Error');

if(!class_exists('CONTACTXP')){

    class CONTACTXP{

        public $option_fields = [
            'domain_settings' => [
                'domain_url' => [
                    'label' => 'Domain URL',
                    'description' => 'URL where the contact form will be searched',
                    'type' => 'text',
                    'required' => true
                ],
                'page_url_keywords' => [
                    'label' => 'Contact Page URL Keywords',
                    'description' => 'These are the key terms to search in the specified domain for a possible contact page. Seperate by comma',
                    'type' => 'textarea',
                    'required' => true
                ],
            ],
            'contact_pages' => [
                'contact_links' => [
                    'label' => 'Contact Us Pages',
                    'description' => 'List of pages found as possible contact forms',
                    'type' => 'page-tables',
                ]
            ]
        ];

        function __CONSTRUCT()
        {
            add_action('init', [&$this, 'init']);
            add_action('admin_init', [&$this, 'admin_init']);
        }

        public function init(){
            add_action('admin_menu',function(){
                add_menu_page( 'Contact Xpress', 'Contact Xpress', 'manage_options', 'contact-xp', [&$this,'menu_admin_view'], 'dashicons-sos', 66);
                add_submenu_page( 'contact-xp' , 'Domain List' , 'Domain List' , 'manage_options' , 'cxp-domain-list', [&$this, 'menu_domain_list_view']);
            });

            $labels = array(
                'name' => 'Domain',
                'singular_name' => 'Domain',
                'add_new' => 'Add Domain',
                'all_items' => 'All Domains',
                'add_new_item' => 'Add Domain',
                'edit_item' => 'Edit Domain',
                'new_item' => 'New Domain',
                'view_item' => 'View Domain',
                'search_items' => 'Search Domains',
                'not_found' => 'No domains found',
                'not_found_in_trash' => 'No domains found in trash',
                'parent_item_colon' => 'Parent domain'
            );
            $args = array(
                'labels' => $labels,
                'public' => true,
                'has_archive' => false,
                'publicly_queryable' => false,
                'query_var' => false,
                'rewrite' => true,
                'capability_type' => 'post',
                'hierarchical' => false,
                'supports' => array(
                    'title',
                    // 'editor',
                    // 'excerpt',
                    // 'thumbnail',
                    //'author',
                    //'trackbacks',
                    // 'custom-fields',
                    //'comments',
                    // 'revisions',
                    //'page-attributes', // (menu order, hierarchical must be true to show Parent option)
                    //'post-formats',
                ),
                'taxonomies' => array( 'category' ), // add default post categories and tags
                'menu_position' => 5,
                'exclude_from_search' => true,
                'register_meta_box_cb' => function(){
                    add_meta_box( 'domain_metabox', 'Domain Settings', [&$this,'show_meta_box'], 'domain', 'normal','high',['group'=>'domain_settings']);
                    add_meta_box( 'contact_pages_metabox', 'Contact Form Pages', [&$this,'show_meta_box_contact_pages'], 'domain', 'normal','high',['group'=>'contact_pages']);

                }
            );
            register_post_type( 'domain', $args );
            add_action( 'save_post_domain', [ &$this , 'save_meta_box' ]);
        }
        public function show_meta_box($post,$meta){
            $rss_data  = [];
            foreach ($this->option_fields[$meta['args']['group']] as $field => $data) {
                $this->option_fields[$meta['args']['group']][$field]['value'] = get_post_meta( $post->ID, $field, true );
            }
            wp_nonce_field( basename( __FILE__ ), '_'.$meta['args']['group'].'_metabox_nonce' );
            include_once( CONTACTXP_PLUGIN_DIR . 'partials/metabox_'.$meta['args']['group'].'.php');
        }

        public function show_meta_box_contact_pages($post,$meta){
            $rss_data  = [];
            foreach ($this->option_fields[$meta['args']['group']] as $field => $data) {
                $this->option_fields[$meta['args']['group']][$field]['value'] = get_post_meta( $post->ID, $field, true );
            }
            include_once( CONTACTXP_PLUGIN_DIR . 'partials/metabox_'.$meta['args']['group'].'.php');
        }
        public function save_meta_box(){
            global $post;
            if(empty($post) || !isset($post->ID)){
                return;
            }

            if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ){
                return;
            }
            if( ! current_user_can( 'edit_post', $post->ID ) ){
                return;
            }
            foreach ($this->option_fields as $group => $fields) {
                if( !isset( $_POST['_'.$group.'_metabox_nonce'] ) || !wp_verify_nonce( $_POST['_'.$group.'_metabox_nonce'], basename( __FILE__ ) ) ){
                    return;
                }

                foreach ($fields as $key => $data) {
                    $this->save_meta_value($post->ID,$key,stripslashes_deep($_POST[$key]));
                }
            }
        }
        public function save_meta_value($id,$meta_id = '',$value = ''){
            if(!empty($meta_id)){
                if( isset( $value ) ){
                    update_post_meta( $id , $meta_id , $value );
                }else{
                    delete_post_meta( $id , $meta_id  );
                }
            }
        }

        public function domain_metabox(){
            include CONTACTXP_PLUGIN_DIR . 'partials/menu-domain-settings.php';
        }

        public function admin_init(){

        }

        public function menu_admin_view(){
            require_once CONTACTXP_PLUGIN_DIR . 'partials/menu-admin-view.php';
        }

        public function menu_domain_list_view(){
            require_once CONTACTXP_PLUGIN_DIR . 'partials/menu-domain-list-view.php';
        }

        public static function activate(){

        }

        public static function deactivate(){

        }

    }

}
