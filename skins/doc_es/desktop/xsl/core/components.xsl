<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="xml" encoding="UTF-8" omit-xml-declaration="yes" />
<xsl:strip-space elements="*" />

<xsl:variable name="config" select="/xml/configuration"/>
<xsl:variable name="content" select="/xml/content"/>
<xsl:variable name="context" select="/xml/context"/>
<xsl:variable name="appUrl"><!-- 
	 --><xsl:choose><!-- 
		 --><xsl:when test="$config/system/domain/@subdir != ''"><!-- 
			 --><xsl:value-of select="$config/system/domain" />/<xsl:value-of select="$config/system/domain/@subdir" /><!-- 
		 --></xsl:when><!-- 
		 --><xsl:otherwise><!-- 
			 --><xsl:value-of select="substring($config/system/domain, 0, string-length($config/system/domain))" /><!-- 
		 --></xsl:otherwise><!-- 
	 --></xsl:choose><!-- 
 --></xsl:variable>

<xsl:param name="htmlHeadExtra" />
<xsl:param name="skinpath" />
<xsl:param name="page" />
<xsl:param name="category_id" />

<xsl:param name="dinamicHead" />

<!-- ************** html head ************** -->
<xsl:template name="htmlHead">
	<meta charset="UTF-8" />
	<title><xsl:value-of select="$config/system/applicationID" /></title>
	<xsl:choose>
		<xsl:when test="$dinamicHead != ''">
			<xsl:copy-of select="$dinamicHead" />
		</xsl:when>
		<xsl:otherwise>
			<xsl:apply-templates select="$config/skin/header" mode="htmlhead"/>
			<xsl:copy-of select="$htmlHeadExtra" />
		</xsl:otherwise>
	</xsl:choose>
	<script type="text/javascript"><!-- 
		 -->var domain = "<xsl:value-of select="$config/system/domain" />";<!-- 
		 -->var skinpath = "<xsl:value-of select="$skinpath" />";<!-- 
	 --></script>
	<xsl:if test="/xml/configuration/client/browser/browser_working = 'ie'">
		<link rel="stylesheet" type="text/css" href="{$skinpath}/css/ie.css" />
		<script type="text/javascript" src="{$skinpath}/js/ie_scripts.js">&#xa0;</script>
	</xsl:if>
	<xsl:if test="/xml/configuration/client/browser/browser_working = 'moz' and /xml/configuration/client/browser/browser_number = '1.9'">
		<link rel="stylesheet" type="text/css" href="{$skinpath}/css/ie.css" />
	</xsl:if>
</xsl:template>
<!-- ************** html head ************** -->


<!-- ************** html head templates ************** -->
<xsl:template match="title" mode="htmlhead">
	<title><xsl:value-of select="."/></title>
</xsl:template>
<xsl:template match="css" mode="htmlhead">
	<xsl:variable name="cssfile"><xsl:apply-templates /></xsl:variable>
	<link rel="stylesheet" type="text/css" href="{$cssfile}">
		<xsl:apply-templates select="@*" mode="htmlhead" />
	</link>
</xsl:template>
<xsl:template match="skinpath">
	<xsl:value-of select="$skinpath" />
</xsl:template>
<xsl:template match="favicon" mode="htmlhead">
	<xsl:variable name="iconfile"><xsl:apply-templates /></xsl:variable>
	<link rel="shortcut icon" href="{$iconfile}" />
</xsl:template>
<xsl:template match="jquery" mode="htmlhead">
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js">&#xa0;</script>
</xsl:template>
<xsl:template match="style" mode="htmlhead">
	<xsl:copy-of select="." />
</xsl:template>
<xsl:template match="script" mode="htmlhead">
	<xsl:variable name="jsfile"><xsl:apply-templates /></xsl:variable>
	<script type="text/javascript" src="{$jsfile}">&#xa0;</script>
</xsl:template>
<xsl:template match="meta" mode="htmlhead">
	<xsl:copy-of select="." />
