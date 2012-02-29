<?php if ($endpoints = $ConfigGraph->getItemEndpoints()): ?>
      <ul>
      <?php foreach ($endpoints as $endpointUri): ?>
  <li>
      <h3>
          <?php $uriTemplate = $ConfigGraph->get_first_literal($endpointUri, API.'uriTemplate') ; 
              echo $uriTemplate ;
               ?> <em class="type">Item Endpoint</em>
      </h3>
  
      <dl>
          <dt>URI Template:</dt>
          <dd>
              <?php echo $uriTemplate ?>
          </dd>
          <?php if ($exampleRequestPaths = $ConfigGraph->get_literal_triple_values($endpointUri, API.'exampleRequestPath')): ?>
        <dt>Example URIs:</dt>
        <dd>
            <ul>
            <?php foreach ($exampleRequestPaths as $exampleRequestPath): ?>
            <li>
                <a href="<?php echo $Request->getInstallSubDir().$exampleRequestPath ?>"><?php echo $exampleRequestPath ?></a>
                <?php include 'config.pathTemplateMatch.php' ?>
            </li>
            <?php endforeach ?>
            </ul>
        </dd>
        <?php endif ?>
        
        <?php if ($viewers=$ConfigGraph->get_resource_triple_values($endpointUri, API.'viewer')) :?>
  <dt>Viewers:</dt>
  <dd>
      <ul>
      <?php foreach ($viewers as $viewerUri): ?>
          <li>
              <h4><?php echo $ConfigGraph->get_first_literal($viewerUri, API.'name') ?></h4>
              <dl>
                  <?php if ($apiProperties = $ConfigGraph->get_resource_triple_values($viewerUri, API.'property')): ?>
                  <dt>Properties:</dt>
                  <dd>
                      <ul>
                  <?php foreach ($apiProperties as $propUri): ?>
                      <li><a href="<?php echo $propUri ?>"><?php echo $propUri ?></a></li>
                  <?php endforeach ?>                                        
                  </ul>
                  </dd>
                  <?php endif ?>

                  
              </dl>
          </li>
      <?php endforeach ?>
      </ul>
  </dd>
<?php endif?>

        
      </dl>
  
      
  </li>
<?php endforeach ?>
      </ul>
<?php endif ?>