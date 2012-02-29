<dl class="list-item">
    <?php foreach ($page->data->get_subject_properties($itemUri) as $p): ?>
        <dt><?php echo $page->data->get_label($p) ?></dt>
        <?php foreach ($page->data->get_subject_property_values($itemUri, $p) as $object): ?>
        <?php $langMarkup = (!empty($object['lang']))? ' lang="'.$object['lang'].'"' : '';?>    
            <dd<?php echo $langMarkup ?>>
            <?php switch($object['type']):
                case 'literal':?>
                    <?php echo $object['value'] ?>
                    <?php break ?>
                <?php case 'uri':?>
                <?php 
                    $obUri = $object['value']; 
                    $obLabel = $page->data->get_label($obUri);
                ?>                        
                    <?php if (isImg($obUri)): ?>
                        <img alt="image of <?php echo $obLabel ?>" src="<?php echo $obUri ?>">
                    <?php else: ?>
                    <a href="<?php echo $obUri ?>"><?php echo $obLabel ?></a>
                    <?php endif ?>
                    <?php break ?>
            <?php endswitch ?>
            </dd>
        <?php endforeach ?>
    <?php endforeach ?>
</dl>
<?php $renderedUris[]=$itemUri ?>