</xsl:template>
<xsl:template match="seccion_eplad" mode="htmlhead">
	<script type="text/javascript">var seccion_id = "<xsl:copy-of select="text()" />";</script>
</xsl:template>
<xsl:template match="@*" mode="htmlhead">
	<xsl:attribute name="{name()}"><xsl:value-of select="." /></xsl:attribute>
</xsl:template>
<!-- ************** html head templates ************** -->



<xsl:template name="fecha.con.dia">
<xsl:param name="fecha" />
<xsl:param name="dia" />
<xsl:param name="showTime">0</xsl:param>
<xsl:param name="showYear">0</xsl:param>

<xsl:variable name="diaNum" select="number(substring($fecha, 9, 2))" />
<xsl:variable name="mes" select="substring($fecha, 6, 2)" />
<xsl:variable name="anio" select="substring($fecha, 1, 4)" />
<xsl:variable name="hora" select="substring(substring-after($fecha, ' '),1,5)" />
<xsl:variable name="mesNombre">
<xsl:choose><!--
	--><xsl:when test="$mes=01">Enero</xsl:when><!--
	--><xsl:when test="$mes=02">Febrero</xsl:when><!--
	--><xsl:when test="$mes=03">Marzo</xsl:when><!--
	--><xsl:when test="$mes=04">Abril</xsl:when><!--
	--><xsl:when test="$mes=05">Mayo</xsl:when><!--
	--><xsl:when test="$mes=06">Junio</xsl:when><!--
	--><xsl:when test="$mes=07">Julio</xsl:when><!--
	--><xsl:when test="$mes=08">Agosto</xsl:when><!--
	--><xsl:when test="$mes=09">Septiembre</xsl:when><!--
	--><xsl:when test="$mes=10">Octubre</xsl:when><!--
	--><xsl:when test="$mes=11">Noviembre</xsl:when><!--
	--><xsl:when test="$mes=12">Diciembre</xsl:when><!--
--></xsl:choose><!--
--></xsl:variable>

<xsl:variable name="diaNombre">
<xsl:choose><!--
	--><xsl:when test="$dia='Sunday'">Domingo</xsl:when><!--
	--><xsl:when test="$dia='Monday'">Lunes</xsl:when><!--
	--><xsl:when test="$dia='Tuesday'">Martes</xsl:when><!--
	--><xsl:when test="$dia='Wednesday'">Miércoles</xsl:when><!--
	--><xsl:when test="$dia='Thursday'">Jueves</xsl:when><!--
	--><xsl:when test="$dia='Friday'">Viernes</xsl:when><!--
	--><xsl:when test="$dia='Saturday'">Sábado</xsl:when><!--
--></xsl:choose><!--
--></xsl:variable>
<xsl:value-of select="$diaNombre"/>&#xa0;<xsl:value-of select="$diaNum"/> de <xsl:value-of select="$mesNombre"/><!-- 
 --><xsl:if test="$showYear = 1">, <xsl:value-of select="$anio"/></xsl:if><!-- 
 --><xsl:if test="$showTime = 1"> | <xsl:value-of select="$hora" /> hs</xsl:if> <!-- 
 --></xsl:template>




<xsl:template name="fecha.formato.nota">
<xsl:param name="fecha" />
<xsl:variable name="dia" select="number(substring($fecha, 9, 2))" />
<xsl:variable name="mes" select="substring($fecha, 6, 2)" />
<xsl:variable name="anio" select="substring($fecha, 1, 4)" />
<xsl:variable name="mesNombre">
<xsl:choose><!--
	--><xsl:when test="$mes=01">Enero</xsl:when><!--
	--><xsl:when test="$mes=02">Febrero</xsl:when><!--
	--><xsl:when test="$mes=03">Marzo</xsl:when><!--
	--><xsl:when test="$mes=04">Abril</xsl:when><!--
	--><xsl:when test="$mes=05">Mayo</xsl:when><!--
	--><xsl:when test="$mes=06">Junio</xsl:when><!--
	--><xsl:when test="$mes=07">Julio</xsl:when><!--
	--><xsl:when test="$mes=08">Agosto</xsl:when><!--
	--><xsl:when test="$mes=09">Septiembre</xsl:when><!--
	--><xsl:when test="$mes=10">Octubre</xsl:when><!--
	--><xsl:when test="$mes=11">Noviembre</xsl:when><!--
	--><xsl:when test="$mes=12">Diciembre</xsl:when><!--
