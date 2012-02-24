<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet version="1.0"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns="http://www.w3.org/1999/xhtml"
                xmlns:lda="http://purl.org/NET/linked-data-api"
                >

	<xsl:output encoding="UTF-8" indent="yes" method="html" />

	<xsl:template match="/">
        <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
        <head>
        	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        	<title>Test Stylesheet</title>
        	<link rel="stylesheet" href="/css/puelia.css" type="text/css" media="screen" title="puelia main css" charset="utf-8"/>
        </head>

        <body>
            <h1>Test XML to HTML transform</h1>
            <dl>
                <dt>page URI</dt>
                <dd><xsl:value-of select="@lda:href"/></dd>
                <dt>parameter foo:</dt>
                <dd>
                    <xsl:value-of select="$foo"/>
                </dd>
            </dl>
            <xsl:apply-templates select="//lda:items"/>
        </body>
    </html>
        
	</xsl:template>
	
	<xsl:template match="//lda:items">
	    <h2>List</h2>
	    <ul>
	       <xsl:apply-templates select="lda:item"/>
	    </ul>
	</xsl:template>

	<xsl:template match="lda:item">
	    <li>
	       <a><xsl:attribute name="href"><xsl:apply-templates select="@lda:href"/></xsl:attribute><xsl:apply-templates select="@lda:href"/></a>
	    </li>
	</xsl:template>

	
</xsl:stylesheet>