<dt>Variables</dt>
<dd>
    <?php if ($variables=$ConfigGraph->get_resource_triple_values($apiUri, API.'variable')): ?>
        

    <dl class="variables">
<?php foreach($variables as $variableUri): 
    $name = $ConfigGraph->get_first_literal($variableUri, API.'name');
    $value = $ConfigGraph->get_first_literal($variableUri, API.'value');
    ?>
    <dt>{<?php echo $name ?>}</dt>
    <dd><?php echo $value ?></dd>
<?php endforeach ?>
  </dl>
  
      <?php endif ?>
</dd>
