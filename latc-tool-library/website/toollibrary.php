<?php

    $licenses = array (
            "http://www.opensource.org/licenses/bsd-license.php" => "BSD License",
            "http://www.apache.org/licenses/LICENSE-2.0" => "Apache License, Version 2.0",
            "http://www.gnu.org/licenses/gpl-3.0-standalone.html" => "GNU General Public License",
            "http://www.gnu.org/licenses/gpl.html" => "GNU General Public License",
            "http://www.gnu.org/licenses/#GPL" => "GNU General Public License"
        );

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



    function gettoolboxwithcategory($name) {
        global $licenses;
        if (($handle = fopen("toollibrary.csv", "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if ($data[0] == $name) {
                    ?>
                                <div class="tool">
                                        <div class="name"><a href="tool.php?name=<?php echo $name; ?>"><?php echo $data[2]; ?><img src="images/expand.png" style="float:right;" border="0" title="go to tool documentation"/></a></div>
					<div class="col1">Category: </div><div class="col2"><a href="categories.php#<?php echo str_replace(" ", "", strtolower(trim($data[9]))); ?>"><?php echo $data[9]; ?></a><?php if (trim($data[10]) != "") echo " / <a href=\"categories.php#".str_replace(" ", "", strtolower(trim($data[10])))."\">".$data[10]."</a>"; ?></div>
                                        <div class="col1">Author(s): </div><div class="col2"><?php echo $data[4]; ?>&nbsp;</div>
                                        <div class="col1">Website: </div><div class="col2"><a href="<?php echo $data[3]; ?>"><?php echo $data[3]; ?></a>&nbsp;</div>
                                        <div class="col1">License: </div><div class="col2"><a href="<?php echo $data[15]; ?>"><?php if (in_array($data[15], array_keys($licenses))) echo $licenses[$data[15]]; else echo $data[15]; ?></a>&nbsp;</div>
                                        <div class="col1">User Interface: </div><div class="col2"><?php echo $data[11]; ?>&nbsp;</div>
                                        <div class="clear"></div>
                                </div>
                    <?php
                }
            }
            fclose($handle);
        }

    }

   function gettoolbox($name) {
        if (($handle = fopen("toollibrary.csv", "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if ($data[0] == $name) {
                    ?>
				<div class="tool">
                                        <div class="name"><a href="tool.php?name=<?php echo $name; ?>"><?php echo $data[2]; ?><img src="images/expand.png" style="float:right;" border="0" title="go to tool documentation"/></a></div>
					<div class="col1">Author(s): </div><div class="col2"><?php echo $data[4]; ?>&nbsp;</div>
                                        <div class="col1">Website: </div><div class="col2"><a href="<?php echo $data[3]; ?>"><?php echo $data[3]; ?></a>&nbsp;</div>
					<div class="col1">License: </div><div class="col2"><a href="<?php echo $data[15]; ?>"><?php echo $data[15]; ?></a>&nbsp;</div>
                                        <div class="col1">User Interface: </div><div class="col2"><?php echo $data[11]; ?>&nbsp;</div>
                                        <div class="clear"></div>
				</div>
                    <?php
                }
            }
            fclose($handle);
        }

    }

    function getfulltoolbox($name) {
        global $licenses;
        if (($handle = fopen("toollibrary.csv", "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if ($data[0] == $name) {
                    ?>
                                <p><?php 
                                    $desc = str_replace("\n", "</p><p>", $data[6]);
                                    $desc = str_replace("<p></p>", "", $desc);
                                    echo $desc;
                                ?></p>
				<div class="tool">
					<div class="col1">Category: </div><div class="col2"><a href="categories.php#<?php echo str_replace(" ", "", strtolower(trim($data[9]))); ?>"><?php echo $data[9]; ?></a><?php if (trim($data[10]) != "") echo " / <a href=\"categories.php#".str_replace(" ", "", strtolower(trim($data[10])))."\">".$data[10]."</a>"; ?></div>
                                        <div class="col1">Website: </div><div class="col2"><a href="<?php echo $data[3]; ?>"><?php echo $data[3]; ?></a>&nbsp;</div>
					<div class="col1">Author(s): </div><div class="col2"><?php echo $data[4]; ?>&nbsp;</div>
                                        <div class="col1">Contact Email: </div><div class="col2"><?php echo $data[5]; ?>&nbsp;</div>
                                        <div class="col1">License: </div><div class="col2"><a href="<?php echo $data[15]; ?>"><?php if (in_array($data[15], array_keys($licenses))) echo $licenses[$data[15]]; else echo $data[15]; ?></a>&nbsp;</div>
                                        <div class="col1">User Interface: </div><div class="col2"><?php echo $data[11]; ?>&nbsp;</div>
                                        <div class="col1">Programming Language(s): </div><div class="col2"><?php echo $data[12]; ?>&nbsp;</div>
					<div class="col1">Online Manual: </div><div class="col2"><a href="<?php echo $data[13]; ?>"><?php echo $data[13]; ?></a>&nbsp;</div>
                                        <div class="col1">Mailing List: </div><div class="col2"><a href="<?php echo $data[14]; ?>"><?php echo $data[14]; ?></a>&nbsp;</div>
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
                                <?php 
                                    $docu = file_get_contents("tools/".$name.".html");
                                ?>
                                <p><?php echo $docu; ?></p>
        <?php
    }

?>