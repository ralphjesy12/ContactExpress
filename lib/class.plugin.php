<?php if(!defined('CONTACTXP_VERSION')) die('Fatal Error');

if(!class_exists('CONTACTXP')){

    class CONTACTXP{

        function __CONSTRUCT()
        {
            add_action('init', [&$this, 'init']);
            add_action('admin_init', [&$this, 'admin_init']);
        }

        public function init(){
            add_action('admin_menu',function(){
                add_menu_page( 'Contact Xpress', 'Contact Xpress', 'manage_options', 'contact-xp', [&$this,'menu_admin_view'], 'dashicons-sos', 66);
            });

        }

        public function admin_init(){

        }

        public function menu_admin_view(){
            require_once CONTACTXP_PLUGIN_DIR . 'partials/menu-admin-view.php';
        }

    }

}
