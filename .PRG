VV = 'S'

FDISP('Confirma a carga de dados da web ?',4)

IF VV = 'N'
   RETURN
ENDIF

ABRIR4('ORG','SIG','SIG_ORG')

FSELE('SIG_DOU')
*SET ORDER TO TAG CHA
FINDEX('CHA')

LICPAG = "&licitacoesPorPagina=100"

XBUSPAR = ""
XBUSPAR = XBUSPAR + LICPAG 

XPAS = ALLTRIM(THISFORM.XCHA.VALUE)
XEST = ALLTRIM(THISFORM.XEST.VALUE)

IF EMPTY(XPAS)
   FDISP('É necessário uma palavra chave',10)
   RETURN
ENDIF
IF EMPTY(XEST)
   FDISP('É necessário o estado',10)
   RETURN
ENDIF

if !EMPTY(XPAS)
   XBUSPAR = XBUSPAR +"&palavra_chave="+ xpas
ELSE

ENDIF

W1 = FPAR3('SIG_410:TOK_DOU')

IF EMPTY(W1)
   W1 = "986259b8ae392b22491634a213258539"
*  FPAR3('SIG_410:TOK_DOU',W1)
ENDIF
xtoken = W1
xpag = 1
*xtoken = "986259b8ae392b22491634a213258539"
*ytoken = "abcdefabcdefabcdefabcdefabcdef97"
**Solicitação API
*&pagina=30

FWAIT()
srvXMLHttp = Createobject("MSXML2.ServerXMLHTTP")
*srvXMLHttp.open("GET", XAPI, .F.)



xpag = xpag + 1
FDISP('Iniciando busca... ' + TRANS(xpag),0)
XAPI = "http://localhost/licitafox/rest.php?class=LicitacoesRestService&method=getFox&objeto="+ XPAS +"&estado=" + XEST
*messagebox("PAGINA" + trans(xpag))
*messagebox("Substring encontrada na posição: ", AT(lcString, wRES))
srvXMLHttp.open("GET", XAPI, .F.)
FDISP("GET enviado! Aguarde alguns instantes...",7)
srvXMLHttp.Send
wret = srvXMLHttp.ResponseText
IF srvXMLHttp.Status = 200 and AT(xbuscar, wret)> 0
   wres = wres + wret

   ***** TODAS LICITACOES
   WJSON = (strextract(WRESPONSE ,'[{','}]'))
   WJSON = "{"+WJSON+"}"

   *** TOTAL DE LICITAÇÕES
   WTOTLIC = VAL(strextract(WRESPONSE ,'"RegCont":',','))
   *** CONTADOR
   WCONLIC = 0


DO WHILE WCONLIC <= WTOTLIC
    WCONLIC = WCONLIC + 1

   ******** UMA LICITACAO
   WLIC = STREXTRACT(WJSON ,'{','}')
   WJSON = STRTRAN(WJSON, '{', 1, 1)
   *wid = strextrac(wlic , "<id>", "</id>")
   WID = STREXTRACT(WLIC ,'{"id":',',')

   *wmod = strextrac(wlic , "<modalidade>", "</modalidade>")
   WMOD = STREXTRACT(WLIC ,'"modalidade":"','",')
   wmod = ALLTRIM(FTCE(wmod))

   *wdat = strextrac(wlic , "<abertura>", "</abertura>")
   WDAT = STREXTRACT(WLIC ,'"abertura":"','",')
   wdat = SUBS(wdat,9,2) + '/' + SUBS(wdat,6,2) + '/' + SUBS(wdat,1,4)
   wdat = CTOD(wdat)

   *wcap = strextrac(wlic , "<dataCaptura>", "</dataCaptura>")
   WCAP = STREXTRACT(WLIC ,'"publicado_em":"','",')
   wcap = SUBS(wcap,9,2) + '/' + SUBS(wcap,6,2) + '/' + SUBS(wcap,1,4)
   wcap = CTOD(wcap)

   *worg = strextrac(wlic , "<orgao>", "</orgao>")
   WORG = STREXTRACT(WLIC ,'"orgao":"','",')

   *wuf = strextrac(wlic , "<uf>", "</uf>")
   WUF = STREXTRACT(WLIC ,'"estado":"','",')

   *wmun = strextrac(wlic , "<municipio>", "</municipio>")
   WMUN = STREXTRACT(WLIC ,'"municipio":"','",')

   *wobj = strextrac(wlic , "<objeto>", "</objeto>")
   WOBJ = STREXTRACT(WLIC ,'"objeto":"','",')

   *wibge = strextrac(wlic , "<municipioIBGE>", "</municipioIBGE>")
   WIBGE = ''

   *wlink = strextrac(wlic , "<link>", "</link>")
   WLINK = STREXTRACT(WLIC ,'"site_original":"','",')
   *? wlic
   *wlink = STRTRAN(wlink, "amp;", "")

   if EMPTY(wid)
      exit
   endif
   FSELE('SIG_DOU')
   *LOCA FOR lid = wid
   SEEK WID
   IF EOF()
      FAPPEND()
      REPLACE CHA WITH wid
   ENDIF

   REPLACE ABE WITH wdat , MDLDES WITH wmod, ORG WITH worg, EST WITH wuf, CID WITH wmun, OBJ WITH MDL + ' : ' + wobj, IBG WITH wibge, LNK WITH wlink
   REPLACE DAT WITH WCAP

   FSELE('SIG_TIP')

   LOCA FOR DES = wmod
   IF EOF()

      GO BOTT
      WCOD = COD
      IF WCOD = '99'
         LOOP
      ENDIF
      WCOD = TRANS(VAL(WCOD)+1,'@L 99')
      FAPPEND()
      REPLACE COD WITH WCOD
   ENDIF
   REPL DES WITH wmod
   

   FSELE('SIG_ORG')

   WORG = UPPER(WORG)
   LOCA FOR DES = WORG
   IF EOF()

      GO BOTT
      WCOD = COD
      IF WCOD = '9999999'
         LOOP
      ENDIF
      WCOD = TRANS(VAL(WCOD)+1,'@L 9999999')
      FAPPEND()
      REPLACE COD WITH WCOD
   ENDIF
   REPL DES WITH WORG

   SELE SIG_DOU
   REPL MDL WITH SIG_TIP.COD IN SIG_DOU

ENDDO
RELEASE srvXMLHttp

SELE SIG_DOU 

REPL ALL FLG WITH ' ' FOR FLG # ' '
REPL ALL FLG WITH 'X' FOR DAT = DATE() AND FLG = ' ' 
LOCA FOR FLG = 'X'
IF EOF()
   GO TOP
ENDIF

FWAIT('CLOSE')
 
THISFORM.REFRESH()

FDISP('OK',11)