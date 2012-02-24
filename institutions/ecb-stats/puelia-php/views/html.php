<?php
$endpointTitle = $this->ConfigGraph->get_label($this->ConfigGraph->getEndpointUri());
$endpointDescription = $this->ConfigGraph->get_description($this->ConfigGraph->getEndpointUri());
$resultList = $DataGraph->list_to_array($this->listUri);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <?php include 'header.html' ?>

	<title><?php echo $endpointTitle ?> Linked Data API  </title>
	
</head>

<body>

<div class="container">
<div class="header">Puelia: an implementation of the Linked Data API</div>
<div class="contents">
<h1><?php echo $endpointTitle ?></h1>
<p class="description">
    <?php echo $endpointDescription ?>
</p>
<ol class="results-list">
    <?php foreach ($resultList as $uri): ?>
        <li about="<?php echo $uri ?>">
            <h3>
                <?php echo $DataGraph->get_label($uri) ?>
            </h3>

            <dl>
                <?php foreach($DataGraph->get_subject_properties($uri) as $p): ?>
                <dt title="<?php echo $p ?>"><?php echo $DataGraph->get_label($p) ?></dt>
                <?php foreach ($DataGraph->get_subject_property_values($uri, $p) as $object): ?>
                    <dd property="<?php echo $p ?>">
                        <?php switch($object['type']) :
                         case "uri" : ?>
                            <a href="<?php echo $object['value']?> "><?php echo $DataGraph->get_label($object['value']) ?></a>
                            <?php break ;
                            case "literal" : ?>
                            <p>
                                <?php echo $object['value'] ?>
                            </p>
                        <?php endswitch ?>
                    </dd>
                    <?php endforeach ?>
                <?php endforeach ?>
            </dl>
        </li>
    <?php endforeach ?>
</ol>
<?php //echo $DataGraph->to_html() ?></div>
<?php include 'footer.html' ?>

</div>
</body>
</html>
