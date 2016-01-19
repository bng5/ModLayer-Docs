<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="xml" encoding="UTF-8" omit-xml-declaration="yes" />
<xsl:param name="error" />

<xsl:variable name="htmlHeadExtra"></xsl:variable>

<xsl:template name="content">
	<div class="page">
		<xsl:choose>
			<xsl:when test="$error = '404'">
				<h1>404 - Página no encontrada</h1>
				<p>La url solicitada no pudo ser reconocida por el sistema</p>
			</xsl:when>
			<xsl:otherwise>
				<h1>Se ha producido un Error</h1>
				<p>El sistema ha encontrado un error interno. Hemos generado un ticket para solucionarlo.</p>
			</xsl:otherwise>
		</xsl:choose>
	</div>
</xsl:template>


</xsl:stylesheet>