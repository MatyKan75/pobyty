<?php

/**
 * Třída SettingsCtrl umožňuje upravovat nastavení aplikace.
 */
class ConfigCtrl extends AbstractCtrl {
    
    /**
     * Metoda process() zpracuje požadavek uživatele na nastavení chování aplikace.
     */
    public function process($params){
        $this->checkUser();
        $eventHelper = new EventHelper();
        $events = $eventHelper->getEvents();
        $attendeeHelper = new AttendeeHelper();
        $persons = $attendeeHelper->getPersons();
        $userHelper = new UserHelper();
        if($_POST){
            $this->redirectTo("attendees/default");
        }
        $this->data['events'] = $events;
        $this->data['persons'] = $persons;
        $this->data['users'] = $userHelper->getUsers();
        $this->data['accomodations']=$eventHelper->getAccomodations();
        $this->view = "config";
        $_SESSION['current-page']="config";

    }
}

?>