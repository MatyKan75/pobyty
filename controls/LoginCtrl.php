<?php

/**
 * Třída LoginCtrl zpracuje požadavek uživatele na přihlášení do systému.
 */
class LoginCtrl extends AbstractCtrl {
    
    /**
     * Metoda process() zobrazí přihlašovací dialog a zpracuje zadané údaje.
     * Pokud proběhne přihlášení správně zobrazí základní informce o aktuálním 
     * pobytu, jinak umožní nové přihlášení.
     */
    public function process($params){
        $userHelper = new UserHelper();
        $user = $userHelper->getLoggedInUser();
        if(empty($user)){
            $this->header['title']="Přihlášení do systému ...";
        } else {
            $this->redirectTo("attendees/default");
        }
        if($_POST){
            try{
                $event_id = $userHelper->login($_POST['user'],$_POST['password']);
                if(!isset($_COOKIE['event'])){
                    if(empty($event_id)){
                        $this->redirectTo("settings");
                    } else {
                        setcookie('event',$event_id,86400 * 30);
                    }
                }
                $this->redirectTo("attendees/default");
            } catch(UserError $err) {
                $this->addMessage($err->getMessage());
                //$this->addMessage($err->getMessage()." ".$_POST['user']." ".$userHelper->getImprint($_POST['password']));
            }
        }
        $this->view="login";
    }
}

?>