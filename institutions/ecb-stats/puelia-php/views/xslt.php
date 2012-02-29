<?php

$this->addViewsMetadata();
$this->addFormattersMetadata();
$this->addExecutionMetadata();
$this->addTermBindingsMetadata();
$this->addSiteMetadata();

$simpleXml = $this->DataGraph->to_simple_xml($pageUri);
$dom = new DomDocument();
$dom->loadXML($simpleXml);
try{
    $xslt = new XSLTProcessor(); 
    $XSL = new DOMDocument();
    $XSL->load($styleSheetFile);
    $xslt->importStylesheet( $XSL );
    foreach($this->ConfigGraph->getAllVariableBindings() as $k => $v){
        $xslt->setParameter('',$k, $v['value']);
    }
#PRINT
    header("Content-Type: {$mimetype}");
    print $xslt->transformToXML($dom);
} catch (Exception $e) {
    throw new Exception("Error with XSLT transform. Try validating XSLT file ?");
}
// 
?>
