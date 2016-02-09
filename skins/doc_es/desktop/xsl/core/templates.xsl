<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:g="http://www.google.com/+1/button/" xmlns:fb="http://developers.facebook.com/">
<xsl:output method="xml" encoding="UTF-8" omit-xml-declaration="yes" />


<!-- Menu -->
<xsl:template name="navigation">
	<nav id="menu">
		<a href="#menu" class="menu">&#xa0;</a>
		<xsl:apply-templates select="$context/nav" mode="nav" />
	</nav>
</xsl:template>

<xsl:template match="section" mode="nav">
	<div class="block">
		<h2 style="text-transform:capitalize;"><xsl:value-of select="@label" /></h2>
		<ul>
			<xsl:apply-templates mode="nav"/>
		</ul>
	</div>
</xsl:template>

<xsl:template match="item" mode="nav">
	<li>
		<xsl:variable name="itemUrl">
			<xsl:call-template name="item.url" />
		</xsl:variable>
		<a href="{source}">
			<xsl:if test="$page_url = source">
				<xsl:attribute name="class">active</xsl:attribute>
			</xsl:if>
			<xsl:if test="target != ''">
				<xsl:attribute name="target"><xsl:value-of select="@target" /></xsl:attribute>
			</xsl:if>
			<xsl:apply-templates select="label" />
		</a>
	</li>
</xsl:template>

<xsl:template name="item.url">
	<xsl:value-of select="$config/system/domain" />/<xsl:value-of select="name(../../*)" />/<xsl:value-of select="."  />
</xsl:template>
<!-- Menu -->

<!-- Templates de content xml  -->
<xsl:template match="page">
	<xsl:apply-templates />
</xsl:template>

<xsl:template match="title[text()]">
	<h2>
		<xsl:apply-templates select="@*" mode="atts" />
		<xsl:apply-templates />
	</h2>
</xsl:template>

<xsl:template match="subtitle[text()]">
	<xsl:variable name="thisTxt">
		<xsl:call-template name="normalize.text">
			<xsl:with-param name="string" select="."/>
		</xsl:call-template>
	</xsl:variable>
	<h3 class="subtitle" id="{$thisTxt}">
		<xsl:apply-templates select="@*" mode="atts" />
		<xsl:apply-templates />
	</h3>
</xsl:template>

<xsl:template match="method[text()]">
	<xsl:variable name="thisTxt">
		<xsl:call-template name="normalize.text">
			<xsl:with-param name="string" select="."/>
		</xsl:call-template>
	</xsl:variable>
	<h2 class="method" id="{$thisTxt}">
		<xsl:apply-templates select="@*" mode="atts" />
		<xsl:apply-templates />
	</h2>
</xsl:template>

<xsl:template match="box">
	<p class="box mono">
		<xsl:apply-templates select="@*" mode="atts" />
		<xsl:apply-templates />
	</p>
</xsl:template>

<xsl:template match="message">
	<p class="message">
		<xsl:apply-templates select="@*" mode="atts" />
		<xsl:apply-templates />
	</p>
</xsl:template>

<xsl:template match="text">
	<p>
		<xsl:apply-templates select="@*" mode="atts" />
		<xsl:apply-templates />
	</p>
</xsl:template>

<xsl:template match="code">
	<pre class="prettyprint">
		<xsl:if test="@linenums">
			<xsl:attribute name="class">prettyprint linenums:<xsl:value-of select="@linenums" /></xsl:attribute>
		</xsl:if>
		<xsl:apply-templates mode="code" />
	</pre>
</xsl:template>

<xsl:template match="*" mode="code"><!-- 
 -->&lt;<xsl:value-of select="name()" /><!-- 
	 --><xsl:apply-templates select="@*" mode="code" /><!-- 
 --><xsl:if test="not(./text())">/</xsl:if><!-- 
 -->><!-- 
 --><xsl:apply-templates mode="code" /><!-- 
 --><xsl:if test="./text()"><!-- 
	-->&lt;/<xsl:value-of select="name()" />><!-- 
 --></xsl:if><!--  -->
</xsl:template>

<xsl:template match="comment()" mode="code"><!-- 
	 -->&lt;!-- <xsl:value-of select="." /> --><!--  -->
</xsl:template>

<xsl:template match="@*" mode="code"><!-- 
	 -->&#xa0;<xsl:value-of select="name()" />="<xsl:value-of select="." />"<!-- 
 --></xsl:template>


<xsl:template match="divider">
	<p class="divider">&#xa0;</p>
</xsl:template>

<xsl:template match="mono">
	<span class="mono">
		<xsl:apply-templates />
	</span>
</xsl:template>

<xsl:template match="arrow_right">
	<span class="arrow-right">&#xa0;</span>
</xsl:template>

<xsl:template match="arrow_left">
	<span class="arrow-left">&#xa0;</span>
</xsl:template>


<xsl:template match="list[@type='ordered']">
	<ol>
		<xsl:apply-templates select="@*" mode="atts" />
		<xsl:apply-templates />
	</ol>
