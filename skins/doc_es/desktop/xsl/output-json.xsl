<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="text" encoding="utf-8"/>
	<xsl:strip-space elements="*" />

	<xsl:template match="/xml">
		<!-- <xsl:copy-of select="." /> -->
		<xsl:apply-templates select="/xml/content/*" mode="json"/>
	</xsl:template>

	<xsl:template match="node()" mode="json">
		<xsl:text>{</xsl:text>
		<xsl:apply-templates select="." mode="detect" />
		<xsl:text>}</xsl:text>
	</xsl:template>

	<xsl:template match="*" mode="detect">
		<xsl:choose>
			<xsl:when test="parent::*/@data-type = 'list' and count(parent::*/*) = 1">
				<xsl:text>"</xsl:text><xsl:value-of select="name()"/><xsl:text>" : [</xsl:text>
				<xsl:apply-templates select="." mode="obj-content" /><xsl:text>]</xsl:text> 
			</xsl:when>
			<xsl:when test="name(preceding-sibling::*[1]) = name(current()) and name(following-sibling::*[1]) != name(current())">
				<xsl:apply-templates select="." mode="obj-content" />
				<xsl:text>]</xsl:text>
				<xsl:if test="count(following-sibling::*[name() != name(current())]) &gt; 0">, </xsl:if>
			</xsl:when>
			<xsl:when test="name(preceding-sibling::*[1]) = name(current())">
				<xsl:apply-templates select="." mode="obj-content" />
				<xsl:if test="name(following-sibling::*) = name(current())">, </xsl:if>
			</xsl:when>
			<xsl:when test="following-sibling::*[1][name() = name(current())]">
				<xsl:text>"</xsl:text><xsl:value-of select="name()"/><xsl:text>" : [</xsl:text>
				<xsl:apply-templates select="." mode="obj-content" /><xsl:text>, </xsl:text> 
			</xsl:when>
			
			<xsl:when test="count(./child::*) > 0 or count(@*) > 0">
				<xsl:text>"</xsl:text><xsl:value-of select="name()"/>" : <xsl:apply-templates select="." mode="obj-content" />
				<xsl:if test="count(following-sibling::*) &gt; 0">, </xsl:if>
			</xsl:when>
			<xsl:when test="count(./child::*) = 0">
				<xsl:text>"</xsl:text><xsl:value-of select="name()"/>" : "<xsl:apply-templates select="." mode="cleartext"/><xsl:text>"</xsl:text>
				<xsl:if test="count(following-sibling::*) &gt; 0">, </xsl:if>
			</xsl:when>
		</xsl:choose>
	</xsl:template>

	<xsl:template match="*" mode="obj-content">
		<xsl:text>{</xsl:text>
			<xsl:apply-templates select="@*" mode="attr" />
			<xsl:if test="count(@*) &gt; 0 and (count(child::*) &gt; 0 or text())">, </xsl:if>
			<xsl:apply-templates select="./*" mode="detect" />
			<xsl:if test="count(child::*) = 0 and text() and not(@*)">
				<xsl:text>"</xsl:text><xsl:value-of select="name()"/>" : "<xsl:apply-templates select="." mode="cleartext"/><xsl:text>"</xsl:text>
			</xsl:if>
			<xsl:if test="count(child::*) = 0 and text() and @*">
				<xsl:text>"text" : "</xsl:text><xsl:apply-templates select="." mode="cleartext"/><xsl:text>"</xsl:text>
			</xsl:if>
		<xsl:text>}</xsl:text>
		<xsl:if test="position() &lt; last()">, </xsl:if>
	</xsl:template>
	
	<xsl:template match="@*" mode="attr">
		<xsl:text>"</xsl:text><xsl:value-of select="name()"/>" : "<xsl:call-template name="escape.quotes"><xsl:with-param name="string" select="."/></xsl:call-template><xsl:text>"</xsl:text>
		<xsl:if test="position() &lt; last()">,</xsl:if>
	</xsl:template>

	<xsl:template match="node/@TEXT | text()" name="removeBreaks" mode="cleartext">
		<xsl:param name="pText" select="normalize-space(.)"/>
		<xsl:choose>
			<xsl:when test="not(contains($pText, '&#xA;'))">
				<xsl:call-template name="escape.quotes">
					<xsl:with-param name="string" select="$pText"/>
				</xsl:call-template>
				<!-- <xsl:copy-of select=""/> -->
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="concat(substring-before($pText, '&#xD;&#xA;'), ' ')"/>
				<xsl:call-template name="removeBreaks">
					<xsl:with-param name="pText" select="substring-after($pText, '&#xD;&#xA;')"/>
				</xsl:call-template>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	
	<xsl:template name="escape.quotes"><!--
	--><xsl:param name="string" /><!--
	--><xsl:choose><!--
		--><xsl:when test="contains($string, '&quot;')"><!--
			--><xsl:value-of select="substring-before($string, '&quot;')" />\"<!--
			--><xsl:call-template name="escape.quotes"><!--
				--><xsl:with-param name="string" select="substring-after($string, '&quot;')" /><!--
			--></xsl:call-template><!--
		--></xsl:when><!--
		--><xsl:when test="contains($string, '&#9;')"><!--
			--><xsl:value-of select="substring-before($string, '&#9;')" />\"<!--
			--><xsl:call-template name="escape.quotes"><!--
				--><xsl:with-param name="string" select="substring-after($string, '&#9;')" /><!--
			--></xsl:call-template><!--
		--></xsl:when><!--
		--><xsl:otherwise><!--
			--><xsl:call-template name="replace.newline"><!--
				--><xsl:with-param name="string" select="$string" /><!--
			--></xsl:call-template><!--
			<xsl:value-of select="$string" disable-output-escaping="yes" />
		--></xsl:otherwise><!--
	--></xsl:choose><!--
--></xsl:template>

<xsl:template name="replace.newline"><!--
	--><xsl:param name="string" /><!--
	--><xsl:choose><!--
		--><xsl:when test="contains($string, '&#xa;')"><!--
			--><xsl:value-of select="substring-before($string, '&#xa;')" /><!--
			--><xsl:call-template name="replace.newline"><!--
				--><xsl:with-param name="string" select="substring-after($string, '&#xa;')" /><!--
			--></xsl:call-template><!--
		--></xsl:when><!--
		--><xsl:otherwise><!--
			--><xsl:value-of select="normalize-space($string)" disable-output-escaping="yes" /><!--
		--></xsl:otherwise><!--
	--></xsl:choose><!--
--></xsl:template>
</xsl:stylesheet>