<ol>
<?php
$renderedUris = array();
foreach($page->getItems() as $itemUri):
?>
    <li>
        <?php
         $topic = $itemUri;
         require 'puelia-item.php' 
         ?>
    </li>
<?php endforeach ?>
</ol>

<nav class="pager">
<ul>
  <?php
  foreach (array('first' => $page->getFirst(), 'prev' => $page->getPrev(), 'next'=> $page->getNext()) as $rel => $link) {
    if($link){?>
      <li><a href="<?php echo $link?>" rel="<?php echo $rel?>"><?php echo $rel?></a></li>
<?php
    }
  }
?>
</ul>
 </nav>
