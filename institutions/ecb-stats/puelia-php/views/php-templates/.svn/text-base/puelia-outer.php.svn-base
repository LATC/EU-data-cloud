<?php 
    $this->addViewsMetadata();
    $this->addFormattersMetadata();
    $this->addExecutionMetadata();
    $this->addTermBindingsMetadata();
    require 'page.php'; 
    $page = new PueliaPage($this->pageUri, $this->DataGraph, $this->ConfigGraph, $Request);
?><!DOCTYPE html>

<html lang="en">

<head class="html5reset-bare-bones">
    <base href="<?php echo $page->base ?>">
	<meta charset="utf-8">
<link rel="shortcut icon" href="/imgs/favicon.png" type="image/png">
	<!--[if IE]><![endif]-->
	
	<title><?php echo $page->getTitle()?>: Linked Data API</title>
	
	<meta name="description" content="<?php echo $page->getDescription() ?>">
	<!--[if lt IE 9]>
			<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
		<![endif]-->
		
		<meta name="viewport" content="width=device-width; initial-scale=1"/>
		<!-- Add "maximum-scale=1" to fix the weird iOS auto-zoom bug on orientation changes. -->
		<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.3/jquery.min.js"></script>
    <link rel="stylesheet" media="all" href="css/puelia.css">
	
</head>

<body>

<div>

	<header>
	
		<div class="site_name"><a href="<?php echo $page->base ?>"><?php echo $page->getTitle() ?></a></div>
		<div class="api-description">
		  <?php echo $page->getDescription() ?>
		</div>
		
		<nav>
		    <ul>
    		    <?php foreach ($page->getEndpointLinks() as $href => $label): ?>
    				<li><a href="<?php echo $href ?>"><?php echo $label ?></a></li>		      
    		    <?php endforeach ?>
			</ul>
		
		</nav>
	
	</header>

<?php
   		if(!isset($innerTemplate)) $innerTemplate = 'puelia-inner.php';
		require $innerTemplate 
    ?>


	
	<footer>
		 <nav class="formats">	
      <h3>Formats</h3>
			<ul>
				<?php foreach($page->getFormatLinksAndLabels() as $link => $label):?>
				<li><a href="<?php echo $link?>"><?php echo $label?></a></li>
				<?php endforeach ?>
			</ul>
		</nav>
    <nav class="viewers">
      <h3>Viewers</h3>
			<ul>
			<?php foreach($page->getViewerLinksAndLabels() as $link => $label):?>
				<li>
					<a href="<?php echo $link ?>"><?php echo $label ?></a>
				</li>
			<?php endforeach ?>
			</ul>

		</nav>
			
	   <p>Powered by <a href="http://code.google.com/p/puelia-php/">Puelia</a>.</p>
	   <p><a href="<?php echo $page->apiUri ?>">About This API</a></p>
	   <p>Data sourced from: <a href="<?php echo $page->datasetUri ?>"><?php echo $page->datasetName ?></a></p>
	</footer>

</div>

</body>
</html>
