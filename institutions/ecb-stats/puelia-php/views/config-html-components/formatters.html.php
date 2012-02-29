
<dt>Formatters</dt>
<dd>
    <?php 
    if ($formatters = $ConfigGraph->getFormatters()):?>
    <ul class="formatters">
    <?php
    foreach ($formatters as $formatterUri): ?>
    <?php
    $formatterType = $ConfigGraph->get_first_resource($formatterUri, RDF_TYPE);
    ?>
        <li>
            <h3>
                <span class="api-name"><?php echo $ConfigGraph->get_first_literal($formatterUri, API.'name') ?></span>
                
                <span class="rdf-type"><?php echo $ConfigGraph->get_label($formatterType) ?></span>
                
                </h3>
            <dl>
            <dt title="this can be used in the `_format` query parameter">name</dt>
            <dd><?php echo $ConfigGraph->get_first_literal($formatterUri, API.'name') ?></dd>
            <dt>mimetype</dt>
            <dd>
                <?php echo $ConfigGraph->get_first_literal($formatterUri, API.'mimeType') ?>
            </dd>
            <?php if ($ConfigGraph->has_resource_triple($formatterUri, RDF_TYPE, API.'XsltFormatter')): ?>
            <dt>Stylesheet</dt>
            <dd>
                <?php echo $ConfigGraph->get_first_literal($formatterUri, API.'stylesheet') ?>
            </dd>
            <?php endif ?>
            </dl>
        </li>
    <?php endforeach ?>
    </ul>
    <?php endif ?>
</dd>
