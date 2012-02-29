
<h2>
    <a href="<?php echo $topic->docUri ?>"><?php echo $topic->label ?></a>
</h2>

    <?php if ($topic->img): ?>
        <div class="depiction">        
            <img alt="<?php echo $topic->img()->label ?>" src="<?php echo $topic->img ?>" width="200">
        </div>
    <?php endif ?>

<p class="description">
    <?php echo $topic->description ?>
</p>

    <?php if ($topic->isMappable()): ?>
        <div class="map topic-map">
            <span class="lat" title="Latitiude"><?php echo $topic->latitude ?></span>
            <span class="long" title="Longitude"><?php echo $topic->longitude ?></span>
        </div>
    <?php endif ?>

<dl>
    <?php foreach ($topic->otherPropertyValues() as $p => $vals): ?>
        <dt title="<?php
        echo $p;
        ?>"><?php echo $page->data->get_label($p) ?></dt>
        <?php foreach ($vals as $object): ?>
        <?php $langMarkup = (!empty($object['lang']))? ' lang="'.$object['lang'].'"' : '';?>    
            <dd<?php echo $langMarkup ?>>
            <?php switch($object['type']):
                case 'literal':?>
                    <?php echo $object['value'] ?>
                    <?php break ?>
                <?php case 'uri':?>
                <?php case 'bnode':?>
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