</xsl:template>

<xsl:template match="list">
	<ul>
		<xsl:apply-templates select="@*" mode="atts" />
		<xsl:apply-templates />
	</ul>
</xsl:template>

<xsl:template match="item">
	<li>
		<xsl:apply-templates select="@*" mode="atts" />
		<xsl:apply-templates />
	</li>
</xsl:template>

<xsl:template match="url">
	<a>
		<xsl:apply-templates select="@*" mode="url" />
		<xsl:apply-templates />
	</a>
</xsl:template>

<xsl:template match="@target" mode="url">
	<xsl:attribute name="href"><xsl:value-of select="." /></xsl:attribute>
</xsl:template>

<xsl:template match="@open" mode="url">
	<xsl:choose>
		<xsl:when test=". = 'window'">
			<xsl:attribute name="target">_blank</xsl:attribute>
		</xsl:when>
	</xsl:choose>
</xsl:template>

<xsl:template match="parameters">
	<table class="parameters" cellpadding="0" cellspacing="0" border="0">
		<tr>
			<th width="40%">Nombre</th>
			<!-- <th width="10%">Tipo</th>
			<th width="10%">Requerido</th> -->
			<th width="60%">Descripción</th>
		</tr>
		<xsl:apply-templates mode="parameter" />	
	</table>
</xsl:template>

<xsl:template match="param" mode="parameter">
	<tr>
		<td>
			<span class="name"><xsl:apply-templates select="name" /></span>
			<span class="type"><b>Tipo: </b> <xsl:apply-templates select="type" /></span>
			<span class="required"><b>Requerido: </b> <xsl:apply-templates select="required" /></span>
		</td>
		<!-- <td class="type">
			<span><xsl:apply-templates select="type" /></span>
		</td>
		<td class="required">
			<span class="required"><xsl:apply-templates select="required" /></span>
		</td> -->
		<td class="desc">
			<xsl:apply-templates select="desc" />
		</td>
	</tr>
</xsl:template>


<xsl:template match="html">
	<xsl:apply-templates />
</xsl:template>


<!-- Templates de content xml  -->



<xsl:template name="share.box">
<div class="share">
	<span class="tw-btn">
		<a href="http://twitter.com/share" class="twitter-share-button" data-count="horizontal"  data-lang="es">Tweet</a> <!-- data-via="LivePassArg" -->
		<script type="text/javascript" src="http://platform.twitter.com/widgets.js">&#xa0;</script>
	</span>
	<span class="fb-btn">
		 <div id="fb-root">&#xa0;</div><!-- 
		 --><script type="text/javascript"><!-- 
			 -->(function(d, s, id) {<!-- 
					-->var js, fjs = d.getElementsByTagName(s)[0];<!-- 
					-->if (d.getElementById(id)) return;<!-- 
					-->js = d.createElement(s); js.id = id;<!-- 
					-->js.src = "//connect.facebook.net/es_LA/all.js#xfbml=1";<!-- 
					-->fjs.parentNode.insertBefore(js, fjs);<!-- 
				-->}<!-- 
				-->(document, 'script', 'facebook-jssdk'));<!-- 
			--></script>
		 <div class="fb-like" data-send="false" data-layout="button_count" data-width="110" data-show-faces="true" data-font="arial">&#xa0;</div>
		<!-- <a name="fb_share">&#xa0;</a>
		<script src="http://static.ak.fbcdn.net/connect.php/js/FB.Share" type="text/javascript"></script> -->
		<!-- <div id="fb-root">&#xa0;</div>
		<script src="http://connect.facebook.net/en_US/all.js#appId=174719735922838&amp;xfbml=1">&#xa0;</script>
		<fb:like send="false" layout="button_count" width="100" show_faces="false" font="lucida grande">&#xa0;</fb:like> -->
	</span>
	<span class="gp-btn">
		<!-- Place this tag where you want the +1 button to render -->
		<g:plusone size="medium">&#xa0;</g:plusone>
	</span>
	<!-- Place this render call where appropriate -->
	<script type="text/javascript">
	  window.___gcfg = {lang: 'es-419'};
	  (function() {
	    var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
	    po.src = 'https://apis.google.com/js/plusone.js';
	    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
	  })();
	</script>
</div>
</xsl:template>


<!--TEMPLATES DE HTML-->
<xsl:template match="p|P">
<!-- 	<p>
		<xsl:apply-templates select="@*" mode="atts" />
		<xsl:apply-templates />
		<xsl:if test="not(text())">&#xa0;</xsl:if>
	</p> -->
	<xsl:choose>
		<xsl:when test="not(@class = 'embed')">
			<p>
				<xsl:apply-templates select="@*" mode="atts" />
				<xsl:apply-templates />
			</p>
		</xsl:when>
		<xsl:otherwise>
			<xsl:apply-templates />
		</xsl:otherwise>
	</xsl:choose>
</xsl:template>

