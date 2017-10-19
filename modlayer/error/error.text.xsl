<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="xml" omit-xml-declaration="yes" encoding="UTF-8" indent="yes" />



<xsl:param name="referer" />
<xsl:param name="sqlquery" />
<xsl:param name="message" />



<xsl:template match="/xml">
<xsl:text disable-output-escaping='yes'>&lt;!DOCTYPE html>
</xsl:text>
<html lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
		<title>Error</title>
		<!-- <xsl:call-template name="htmlHead" /> -->
		<!-- <link rel="stylesheet" type="text/css" href="{$modPath}/desktop/css/error.css"/> -->
		<!-- <script type="text/javascript" src="{$modPath}/desktop/js/error.js">&#xa0;</script> -->
	</head>
	<body style="font-family:Helvetica,Arial,sans-serif;font-size:14px;">
		<div class="container rounded" style="width:80%;margin:auto;">
			<div class="header">
				<h2>Bam! Ouch!
					<small style="color:#999;">El sistema ha encontrado un problema del cual no pudo recuperarse</small>
				</h2>
			</div>

			<div style="background-color: #d9edf7;color: #3a87ad;padding: 8px 35px 8px 14px;margin-bottom:10px;text-shadow: 0 1px 0 rgba(255, 255, 255, 0.5);border: 1px solid #bce8f1;-webkit-border-radius: 4px;-moz-border-radius: 4px;border-radius: 4px;">
				<xsl:value-of select="$message" disable-output-escaping="yes"  />
			</div>

			<div class="row show-grid">

				<div class="header">
					<h4>
						Server:
						<small style="color:#999;font-size:12px;font-weight:normal;">
							Host: <span style="color:#000"><xsl:value-of select="content/server/HTTP_HOST" /></span>, 
							IP: <span style="color:#000"><xsl:value-of select="content/server/SERVER_ADDR" /></span> | 
							Remote IP: <span style="color:#000"><xsl:value-of select="content/server/REMOTE_ADDR" /></span> | 
							<xsl:value-of select="$fechaActual" /> - <xsl:value-of select="$horaActual" />
							<br/>
							UserAgent: <span style="color:#000"><xsl:value-of select="content/server/HTTP_USER_AGENT" /></span>
						</small>
					</h4>
					<!-- <xsl:for-each select="content/server/*">
						<span><xsl:value-of select="name()" />: <xsl:value-of select="." /></span>
					</xsl:for-each> -->
				</div>

				<span class="span6">
					<span class="padding">
						
						<div class="header">
							<h4>Detalles del error</h4>
						</div>
						<div id="accordion2" class="accordion">
							<xsl:for-each select="content/backtrace/resource">
								<div style="background:#eee;padding:10px;margin-bottom:10px;-webkit-border-radius: 4px;-moz-border-radius: 4px;border-radius: 4px;">
									
									<span style="color:#233245;display:block;padding-bottom:5px;font-weight:bold;"><!-- 
					                  --><xsl:value-of select="class" /><xsl:value-of select="type" /><xsl:value-of select="function" /><!-- 
					                  --><xsl:if test="not(class) and not(type) and not(function)"><!-- 
					                 	 -->Internal function error: View arguments<!-- 
									 --></xsl:if><!-- 
					                --></span>
								
									
                					<div style="font-size:11px;">
                							File: <xsl:value-of select="file" />
                							<br/>
											Line: <xsl:value-of select="line" />
											<xsl:if test="args/*">
												<br/>
												<xsl:for-each select="args/node0/*">
													<xsl:value-of select="name()" />: <xsl:apply-templates />
													<br/>
												</xsl:for-each>
											</xsl:if>
											<xsl:if test="not(class) and not(type) and not(function)">
												<xsl:call-template name="thisNodos">
													<xsl:with-param name="pi_nombreVar" select="'var'"/>
													<xsl:with-param name="pi_nodos" select="."/>
												</xsl:call-template>
											</xsl:if>
										</div>
									
								</div>
							</xsl:for-each>
						</div>

						
					</span>
				</span>

				<span class="span6 transparent">
					<span class="padding">
						<h4>
							Dispositivo:
							<small style="color:#999;"><xsl:value-of select="$config/client/name" /> (<xsl:value-of select="$config/client/os" /> <xsl:value-of select="$config/client/os_number" />)</small>
						</h4>
						<h4>
							Página solicitada:
							<small style="color:#999;"><xsl:value-of select="$page_url" /></small>
						</h4>
						<xsl:if test="$referer != ''">
							<h4>
								Referer:
								<small style="color:#999;"><xsl:value-of select="$referer" /></small>
							</h4>
						</xsl:if>
						<h4>
							Query:
							<small style="color:#999;"><xsl:value-of select="$sqlquery" />&#xa0;</small>
						</h4>
						<xsl:if test="content/user">
							<h4>
								Usuario:
								<small style="color:#999;">
									<xsl:value-of select="content/user/name" />&#xa0;<xsl:value-of select="content/user/lastname" /> (<xsl:value-of select="content/user/username" />)
								</small>
							</h4>
						</xsl:if>
						<xsl:if test="content/backtrace/post_params/*">
							<h4>
								Post params:
								<small style="color:#999;">
								<xsl:for-each select="content/backtrace/post_params/*">
									<xsl:value-of select="name()"/>: <xsl:apply-templates />
									<xsl:if test="position() != last()">, </xsl:if>
								</xsl:for-each>
								</small>
							</h4>
						</xsl:if>
						<xsl:if test="content/backtrace/get_params/*">
							<h4>
								Post params:
								<small style="color:#999;">
								<xsl:for-each select="content/backtrace/get_params/*">
									<xsl:value-of select="name()"/> => <xsl:apply-templates />
									<xsl:if test="position() != last()">, </xsl:if>
								</xsl:for-each>
								</small>
							</h4>
						</xsl:if>
					</span>
				</span>
			</div>
		</div>

		<!-- <xsl:call-template name="debug" /> -->
	</body>
