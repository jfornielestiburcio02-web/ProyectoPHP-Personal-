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
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<HTML>
<HEAD>
    <META HTTP-EQUIV="CONTENT-TYPE" CONTENT="TEXT/HTML; CHARSET=UTF-8">
    <TITLE>IP CHECKER PRO - VERSI&Oacute;N 2.0 (2006)</TITLE>
</HEAD>
<BODY STYLE="BACKGROUND-COLOR: #D1D9E0; MARGIN: 0PX; PADDING: 0PX; FONT-FAMILY: 'LUCIDA GRANDE', VERDANA, SANS-SERIF;">

    <DIV ID="MAIN-WRAPPER" STYLE="WIDTH: 100%; TEXT-ALIGN: CENTER; PADDING-TOP: 80PX;">
        
        <DIV ID="CONTENT-BOX" STYLE="WIDTH: 450PX; MARGIN: 0 AUTO; BACKGROUND-COLOR: #FFFFFF; BORDER: 2PX SOLID #4A6E9B; BORDER-RADIUS: 10PX; BOX-SHADOW: 8PX 8PX 0PX #888888; OVERFLOW: HIDDEN;">
            
            <DIV STYLE="BACKGROUND: #4A6E9B; PADDING: 10PX; BORDER-BOTTOM: 2PX SOLID #2A4E7B;">
                <H1 STYLE="COLOR: #FFFFFF; FONT-SIZE: 14PX; MARGIN: 0; TEXT-TRANSFORM: UPPERCASE; LETTER-SPACING: 2PX;">
                    SISTEMA DE DIAGN&Oacute;STICO DE RED
                </H1>
            </DIV>

            <DIV STYLE="PADDING: 30PX;">
                
                <P STYLE="COLOR: #666666; FONT-SIZE: 13PX; MARGIN-BOTTOM: 20PX; FONT-WEIGHT: BOLD;">
                    ESTIMADO USUARIO, ESTA ES SU IP:
                </P>

                <DIV STYLE="BACKGROUND-COLOR: #F0F8FF; BORDER: 1PX SOLID #B0C4DE; PADDING: 20PX; COLOR: #003399; FONT-FAMILY: 'COURIER NEW', MONOSPACE; FONT-SIZE: 28PX; FONT-WEIGHT: BOLD; TEXT-SHADOW: 1PX 1PX 0PX #FFFFFF;">
                    <?PHP ECHO $user_ip; ?>
                </DIV>

                <P STYLE="COLOR: #999999; FONT-SIZE: 10PX; MARGIN-TOP: 25PX;">
                    ESTADO DEL SERVIDOR: <SPAN STYLE="COLOR: #008000;">CONECTADO</SPAN><BR>
                    FECHA: <?PHP ECHO DATE("Y/M/D - H:I:S"); ?>
                </P>

            </DIV>

            <DIV STYLE="BACKGROUND-COLOR: #F4F4F4; PADDING: 8PX; BORDER-TOP: 1PX SOLID #CCCCCC; FONT-SIZE: 9PX; COLOR: #777777;">
                &copy; 2006 PROFESIONAL IP LOCATOR - TODOS LOS DERECHOS RESERVADOS.
            </DIV>

        </DIV>

    </DIV>

</BODY>
</HTML>
