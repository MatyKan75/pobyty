<?php

/**
 * Třída AttendeeHelper umožňuje správu dat o jednoltivých účastnících pobytu.
 */
class AttendeeHelper {
    
    /**
     * Metoda getAttendee() vrátí informace o daném účastníkovi pobytu.
     *  $attendee_id     ID účastníka.
     */
    public function getAttendee($attendee_id,$event_id){
        $sql = 'SELECT * FROM osoba o, pobyt p WHERE o.ID=? AND o.ID=p.osoba AND p.akce=?';
        $attendee = DatabaseHelper::queryOne($sql,array($attendee_id,$event_id,));
        return $attendee;
    }

    /**
     * Metoda createAttendee() vloží novou osobu s danými vlastnostmi do databáze a vytvoří nový záznam
     * o její účastni na pobytu/akci.
     */
    public function createAttendee($name,$surname,$street,$city,$zip,$country,$id_card,$gender,$phone,$email,
                                    $birthday,$movement,$staff,$staff_type,$event_id,$attende_type,$veget,$portion,
                                    $deposit,$payment,$payment_type,$surcharge,$order,$apply_date,$notes,$room,
                                    $accomodation_type,$price,$paid,$overheads,$fixed_price,$form_to,$child_accomod,
                                    $group,$services){
        $attendee_id = $this->getMaxAttendeeID()[0] + 1;
        $values = array(
                'ID'        => $attendee_id,    'jmeno'     => $name,
                'prijmeni'  => $surname,        'ulice'     => $street,
                'mesto'     => $city,           'psc'       => $zip,
                'stat'      => $country,        'cis_pasu'  => $id_card,
                'pohlavi'   => $gender,         'telefon'   => $phone,
                'email'     => $email,          'dat_naroz' => $birthday,
                'hnuti'     => $movement,       'pracovnik' => $staff,
                'typ_prac' => $staff_type,
            );
        $updated = DatabaseHelper::insert('osoba',$values);
        $order_id = $this->getMaxOrderID()[0] + 1;
        $values = array(
                'ID'        => $order_id,       'event'     => $event_id,
                'osoba'     => $attendee_id,    'typ_ucast' => $attende_type,
                'veget'     => $veget,          'porce'     => $portion,
                'zaloha'    => $deposit,        'doplatek'  => $payment,
                'typ_dopl'  => $payment_type,   'priplatek' => $surcharge,
                'objednavka'=> $order,          'dat_prihl' => $apply_date,
                'poznamka'  => $notes,          'pokoj'     => $room,
                'typ_ubyt'  => $accomodation_type,
                'cena'      => $price,          'zaplaceno' => $paid,
                'rezie'     => $overheads,      'fix_cena'  => $fixed_price,
                'od_do'     => $form_to,        'ubyt_dite' => $child_accomod,
                'skupina'   => $group,          'sluzby'    => $services,
        );
        $updated = DatabaseHelper::insert('pobyt',$values);
        return $updated;
    }

    /**
     * Metoda updateAttendee() upraví danou osobu v databázi.
     */
    public function updateAttendee($attendee_id,$name,$surname,$street,$city,$zip,$country,$id_card,$gender,$phone,$email,
                                    $birthday,$movement,$staff,$staff_type,$event_id,$attende_type,$veget,$portion,
                                    $deposit,$payment,$payment_type,$surcharge,$order,$apply_date,$notes,$room,
                                    $accomodation_type,$price,$paid,$overheads,$fixed_price,$form_to,$child_accomod,
                                    $group,$services){
        $values = array(
                'jmeno'     => $name,           'prijmeni'  => $surname,
                'ulice'     => $street,         'mesto'     => $city,
                'psc'       => $zip,            'stat'      => $country,
                'cis_pasu'  => $id_card,        'pohlavi'   => $gender,
                'telefon'   => $phone,          'email'     => $email,
                'dat_naroz' => $birthday,       'hnuti'     => $movement,
                'pracovnik' => $staff,          'typ_prac' => $staff_type,
            );
        $updated = DatabaseHelper::update('osoba',$values,'WHERE id=?',array($attendee_id,));
        $attendee = $this->getAttendee($attendee_id,$event_id);
        $order_id = $attendee['p.ID'];
        $values = array(
                'typ_ucast' => $attende_type,   'veget'     => $veget,
                'porce'     => $portion,        'zaloha'    => $deposit,
                'doplatek'  => $payment,        'typ_dopl'  => $payment_type,
                'priplatek' => $surcharge,      'objednavka'=> $order,
                'dat_prihl' => $apply_date,     'poznamka'  => $notes,
                'pokoj'     => $room,           'typ_ubyt'  => $accomodation_type,
                'cena'      => $price,          'zaplaceno' => $paid,
                'rezie'     => $overheads,      'fix_cena'  => $fixed_price,
                'od_do'     => $form_to,        'ubyt_dite' => $child_accomod,
                'skupina'   => $group,          'sluzby'    => $services,
        );
        $updated = DatabaseHelper::update('pobyt',$values,'WHERE id=?',array($order_id,));
        return $updated;
    }

    /**
     * Metoda deleteAttendee() smaže z databáze danou osobu, včetně všech objednávek.
     *  $attendee_id    ID osoby.
     */
    public function deleteAttendee($attendee_id){
        $sql = "DELETE FROM pobyt WHERE osoba=?";
        $deleted = DatabaseHelper::query($sql,array($attendee_id,));
        $sql = "DELETE FROM osoba WHERE id=?";
        $deleted += DatabaseHelper::query($sql,array($attendee_id,));
        return $deleted;
    }



