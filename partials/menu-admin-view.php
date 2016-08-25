<?php
$sites = [
    'http://localhost/mrdwight',
    'https://www.flyskyjetair.com',
    'https://www.google.com',
];
?>

<div class="wrap">
    <h1>Contact Express</h1>
    <textarea style="width:500px" rows="4"><?php array_walk($sites,function($site){
        echo $site . "\n";
    });?></textarea>

</div>
<?php

foreach ($sites as $site) {
    $contactform = new ContactXpress($site);
    $contactform->formLookUp();
    var_dump($contactform->urls);
    $contactform->showlog();
}

?>
