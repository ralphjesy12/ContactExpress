<?php if(!defined('CONTACTXP_VERSION')) die('Fatal Error');

if(!class_exists('ContactXpress')){

    class ContactXpress{

        public $browser;
        public $url;
        public $page;
        public $log;
        public $variations = [
            'sitemap' => [
                'sitemap.xml',
                'sitemap_index.xml',
            ],
            'contact' => [
                'contactus',
                'contact-us',
                'about',
                'about-us'
            ]
        ];
        public $urls = [];
        public $testedUrls = [];

        function __CONSTRUCT($url)
        {
            if($url==null) return;

            $this->logger('Browser','Start',1);
            $driver = new \Behat\Mink\Driver\GoutteDriver();
            $this->browser = new \Behat\Mink\Session($driver);
            $this->browser->start();
            $this->url = $url;
            // $this->fill();
        }

        function getFileContentsHTTPS($URL,$len = null,$offset = 0){

            $driver = new \Behat\Mink\Driver\GoutteDriver();
            $browser = new \Behat\Mink\Session($driver);
            $browser->start();
            $browser->visit($URL);
            return $browser->getPage()->getOuterHtml();

            // Open the file using the HTTP headers set above
            if($len!=null) return file_get_contents($URL, NULL, null ,$offset,$len);

            return file_get_contents($URL, false, stream_context_create([
                'http' => [
                    'method'  => 'GET',
                    'user_agent '  => "Mozilla/5.0 (X11; U; Linux x86_64; en-US; rv:1.9.2) Gecko/20100301 Ubuntu/9.10 (karmic) Firefox/3.6",
                    'header' => [
                        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*\/*;q=0.8
                        '
                    ],
                ]
            ]));
        }

        function getAllUrlsFromSitemap($uri){
            $urls = [];
            // Do not test again same sitemap;
            if(in_array(hash('crc32b',$uri),$this->testedUrls)){
                return true;
            }
            $this->testedUrls[] = hash('crc32b',$uri);

            // $strXml = $this->getFileContentsHTTPS($uri);
            // var_dump($strXml);

            $driver = new \Behat\Mink\Driver\GoutteDriver();
            $browser = new \Behat\Mink\Session($driver);
            $browser->start();
            $browser->visit($uri);
            $page = $browser->getPage();

            if($page){
                $this->logger('Lookup','Parsing Sitemap URI : '.$uri,2);
                $locs = $page->findAll('xpath','//loc');
                foreach ($locs as $key => $loc) {
                    $locurl = $loc->getHtml();
                    $urls[hash('crc32b',$locurl)] = strval($locurl);
                }

                $grabbed = 0;
                // Check if the gathered URLS here are sitemaps
                foreach ($urls as $key => $url) {

                    $isxml = false;

                    foreach ($this->variations['sitemap'] as $str) {
                        if(strpos($url, $str)!==false){
                            $isxml = true;
                            break;
                        }
                    }

                    if($isxml){
                        $this->getAllUrlsFromSitemap($url);
                    }else{

                        // Add Only URLS that are similar to what we are looking for
                        foreach ($this->variations['contact'] as $str) {
                            if(strpos($url, $str)!==false){
                                $grabbed++;
                                $this->urls[$key] = $url;
                                break;
                            }
                        }
                    }
                }

                $this->logger('Lookup','Grabbed URLS on ['.$uri.']: '.count($grabbed),2);
            }else{
                $this->logger('Lookup','Empty Sitemap URI : '.$uri,0);
            }
            return true;
        }

        function isValidXml($content)
        {
            $content = trim($content);
            if (empty($content)) {
                return false;
            }
            //html go to hell!
            if (stripos($content, '<!DOCTYPE html>') !== false) {
                return false;
            }

            libxml_use_internal_errors(true);
            simplexml_load_string($content);
            $errors = libxml_get_errors();
            libxml_clear_errors();

            return empty($errors);
        }

        function formLookUp(){
            // Look for all possible sitemap.xml


            $this->logger('Lookup','Trying Sitemap URI Variations',2);
            foreach ($this->variations['sitemap'] as $uri) {
                $uri = rtrim($this->url,'/') . '/' . $uri;

                $this->logger('Lookup','Trying Sitemap URI : '.$uri,2);
                $this->browser->visit($uri);
                if($this->browser->getStatusCode()==200){
                    $this->logger('Lookup','Sitemap URI : '.$uri.' Exist',1);

                    // If Exist try to grab all links
                    $this->getAllUrlsFromSitemap($uri);

                    $this->logger('Lookup','Total Grabbed Unique URLS : '.count($this->urls),2);
                    // $this->logger('Lookup','Grabbed URLS : '.count($this->urls),2);
                }else{
                    $this->logger('Lookup','Sitemap URI : '.$uri.' doesnt exist',0);
                }
            }

        }

        function fill(){
            $this->logger('Browser','Filling Up Form : Start',1);
            $this->browser->visit($this->url);
            $this->page = $this->browser->getPage();

            $this->logger('Browser','Filling Up Form : Name',1);
            $namefield = $this->page->find('named', array('id_or_name',  $this->browser->getSelectorsHandler()->xpathLiteral('your-name')));
            $namefield->setValue('Judy Anne');

            $this->logger('Browser','Filling Up Form : Email',1);
            $emailfield = $this->page->find('named', array('id_or_name',  $this->browser->getSelectorsHandler()->xpathLiteral('your-email')));
            $emailfield->setValue('judyanne@gmail.com');

            $this->logger('Browser','Filling Up Form : Subject',1);
            $subj = $this->page->find('named', array('id_or_name',  $this->browser->getSelectorsHandler()->xpathLiteral('your-subject')));
            $subj->setValue('General Inquiry');

            $this->logger('Browser','Filling Up Form : Message',1);
            $subj = $this->page->find('named', array('id_or_name',  $this->browser->getSelectorsHandler()->xpathLiteral('your-message')));
            $subj->setValue('Yow!');

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
                    $logfile = fopen(CONTACTXP_PLUGIN_DIR . "debug.log", "a") or die("Unable to open file!");
                    $txt = JSON_ENCODE($lognow)."\n";
                    fwrite($logfile, $txt);
                    fclose($logfile);
                }
            }
        }

        function showlog(){
            echo '<pre>';
            foreach ($this->log as $key => $log) {
                echo "[".$log['time']."]".sprintf("[%-'#7s]",   $log['status']).' : '.$log['type'].' ~ '.$log['message'].'<br>';
            }
            echo '</pre>';
        }

    }

}