--></xsl:choose><!--
--></xsl:variable>
<xsl:value-of select="$dia"/> de <xsl:value-of select="$mesNombre"/>, <xsl:value-of select="$anio"/>
</xsl:template>


<xsl:template name="fecha.formato.comentario">
	<xsl:param name="fecha" />
	<xsl:variable name="dia" select="substring($fecha, 9, 2)" />
	<xsl:variable name="mes" select="substring($fecha, 6, 2)" />
	<xsl:variable name="anio" select="substring($fecha, 1, 4)" />
	<xsl:variable name="hora" select="substring(substring-after($fecha, ' '),1,5)" />

	<xsl:value-of select="$dia" />.<xsl:value-of select="$mes" />.<xsl:value-of select="$anio" /> | <xsl:value-of select="$hora" /> hs
</xsl:template>

<xsl:template name="fecha.formato.simple">
	<xsl:param name="fecha" />
	<xsl:variable name="dia" select="substring($fecha, 9, 2)" />
	<xsl:variable name="mes" select="substring($fecha, 6, 2)" />
	<xsl:variable name="anio" select="substring($fecha, 1, 4)" />
	<xsl:value-of select="$dia" />/<xsl:value-of select="$mes" />/<xsl:value-of select="$anio" />
</xsl:template>




<xsl:template name="normalize.text">
	<xsl:param name="nCaracteres"/>
	<xsl:param name="string"/>
	<xsl:variable name="caracteresReemplazar">!¡°¿?%$¥ø,;:()“”"'`´’_.ë&amp;/</xsl:variable>

	<xsl:variable name="lower">
		<xsl:value-of select="translate($string,'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz')"/>
	</xsl:variable>
	<xsl:variable name="result1a">
		<xsl:value-of select="translate($lower,'áéíóúñàèìòùç','aeiounaeiouc')"/>
	</xsl:variable>
	<xsl:variable name="result1">
		<xsl:value-of select="translate($result1a,'âêîôû','aeiou')"/>
	</xsl:variable>
	<xsl:variable name="result2">
		<xsl:value-of select="translate($result1,'ÁÉÍÓÚÑÀÈÌÒÙÇ','aeiounaeiouc')"/>
	</xsl:variable>
	<xsl:variable name="result3">
		<xsl:value-of select="translate($result2,$caracteresReemplazar,'')"/>
	</xsl:variable>
	<xsl:variable name="result4">
		<xsl:value-of select="translate($result3,'+','')"/>
	</xsl:variable>
	<xsl:variable name="result5">
	<xsl:choose>
		<xsl:when test="$nCaracteres != ''">
			<xsl:value-of select="substring(translate($result4,' ','-'),0,$nCaracteres)"/>
		</xsl:when>
		<xsl:otherwise>
			<xsl:value-of select="translate($result4,' ','-')"/>
		</xsl:otherwise>
	</xsl:choose>
	</xsl:variable>
	<xsl:variable name="result6">
		<xsl:call-template name="limpiar.guiones">
			<xsl:with-param name="string" select="$result5" />
		</xsl:call-template>
	</xsl:variable>
	<xsl:value-of select="$result6"/>
</xsl:template>

