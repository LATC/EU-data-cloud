<?php

if($matches = $ConfigGraph->pathTemplateMatches($uriTemplate, $exampleRequestPath)):?>
    <div class="info diagnostic">matches uriTemplate</div>
<?php else: ?>    
    <div class="warning">
        <strong><?php echo $exampleRequestPath ?></strong> does not match <strong><?php echo $uriTemplate ?></strong>
    </div>
<?php endif; ?>