<?php global $post; ?>
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
                        case 'textarea': ?>
                        <textarea type="text" name="<?=($field)?>" id="<?=($field)?>" <?=($data['required'] ? 'required' : '')?> style="width:100%;" rows="2"><?=(!empty($data['value']) ? esc_textarea($data['value']) : '')?></textarea>
                        <?php break;
                        case 'categorycheckbox':
                        $terms = get_terms( array(
                            'taxonomy' => 'response_categories',
                            'hide_empty' => true,
                        ));
                        if ( $terms && !is_wp_error( $terms ) ) :
                            $data['value'] = $data['value'] ?: [];
                            ?>
                            <ul>
                                <?php foreach ( $terms as $term ): ?>
                                    <li><input type="checkbox" name="<?=($field)?>[]" value="<?=$term->term_id?>" <?=(in_array($term->term_id,$data['value']) ? 'checked' : '')?>/><?=$term->name?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif;
                        break;
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
<a href="<?=add_query_arg([ 'cxp_action' => 'scan_domain' ])?>" class="button">Scan Domain</a>
<a href="<?=add_query_arg([ 'cxp_action' => 'send_responses' ])?>" class="button">Send Responses</a>
<a href="#" class="button export-log">Export Logs</a>
<?php
if(isset($_GET['cxp_action'])):
    switch ($_GET['cxp_action']) {
        case 'scan_domain':
        $site = $this->option_fields['domain_settings']['domain_url']['value'];
        if(empty($site)){
            echo 'Site URL Empty';
        }else{
            $contactform = new ContactXpress($site);

            $keywords = $this->option_fields['domain_settings']['page_url_keywords']['value'];

            if(!empty($keywords)){
                $contactform->variations['contact'] = explode(',',$this->option_fields['domain_settings']['page_url_keywords']['value']);
            }
            $contactform->getAllUrlsFromPage($site);
            $contactform->formLookUp();
            if(count($contactform->urls)){
                update_post_meta( $post->ID , 'contact_links' , $contactform->urls );
            }
            ?>
            <!-- <script>window.location.assign(<?=("'".add_query_arg([
            'cxp_action' => 'scan_domain_success'
            ])."'")?>)</script> -->
            <?php
        }

        break;
        case 'send_responses':
        $site = $this->option_fields['domain_settings']['domain_url']['value'];
        if(empty($site)){
            echo 'Site URL Empty';
        }else{
            $contactlinks = get_post_meta($post->ID,'contact_links',true);

            if(!empty($contactlinks)){
                $categories = $this->option_fields['domain_settings']['response_categories']['value'] ?: [];
                $args = [
                    'post_type' => 'response',
                    'post_status' => 'publish',
                ];

                $contactlinks = get_post_meta($post->ID,'contact_links',true);
                if(!empty($categories)){
                    $args['tax_query'] = array(
                        array(
                            'taxonomy' => 'response_categories',
                            'field' => 'id',
                            'terms' => $categories, // Where term_id of Term 1 is "1".
                            'include_children' => true
                        )
                    );
                }
                $responses = get_posts($args);
                if(!empty($responses)){
                    $data = [];
                    foreach ($responses as $response) {
                        $d = [];
                        $d['id'] = $response->ID;
                        $d['firstname'] = get_post_meta($response->ID,'firstname',true);
                        $d['lastname'] = get_post_meta($response->ID,'lastname',true);
                        $d['company'] = get_post_meta($response->ID,'company',true);
                        $d['phone'] = get_post_meta($response->ID,'phone',true);
                        $d['email'] = get_post_meta($response->ID,'email',true);
                        $d['message'] = get_post_meta($response->ID,'message',true);
                        $data[] = $d;
                    }

                    foreach ($contactlinks as $key => $url) {
                        $contactform = new ContactXpress($url['url']);
                        foreach ($data as $d) {
                            $contactform->fill($d);
                            $contactform->saveRecord($post->ID);
                        }
                    }

                    ?>
                    <script>window.location.assign(<?=("'".add_query_arg([
                        'cxp_action' => 'send_responses_success'
                    ])."'")?>)</script>
                    <?php
                }else{
                    ?>
                    <script>window.location.assign(<?=("'".add_query_arg([
                        'cxp_action' => 'send_responses_error_noresponses'
                    ])."'")?>)</script>
                    <?php
                }
            }else{
                ?>
                <script>window.location.assign(<?=("'".add_query_arg([
                    'cxp_action' => 'send_responses_error_nopages'
                ])."'")?>)</script>
                <?php
            }
        }
        break;
        case 'scan_domain_success':
        ?>
        <label style=" line-height: 28px; padding: 4px 12px; color: #00af00; font-weight: bold; "> Scan Success : Found <?=(count(get_post_meta($post->ID,'contact_links',true) ?: []))?></label>
        <?php break;
        case 'send_responses_success': ?>
        <label style=" line-height: 28px; padding: 4px 12px; color: #00af00; font-weight: bold; "> Send Success</label>
        <?php break;
        case 'send_responses_error_nopages': ?>
        <label style=" line-height: 28px; padding: 4px 12px; color: red; font-weight: bold; "> Send Failed. No Pages to submit. Please scan domain first.</label>
        <?php break;
        case 'send_responses_error_noresponses': ?>
        <label style=" line-height: 28px; padding: 4px 12px; color: red; font-weight: bold; "> Send Failed. No Responses to use. Please add responses first.</label>
        <?php break;
        default:
        # code...
        break;
    }

endif;
?>

<script>
jQuery(function($){

    $('.export-log').on('click',function(e){
        e.preventDefault();
        $.ajax({
            url: '<?=admin_url('admin-ajax.php')?>' ,
            data: {
                post : '<?=$_GET['post']?>',
                action : 'export_contact_grab_logs'
            },
            tryCount : 0,
            retryLimit : 5,
            timeout: 25000,
            type: 'POST',
            error: function(x, t, m) {

            },
            success: function(data){
                var retReq = jQuery.parseJSON(data);
                //$('#message').html(data);
                console.log(location.href = retReq.url);
                //alert(data);
                return;
            }
        });
    });
});
</script>
