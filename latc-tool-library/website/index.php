<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<title>Linked Data Publication & Consumption Tool Library</title>
<meta name="keywords" content="" />
<meta name="description" content="" />
<link href="default.css" rel="stylesheet" type="text/css" />
</head>
<body>

<?php

require_once ('toollibrary.php');

?>
<!-- start header -->
<div id="header">
	<div id="logo">
		<h1><a href="#">LATC Data Publication & Consumption Tools Library</a></h1>
	</div>
	<div id="menu">
		<ul>
			<li class="active"><a href="#">Home</a></li>
			<li><a href="categories.php">Tool Categories</a></li>
			<li><a href="example.php">Example</a></li>
			<li><a href="contact.html">About</a></li>
			<li><a href="http://latc-project.eu" title="LATC" style="background: none; align:right; margin-left: 169px; margin-top: 10px;padding: 7px;"><img src="images/logo-latc.png"/></a></li>
		</ul>
	</div>
</div>
<!-- end header -->
<!-- start page -->
<div id="page">
	<!-- start content -->
	<div id="content">
		<div class="box1">
<!--			<img src="images/sw_cube.gif" alt="" class="right" />-->
                    <!--
                    <p>
                    This website presents the Data Publication & Consumption Tools Library. It gives an overview
                    of the steps involved in the publication and consumption process of Linked Data.
                    It furthermore gives open source tool recommendations.</p>
                    <p>The Tools Library contains the following tools:
                            <ul>
                                    <li><?php echo gettoollink("neologism"); ?></li>
                                    <li><?php echo gettoollink("d2rserver"); ?></li>
                                    <li><?php echo gettoollink("googlerefine"); ?></li>
                                    <li><?php echo gettoollink("pubby"); ?></li>
                                    <li><?php echo gettoollink("any23"); ?></li>
                                    <li><?php echo gettoollink("ldspider"); ?></li>
                                    <li><?php echo gettoollink("sindice"); ?></li>
                                    <li><?php echo gettoollink("r2r"); ?></li>
                                    <li><?php echo gettoollink("silk"); ?></li>
                                    <li><?php echo gettoollink("sigma"); ?></li>
                                    <li><?php echo gettoollink("relfinder"); ?></li>
                                    <li><?php echo gettoollink("ontowiki"); ?></li>
                                    <li><?php echo gettoollink("sparqlviews"); ?></li>
                            </ul>
                    </p> -->
                    <!--			<img src="images/sw_cube.gif" alt="" class="right" />-->

                    <p>In order to support data set owners to publish their datasets as Linked Data
                    on the Web, as well as to support data consumers to access and integrate
                    Linked Data from the Web, the LATC project has compiled a library of open
                    source toolkits that cover all stages of the Linked Data publication
                    (modeling, linking, serving) and consumption process (discovery,
                    consolidation, application). By gathering high-quality open source tools in
                    the form of a consistent library, we hope to lower the barriers to
                    publishing Linked Data as well as to interacting with the Web of Data.</p>

                    <p>This website presents the initial version of the LATC Data Publication &
                    Consumption Tools Library. The website gives an overview of the main steps
                    of the Linked Data publication and consumption process and associates open
                    source tools from the library to each step in the process.</p>

		</div>
<!--
		<div class="post">
			<h1 class="title">…</h1>
			<div class="entry">
				<p>...</p>
			</div>
		</div>
-->

		<div class="post">
			<h2 class="title">Tools</h2>
			<div class="entry">
                                <?php gettoolboxwithcategory("neologism"); ?>
                                <?php gettoolboxwithcategory("d2rserver"); ?>
				<?php gettoolboxwithcategory("googlerefine"); ?>
                                <?php gettoolboxwithcategory("pubby"); ?>
                                <?php gettoolboxwithcategory("ldspider"); ?>
                                <?php gettoolboxwithcategory("sindice"); ?>
                                <?php gettoolboxwithcategory("any23"); ?>
                                <?php gettoolboxwithcategory("r2r"); ?>
                                <?php gettoolboxwithcategory("silk"); ?>
                                <?php gettoolboxwithcategory("sigma"); ?>
                                <?php gettoolboxwithcategory("relfinder"); ?>
                                <?php gettoolboxwithcategory("ontowiki"); ?>
                                <?php gettoolboxwithcategory("sparqlviews"); ?>
                        </div>
		</div>
	</div>
	<!-- end content -->
	<!-- start sidebar -->
	<div id="sidebar">
		<ul>
			<li id="search">
				<h2><b>Search</b></h2>
				<form method="get" action="">
					<fieldset>
					<input type="text" id="s" name="s" value="" />
					<input type="submit" id="x" value="Search" />
					</fieldset>
				</form>
			</li>
		</ul>
	</div>
	<!-- end sidebar -->
	<div style="clear: both;">&nbsp;</div>
</div>
<!-- end page -->
<!-- start footer -->
<div id="footer">
	<div class="wrap">
		<div id="fbox1" class="box2">
			<h2></h2>
			<p></p>
		</div>
		<div id="fbox2" class="box2">
			<h2></h2>
			<p></p>
		</div>
		<div id="fbox3" class="box2">
			<h2></h2>
			<p></p>
		</div>
	</div>
</div>
<!-- end footer -->
</body>
</html>
