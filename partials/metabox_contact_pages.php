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
