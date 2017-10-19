<?xml version="1.0" encoding="UTF-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="xml" encoding="UTF-8" omit-xml-declaration="yes" />


<xsl:template name="debug">
	<link rel="stylesheet" href="{$device}/debug/debug.css" type="text/css" />
	<div class="debug">&#xa0;</div>
	<div class="techoDebug">
		<a href="#">Debug +</a>
	</div>
	<div class="xmlContentBack">&#xa0;</div>
	<div class="xmlContent">
		<xsl:call-template name="verNodos">
			<xsl:with-param name="pi_nodos" select="."/>
		</xsl:call-template>
	</div>
	<!-- <script type="text/javascript">
		require(['app/view/debug.xml'], function(deb){ deb.Init(); });
	</script> -->
</xsl:template>

<xsl:template name="verNodos">
	<xsl:param name="pi_nodos"/>
	<xsl:apply-templates select="$pi_nodos" mode="docs"/>
	
</xsl:template>
  
<xsl:template match="*[*]" mode="docs">
	<div class="codigo">
		<a href="#">
			<img src="{$device}/debug/imgs/closetree.gif" alt=""/>
			<img src="{$device}/debug/imgs/opentree.gif" alt="" style="display:none"/>
		</a> 
		<span class="tag">&lt;<b class="node"><xsl:value-of select="name()" /></b><xsl:apply-templates select="@*" mode="docs"/>&gt;</span>
		<div class="bloque"><xsl:apply-templates mode="docs"/></div>
		<span class="tag close">&lt;/<b class="node"><xsl:value-of select="name()" /></b>&gt;</span>
	</div>
</xsl:template>
    
    
<xsl:template match="*[not(*)]" mode="docs">
	<div class="codigo">
		<b class="tag">&lt;<span><xsl:value-of select="name()" /></span></b>
		<xsl:apply-templates select="@*" mode="docs"/>
		<b class="tag">/&gt;</b>
	</div>
</xsl:template>
  
<xsl:template match="*[not(*)][node()]" mode="docs">
	<div class="codigo">
		<b class="tag">&lt;</b><b class="node"><xsl:value-of select="name()" /></b>
		<xsl:apply-templates select="@*" mode="docs"/>
		<b class="tag">&gt;</b>
			<xsl:value-of select="node()"/>
		<b class="tag">&lt;/</b><b class="node"><xsl:value-of select="name()" /></b><b class="tag">&gt;</b>
	</div>
</xsl:template>
  
<xsl:template match="@*" mode="docs">
	<xsl:text> </xsl:text><span class="attname"><xsl:value-of select="name()" />=</span><span class="att">"<xsl:value-of select="." />"</span>
</xsl:template>

</xsl:stylesheet>