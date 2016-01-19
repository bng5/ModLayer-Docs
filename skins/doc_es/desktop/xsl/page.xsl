<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="xml" encoding="UTF-8" omit-xml-declaration="yes" />
<xsl:strip-space elements="*" />

<xsl:variable name="htmlHeadExtra">
</xsl:variable>
<xsl:variable name="cellsPerRow">3</xsl:variable>

<xsl:template name="content">
	<xsl:apply-templates select="content/*" />
</xsl:template>


</xsl:stylesheet>