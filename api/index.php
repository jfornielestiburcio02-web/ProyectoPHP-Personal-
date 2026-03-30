<?php
/**
 * LÓGICA DE EXTRACCIÓN DE IP PROFESIONAL (SOPORTA PROXIES Y IPV6)
 */
function get_client_ip_professional() {
    $ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR');
    foreach ($ip_keys as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $ip;
                }
            }
        }
    }
    return $_SERVER['REMOTE_ADDR'];
}

$user_ip = get_client_ip_professional();
?>


<!---
Creado por JFT
Fecha = 2007
--->








<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<HTML>
<HEAD>
    <META HTTP-EQUIV="CONTENT-TYPE" CONTENT="TEXT/HTML; CHARSET=UTF-8">
    <TITLE>IP LOCATOR PROFESIONAL V2.1</TITLE>
</HEAD>
<BODY STYLE="BACKGROUND-COLOR: #E5E5E5; MARGIN: 0PX; PADDING: 0PX; FONT-FAMILY: TAHOMA, VERDANA, ARIA, SANS-SERIF;">

    <DIV ID="CONTAINER" STYLE="WIDTH: 100%; TEXT-ALIGN: CENTER; PADDING-TOP: 60PX;">
        
        <DIV ID="MAIN-CARD" STYLE="WIDTH: 480PX; MARGIN: 0 AUTO; BACKGROUND-COLOR: #FFFFFF; BORDER: 1PX SOLID #999999; BOX-SHADOW: 5PX 5PX 15PX #AAAAAA;">
            
            <DIV STYLE="BACKGROUND-COLOR: #0055AA; PADDING: 12PX; BORDER-BOTTOM: 3PX SOLID #003366; TEXT-ALIGN: LEFT;">
                <SPAN STYLE="COLOR: #FFFFFF; FONT-WEIGHT: BOLD; FONT-SIZE: 12PX; TEXT-TRANSFORM: UPPERCASE;">
                    &raquo; PANEL DE INFORMACI&Oacute;N DEL VISITANTE
                </SPAN>
            </DIV>

            <DIV STYLE="PADDING: 40PX 20PX;">
                
                <SPAN STYLE="COLOR: #333333; FONT-SIZE: 16PX; FONT-WEIGHT: BOLD; DISPLAY: BLOCK; MARGIN-BOTTOM: 15PX;">
                    ESTIMADO USUARIO, ESTA ES SU IP:
                </SPAN>

                <DIV STYLE="BACKGROUND-COLOR: #F9F9F9; BORDER: 2PX SOLID #CCCCCC; PADDING: 25PX; COLOR: #D90000; FONT-FAMILY: 'COURIER NEW', MONOSPACE; FONT-SIZE: 32PX; FONT-WEIGHT: BOLD; MARGIN-BOTTOM: 30PX; TEXT-SHADOW: 1PX 1PX 0PX #DDDDDD;">
                    <?PHP ECHO $user_ip; ?>
                </DIV>

                <A HREF="#" STYLE="TEXT-DECORATION: NONE;">
                    <DIV STYLE="DISPLAY: INLINE-BLOCK; BACKGROUND: #4CAF50; BACKGROUND: LINEAR-GRADIDIENT(TO BOTTOM, #4CAF50 0%, #2E7D32 100%); BORDER: 2PX SOLID #1B5E20; PADDING: 12PX 30PX; COLOR: #FFFFFF; FONT-WEIGHT: BOLD; FONT-SIZE: 14PX; TEXT-TRANSFORM: UPPERCASE; BORDER-RADIUS: 5PX; CURSOR: POINTER; BOX-SHADOW: INSET 0PX 1PX 0PX #A5D6A7, 0PX 2PX 4PX #666666;">
                        EXPLORA M&Aacute;S
                    </DIV>
                </A>

            </DIV>

            <DIV STYLE="BACKGROUND-COLOR: #F0F0F0; PADDING: 10PX; BORDER-TOP: 1PX SOLID #DDDDDD; FONT-SIZE: 11PX; COLOR: #666666;">
                PROCESADO POR EL MOTOR PHP 5.x &copy; 2006 - <STRONG>CONEXI&Oacute;N SEGURA</STRONG>
            </DIV>

        </DIV>

        <DIV STYLE="MARGIN-TOP: 20PX; FONT-SIZE: 10PX; COLOR: #999999;">
            OPTIMIZADO PARA INTERNET EXPLORER 6.0 Y FIREFOX 1.5
        </DIV>

    </DIV>

</BODY>
</HTML>
