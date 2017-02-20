<?php

/**
 * Třída SettingsCtrl umožňuje upravovat nastavení aplikace.
 */
class SettingsCtrl extends AbstractCtrl {
    
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
        $this->data['events'] = $events;
        $this->data['persons'] = $persons;
        $this->data['users'] = $userHelper->getUsers();
        $this->view = "settings";
        $_SESSION['current-page']="settings";
        
        if($_POST){
            $event = $eventHelper->getEvent($_POST['active_event']);
            setcookie("event",$event['id'],time() + (86400 * 30));
            if(!empty($_POST['password'])){
                $userHelper = new UserHelper();
                $userHelper->updatePassword($_SESSION['user']['jmeno'],$_POST['password']);
                $this->addMessage('Změny byly uloženy!');
            }
            $this->redirectTo("attendees/default");
        }
    }
}

?>