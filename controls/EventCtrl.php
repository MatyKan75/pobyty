<?php

/**
 * Třída EventCtrl zpracuje požadavek na zobrazení a nastavení vlastností akce.
 */
class EventCtrl extends AbstractCtrl {
    
    /**
     * Metoda process() zpracuje požadavek zobrazení akce. 
     * URL: /event/{eventID}
     */
    public function process($params){
        $this->checkUser();
        $eventHelper = new EventHelper();
        if(!empty($params[0])){
            // zobrazení informací o akci eventID
            $event_id = $params[0];
            if($event_id > 0){
                $this->data['event'] = $eventHelper->getEvent($event_id);
                $this->data['attendees_count'] = $eventHelper->getAttendeesCount($event_id)[0];
                $this->data['cash'] = $eventHelper->getCashStatus($event_id);
            }
            if($_POST){
                $this->redirectTo("config");
            }
            $this->data['accomodations']=$eventHelper->getAccomodations();
            $this->data['accomodationsR']=$eventHelper->getAccomodationsR();
            $this->view = "event";
            $_SESSION['current-page']="config";
        } else {
            // URL adresu /event považuji za chybu
            $this->redirectTo("error");
        }
    }
}

?>