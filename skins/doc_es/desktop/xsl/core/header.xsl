<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="xml" encoding="UTF-8" omit-xml-declaration="yes" />

<xsl:param name="query" />

<xsl:template name="header">

	<header id="top">

			<a href="/" class="logo">
				<img src="{$skinpath}/imgs/logo.svg" alt="{$config/system/applicationID}" />
			</a>

			<div class="version">
				<xsl:if test="count($context/versions/version) &gt; 1">
					<xsl:attribute name="class">version toggle</xsl:attribute>
				</xsl:if>
				<span class="trigger">Version <xsl:value-of select="$context/nav/@version" /></span>
				<xsl:if test="count($context/versions/version) &gt; 1">
					<ul>
						<xsl:for-each select="$context/versions/version">
							<li>
								<a href="/version/?v={.}"><xsl:value-of select="." /></a>
							</li>
						</xsl:for-each>
					</ul>	
				</xsl:if>
			</div>

			<h3><xsl:value-of select="$config/system/applicationID" /></h3>


			<!-- <section class="language">
				<a href="/lang/br">BR</a>
				<a href="/lang/en">IN</a>
				<a class="active" href="/lang/es">ES</a>
				<span>
					<xsl:call-template name="fecha.con.dia">
						<xsl:with-param name="fecha" select="$fechaActual" />
						<xsl:with-param name="dia" select="$diaActual" />
					</xsl:call-template>
				</span>
			</section> -->
		

		<!-- <nav>
			<xsl:apply-templates select="context/menu" mode="nav" />
		</nav> -->
	</header>
</xsl:template>





</xsl:stylesheet>