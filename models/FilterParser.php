<?php

/**
 * Třída FilterParser umožňuje na základě podmínky definované uživatelem 
 * sestavit odpovídající SQL podmínku. 
 */
class FilterParser {
    
    public static function parse($filter){
        $GLOBALS['Logger']->debug("FilterParser::parse($filter) ...");
        $and_expr = mb_split("A",$filter);
        foreach($and_expr as $expr){
            $GLOBALS['Logger']->debug("  $expr");
            if(mb_strpos($expr,'NEBO')){
                $or_expr = mb_split("NEBO",$expr);
                foreach($or_expr as $expr2){
                    $parsed_expr = FilterParser::parse_expression($expr2);
                    $sql_filter = $sql_filter.$parsed_expr.' OR ';
                    $GLOBALS['Logger']->debug("  $expr2 -> $parsed_expr");
                }
            } else {
                $parsed_expr = FilterParser::parse_expression($expr);
                $sql_filter = $sql_filter.' '.$parsed_expr.' AND ';
                $GLOBALS['Logger']->debug("  $expr -> $parsed_expr");
            }
        }
        $sql_filter = substr($sql_filter,0,strlen($sql_filter)-4);
        $GLOBALS['Logger']->debug("  Parsed filter> ".$sql_filter);
        return $sql_filter;
    }

    public static function parse_expression($expr){
    	$sql_expr = "";
        $parsed_expr = explode(" ",trim($expr));
        $attendeeHelper = new AttendeeHelper();
        if(count($parsed_expr)>2){
            switch (mb_strtolower($parsed_expr[0])){
                case "hnutí":
                    if($parsed_expr[2] == '#NIC'){
                        $sql_expr = 'hnuti is null';
                    } else {
                        $sql_expr = 'hnuti'.$parsed_expr[1].'"'.$parsed_expr[2].'"';
                    }
                    break;

                case "jméno":
                    if($parsed_expr[1] == '~'){
                        $sql_expr = '(jmeno LIKE "%'.$parsed_expr[2].'%" OR prijmeni LIKE "%'.$parsed_expr[2].'%")';
                    } else {
                        $sql_expr = '(jmeno'.$parsed_expr[1].'"'.$parsed_expr[2].'" OR prijmeni'.$parsed_expr[1].'"'.$parsed_expr[2].'")';
                    }
                    break;

                case "č.op":
                    if($parsed_expr[1] == '~'){
                        $sql_expr = '(cis_pasu LIKE "%'.$parsed_expr[2].'%")';
                    } else {
                        $sql_expr = '(cis_pasu'.$parsed_expr[1].'"'.$parsed_expr[2].'")';
                    }
                    break;
                
                case "pohlaví":
                    $sql_expr = 'pohlavi'.$parsed_expr[1].'"'.$parsed_expr[2].'"';
                    break;
                
                case "pracovník":
                    $sql_expr = 'pracovnik'.$parsed_expr[1].'"'.$parsed_expr[2].'"';
                    break;
                
                case "účastník":
                    $attendeeTypesR = $attendeeHelper->getAttendeeTypesR();
                    $code = $attendeeTypesR[$parsed_expr[2]];
                    if($code){
                        $sql_expr = 'typ_ucast'.$parsed_expr[1].$code;
                    } else {
                        throw new UserError("Neplatný typ účastníka!");
                    }
                    break;

                case "ubytování":
                    $accommodationTypesR = $attendeeHelper->getAccommodationTypesR($_COOKIE['event']);
                    $code = $accommodationTypesR[$parsed_expr[2]];
                    $room = (count($parsed_expr)==4)?$parsed_expr[3]:0;
                    if($code){
                        if($room){
                            $sql_expr = "typ_ubyt".$parsed_expr[1].$code." AND pokoj='".$room."'";
                        } else {
                            $sql_expr = 'typ_ubyt'.$parsed_expr[1].$code;
                        }
                    } else {
                        throw new UserError("Neplatný typ ubytování!");
                    }
                    break;
                
                case "platba":
                    $paymentTypesR = $attendeeHelper->getPaymentTypesR();
                    $code = $paymentTypesR[$parsed_expr[2]];
                    if($code){
                        $sql_expr = 'typ_dopl'.$parsed_expr[1].$code;
                    } else {
                        throw new UserError("Neplatný typ platby!");
                    }
                    break;

                case "záloha":
                    $sql_expr = 'zaloha'.$parsed_expr[1].$parsed_expr[2];
                    break;
                
                case "doplatek":
                    $sql_expr = 'doplatek'.$parsed_expr[1].$parsed_expr[2];
                    break;

                case "cena":
                    $sql_expr = 'cena'.$parsed_expr[1].$parsed_expr[2];
                    break;
                
                case "zaplaceno":
                    $sql_expr = 'zaplaceno'.$parsed_expr[1].'"'.$parsed_expr[2].'"';
                    break;
                
                case "veget?":
                    $sql_expr = 'veget'.$parsed_expr[1].'"'.$parsed_expr[2].'"';
                    break;

                case "porce":
                    $porce = 1;
                    if(trim($parsed_expr[2])=='Dětská'){
                        $porce = 2;
                    }
                    $sql_expr = 'porce'.$parsed_expr[1].$porce;
                    break;

                case "dítě":
                    $sql_expr = 'ubyt_dite'.$parsed_expr[1].'"'.$parsed_expr[2].'"';
                    break;

                case "přihlášen":
                case "přihlášena":
                    $sql_expr = 'dat_prihl'.$parsed_expr[1].'"'.date("Y-m-d",strtotime($parsed_expr[2])).'"';
                    break;
                
                case "narozen":
                case "narozena":
                    $sql_expr = 'dat_naroz'.$parsed_expr[1].'"'.date("Y-m-d",strtotime($parsed_expr[2])).'"';
                    break;

                case "skupina":
                    $sql_expr = 'skupina'.$parsed_expr[1].'"'.$parsed_expr[2].'"';
                    break;

                case "email":
                    if($parsed_expr[1] == '~'){
                        $sql_expr = 'email LIKE "%'.$parsed_expr[2].'%"';
                    } else {
                        $sql_expr = 'email'.$parsed_expr[1].'"'.$parsed_expr[2].'"';
                    }
                    break;

                case "objednáno":
                    
                    break;

                case "věk":
                    
                    break;

                default:
                    throw new UserError("Neplatný výraz!");
            }
        } else {
            $sql_expr = '(jmeno LIKE "%'.$parsed_expr[0].'%" OR prijmeni LIKE "%'.$parsed_expr[0].'%")';
        }
        return $sql_expr;
    }
}

?>