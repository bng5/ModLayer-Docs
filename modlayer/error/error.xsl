<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="xml" omit-xml-declaration="yes" encoding="UTF-8" indent="yes" />



<!-- <xsl:variable name="modName" select="$config/module/@name" /> -->

<xsl:param name="message" />

<xsl:param name="referer" />
<xsl:param name="sqlquery" />


<xsl:template match="/xml">
<xsl:text disable-output-escaping='yes'>&lt;!DOCTYPE html>
</xsl:text>
<html lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
		<title>Modion -> Error</title>
		<!-- <xsl:call-template name="htmlHead" /> -->
		
		<link rel="stylesheet" type="text/css" href="/modlayer/error/bootstrap.min.css"/>
		<link rel="stylesheet" type="text/css" href="/modlayer/error/error.css"/>
		<script type="text/javascript" src="/modlayer/error/jquery-1.7.1.min.js">&#xa0;</script>
		<script type="text/javascript" src="/modlayer/error/bootstrap.min.js">&#xa0;</script>
		<script type="text/javascript" src="/modlayer/error/error.js">&#xa0;</script>
	</head>
	<body style="overflow:auto;">
		<div class="container rounded">
			<div class="header">
				<h2>Bam! Ouch!
					<small>El sistema ha encontrado un problema del cual no pudo recuperarse</small>
				</h2>
			</div>

			<div class="alert alert-info">
				<xsl:value-of select="$message" disable-output-escaping="yes" />
			</div>

			<div class="row show-grid">
				<span class="span6">
					<span class="padding">
						
						<div class="header">
							<h4>Detalles del error</h4>
						</div>
						<div id="accordion2" class="accordion">
							<xsl:for-each select="content/backtrace/resource">
								<div class="accordion-group">
									<div class="accordion-heading">
										<a href="#collapse{position()}" data-parent="#accordion2" data-toggle="collapse" class="accordion-toggle"><!-- 
						                  --><xsl:value-of select="class" /><xsl:value-of select="type" /><xsl:value-of select="function" /><!-- 
						                  --><xsl:if test="not(class) and not(type) and not(function)"><!-- 
						                 	 -->Internal function error: View arguments<!-- 
										 --></xsl:if><!-- 
						                 --></a>
									</div>
									<div class="accordion-body collapse" id="collapse{position()}" style="height: 0px;">
                						<div class="accordion-inner">
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
												<xsl:call-template name="verNodos">
													<xsl:with-param name="pi_nombreVar" select="'var'"/>
													<xsl:with-param name="pi_nodos" select="."/>
												</xsl:call-template>
											</xsl:if>
										</div>
									</div>
								</div>
							</xsl:for-each>
						</div>

						
					</span>
				</span>

				<span class="span5 transparent">
					<span class="padding">
						<h4>
							Página solicitada:<br/>
							<small><xsl:value-of select="$page_url" /></small>
						</h4>
						<xsl:if test="$referer != ''">
							<h4>
								Referer:<br/>
								<small><xsl:value-of select="$referer" /></small>
							</h4>
						</xsl:if>
						<h4>
							Query:<br/>
							<small><xsl:value-of select="$sqlquery" /></small>
						</h4>
						<xsl:if test="content/user">
							<h4>
								Usuario:<br/>
								<small>
									<xsl:value-of select="content/user/name" />&#xa0;<xsl:value-of select="content/user/lastname" /> (<xsl:value-of select="content/user/username" />)
								</small>
							</h4>
						</xsl:if>
						<xsl:if test="content/backtrace/post_params/*">
							<h4>
								Post params:<br/>
								<small>
								<xsl:for-each select="content/backtrace/post_params/*">
									<xsl:value-of select="name()"/>: <xsl:apply-templates />
									<xsl:if test="position() != last()">, </xsl:if>
								</xsl:for-each>
								</small>
							</h4>
						</xsl:if>
						<xsl:if test="content/backtrace/get_params/*">
							<h4>
								Get params:<br/>
								<small>
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

<xsl:template match="*">
	<xsl:apply-templates />
</xsl:template>

</xsl:stylesheet>