<xsl:template match="b|strong">
	<strong>
		<xsl:apply-templates select="@*" mode="atts" />
		<xsl:apply-templates />
		<xsl:if test="not(text())">&#xa0;</xsl:if>
	</strong>
</xsl:template>

<xsl:template match="i|em">
	<em>
		<xsl:apply-templates select="@*" mode="atts" />
		<xsl:apply-templates />
		<xsl:if test="not(text())">&#xa0;</xsl:if>
	</em>
</xsl:template>

<xsl:template match="span">
	<span>
		<xsl:apply-templates select="@*" mode="atts" />
		<xsl:apply-templates />
		<xsl:if test="not(text())">&#xa0;</xsl:if>
	</span>
</xsl:template>

<xsl:template match="small">
	<small>
		<xsl:apply-templates select="@*" mode="atts" />
		<xsl:apply-templates />
	</small>
</xsl:template>

<xsl:template match="a|A">
	<a>
		<xsl:if test="@target">
			<xsl:attribute name="class">external</xsl:attribute>
		</xsl:if>
		<xsl:apply-templates select="@*" mode="atts" />
		<xsl:apply-templates />
		<xsl:if test="not(text())">&#xa0;</xsl:if>
	</a>
</xsl:template>

<xsl:template match="img">
	<img>
		<xsl:choose>
			<xsl:when test="contains(@src, '#skinpath#')">
				<xsl:attribute name="src"><xsl:value-of select="$skinpath" /><xsl:value-of select="substring-after(@src, '#skinpath#')" /></xsl:attribute>
			</xsl:when>
			<xsl:otherwise>
				<xsl:attribute name="src"><xsl:value-of select="@src" /></xsl:attribute>
			</xsl:otherwise>
		</xsl:choose>
		<xsl:apply-templates select="@*" mode="atts" />
	</img>
</xsl:template>

<xsl:template match="br">
	<br/>
</xsl:template>

<xsl:template match="h1|h2|h3|h4|h5">
	<xsl:element name="{name()}">
		<xsl:if test="@class"><xsl:attribute name="class"><xsl:value-of select="@class" /></xsl:attribute></xsl:if>
		<xsl:if test="@width"><xsl:attribute name="width"><xsl:value-of select="@width" /></xsl:attribute></xsl:if>
		<xsl:if test="@height"><xsl:attribute name="height"><xsl:value-of select="@height" /></xsl:attribute></xsl:if>
		<xsl:if test="@onclick"><xsl:attribute name="onclick"><xsl:value-of select="@onclick" /></xsl:attribute></xsl:if>
		<xsl:apply-templates />
	</xsl:element>
</xsl:template>


<xsl:template match="iframe">
	<iframe>
		<xsl:apply-templates select="@*" mode="atts" />
		<xsl:apply-templates />&#xa0;
	</iframe>
	<!-- <xsl:copy-of select="." /> -->
</xsl:template>

<xsl:template match="div">
	<div>
		<xsl:apply-templates select="@*" mode="atts" />
		<xsl:apply-templates />
	</div>
</xsl:template>

<xsl:template match="ul">
	<ul>
		<xsl:apply-templates select="@*" mode="atts" />
		<xsl:apply-templates />
	</ul>
</xsl:template>

<xsl:template match="ol">
	<ol>
		<xsl:apply-templates select="@*" mode="atts" />
		<xsl:apply-templates />
	</ol>
</xsl:template>

<xsl:template match="li">
	<li>
		<xsl:apply-templates select="@*" mode="atts" />
		<xsl:apply-templates />
	</li>
</xsl:template>

<xsl:template match="blockquote">
	<blockquote>
		<xsl:apply-templates select="@*" mode="atts" />
		<xsl:apply-templates />
	</blockquote>
</xsl:template>


<xsl:template match="object">
	<xsl:copy-of select="." />
</xsl:template>

<xsl:template match="embed">
	<xsl:copy-of select="." />
</xsl:template>

<xsl:template match="@*" mode="atts">
	<xsl:attribute name="{name()}"><xsl:value-of select="." /></xsl:attribute>
</xsl:template>

<xsl:template match="text()">
	<xsl:value-of select="." disable-output-escaping="yes" />
</xsl:template>

<xsl:template match="table">
	<table>
		<xsl:apply-templates select="@*" mode="atts" />
		<xsl:apply-templates />
	</table>
</xsl:template>

<xsl:template match="tr">
	<tr>
		<xsl:apply-templates select="@*" mode="atts" />
		<xsl:apply-templates />
	</tr>
</xsl:template>

<xsl:template match="td">
	<td>
		<xsl:apply-templates select="@*" mode="atts" />
		<xsl:apply-templates />
	</td>
</xsl:template>

<xsl:template match="th">
	<th>
		<xsl:apply-templates select="@*" mode="atts" />
		<xsl:apply-templates />
	</th>
</xsl:template>

<!--TEMPLATES DE HTML-->


</xsl:stylesheet>