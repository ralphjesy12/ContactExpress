<?php
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache'); // recommended to prevent caching of event data.


function sendMsg($type = 'log',$msg = '') {
    // $msg = JSON_ENCODE($msg);
    echo "id: ". time() . PHP_EOL;
    echo "event: ". $type . PHP_EOL;
    echo "data: $msg" . PHP_EOL;
    echo PHP_EOL;
    ob_flush();
    flush();
}




/**
* Constructs the SSE data format and flushes that data to the client.
*
* @param string $id Timestamp/id of this connection.
* @param string $msg Line of text that should be transmitted.
*/

define( 'CONTACTXP_VERSION', '1.0' );
define( 'CONTACTXP_MIN_WP_VERSION', '4.4' );
define( 'CONTACTXP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CONTACTXP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'CONTACTXP_DEBUG' , false );
define( 'CONTACTXP_DEBUG_LOG' , false );

require_once( CONTACTXP_PLUGIN_DIR . 'vendor/autoload.php');
require_once( CONTACTXP_PLUGIN_DIR . 'lib/class.contactxpress.php');
// sendMsg('log','yow');




exit;



use Sse\Event;
use Sse\SSE;

//create the event handler
class ContactXpressSSE implements SSEEvent {
    public $sites = [
        // 'http://localhost/mrdwight',
        // 'https://www.flyskyjetair.com',
        'https://www.google.com',
    ];

    public function callClass(){
        foreach ($this->sites as $site) {
            $contactform = new ContactXpress($site);
            $contactform->formLookUp();
            // var_dump($contactform->urls);
            // $contactform->showlog();
        }
    }
    public function update(){
        //Here's the place to send data
        return 'Hello, world!';
    }

    public function check(){
        //Here's the place to check when the data needs update
        return true;
    }
}

$sse = new SSE();//create a libSSE instance
$sse->addEventListener('log',new ContactXpressSSE());//register your event handler
$sse->start();//start the event loop
?>
