<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="xml" omit-xml-declaration="yes" encoding="UTF-8" indent="yes" />


<xsl:template match="/xml">
	<xsl:text disable-output-escaping='yes'>&lt;!DOCTYPE html>
</xsl:text>
<html lang="es">
	<head>
		<xsl:call-template name="htmlHead" />
		<!-- <script>
			(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
			(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
			m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
			})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

			ga('create', 'UA-23022322-2', 'modion.org');
			ga('send', 'pageview');
		</script> -->
	</head>
	<body>
		<xsl:call-template name="header" />

		<section id="page">
			<section class="navpane">
				<xsl:call-template name="navigation" />
			</section>
			<section class="pageview">
				<xsl:call-template name="content" />
			</section>
		</section>

		<xsl:call-template name="footer" />
		
		<xsl:if test="$debug=1">
			<xsl:call-template name="debug" />
		</xsl:if>
	</body>
</html>
</xsl:template>





</xsl:stylesheet>