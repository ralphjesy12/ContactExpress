<?php
$sites = [
    // 'http://localhost/mrdwight',
    'https://www.flyskyjetair.com',
    // 'https://www.google.com',
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
<!-- <script>
if (!!window.EventSource) {
    console.log('SSE Started');
    var source = new EventSource("<?=CONTACTXP_PLUGIN_URL?>lib/sse.php");
    console.log(source);
    source.addEventListener("log", function(e) {
        console.log(e.data);
    }, false);
} else {
    alert("Your browser does not support Server-sent events! Please upgrade it!");
}
</script> -->
