<div class="wrap">
    <h1>Contact Express</h1>
    <textarea style="width:500px" rows="4">http://localhost/mrdwight/contact-us/</textarea>
</div>

<?php

$sites = [
    'http://localhost/mrdwight/contact-us/'
];

foreach ($sites as $site) {
    $contactform = new ContactXpress($site);
    echo "<pre>";
    var_dump($contactform->log);
    echo "</pre>";
}

?>
