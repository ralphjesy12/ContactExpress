<?php //if(!defined('CONTACTXP_VERSION')) die('Fatal Error');

if(!class_exists('ContactXpress')){

    class ContactXpress{

        public $browser;
        public $url;
        public $page;
        public $log;
        public $logname;
        public $scanlog;
        public $scanStart;
        public $scanEnd;
        public $scanStatus = [];
        public $variations = [
            'sitemap' => [
                'sitemap.xml',
                'sitemap_index.xml',
            ],
            'contact' => [
                'contact',
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

            $this->logname = time().'.log';
            // var_dump($this->logname);
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

        function getPageTitle($uri){
            $driver = new \Behat\Mink\Driver\GoutteDriver();
            $browser = new \Behat\Mink\Session($driver);
            $browser->start();
            $browser->visit($uri);
            $page = $browser->getPage();
            return $page->find('css','title')->getText();
        }

        function getAllUrlsFromPage($uri){
            $this->logger('Lookup','Parsing Sitemap URI : '.$uri,2);
            $urls = [];
            $driver = new \Behat\Mink\Driver\GoutteDriver();
            $browser = new \Behat\Mink\Session($driver);
            $browser->start();
            $browser->visit($uri);
            $page = $browser->getPage();

            $urlshere = $page->findAll('xpath', '//a');
            $this->logger('Lookup','URLS Found : '.count($urlshere),1);

            if(count($urlshere)){
                foreach ($urlshere as $url) {
                    $urlhere = $url->getAttribute('href');
                    if(!empty($urlhere)){
                        $urls[] = $urlhere;
                    }
                }
            }

            $this->logger('Lookup','URLS Found : '.count($urls),1);
            $urlsbefore = count($this->urls);
            foreach ($urls as $key => $url) {

                // Add Only URLS that are similar to what we are looking for
                foreach ($this->variations['contact'] as $str) {
                    if(strpos($url, $str)!==false){

                        $hashkey = hash('crc32b',$url);
                        if(empty($this->urls[$hashkey])){
                            $this->urls[$hashkey] = [
                                'url' => $url,
                                'title' => $this->getPageTitle($url)
                            ];
                        }
                        break;
                    }
                }
            }

            $urlsnow = count($this->urls);
            $this->logger('Lookup','URLS Valid : '.($urlsnow - $urlsbefore),1);


            foreach ($this->urls as $k => $formurl) {
                $this->logger('Fillup','Trying to find forms on : '.$formurl['url'],2);
                $this->findForm($formurl['url'],hash('crc32b',$formurl['url']));
            }

            return;
        }

        function getAllUrlsFromSitemap($uri){
            $urls = [];
            // Do not test again same sitemap;
            if(in_array(hash('crc32b',$uri),$this->testedUrls)){
                return true;
            }
            $this->testedUrls[] = hash('crc32b',$uri);

            $driver = new \Behat\Mink\Driver\GoutteDriver();
            $browser = new \Behat\Mink\Session($driver);
            $browser->start();
            $browser->visit($uri);
            $page = $browser->getPage();

            if($page){
                $xmlcont = $page->getContent();

                try{
                    $xmlparser = @simplexml_load_string($xmlcont);
                }catch(Exception $e){
                    var_dump($xmlcont);
                }

                $this->logger('Lookup','Parsing Sitemap URI : '.$uri,2);

                $urls = [];
                if($xmlparser){
                    switch ($xmlparser->getName()) {
                        case 'urlset':
                        foreach ($xmlparser->url as $urltag) {
                            foreach ($urltag->loc as $loctag) {
                                $urls[hash('crc32b',$loctag->__toString())] = strval($loctag->__toString());
                            }
                        }
                        break;
                        case 'sitemapindex':
                        foreach ($xmlparser->sitemap as $sitemaptag) {
                            foreach ($sitemaptag->loc as $loctag) {
                                foreach ($this->variations['sitemap'] as $str) {
                                    if(strpos($loctag, $str)!==false){
                                        $this->getAllUrlsFromSitemap($loctag->__toString());
                                        break;
                                    }
                                }
                            }
                        }
                        break;
                    }
                }

                $grabbed = 0;
                // Check if the gathered URLS here are sitemaps
                foreach ($urls as $key => $url) {

                    // Add Only URLS that are similar to what we are looking for
                    foreach ($this->variations['contact'] as $str) {
                        if(strpos($url, $str)!==false){
                            $grabbed++;

                            $hashkey = hash('crc32b',$url);
                            if(empty($this->urls[$hashkey])){
                                $this->urls[$hashkey] = [
                                    'url' => $url,
                                    'title' => $this->getPageTitle($url)
                                ];
                            }
                            break;
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



                    foreach ($this->urls as $k => $formurl) {
                        $this->logger('Fillup','Trying to find forms on : '.$formurl['url'],2);
                        $this->findForm($formurl['url'],$k);
                    }


                    // $this->logger('Lookup','Grabbed URLS : '.count($this->urls),2);
                }else{
                    $this->logger('Lookup','Sitemap URI : '.$uri.' doesnt exist',0);
                }
            }

        }

        function saveRecord($id){
            add_post_meta($id,'record_entry',[
                'started' => $this->scanStart,
                'ended' => $this->scanEnd,
                'log' => $this->scanlog,
                'status' => $this->scanStatus
            ]);
        }

        function addScanRecord($type,$desc,$status = 2){
            $this->scanlog[] = [
                'time' => date("Y-m-d H:i:s"),
                'type' => $type,
                'desc' => $desc,
                'status' => $status
            ];
        }

        function findForm($url,$key = 0){
            $this->scanlog = [];
            // echo '<pre>';
            $this->logger('Fillup','Inspecting URL : '.$url,2);
            $this->addScanRecord('Start','Visiting URL : '.$url);
            $this->browser->visit($url);
            $this->page = $this->browser->getPage();
            $formshere = $this->page->findAll('xpath', '//form');
            if(count($formshere)){
                $this->logger('Fillup','Possible Forms found : '.count($formshere),1);
                $this->addScanRecord('Scan','Forms found : '.count($formshere),1);
                $validForms = 0;
                foreach ($formshere as $k => $form) {
                    $this->addScanRecord('Input','Filling up form #'.($k+1));
                    $numInput = count($form->findAll('css','input'));
                    $this->logger('Fillup','Inputs inside form : '.$numInput,1);
                    $inputsRequiredToValidate = [
                        'name',
                        'email',
                        'message',
                    ];
                    $found = 0;
                    foreach ($inputsRequiredToValidate as $input) {

                        // For Case Insensitivity
                        $input = strtolower($input);
                        $totalFound = (
                        count($form->findAll('css','[name*="'.$input.'"]')) + // name
                        count($form->findAll('css','[name*="'.ucfirst($input).'"]')) + // Name
                        count($form->findAll('css','[name*="'.strtoupper($input).'"]'))); // NAME

                        if($totalFound){
                            $this->logger('Fillup','Looking for Field : '.'[name*="'.$input.'"] Found'.'',1);
                            $found++;
                        }else{
                            $this->logger('Fillup','Looking for Field : '.'[name*="'.$input.'"] Not Found'.'',1);
                        }
                    }

                    if($found){
                        $validForms++;
                    }

                    $this->logger('Fillup','Possible Forms found : '.$found.'/'.count($inputsRequiredToValidate).' inputs passed',1);

                }
                $this->urls[$key]['forms'] = [
                    'found' => count($formshere),
                    'valid' => $validForms
                ];
                $this->logger('Fillup','Valid Forms : '.$validForms.'/'.count($formshere),2);
            }else{
                $this->logger('Fillup','No Forms found',0);
                $this->addScanRecord('End','No forms found.',0);
            }
            // echo '</pre>';

        }

        function saveScreenShot($type){
            return;
            $screenshotfolder = 'CXPScreenshots';
            $extension = '.png';
            $filename = sanitize_title($type);
            if(empty($filename)){
                $filename = 'screenshot-';
            }
            $filename .= '-' . date('Y-m-d_H-i-s') . $extension;

            $this->logger('Screenshot','Saving screenshot to '.$filename,2);

            $upload_dir = wp_upload_dir();
            $uploadScreenshots = $upload_dir['basedir'].'/'.$screenshotfolder;
            if ( ! file_exists( $uploadScreenshots ) )
            {
                if ( ! (wp_mkdir_p( $uploadScreenshots ) === TRUE) )
                {
                    // Unable to Make Default screenshot Folder
                    $this->logger('Screenshot','Unable to create Screenshot Directory '.$uploadScreenshots,0);
                    return false;
                }
            }

            $uploadScreenshotsfile = $uploadScreenshots . '/' . $filename;
            // Save Screenshot
            // $screenshot = $this->browser->getDriver()->getScreenshot();
            $screenCapture = new \Screen\Capture();
            $screenCapture->setUrl($this->browser->getCurrentUrl());
            $screenCapture->setWidth(1200);
            $screenCapture->setHeight(800);
            $screenCapture->setClipWidth(1200);
            $screenCapture->setClipHeight(800);
            $screenCapture->setBackgroundColor('#ffffff');
            $screenCapture->setImageType('png');
            $screenCapture->save($uploadScreenshotsfile);

            if(file_exists( $uploadScreenshotsfile )===FALSE){
                // Unable to write screenshot to file
                $this->logger('Screenshot','Unable to write screenshot to file'.$filename,0);
                return false;
            }
            return true;
        }

        function fill($data = []){
            $url = $this->url;
            $this->scanlog = [];
            $this->scanStart = null;
            $this->scanEnd = null;
            $this->scanStatus = [];
            $this->scanStart = date("Y-m-d H:i:s");
            if(empty($data) || empty($url)) return false;

            $this->addScanRecord('Start','Visiting URL : '.$url);
            $this->logger('Fillup','Inspecting URL : '.$url,2);
            $this->browser->visit($url);
            $this->page = $this->browser->getPage();
            $formshere = $this->page->findAll('xpath', '//form');
            if(count($formshere)){

                $this->logger('Fillup','Possible Forms found : '.count($formshere),1);
                $this->addScanRecord('Fillup','Possible Forms found : '.count($formshere),1);
                $validForms = 0;
                foreach ($formshere as $k=>$form) {
                    $numInput = count($form->findAll('css','input'));
                    $this->logger('Fillup','Inputs inside form : '.$numInput,1);
                    $inputsRequiredToValidate = [
                        'name',
                        'email',
                        'message',
                    ];
                    $found = 0;
                    $this->addScanRecord('Fillup','Checking Form # '.($k+1));
                    foreach ($inputsRequiredToValidate as $input) {

                        // For Case Insensitivity
                        $input = strtolower($input);
                        $totalFound = (
                        count($form->findAll('css','[name*="'.$input.'"]')) + // name
                        count($form->findAll('css','[name*="'.ucfirst($input).'"]')) + // Name
                        count($form->findAll('css','[name*="'.strtoupper($input).'"]'))); // NAME

                        if($totalFound){
                            $this->logger('Fillup','Looking for Field : '.'[name*="'.$input.'"] Found'.'',1);
                            $found++;
                        }else{
                            $this->logger('Fillup','Looking for Field : '.'[name*="'.$input.'"] Not Found'.'',1);
                        }
                    }

                    if($found){
                        $validForms++;

                        if(!empty($data)){
                            $this->addScanRecord('Fillup','Filling up Form # '.($k+1).' using response template id : '.$data['id']);
                            $this->logger('Browser','Filling Up Form : Start',1);
                            // Save Screenshot before filling up form
                            $this->saveScreenShot('1-before-fillup');
                            foreach ([
                                'firstname' => 'first',
                                'lastname' => 'last',
                                'company' => 'company',
                                'phone' => 'phone',
                                'email' => 'email',
                                'message' => 'message',
                                'subject' => 'subject',
                                ] as $key => $input) :
                                if(!empty($data[$key])){
                                    foreach ([
                                        '[name*="'.$input.'"]',
                                        '[name*="'.ucfirst($input).'"]',
                                        '[name*="'.strtoupper($input).'"]'
                                        ] as $css):
                                        $namefield = $form->findAll('css','[name*="'.$input.'"]');
                                        if(count($namefield)){
                                            foreach ($namefield as $kk => $name) {
                                                $name->setValue($data[$key]);
                                            }
                                        }
                                    endforeach;
                                    $this->logger('Browser','Filling Up Form : '.$key,1);
                                }
                            endforeach;

                            // Save Screenshot after filling up form
                            $this->saveScreenShot('2-after-fillup');
                            $this->logger('Browser','Submitting Form',1);
                            $form->submit();

                            $this->addScanRecord('Submit','Form # '.($k+1).' has been submitted');
                            // Save Screenshot after submission
                            $this->saveScreenShot('3-after-submit');

                            $this->scanStatus[] = [
                                'time' => date("Y-m-d H:i:s"),
                                'url' => $this->url,
                                'current' => $this->browser->getCurrentUrl(),
                                'title' => $this->browser->getPage()->find('css','title')->getText(),
                                'response' => $this->browser->getResponseHeaders(),
                                'code' => $this->browser->getStatusCode()
                            ];
                        }
                    }

                    $this->logger('Fillup','Possible Forms found : '.$found.'/'.count($inputsRequiredToValidate).' inputs passed',1);

                }
                $hashkey = hash('crc32b',$url);

                $this->urls[$hashkey]['forms'] = [
                    'found' => count($formshere),
                    'valid' => $validForms
                ];
                $this->addScanRecord('Fillup',''.$validForms.' out of '.count($formshere).' forms has been submitted',2);


                // $this->addScanRecord('Done','Form # '.($k+1).' has been submitted');
            }else{
                $this->logger('Fillup','No Forms found',0);
                $this->addScanRecord('Fillup','No Forms found',0);
            }
            $this->scanEnd = date("Y-m-d H:i:s");

        }

        function logger($type,$message,$level){
            if(CONTACTXP_DEBUG){
                $lognow = [
                    'status' => ['ERROR','OK','INFO','WARNING'][$level], // 0 = Error, 1 = OK , 2 = Info , 3 = Warning
                    'type' => $type,
                    'message' => $message,
                    'time' => date('Y-m-d H:i:s')
                ];

                $this->writeLog('log',"[".$lognow['time']."]".sprintf("[%-'#7s]",   $lognow['status']).' : '.$lognow['type'].' ~ '.$lognow['message']);
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

        function writeLog($type = 'log',$msg = '') {
            if(CONTACTXP_DEBUG_LOG){
                if(!is_string($msg)){
                    $msg = JSON_ENCODE($msg);
                }
                if(!$this->logname){
                    $this->logname = time().'.log';
                }

                $logfile = fopen(CONTACTXP_PLUGIN_DIR . "logs/".$this->logname, "a") or die("Unable to open file!");
                $txt = $msg."\n";
                fwrite($logfile, $txt);
                fclose($logfile);
            }
        }




    }

}
