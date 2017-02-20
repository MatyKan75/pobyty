<?php

/**
 * Třída AttendeesCtrl zpracovává požadavky uživatele na zobrazení seznamu přihlášených 
 * účastníků. Zobrazuje seznam všech účastníků nebo skupinu účastníků na základě 
 * zadaného kritéria.
 */
class AttendeesCtrl extends AbstractCtrl {
    
    /**
     * Metoda process() zpracuje požadavek uživatele na zobrazení účastníků.
     * Např.: 
     *      /attendees/?<query>   
     */
    public function process($params){
        // získáná číselníků typ_platby, typ_ucast a typ_ubyt
        $attendeeHelper = new AttendeeHelper();
        $this->data['attendee_types'] = $attendeeHelper->getAttendeeTypes();
        $this->data['payment_types'] = $attendeeHelper->getPaymentTypes();
        $this->data['accomodation_types'] = $attendeeHelper->getAccommodationTypes($_COOKIE['event']);
        $this->data['columns'] = array();
        $_SESSION['current-page']="attendees";
        
        if(!empty($params[0])){
            // zobrazení účastníků dle zadaných kriterií
            $filter = $_SESSION['filter'];
            if($_POST){
                if(!empty($_POST['filter']) && $_POST['filter']!= "#VŠICHNI"){
                    try{
                        $filter = FilterParser::parse($_POST['filter']);
                        $_SESSION['filter'] = $filter;
                        $_SESSION['filter_text'] = $_POST['filter'];
                    }
                    catch(UserError $err){
                        $this->addMessage($err->getMessage());
                    }
                } else {
                    $filter = null;
                    $_SESSION['filter'] = $filter;
                    $_SESSION['filter_text'] = '';
                }
            }
            // načtení seznamu sloupců ze souboru etc/columns.xml
            $xml = simplexml_load_file('etc/columns.xml');
            if(!empty($xml)){
                foreach($xml->children() as $col_set){
                    if($col_set['id'] == $params[0]){
                        $this->data['columns'] = $col_set;
                        break;
                    }
                }
            }
            
        } else {
            $filter = null;
        }

        // získání účastníků vyhovujících zadanému kritériu $filter
        $records = $attendeeHelper->getAttendees($_COOKIE['event'],$filter);
        $attendees = array();
        //$ids = array();
        $row = 0;
        foreach($records as $record){
            $attendee = array();
            $attendee['ID']=$record['osoba'];
            //array_push($ids,record['osoba']);
            if(stristr($this->data['columns'],'Jméno')){
                $attendee['Jméno'] = $record['prijmeni'].' '.$record['jmeno'];
            }
            if(stristr($this->data['columns'],'Adresa')){
                $attendee['Adresa'] = $record['ulice'].(!empty($record['ulice'])?', ':'').$record['pcs'].
                    $record['mesto'].(!empty($record['mesto'])?', ':' ').$record['stat'];
            }
            if(stristr($this->data['columns'],'Narozen(a)')){
                $attendee['Narozen(a)'] = (!empty($record['dat_naroz'])?date('j.n.Y',strtotime($record['dat_naroz'])):'');
            }
            if(stristr($this->data['columns'],'Telefon')){
                $attendee['Telefon'] = (!empty($record['telefon'])?$record['telefon']:'');
            }
            if(stristr($this->data['columns'],'Email')){
                $attendee['Email'] = (!empty($record['email'])?$record['email']:'');
            }
            if(stristr($this->data['columns'],'Č.OP')){
                $attendee['Č.OP'] = (!empty($record['cis_pasu'])?$record['cis_pasu']:'');
            }
            if(stristr($this->data['columns'],'Účastník')){
                $attendee['Účastník'] = $this->data['attendee_types'][$record['typ_ucast']];
            }
            if(stristr($this->data['columns'],'Hnutí')){
                $attendee['Hnutí'] = $record['hnuti'];
            }
            if(stristr($this->data['columns'],'Dítě')){
                $attendee['Dítě'] = $record['ubyt_dite'];
            }
            if(stristr($this->data['columns'],'Ubytování')){
                $attendee['Ubytování'] = $this->data['accomodation_types'][$record['typ_ubyt']].' '.$record['pokoj'];
            }
            if(stristr($this->data['columns'],'Pracovník')){
                $attendee['Pracovník'] = $record['pracovnik'];
            }
            if(stristr($this->data['columns'],'Porce')){
                $attendee['Porce'] = ($record['porce']==1)?'Dospělá':'Dětská';
            }
            if(stristr($this->data['columns'],'Veget.')){
                $attendee['Veget.'] = $record['veget'];
            }
            if(stristr($this->data['columns'],'Pozn.')){
                $attendee['Pozn.'] = $record['poznamka'];
            }
            if(stristr($this->data['columns'],'Přihlášen(a)')){
                $attendee['Přihlášen(a)'] = (!empty($record['dat_prihl'])?date('j.n.Y',strtotime($record['dat_prihl'])):'');
            }
            if(stristr($this->data['columns'],'Od-Do')){
                $attendee['Od-Do'] = $record['od_do'];
            }
            if(stristr($this->data['columns'],'Skupina')){
                $attendee['Skupina'] = $record['skupina'];
            }
            if(stristr($this->data['columns'],'Objednávka')){
                $attendee['Objednávka'] = $record['objednavka'];
            }
            if(stristr($this->data['columns'],'Záloha')){
                $attendee['Záloha'] = $record['zaloha'];
            }
            if(stristr($this->data['columns'],'Doplatek')){
                $attendee['Doplatek'] = $record['doplatek'];
            }
            if(stristr($this->data['columns'],'Platba')){
                $attendee['Platba'] = $this->data['payment_types'][$record['typ_dopl']];
            }
            if(stristr($this->data['columns'],'Cena')){
                $attendee['Cena'] = $record['cena'];
            }
            if(stristr($this->data['columns'],'Zaplaceno')){
                $attendee['Zaplaceno'] = $record['zaplaceno'];
            }
            $attendees[$row]=$attendee;
            $row++;
        }
        //$_SESSION['attendees']=$ids;
        $this->data['attendees'] = $attendees;
        $this->data['attendee_filter'] = $_SESSION['filter_text'];
        $this->view = 'attendees';

        // zpracování požadavku na export dat tabulky, nebo pro ubytovací linku
        if(count($params)>1){
            $file = fopen('export.csv','w');
            switch($params[1]){
                case 'exportTable':
                    // export dat z tabulky: použijí se aktuální sloupce a data se zapíšou 
                    // do souboru files/export.csv
                    $columns = $this->data['columns']->__toString();
                    $columns = 'ID;'.str_ireplace(",",";",$columns);
                    fwrite($file,$columns."\n");
                    foreach($attendees as $attendee){
                        $values = null;
                        foreach($attendee as $value){
                            $values = $values.$value.';';
                        }
                        fwrite($file,$values."\n");
                    }
                    downloadFile($file);
                    break;

                case 'exportCheckIn':
                    // export pro ubyt. linku: do souboru files/export.csv se zapíšou pouze
                    // data ze sloupců ID, jméno, adresa, telefon, email, od-do, ubytování a
                    // poznámka
                    $records = $attendeeHelper->getAttendees($_COOKIE['event'],"");
                    $columns = "ID;Jméno;Hnutí;Kontakt;Ubytování;Pokoj;Od-do;Poznámka";
                    fwrite($file,$columns."\n");
                    foreach($records as $record){
                        $values = $record['ID'].";".$record['prijmeni']." ".$record['jmeno'].";".$record['hnuti'].';'.
                            $record['telefon']." ".$record['email'].";".$this->data['accomodation_types'][$record['typ_ubyt']].";".
                            $record['pokoj'].";".$record['od_do'].";".$record['poznamka'].";\n";
                        fwrite($file,$values);
                    }
                    downloadFile($file);
                    break;

                case 'printTable':
                case 'printGuestBook':
                case 'printRooms':
                case 'printWaybill':
                case 'printPayments':
                    $this->addMessage("Tato funkce není zatím implementována!");
                    break;
            }
        }
    }

    function downloadFile($file){
        fflush($file);
        fclose($file);
        // zobrazení dialogu pro stažení souboru ...
        $dl_file = 'export.csv';
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.basename($dl_file).'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($dl_file));
        readfile($dl_file);
    }
}


?>