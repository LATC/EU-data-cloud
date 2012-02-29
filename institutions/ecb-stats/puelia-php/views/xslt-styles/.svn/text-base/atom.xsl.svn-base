<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet version="1.0"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns="http://www.w3.org/2005/Atom"
                >

	<xsl:output encoding="UTF-8" indent="yes" method="xml" />

	<xsl:template match="/result">
    <feed xmlns="http://www.w3.org/2005/Atom">
      <title><xsl:value-of select="label"/></title>
      <xsl:apply-templates select="format/item"/>
      <link rel="self" type="application/atom+xml"><xsl:attribute name="href"><xsl:value-of select="@href"/></xsl:attribute></link>
      <id><xsl:value-of select="@href"/></id>
      <xsl:call-template name="updated"><xsl:with-param name="el" select="."></xsl:with-param></xsl:call-template> 
      <xsl:apply-templates select="items/item"/> 
   </feed>
        
	</xsl:template>
	
  <xsl:template match="format/item">
    <xsl:element name="link">
      <xsl:attribute name="href"><xsl:value-of select="@href"/></xsl:attribute>
      <xsl:attribute name="rel">alternate</xsl:attribute>
      <xsl:attribute name="type"><xsl:value-of select="format/label"/></xsl:attribute>
      </xsl:element>
  </xsl:template>

  <xsl:template match="items/item">
    <entry>
      <title><xsl:value-of select="label"/></title>
      <link><xsl:attribute name="href"><xsl:value-of select="@href"/></xsl:attribute></link>
      <id><xsl:value-of select="@href"/></id>
      <xsl:call-template name="updated"><xsl:with-param name="el" select="."></xsl:with-param></xsl:call-template>
      <author>
        <name><xsl:value-of select="creator/label"/></name>
      </author>
      <content type="application/xml">
        <item xmlns="http://purl.org/net/linked-data-api"><xsl:copy-of select="*"/></item>
      </content>
      <content type="text/plain">
        <xsl:apply-templates select="*" mode="propertyToText"/>
      </content>
	  </entry>
  </xsl:template>


  <xsl:template match="*" mode="propertyToText">
    <xsl:text>
    </xsl:text>
   <xsl:value-of select ="local-name()"/> :  <xsl:apply-templates select ="." mode="propertyValueToText"/>  
 </xsl:template>

  <xsl:template match="node()[@href]" mode="propertyValueToText">
     <xsl:value-of select="@href"/><xsl:text>
      </xsl:text>
  </xsl:template>

  
  <xsl:template match="text()" mode="propertyValueToText">
    <xsl:value-of select="."/><xsl:text>
     </xsl:text>
  </xsl:template>




  <xsl:template name="updated">
    <xsl:param name="el"/>
      <xsl:choose>
        <xsl:when test="$el/modified">
          <updated><xsl:value-of select="$el/modified"/><xsl:if test="not(contains('Z',$el/modified))">Z</xsl:if></updated>
        </xsl:when>
        <xsl:when test="$el/created">
          <updated><xsl:value-of select="$el/created"/><xsl:if test="not(contains('Z', $el/created))">Z</xsl:if></updated>
        </xsl:when>
        <xsl:when test="$el/date">
          <updated><xsl:value-of select="$el/date"/><xsl:if test="not(contains('Z', $el/date))">Z</xsl:if></updated>
        </xsl:when>
        <xsl:otherwise>
          <updated><xsl:value-of select="//modified[1]"/>Z</updated>
        </xsl:otherwise>
      </xsl:choose>
  </xsl:template>

	
</xsl:stylesheet>