<xsl:template name="limpiar.guiones"><!--
	--><xsl:param name="string" /><!--
	--><xsl:variable name="cadena"><!--
	--><xsl:choose><!--
		--><xsl:when test="contains($string, '--')">
			<xsl:value-of select="substring-before($string, '--')" />-<!--
			--><xsl:call-template name="limpiar.guiones"><!--
				--><xsl:with-param name="string" select="substring-after($string, '--')" /><!--
			--></xsl:call-template><!--
		--></xsl:when><!--
		--><xsl:otherwise><!--
			--><xsl:value-of select="$string"/><!--
		--></xsl:otherwise><!--
	--></xsl:choose><!--
	--></xsl:variable><!--
	--><xsl:variable name="texto"><!--
		--><xsl:call-template name="limpiar.tags"><!--
			--><xsl:with-param name="cadena" select="$cadena" /><!--
		--></xsl:call-template><!--
	--></xsl:variable><!--
	--><xsl:value-of select="$texto" /><!--
--></xsl:template>

<xsl:template name="limpiar.tags">
	<xsl:param name="cadena" />
	<xsl:choose>
		<xsl:when test="contains($cadena, '&lt;')">
			<xsl:value-of select="substring-before($cadena, '&lt;')" />
			<xsl:call-template name="limpiar.tags">
				<xsl:with-param name="cadena" select="substring-after($cadena, '&gt;')" />
			</xsl:call-template>
		</xsl:when>
		<xsl:when test="contains($cadena, '&gt;')">
			<xsl:value-of select="substring-before($cadena, '&gt;')" />
			<xsl:call-template name="limpiar.tags">
				<xsl:with-param name="cadena" select="substring-after($cadena, '&gt;')" />
			</xsl:call-template>
		</xsl:when>
		<xsl:otherwise>
			<xsl:value-of select="$cadena" disable-output-escaping="yes" />
		</xsl:otherwise>
	</xsl:choose>
</xsl:template>




