<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="xml" encoding="UTF-8" omit-xml-declaration="yes" />

<xsl:param name="gracias" />

<xsl:variable name="htmlHeadExtra"></xsl:variable>

<xsl:template name="content">

	<!--Content-->
	<div class="page">

		<xsl:choose>
			<xsl:when test="$gracias = 1">
				<h2 class="upper">Su consulta ha sido enviada</h2>
				<p>Gracias por contactarse con nosotros. A la brevedad recibirá una respuesta a su inquietud.</p>
				<div style="border-top:1px solid #f0f0f0;padding:15px 0 0;text-align:center;">
					<a href="/" class="btn">Volver al inicio</a>
				</div>
			</xsl:when>
			<xsl:otherwise>
				<h2 class="upper">Información de Contacto</h2>
				<p>Complete los campos del siguiente formulario para realizar su consulta.</p>

				<form id="contacto" name="contacto" action="/contact/run/" method="post">
					<ul class="form">
						<li>
							<label>Nombre o Razón Social</label>
							<input type="text" placeholder="Nombre" name="nombre" value="" class="required"/>
						</li>
						<li>
							<label>E-mail</label>
							<input type="text" name="email" value="" class="required" placeholder="Email"/>
						</li>
						<li>
							<label>Teléfono de contacto</label>
							<input type="text" name="telefono" value="" class="required" placeholder="Telefono"/>
						</li>
						<li>
							<label>Consulta</label>
							<textarea name="mensaje" class="required" placeholder="Escriba su consulta">&#xa0;</textarea>
						</li>
						<li>
							<label>&#xa0;</label>
							<button type="submit" class="btn">Enviar Consulta</button>
						</li>
					</ul>
				</form>
			</xsl:otherwise>
		</xsl:choose>


	</div>
	<!--Content-->


</xsl:template>
</xsl:stylesheet>