<?php

/**
 * Třída EventHelper umožňuje spravovat informace o akcích.
 */
class EventHelper {

    private static $cs_days = array(
        'Mon' => 'Po',
        'Tue' => 'Út',
        'Wed' => 'St',
        'Thu' => 'Čt',
        'Fri' => 'Pá',
        'Sat' => 'So',
        'Sun' => 'Ne',);

    /**
     * Vrátí informce o daném pobytu.
     */
    public function getEvent($event_id){
        $sql = 'SELECT a.id, a.nazev, a.zacatek, a.prvni_jidlo, a.posl_jidlo, a.konec,'.
            'a.rezie, a.prihl_do, fixni_ceny,z.nazev AS zarizeni, z.kod as kod_zarizeni FROM akce a, zarizeni z '.
            'WHERE a.zarizeni=z.kod AND a.id=?';
        $event = DatabaseHelper::queryOne($sql,array($event_id,));
        return $event;
    }

    /**
     * Vrátí informace o cenách jídel, ubytování, služeb a příplatků.
     */
    public function getPrices($event_id){
        $sql = 'SELECT porce, snidane, obed, vecere FROM ceny_jidel WHERE akce=?';
        $food_prices = DatabaseHelper::queryAll($sql, array($event_id,));
        $prices = array(
            "jidlo" => $food_prices,
        );
        return $prices;
    }

    /**
     * Vrátí zařízení definovaná v databázi.
     */
    public function getAccomodations(){
        $sql = 'SELECT kod, nazev FROM zarizeni';
        $accomodations = array();
        foreach(DatabaseHelper::queryAll($sql,array()) as $accomodation){
            $sql2 = 'SELECT kod, nazev FROM typ_ubyt WHERE zarizeni=?';
            $accRecord = array();
            $accRecord['nazev'] = $accomodation['nazev'];
            $accTypes = '';
            foreach(DatabaseHelper::queryAll($sql2,array($accomodation['kod'])) as $accType){
                 $accTypes .= $accType['nazev'].'; ';
            }
            $accRecord['typy_ubyt']=$accTypes;
            $accomodations[$accomodation['kod']]=$accRecord;
        }
        return $accomodations;
    }

    /**
     * Vrátí zařízení definovaná v databázi.
     */
    public function getAccomodationsR(){
        $sql = 'SELECT kod, nazev FROM zarizeni';
        $accomodations = array();
        foreach(DatabaseHelper::queryAll($sql,array()) as $accomodation){
            $accomodations[$accomodation['nazev']]=$accomodation['kod'];
        }
        return $accomodations;
    }

    /**
     * Vrátí ID naposledy přidané akce.
     */
    public function getMaxEventID(){
        $sql = 'SELECT max(id) FROM akce';
        $max_id = DatabaseHelper::queryOne($sql,array());
        return $max_id;
    }

    /**
     * Vrátí počet účastníků pobytu.
     */
    public function getAttendeesCount($event_id){
        $sql = 'SELECT count(*) FROM pobyt WHERE akce=?';
        $count = DatabaseHelper::queryOne($sql,array($event_id,));
        return $count;
    }

    /**
     * Metoda getCashStatus() vrátí stav pokladny v hotovosti.
     */
    public function getCashStatus($event_id){
        $sql = 'SELECT SUM(cena) AS celkem, SUM(doplatek) AS doplatky, SUM(zaloha) AS zalohy FROM pobyt WHERE akce=? AND zaplaceno="Ano" AND typ_dopl=1';
        $cash = DatabaseHelper::queryOne($sql,array($event_id,));
        return $cash;
    }

    /**
     * Metoda getEventDay() vrátí seznam dní akce.
     */
    public function getEventDays($event_id){
        $days = array();
        $sql = 'SELECT zacatek, konec FROM akce WHERE id=?';
        $result = DatabaseHelper::queryOne($sql,array($event_id,));
        $day = strtotime($result['zacatek']);
        $last_day = strtotime($result['konec']);
        while($day<=$last_day){
            $days[] = self::$cs_days[date("D",$day)]." ".date("j.n.",$day);
            $day += 86400;
        }
        return $days;
    }

    /**
     * Vrátí seznam všech pobytů uložených v databázi.
     */
    public function getEvents(){
        $sql = 'SELECT id, a.nazev AS nazev, zacatek, konec, z.nazev AS zarizeni '.
            'FROM akce a, zarizeni z WHERE a.zarizeni = z.kod ORDER BY zacatek';
        $events = DatabaseHelper::queryAll($sql,array());
        return $events;
    }
}

?>