    /**
     * Metoda getOrdersByDays() vrátí informace o objednaných jídlech a nocích pro jednotlivé 
     * pobytové dny.
     *  $attendee_id    ID účastníka,
     *  $event_id       ID akce/pobytu.
     */
    public function getOrdersByDays($attendee_id,$event_id){
        $orders = array();
        $sql = 'SELECT objednavka FROM pobyt WHERE osoba=? AND akce=?';
        $order = DatabaseHelper::queryOne($sql,array($attendee_id,$event_id,));
        $eventHelper = new EventHelper();
        $event_days = $eventHelper->getEventDays($event_id);
        $binary_order = decbin($order[0]);
        $binary_order = str_pad($binary_order,count($event_days)*4,"0",STR_PAD_LEFT);
        for($day=1; $day <= count($event_days); $day++){
            $day_order = substr($binary_order,($day-1)*4,4);
            $orders[] = array(  'den'=>$event_days[$day-1],
                                'snidane'=>$day_order[0],
                                'obed'=>$day_order[1], 
                                'vecere'=>$day_order[2],
                                'nocleh'=>$day_order[3],
                                );
        }
        return $orders;
    }

    /**
     * Metoda getAttendees() vrátí seznam všech účastníků daného pobytu.
     *  $event_id   ID pobytu.
     */
    public function getAttendees($event_id, $filter){
        $sql = 'SELECT * FROM osoba o, pobyt p WHERE p.osoba=o.id AND p.akce=?';
        if($filter != null && !empty($filter)){
            $sql = $sql.' AND ('.$filter.')';
        }
        $sql = $sql.' ORDER BY prijmeni,jmeno';   
        $attendees = DatabaseHelper::queryAll($sql,array($event_id,));
        return $attendees;
    }

    /**
     * Metoda getPersons() vrátí seznam všech osob v databázi.
     */
    public function getPersons(){
        $sql = 'SELECT id, jmeno, prijmeni,ulice,mesto,psc,stat,dat_naroz FROM osoba ORDER BY prijmeni, jmeno';  
        $attendees = DatabaseHelper::queryAll($sql,array());
        return $attendees;
    }

    /**
     * Metoda getMaxAttendeeID() vrátí největší ID osoby v databázi.
     */
    public function getMaxAttendeeID(){
        $sql = 'SELECT max(id) FROM osoba';
        $max_id = DatabaseHelper::queryOne($sql,array());
        return $max_id;
    }

    /**
     * Metoda getMaxOrderID() vrátí největší ID objednávky v databázi.
     */
    public function getMaxOrderID(){
        $sql = 'SELECT max(id) FROM pobyt';
        $max_id = DatabaseHelper::queryOne($sql,array());
        return $max_id;
    }

    /**
     * Metoda getAttendeeTypes() vrátí definované typy účastníků. 
     */
    public function getAttendeeTypes(){
        $sql = 'SELECT * FROM typ_ucast';
        $attendeeTypes = array();
        foreach(DatabaseHelper::queryAll($sql,array()) as $type){
            $attendeeTypes[$type['kod']]=$type['nazev'];
        }
        return $attendeeTypes;
    }

    /**
     * Metoda getAttendeeTypesR() vrátí definované typy účastníků. 
     */
    public function getAttendeeTypesR(){
        $sql = 'SELECT * FROM typ_ucast';
        $attendeeTypes = array();
        foreach(DatabaseHelper::queryAll($sql,array()) as $type){
            $attendeeTypes[$type['nazev']]=$type['kod'];
        }
        return $attendeeTypes;
    }

    /**
     * Metoda getAccommodationTypes() vrátí definované typy ubytování.
     */
    public function getAccommodationTypes($event_id){
        $sql = 'SELECT u.* FROM typ_ubyt u, akce a WHERE a.zarizeni = u.zarizeni AND a.id = ?';
        $accommodationTypes = array();
        foreach(DatabaseHelper::queryAll($sql,array($event_id,)) as $type){
            $accommodationTypes[$type['kod']]=$type['nazev'];
        }
        return $accommodationTypes;
    }

    /**
     * Metoda getAccommodationTypesR() vrátí definované typy ubytování.
     */
    public function getAccommodationTypesR($event_id){
        $sql = 'SELECT u.* FROM typ_ubyt u, akce a WHERE a.zarizeni = u.zarizeni AND a.id = ?';
        $accommodationTypes = array();
        foreach(DatabaseHelper::queryAll($sql,array($event_id,)) as $type){
            $accommodationTypes[$type['nazev']]=$type['kod'];
        }
        return $accommodationTypes;
    }

    /**
     * Metoda getPaymentTypes() vrátí definované typy plateb.
     */
    public function getPaymentTypes(){
        $sql = 'SELECT * FROM typ_platby';
        $paymentTypes = array();
        foreach(DatabaseHelper::queryAll($sql,array()) as $type){
            $paymentTypes[$type['kod']]=$type['nazev'];
        }
        return $paymentTypes;
    }

    /**
     * Metoda getPaymentTypes() vrátí definované typy plateb.
     */
    public function getPaymentTypesR(){
        $sql = 'SELECT * FROM typ_platby';
        $paymentTypes = array();
        foreach(DatabaseHelper::queryAll($sql,array()) as $type){
            $paymentTypes[$type['nazev']]=$type['kod'];
        }
        return $paymentTypes;
    }
}

?>