</html>

</xsl:template>

<!-- <xsl:template match="*">
	<xsl:apply-templates />
</xsl:template> -->

<xsl:template name="thisNodos">
	<xsl:param name="pi_nombreVar"/>
	<xsl:param name="pi_nodos"/>
	<!-- <a name="{$pi_nombreVar}">Nombre de variable: <xsl:value-of select="$pi_nombreVar"/></a> -->
	<xsl:apply-templates select="$pi_nodos" mode="error"/>
</xsl:template>
  
<xsl:template match="*[*]" mode="error">
	<div class="codigo">
		<span class="tag">&lt;<b class="node"><xsl:value-of select="name()" /></b><xsl:apply-templates select="@*" mode="error"/>&gt;</span>
		<div class="bloque"><xsl:apply-templates mode="error"/></div>
		<span class="tag close">&lt;/<b class="node"><xsl:value-of select="name()" /></b>&gt;</span>
	</div>
</xsl:template>
    
    
<xsl:template match="*[not(*)]" mode="error">
	<div class="codigo">
		<b class="tag">&lt;<span><xsl:value-of select="name()" /></span></b>
		<xsl:apply-templates select="@*" mode="error"/>
		<b class="tag">/&gt;</b>
	</div>
</xsl:template>
  
<xsl:template match="*[not(*)][node()]" mode="error">
	<div class="codigo">
		<b class="tag">&lt;</b><b class="node"><xsl:value-of select="name()" /></b>
		<xsl:apply-templates select="@*" mode="error"/>
		<b class="tag">&gt;</b>
			<xsl:value-of select="node()"/>
		<b class="tag">&lt;/</b><b class="node"><xsl:value-of select="name()" /></b><b class="tag">&gt;</b>
	</div>
</xsl:template>
  
<xsl:template match="@*" mode="error">
	<span class="attname"><xsl:text> </xsl:text><xsl:value-of select="name()" />=</span><span class="att">"<xsl:value-of select="." />"</span>
</xsl:template>

</xsl:stylesheet>