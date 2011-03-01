<?php

    function gettoollink($name) {
        if (($handle = fopen("toollibrary.csv", "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if ($data[0] == $name) {
                    return "<a href=\"tool.php?name=$name\">".$data[2]."</a>";
                }
            }
        }
        if ($name == "sigma") {
            return "<a href=\"tool.php?name=$name\">Sig.ma</a>";
        }
        if ($name == "sindice") {
            return "<a href=\"tool.php?name=$name\">Sindice</a>";
        }
    }

    function gettoolname($name) {
        if (($handle = fopen("toollibrary.csv", "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if ($data[0] == $name) {
                    return $data[2];
                }
            }
        }
        if ($name == "sigma") {
            return "Sig.ma";
        }
        if ($name == "sindice") {
            return "Sindice";
        }
    }
    
   function gettoolbox($name) {
        if (($handle = fopen("toollibrary.csv", "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if ($data[0] == $name) {
                    ?>
				<div class="tool">
					<div class="name"><?php echo $data[2]; ?><a href="tool.php?name=<?php echo $name; ?>"><img src="images/expand.png" style="float:right;" border="0" title="go to tool documentation"/></a></div>
					<div class="col1">Author(s): </div><div class="col2"><?php echo $data[4]; ?></div>
                                        <div class="col1">Website: </div><div class="col2"><a href="<?php echo $data[3]; ?>"><?php echo $data[3]; ?></a></div>
					<div class="col1">License: </div><div class="col2"><a href="<?php echo $data[15]; ?>"><?php echo $data[15]; ?></a></div>
                                        <div class="col1">User Interface: </div><div class="col2"><?php echo $data[11]; ?></div>
                                        <div class="clear"></div>
				</div>
                    <?php
                }
            }
            fclose($handle);
        }

    }

    function getfulltoolbox($name) {
        if (($handle = fopen("toollibrary.csv", "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if ($data[0] == $name) {
                    ?>
                                <p><?php echo $data[6]; ?></p>
				<div class="tool">
					<div class="col1">Category: </div><div class="col2"><?php echo $data[9]; ?><?php if (trim($data[10]) != "") echo " / ".$data[10]; ?></div>
                                        <div class="col1">Website: </div><div class="col2"><a href="<?php echo $data[3]; ?>"><?php echo $data[3]; ?></a></div>
					<div class="col1">Author(s): </div><div class="col2"><?php echo $data[4]; ?></div>
                                        <div class="col1">Contact Email: </div><div class="col2"><?php echo $data[5]; ?></div>
					<div class="col1">License: </div><div class="col2"><a href="<?php echo $data[15]; ?>"><?php echo $data[15]; ?></a></div>
                                        <div class="col1">User Interface: </div><div class="col2"><?php echo $data[11]; ?></div>
                                        <div class="col1">Programming Language(s): </div><div class="col2"><?php echo $data[12]; ?></div>
					<div class="col1">Online Manual: </div><div class="col2"><a href="<?php echo $data[13]; ?>"><?php echo $data[13]; ?></a></div>
                                        <div class="col1">Mailing List: </div><div class="col2"><a href="<?php echo $data[14]; ?>"><?php echo $data[14]; ?></a></div>
                                        <div class="clear"></div>
				</div>
                    <?php
                }
            }
            fclose($handle);
        }

    }

    function getdocumentation($name) {
        ?>
                                <h3 class="title">Documentation</h3>
                                <p><?php include("tools/".$name.".html"); ?></p>
        <?php
    }

?>