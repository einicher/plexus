<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="2.0" 
                xmlns:html="http://www.w3.org/TR/REC-html40"
                xmlns:sitemap="http://www.sitemaps.org/schemas/sitemap/0.9"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  
  <xsl:output method="html" version="1.0" encoding="utf-8" indent="yes"/>
  
  <!-- Root template -->    
  <xsl:template match="/">
    <html>     
      <head>  
        <title>Plexus Sitemap</title>
		<style type="text/css">
		  <![CDATA[
			<!--
			body {
				font-family: arial, sans-serif;
				font-size: 0.8em;
				height:100%;
			}
			-->
		  ]]>
		</style>
      </head>
      <body>  
        <h1>Plexus Sitemap</h1>
        <xsl:call-template name="sitemapTable"/>       
      </body>
    </html>
  </xsl:template>     

  <!-- sitemapTable template -->  
  <xsl:template name="sitemapTable">
    <table>
	  <tr class="header">
	    <td>Sitemap URL</td>
		<td>Last modification date</td>
	  </tr>
	  <xsl:apply-templates select="sitemap:urlset/sitemap:url">
	    <xsl:sort select="sitemap:lastmod" order="descending"/>              
	  </xsl:apply-templates>
	</table>  
  </xsl:template>    
  
  <!-- sitemap:url template -->  
  <xsl:template match="sitemap:url">
    <tr>  
      <td>
        <xsl:variable name="sitemapURL"><xsl:value-of select="sitemap:loc"/></xsl:variable>  
        <a href="{$sitemapURL}"><xsl:value-of select="$sitemapURL"></xsl:value-of></a>
      </td>
      <td><xsl:value-of select="sitemap:lastmod"/></td>
    </tr>  
  </xsl:template>
 
</xsl:stylesheet>

