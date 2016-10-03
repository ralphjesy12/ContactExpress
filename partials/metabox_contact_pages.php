<table class="form-table">
    <tbody>
        <?php foreach($this->option_fields[$meta['args']['group']] as $field => $data): ?>
            <tr class="user-rich-editing-wrap row-<?=sanitize_title($data['label'])?>">
                <th scope="row">
                    <?=($data['label'])?>
                    <p class="description"><?=($data['description'])?></p>
                </th>
                <td>
                    <?php
                    switch ($data['type']){
                        case 'page-tables':
                        ?>
                        <table>
                            <thead>
                                <th>#</th>
                                <th>Page Title</th>
                                <th>Valid Forms</th>
                                <th>Action</th>
                            </thead>
                            <tbody>
                                <?php if(!empty($data['value'])): $k = 0; ?>
                                    <?php foreach($data['value'] as $page): ?>
                                        <tr>
                                            <td><?=(++$k)?></td>
                                            <td><?=$page['title']?></td>
                                            <td><?=$page['forms']['valid']?>/<?=$page['forms']['found']?></td>
                                            <td>
                                                <a href="<?=$page['url']?>" target="_blank">Visit</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif;?>
                            </tbody>
                        </table>
                        <?php break;
                        case 'text':
                        default: ?>
                        <input type="text" name="<?=($field)?>" id="<?=($field)?>" value="<?=($data['value'])?>" class="regular-text" <?=($data['required'] ? 'required' : '')?>>
                        <?php
                        break;
                    }
                    ?>
                </td>
            </tr>
        <?php endforeach;?>
    </tbody>
</table>

<?php
$sessions = get_post_meta($_GET['post'],'record_entry');

if(!empty($sessions)){
    ?>
    <table class="form-table">
        <thead>
            <th>#</th>
            <th>Started</th>
            <th>Ended</th>
            <th>Result</th>
            <th>Action</th>
        </thead>
        <tbody>


            <?php
            foreach ($sessions as $key => $session) {
                ?>
                <tr>
                    <td><?=($key+1)?></td>
                    <td><?=date("M j h:i:s a",strtotime($session['started']))?></td>
                    <td><?=date("M j h:i:s a",strtotime($session['ended']))?></td>
                    <td>
                        <?php
                        foreach ($session['status'] as $kk => $status) {
                            if(isset($status['code'])){
                                switch ($status['code']) {
                                    case '200':
                                    echo "<label>OK (Status 200)</label><br>";
                                    break;
                                    default:
                                    echo "<label>Failed (Status ".$status['code'].")</label><br>";
                                    break;
                                }
                            }else{
                                echo "<label>Failed (No status code)</label><br>";
                            }
                        }
                        ?>

                    </td>
                    <td>
                        <a href="#" class="toggle-log">Show log</a>
                    </td>
                </tr>
                <tr class="hidden">
                    <td colspan="5">
                        <textarea rows="10" style="width:100%"><?php

                        echo "LOG : "."\n";
                        foreach ($session['log'] as $kk => $log) {
                            echo "[".$log['time']."]".sprintf("[%-'#7s]",   ['ERROR','SUCCESS','INFO'][$log['status']]).' : '.$log['type'].' ~ '.$log['desc']."\n";
                        }
                        echo "\n";
                        echo "\n";
                        echo "RESPONSE : "."\n";
                        foreach ($session['status'] as $kk => $log) {
                            echo "Time : ".$log['time']."\n";
                            echo "URL : ".$log['url']."\n";
                            echo "Current : ".$log['current']."\n";
                            echo "Title : ".$log['title']."\n";
                            echo "Response : ".JSON_ENCODE($log['response'])."\n";
                            echo "Code : ".JSON_ENCODE($log['code'])."\n";
                        }
                        ?></textarea>
                    </td>
                </tr>
                <?php
            }
            ?>
        </tbody>
    </table>
    <script>
    jQuery(function($){
        $('.toggle-log').click(function(e){
            e.preventDefault();
            $(this).closest('tr').next().toggleClass('hidden');
        });
    });
    </script>
    <?php
}
?>
