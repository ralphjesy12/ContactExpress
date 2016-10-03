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
                'response_categories' => [
                    'label' => 'Response Categories',
                    'description' => 'Select the categories of the response groups you wish to use',
                    'type' => 'categorycheckbox',
                    'required' => true
                ],
            ],
            'contact_pages' => [
                'contact_links' => [
                    'label' => 'Contact Us Pages',
                    'description' => 'List of pages found as possible contact forms',
                    'type' => 'page-tables',
                ]
            ],
            'response_fields' => [
                'firstname' => [
                    'label' => 'First Name',
                    'description' => '',
                    'type' => 'text',
                    'required' => true
                ],
                'lastname' => [
                    'label' => 'Last Name',
                    'description' => '',
                    'type' => 'text',
                    'required' => true
                ],
                'subject' => [
                    'label' => 'Subject',
                    'description' => '',
                    'type' => 'text',
                    'required' => true
                ],
                'company' => [
                    'label' => 'Company Name',
                    'description' => '',
                    'type' => 'text',
                    'required' => false
                ],
                'phone' => [
                    'label' => 'Phone Number',
                    'description' => '',
                    'type' => 'text',
                    'required' => false
                ],
                'email' => [
                    'label' => 'Email Address',
                    'description' => '',
                    'type' => 'text',
                    'required' => false
                ],
                'message' => [
                    'label' => 'Message',
                    'description' => '',
                    'type' => 'textarea',
                    'required' => false
                ],
            ]
        ];

        function __CONSTRUCT()
        {
            add_action('init', [&$this, 'init']);
            add_action('admin_init', [&$this, 'admin_init']);
            add_action( 'wp_ajax_export_contact_grab_logs', [&$this , 'export_contact_grab_logs'] );
            add_action( 'wp_ajax_nopriv_export_contact_grab_logs', [&$this , 'export_contact_grab_logs'] );

        }

        public function init(){
            register_post_type( 'domain', array(
                'labels' => array(
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
                ),
                'public' => true,
                'has_archive' => false,
                'publicly_queryable' => false,
                'query_var' => false,
                'rewrite' => true,
                'capability_type' => 'post',
                'hierarchical' => false,
                'supports' => array(
                    'title',
                ),
                'show_in_menu'       => false,
                'taxonomies' => array( 'category' ), // add default post categories and tags
                'menu_position' => 5,
                'exclude_from_search' => true,
                'register_meta_box_cb' => function(){
                    add_meta_box( 'domain_metabox', 'Domain Settings', [&$this,'show_meta_box'], 'domain', 'normal','high',['group'=>'domain_settings']);
                    add_meta_box( 'contact_pages_metabox', 'Contact Form Pages', [&$this,'show_meta_box_contact_pages'], 'domain', 'normal','high',['group'=>'contact_pages']);
                }
            ));

            register_post_type( 'response', array(
                'labels' => array(
                    'name' => 'Response',
                    'singular_name' => 'Response',
                    'add_new' => 'Add Response',
                    'all_items' => 'All Responses',
                    'add_new_item' => 'Add Response',
                    'edit_item' => 'Edit Response',
                    'new_item' => 'New Response',
                    'view_item' => 'View Response',
                    'search_items' => 'Search Responses',
                    'not_found' => 'No Responses found',
                    'not_found_in_trash' => 'No Responses found in trash',
                    'parent_item_colon' => 'Parent Response'
                ),
                'public' => true,
                'has_archive' => false,
                'publicly_queryable' => false,
                'query_var' => false,
                'rewrite' => true,
                'capability_type' => 'post',
                'hierarchical' => false,
                'supports' => array(
                    'title',
                ),
                'taxonomies' => array( 'response_categories' ), // add default post categories and tags
                'show_in_menu'       => false,
                'exclude_from_search' => true,
                'register_meta_box_cb' => function(){
                    add_meta_box( 'domain_metabox', 'Response Fields', [&$this,'show_meta_box'], 'response', 'normal','high',['group'=>'response_fields']);
                }
            ));

            register_taxonomy( 'response_categories', array( 'response' ), array(
                'hierarchical'      => true, // Set this to 'false' for non-hierarchical taxonomy (like tags)
                'labels'            => array(
                    'name'              => _x( 'Response Categories', 'taxonomy general name' ),
                    'singular_name'     => _x( 'Response Category', 'taxonomy singular name' ),
                    'search_items'      => __( 'Search Response Categories' ),
                    'all_items'         => __( 'All Response Categories' ),
                    'parent_item'       => __( 'Parent Response Category' ),
                    'parent_item_colon' => __( 'Parent Response Category:' ),
                    'edit_item'         => __( 'Edit Response Category' ),
                    'update_item'       => __( 'Update Response Category' ),
                    'add_new_item'      => __( 'Add New Response Category' ),
                    'new_item_name'     => __( 'New Response Category Name' ),
                    'menu_name'         => __( 'Response Categories' ),
                ),
                'show_ui'           => true,
                'show_admin_column' => true,
                'query_var'         => true,
                'rewrite'           => array( 'slug' => 'categories' ),
                ) );

                add_action( 'save_post_domain', [ &$this , 'save_meta_box' ]);
                add_action( 'save_post_response', [ &$this , 'save_meta_box' ]);

            }


            public function admin_init(){
                add_submenu_page( 'google-miner', 'wp-menu-separator', '', 'read', null, '' );
                add_submenu_page( 'google-miner', 'Domains', 'Domains',
                'manage_options', 'edit.php?post_type=domain', NULL );
                add_submenu_page( 'google-miner', 'Responses', 'Responses',
                'manage_options', 'edit.php?post_type=response', NULL );
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
                        continue;
                    }

                    foreach ($fields as $key => $data) {
                        if(!empty($_POST[$key])){
                            $this->save_meta_value($post->ID,$key,stripslashes_deep($_POST[$key]));
                        }
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



            public function export_contact_grab_logs() {
                global $wpdb;
                $sessions = get_post_meta($_POST['post'],'record_entry');

                $objPHPExcel = new PHPExcel();

                $post = get_post($_POST['post']);

                // Set document properties
                $objPHPExcel->getProperties()->setCreator("Gyps ". GMINER_VERSION ." by: Jenner F. Alagao")
                ->setTitle("Gyps ". GMINER_VERSION ." Exported Data")
                ->setDescription("Gyps ". GMINER_VERSION ." Exported Data");

                $_x = 0;

                $objPHPExcel->setActiveSheetIndex(0);
                $objPHPExcel->getActiveSheet()->setTitle( $post->post_title );
                $objPHPExcel->getActiveSheet()

                ->setCellValue('A1', 'Domain Name')
                ->setCellValue('B1', $post->post_title)

                ->setCellValue('A2', 'Domain URL')
                ->setCellValue('B2', get_post_meta($_POST['post'],'domain_url',true))

                ->setCellValue('A3', 'Page URL Keywords')
                ->setCellValue('B3', get_post_meta($_POST['post'],'page_url_keywords',true))

                ->setCellValue('A7', '#: ' . $_state)
                ->setCellValue('B7', 'Started')
                ->setCellValue('C7', 'Ended')
                ->setCellValue('D7', 'Result')
                ->setCellValue('E7', 'Log')
                ->setCellValue('F7', 'Response');

                $i = 8;
                foreach ( $sessions as $key => $session ){

                    $statusstring = '';
                    foreach ($session['status'] as $kk => $status) {
                        if(isset($status['code'])){
                            switch ($status['code']) {
                                case '200':
                                $statusstring = "OK (Status 200)";
                                break;
                                default:
                                $statusstring = "Failed (Status ".$status['code'].")";
                                break;
                            }
                        }else{
                            $statusstring = "Failed (No status code)";
                        }
                    }

                    $logstring = '';

                    foreach ($session['log'] as $kk => $log) {
                        $logstring .= "[".$log['time']."]".sprintf("[%-'#7s]",   ['ERROR','SUCCESS','INFO'][$log['status']]).' : '.$log['type'].' ~ '.$log['desc']."\n";
                    }

                    $responsestring = '';

                    foreach ($session['status'] as $kk => $log) {
                        $responsestring .= "Time : ".$log['time']."\n";
                        $responsestring .= "URL : ".$log['url']."\n";
                        $responsestring .= "Current : ".$log['current']."\n";
                        $responsestring .= "Title : ".$log['title']."\n";
                        $responsestring .= "Response : ".JSON_ENCODE($log['response'])."\n";
                        $responsestring .= "Code : ".JSON_ENCODE($log['code'])."\n";
                    }


                    $objPHPExcel->getActiveSheet()
                    ->setCellValue('A' . $i, ($key+1))
                    ->setCellValue('B' . $i, date("M j h:i:s a",strtotime($session['started'])))
                    ->setCellValue('C' . $i, date("M j h:i:s a",strtotime($session['ended'])))
                    ->setCellValue('D' . $i, $statusstring)
                    ->setCellValue('E' . $i, $logstring)
                    ->setCellValue('F' . $i, $responsestring);

                    $objPHPExcel->getActiveSheet()->getStyle('E' . $i)->getAlignment()->setWrapText(true);
                    $objPHPExcel->getActiveSheet()->getStyle('F' . $i)->getAlignment()->setWrapText(true);

                    $i++;
                }

                $objPHPExcel->getActiveSheet()
                ->getStyle( $objPHPExcel->getActiveSheet()->calculateWorksheetDimension() )
                ->getAlignment()->setHorizontal('left');


                $objPHPExcel->getActiveSheet()
                ->getStyle( $objPHPExcel->getActiveSheet()->calculateWorksheetDimension() )
                ->getAlignment()->setVertical('top');

                foreach(range('A','F') as $columnID) {
                    $objPHPExcel->getActiveSheet()->getColumnDimension($columnID)
                    ->setAutoSize(true);

                }

                $_fileName = sanitize_title($post->post_title) .'_'. date("Y-M-d_g_iA") . '_'. md5(date("Y-M-d_g_iA"));


                if (wp_mkdir_p(CONTACTXP_PLUGIN_DIR .'/cache/')) {
                    $_filePath = CONTACTXP_PLUGIN_DIR .'/cache/'.$_fileName .'.xlsx';
                    $_fileURL = CONTACTXP_PLUGIN_URL .'/cache/'.$_fileName .'.xlsx';
                }
                
                // Set active sheet index to the first sheet, so Excel opens this as the first sheet
                $objPHPExcel->setActiveSheetIndex(0);

                $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
                $objWriter->save( $_filePath , 'w');

                $_ret = array("status"=>"DONE" , "url" => $_fileURL);
                echo json_encode($_ret);
                exit;

            }

        }

    }
