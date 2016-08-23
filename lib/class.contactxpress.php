<?php if(!defined('CONTACTXP_VERSION')) die('Fatal Error');

if(!class_exists('ContactXpress')){

    class ContactXpress{

        public $browser;
        public $url;
        public $page;
        public $log;

        function __CONSTRUCT($url)
        {
            if($url==null) return;

            $this->logger('Browser','Start',1);
            $driver = new \Behat\Mink\Driver\GoutteDriver();
            $this->browser = new \Behat\Mink\Session($driver);
            $this->browser->start();
            $this->url = $url;
            $this->fill();
        }

        function fill(){
            $this->logger('Browser','Filling Up Form : Start',1);
            $this->browser->visit($this->url);
            $this->page = $this->browser->getPage();

            $this->logger('Browser','Filling Up Form : Name',1);
            $namefield = $this->page->find('named', array('id_or_name',  $this->browser->getSelectorsHandler()->xpathLiteral('your-name')));
            $namefield->setValue('Ralph');

            $this->logger('Browser','Filling Up Form : Email',1);
            $emailfield = $this->page->find('named', array('id_or_name',  $this->browser->getSelectorsHandler()->xpathLiteral('your-email')));
            $emailfield->setValue('ralphjesy@gmail.com');

            $this->logger('Browser','Filling Up Form : Subject',1);
            $subj = $this->page->find('named', array('id_or_name',  $this->browser->getSelectorsHandler()->xpathLiteral('your-subject')));
            $subj->setValue('General Inquiry');

            $this->logger('Browser','Filling Up Form : Message',1);
            $subj = $this->page->find('named', array('id_or_name',  $this->browser->getSelectorsHandler()->xpathLiteral('your-message')));
            $subj->setValue('Howdy!');

            $this->logger('Browser','Submitting Form',1);
            $form = $this->page->find('css', '.wpcf7-form');
            $form->submit();

        }

        function logger($type,$message,$level){
            if(CONTACTXP_DEBUG){
                $lognow = [
                    'status' => ['ERROR','OK','INFO','WARNING'][$level], // 0 = Error, 1 = OK , 2 = Info , 3 = Warning
                    'type' => $type,
                    'message' => $message,
                    'time' => date('Y-m-d H:i:s')
                ];
                $this->log[] = $lognow;
                if(CONTACTXP_DEBUG_LOG){
                    $logfile = fopen(CONTACTXP_PLUGIN_DIR . "dmrss-feeds.log", "a") or die("Unable to open file!");
                    $txt = JSON_ENCODE($lognow)."\n";
                    fwrite($logfile, $txt);
                    fclose($logfile);
                }
            }
        }

    }

}
