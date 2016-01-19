<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="xml" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" omit-xml-declaration="yes" encoding="UTF-8" indent="yes" />


<xsl:template match="/xml">
<html>
	<head>
		<title>Contacto</title>
	</head>
	<body>
		<!-- <textarea style="width:100%;height:500px;">
			<xsl:copy-of select="/" />
		</textarea> -->
		<div style="background:#f0f0f0;margin:10px 10px 15px;color:#000;font:bold 13px/17px Arial,sans-serif;padding:10px;">
			Contacto desde <xsl:value-of select="$config/system/applicationID" />
		</div>
		<div style="padding:10px;">
			<p style="margin:0 0 10px;font:normal 13px/17px Arial,sans-serif;color:#444;">
				<b>Nombre</b>: <xsl:value-of select="content/data/nombre" />
			</p>
			<p style="margin:0 0 10px;font:normal 13px/17px Arial,sans-serif;color:#444;">
				<b>Teléfono</b>: <xsl:value-of select="content/data/telefono" />
			</p>
			<p style="margin:0 0 10px;font:normal 13px/17px Arial,sans-serif;color:#444;">
				<b>Email</b>: <a href="mailto:{content/data/email}"><xsl:value-of select="content/data/email" /></a>
			</p>
			<p style="margin:0 0 10px;font:normal 13px/17px Arial,sans-serif;color:#444;">
				<b>Mensaje</b>: <br/>
				<xsl:value-of select="content/data/mensaje" disable-output-escaping="yes" />
			</p>
		</div>
		<div style="border-top:1px solid #e2e2e2;padding:10px;font:normal 11px/14px arial,sans-serif;color:#777;">
			<span style="float:right;color:#999">Powered by Modion*</span>
			<xsl:value-of select="$config/system/applicationID" /> | Enviado desde <a href="{$config/system/domain}" style="color:#000"><xsl:value-of select="$config/system/domain" /></a>
		</div>
	</body>
</html>
</xsl:template>





</xsl:stylesheet>