<?php $ConfigGraph = $this->ConfigGraph ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <?php include 'header.html' ?>

	<title>Linked Data API Configuration</title>
</head>

<body>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.3/jquery.min.js"></script>    <script type="text/javascript" charset="utf-8">
        $(document).ready(
            function(){
                $("dl.api-properties").hide();
                $(".API>h2").click(function(){  $("dl.api-properties", $(this).parent()).toggle("slow");   }).css({"cursor":"pointer"});
		
		
            }
        );
    </script>
    <div class="container">
    <div class="header">Puelia: an implementation of the Linked Data API</div>
    <div class="contents">
        <h1>Linked Data API Configuration APIs:</h1>
        <?php foreach ($ConfigGraph->get_subjects_of_type(API.'API') as $apiUri): ?>
            <div class="API">
                <?php $ConfigGraph->resetApiAndEndpoint($apiUri) ?>
                <h2><?php echo $ConfigGraph->get_label($apiUri) ?> <em class="type">API</em></h2>
                <p><?php echo $ConfigGraph->get_description($apiUri) ?></p>
                
                <dl class="api-properties">
                    <dt>Base Uri</dt>
                    <dd><?php 
                    $base = $ConfigGraph->get_first_literal($apiUri, API.'base');
					if(empty($base)):?>
					<em>no base URI configured</em>
					<?php 
						$base = $Request->getInstallSubDir();
					endif;
                    ?>
                    <?php echo $base ?>
                    </dd>
                    <dt>SPARQL Endpoint</dt><dd>
                        <?php 
                        $sparqlEndpoint = $ConfigGraph->get_first_resource($apiUri, API.'sparqlEndpoint');
                        echo empty($sparqlEndpoint)? "<em>Warning: No SPARQL Endpoint configured. This API will not work.</em>" : '<a href="'.$sparqlEndpoint.'">'.$sparqlEndpoint.'</a>';
                        ?>  
                    </dd>
                    <dt>voiD Dataset</dt>
                    <dd><?php 
                    $voidDataset = $ConfigGraph->get_first_resource($apiUri, API.'dataset');
                    
                    ?>
                    <a href="<?php echo $voidDataset ?>"><?php echo $ConfigGraph->get_label($voidDataset) ?></a>
</dd>
                    
                    <dt>Vocabularies:</dt>
                    <dd>
                        <?php if ($vocabs = $ConfigGraph->get_resource_triple_values($apiUri, API.'vocabulary')): ?>
                        <ul>
                        <?php foreach ($vocabs as $vocabUri): ?>
                            <li><a href="<?php echo $vocabUri ?>"><?php echo $ConfigGraph->get_label($vocabUri) ?></a></li>
                        <?php endforeach ?>
                        </ul>
                        <?php endif ?>
                    </dd>
                    <dt>Default Viewer</dt>
                    <dd>
                        <a href="<?php
                          $defaultViewerUri = $ConfigGraph->get_first_resource($apiUri, API.'defaultViewer');
                          echo $defaultViewerUri;
                        ?>"><?php
                        echo $ConfigGraph->get_label($defaultViewerUri);
                        ?></a>
                    </dd>
                    <?php require 'config-html-components/variables.html.php' ?>

                    <dt>Item Endpoints:</dt>
                    <dd>
<?php require 'config-html-components/itemendpoint.html.php' ?>
                    </dd>
                    
                    
                      <dt>List Endpoints:</dt>
                      <dd>
<?php require 'config-html-components/listendpoint.html.php' ?>                        
                      </dd>   
<?php require 'config-html-components/formatters.html.php' ?>                
                                       
                </dl>
            </div>
        <?php endforeach ?>

<div>
    <h3>Property Chain Short Names</h3>
    <?php
        $propertyChainNames = array();

     foreach ($ConfigGraph->get_index() as $uri => $props): ?>
        <?php if ($label = $ConfigGraph->get_first_literal($uri, API.'label')): 
        if(isset($propertyChainNames[$label])){?>
            <div class="warning">
                <h4>Warning: Vocabulary clash</h4>
                <p>
                    <?php echo $label ?> for <?php echo $uri ?> is already defined as <?php echo $propertyChainNames[$label] ?>
                </p>
            </div>
        <?php }
        $propertyChainNames[$label] = $uri;
        ?>
        <?php endif ?>
    <?php endforeach;
    ksort($propertyChainNames);
     ?>
    <ul>
        <?php foreach ($propertyChainNames as $label => $uri): ?>
                    <li>
                        <a href="<?php echo $uri ?>"><strong><?php echo $label ?></strong> : <em><?php echo $uri ?></em></a>
                    </li>
        <?php endforeach ?>
    </ul>
</div>    

    </div>
    <?php include 'footer.html' ?>
    </div>
    
</body>
</html>