<xsl:template name="pagination">
	<!--Url para el paginado a la que se agrega el numero de pagina ej: /noticias/page/-->
	<xsl:param name="url" />
	<!--Pagina que estoy mostrando-->
	<xsl:param name="currentPage" />
	<!--Cantidad de items que se muestran por cada pagina-->
	<xsl:param name="display" />
	<!--Total de items-->
	<xsl:param name="total" />

	<xsl:variable name="pageurl"><!-- 
		 --><xsl:choose><!-- 
			 --><xsl:when test="$url=''"><!-- 
				 --><xsl:choose><!-- 
					 --><xsl:when test="$query != ''"><!-- 
				 		 --><xsl:value-of select="$adminroot"/><xsl:value-of select="$modName"/>/search/?page=<!-- 
				 	 --></xsl:when><!-- 
				 	 --><xsl:otherwise><!-- 
				 		 --><xsl:value-of select="$adminroot"/><xsl:value-of select="$modName"/>/list/?page=<!-- 
				 	 --></xsl:otherwise><!-- 
				  --></xsl:choose><!-- 
			  --></xsl:when><!-- 
			  --><xsl:otherwise><xsl:value-of select="$url" />?page=</xsl:otherwise><!-- 
		 --></xsl:choose><!--  
	 --></xsl:variable>
	<xsl:variable name="queryStr"><!-- 
		--><xsl:if test="$query != ''">&amp;q=<xsl:value-of select="$query" /></xsl:if><!-- 
		--><xsl:if test="$category_id != ''">&amp;categories=<xsl:value-of select="$category_id" /></xsl:if><!-- 
	 --></xsl:variable>
	<xsl:variable name="totalPages">
		<xsl:choose>
			<xsl:when test="ceiling($total div $display) != '' and number(ceiling($total div $display))">
				<xsl:value-of select="ceiling($total div $display)" />
			</xsl:when>
			<xsl:otherwise>1</xsl:otherwise>
		</xsl:choose>
	</xsl:variable>


	<xsl:choose>
	<xsl:when test="$currentPage &gt; $totalPages">
		
	</xsl:when>
	<xsl:otherwise>

		<xsl:if test="$totalPages != 1">
		<div class="pagination floatFix">
			<div class="right">
				<ul>
					<xsl:choose>
						<xsl:when test="$currentPage!='' and $currentPage != 1">
							<li><!-- 
								 --><a class="btn" href="{$pageurl}{$currentPage - 1}{$queryStr}">&lt;</a><!-- 
							 --></li>
						</xsl:when>
						<xsl:otherwise>
							<li><!-- 
								 --><span  class="btn gray">&lt;</span><!-- 
							 --></li>
						</xsl:otherwise>
					</xsl:choose>

					<xsl:choose>
						<xsl:when test="$currentPage!='' and $currentPage != 1">
							<li><!-- 
								 --><a class="btn" href="{$pageurl}1{$queryStr}">&lt;&lt;</a><!-- 
							 --></li>
						</xsl:when>
						<xsl:otherwise>
							<li><!-- 
								 --><span class="btn gray">&lt;&lt;</span><!-- 
							 --></li>
						</xsl:otherwise>
					</xsl:choose>

						<xsl:call-template name="link.paginas">
							<xsl:with-param name="pagina" select="$currentPage" />
							<xsl:with-param name="pageurl" select="$pageurl" />
							<xsl:with-param name="queryStr" select="$queryStr" />
							<xsl:with-param name="cantidad" select="$totalPages" />
							<xsl:with-param name="display" select="1" />
							<xsl:with-param name="limit1" select="$currentPage - 4" />
							<xsl:with-param name="limit2" select="$currentPage + 4" />
						</xsl:call-template>

					<xsl:choose>
						<xsl:when test="$currentPage!='' and $currentPage != $totalPages">
							<li><!-- 
								 --><a class="btn arrow" href="{$pageurl}{$totalPages}{$queryStr}">>></a><!-- 
							 --></li><!-- 
							 --><li><!-- 
								 --><a class="btn arrow" href="{$pageurl}{$currentPage + 1}{$queryStr}">></a> <!-- 
							 --></li>
						</xsl:when>
						<xsl:when test="$currentPage='' and $totalPages &gt; 1">
							<li><!-- 
								 --><a class="btn arrow" href="{$pageurl}{$totalPages}{$queryStr}">>></a><!--
							 --></li><!--
							 --><li><!--
								 --><a class="btn arrow" href="{$pageurl}2{$queryStr}">></a><!--
							 --></li>
						</xsl:when>
						<xsl:otherwise>
							<li><!-- 
								 --><span class="btn gray"><xsl:value-of select="queryStr"/>>></span><!--
							 --></li><!--
							 --><li><!--
								 --><span class="btn gray">></span><!--
							 --></li>
						</xsl:otherwise>
					</xsl:choose>
				</ul>
			</div>

			<div class="right total-info"><!-- 
				 -->
				 <xsl:variable name="totalshowing">
				 	<xsl:choose>
				 		<xsl:when test="$currentPage = $totalPages"><xsl:value-of select="$total" /></xsl:when>
				 		<xsl:otherwise><xsl:value-of select="$currentPage * $display" /></xsl:otherwise> 
				 	</xsl:choose>
				 </xsl:variable>
				 <xsl:value-of select="($display * ($currentPage - 1))+1"/> a <xsl:value-of select="$totalshowing" /> de <xsl:value-of select="$total" />
				 <!-- 
				 <xsl:choose>
					 <xsl:when test="$currentPage != ''"><xsl:value-of select="$currentPage" /></xsl:when>
					 <xsl:otherwise>1</xsl:otherwise>
				 </xsl:choose>
				 de <xsl:value-of select="$totalPages" />-->
			</div>
		</div>

		</xsl:if>



	</xsl:otherwise>
</xsl:choose>
</xsl:template>


