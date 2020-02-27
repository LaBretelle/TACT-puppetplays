<?xml version="1.0" encoding="UTF-8" ?>

<xsl:stylesheet version="1.0"
  xmlns="http://www.tei-c.org/ns/1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:xs="http://www.w3.org/2001/XMLSchema"
  exclude-result-prefixes="#default xs xsl">
  <xsl:output method="xml" encoding="UTF-8" indent="yes"/>

  <xsl:template match="xml">
    <TEI xmlns="http://www.tei-c.org/ns/1.0">
      <xsl:apply-templates select="tact_metadatas"/>
      <facsimile>
        <surface >
          <xsl:attribute name="xml:id">
            <xsl:value-of select="//tact_media_url"/>
          </xsl:attribute>
        </surface>
      </facsimile>
      <text>
        <body>
          <xsl:apply-templates select="body"/>
        </body>
      </text>
    </TEI>
  </xsl:template>

  <xsl:template match="tact_metadatas">
      <teiHeader>
        <fileDesc>
          <titleStmt>
            <title>
              Transcription de <xsl:value-of select="//tact_media_name"/>
            </title>
            <xsl:apply-templates select="//tact_media_contributor"/>
          </titleStmt>
          <editionStmt>
            <p>
              Transcription effectuée sur la plateforme TACT: <xsl:value-of select="//tact_platform_url"/>
              Etat de la fiche sur TACT : <xsl:value-of select="//tact_media_status"/>
            </p>
          </editionStmt>
          <publicationStmt>
            <publisher>Laboratoire Litt&amp;Arts</publisher>
            <date>
              <xsl:attribute name="when">
                <xsl:value-of select="//tact_media_export_date"/>
              </xsl:attribute>
            </date>
          </publicationStmt>
          <sourceDesc>
            <p>Texte transcrit</p>
          </sourceDesc>
        </fileDesc>
      </teiHeader>
  </xsl:template>

  <xsl:template match="body">
    <xsl:apply-templates mode="clean"/>
  </xsl:template>

  <xsl:template match="tact_media_contributor">
    <respStmt>
      <resp><xsl:value-of select="role"/></resp>
      <name type="username"><xsl:value-of select="name"/></name>
    </respStmt>
  </xsl:template>
  
  <xsl:template match="node()" mode="clean">
    <xsl:choose>
      <xsl:when test="string-length(name()) = 0">
        <xsl:copy-of select="."/>
      </xsl:when>
      <xsl:otherwise>
        <xsl:element name="{name()}">
          <xsl:for-each select="@*">
            <xsl:if test="name() != 'data-tag'">
              <xsl:copy-of select="."/>
            </xsl:if>
          </xsl:for-each>
          <xsl:apply-templates mode="clean"/>
        </xsl:element>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>
  
  <xsl:template match="br" mode="clean">
    <lb/>
    <xsl:apply-templates/>
  </xsl:template>

</xsl:stylesheet>
