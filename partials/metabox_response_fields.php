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