<xsl:template name="link.paginas"><!-- 
	--><xsl:param name="pageurl" /><!-- 
	--><xsl:param name="queryStr" /><!-- 
	--><xsl:param name="pagina" /><!-- 
	--><xsl:param name="cantidad" /><!-- 
	--><xsl:param name="display" /><!-- 
	--><xsl:param name="limit1" /><!-- 
	--><xsl:param name="limit2" /><!-- 
	--><xsl:if test="$limit1 != ''"><!-- 
		 --><xsl:if test="($pagina - 1) &gt; 0 and ($pagina - 1) &gt;= $limit1"><!-- 
			 --><xsl:if test="$limit1 &gt; 0"><!-- 
				 --><li><!-- 
					 --><a class="btn" href="{$pageurl}{$limit1}{$queryStr}"><xsl:value-of select="$limit1" /></a><!-- 
				 --></li><!-- 
			 --></xsl:if><!-- 
			 --><xsl:call-template name="link.paginas">
					<xsl:with-param name="pagina" select="$pagina" />
					<xsl:with-param name="pageurl" select="$pageurl" />
					<xsl:with-param name="queryStr" select="$queryStr" />
					<xsl:with-param name="cantidad" select="$cantidad" />
					<xsl:with-param name="limit1" select="$limit1 + 1" />
				</xsl:call-template><!-- 
		 --></xsl:if><!-- 
	 --></xsl:if><!-- 
	 --><xsl:if test="$display!=''"><!-- 
		 --><li><!-- 
			 --><span class="btn selected"><xsl:value-of select="$pagina" /></span><!-- 
		 --></li><!-- 
	 --></xsl:if><!-- 
	 --><xsl:if test="$limit2 != ''"><!-- 
		 --><xsl:if test="($pagina + 1) &lt;= $limit2"><!-- 
			 --><xsl:if test="($pagina + 1) &lt;= $cantidad"><!-- 
				 --><li><!-- 
					 --><a class="btn" href="{$pageurl}{$pagina + 1}{$queryStr}"><xsl:value-of select="$pagina + 1" /></a><!-- 
				 --></li><!-- 
			 --></xsl:if><!-- 
			 --><xsl:call-template name="link.paginas">
					<xsl:with-param name="pagina" select="$pagina + 1" />
					<xsl:with-param name="pageurl" select="$pageurl" />
					<xsl:with-param name="queryStr" select="$queryStr" />
					<xsl:with-param name="cantidad" select="$cantidad" />
					<xsl:with-param name="limit2" select="$limit2" />
				</xsl:call-template><!-- 
		 --></xsl:if><!-- 
	 --></xsl:if>
</xsl:template>


<xsl:template name="cortarCadena">
<xsl:param name="cadena" />
<xsl:param name="cantidad" />
<xsl:if test="$cadena!=''">
	<xsl:choose>
		<xsl:when test="string-length($cadena)&gt;$cantidad">
			<xsl:call-template name="limpiar.tags">
				<xsl:with-param name="cadena" select="substring($cadena,0,$cantidad)" />
			</xsl:call-template> [...]
			<!--<xsl:value-of select="substring($cadena,0,$cantidad)" />...-->
		</xsl:when>
		<xsl:otherwise>
			<xsl:call-template name="limpiar.tags">
				<xsl:with-param name="cadena" select="$cadena" />
			</xsl:call-template>
		</xsl:otherwise>
	</xsl:choose>
</xsl:if>
</xsl:template>



<xsl:template name="limpiartags">
	<xsl:param name="cadena" />
	<xsl:choose>
		<xsl:when test="contains($cadena, '&lt;')">
			<xsl:value-of select="substring-before($cadena, '&lt;')" />
			<xsl:call-template name="limpiartags">
				<xsl:with-param name="cadena" select="substring-after($cadena, '&gt;')" />
			</xsl:call-template>
		</xsl:when>
		<xsl:when test="contains($cadena, '&gt;')">
			<xsl:value-of select="substring-before($cadena, '&gt;')" />
			<xsl:call-template name="limpiartags">
				<xsl:with-param name="cadena" select="substring-after($cadena, '&gt;')" />
			</xsl:call-template>
		</xsl:when>
		<xsl:otherwise>
			<xsl:value-of select="$cadena" disable-output-escaping="yes" />
		</xsl:otherwise>
	</xsl:choose>
</xsl:template>



</xsl:stylesheet>