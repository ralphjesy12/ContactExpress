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
<a href="<?=add_query_arg([
    'cxp_action' => 'scan_domain'
    ])?>" class="button">Scan Domain</a>
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
                $contactform->formLookUp();
                if(count($contactform->urls)){
                    update_post_meta( $post->ID , 'contact_links' , $contactform->urls );
                }
                ?>
                <script>window.location.assign(<?=("'".add_query_arg([
                    'cxp_action' => 'scan_domain_success'
                ])."'")?>)</script>
                <?php
            }

            break;
            case 'scan_domain_success': ?>
            <label style=" line-height: 28px; padding: 4px 12px; color: #00af00; font-weight: bold; "> Scan Success</label>
            <?php break;
            default:
            # code...
            break;
        }

    endif;
    ?>
