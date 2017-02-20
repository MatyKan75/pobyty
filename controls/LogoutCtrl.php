<?php

/**
 * Třída LogoutCtrl zpracuje požadavek uživatele na odhlášení ze systému.
 */
class LogoutCtrl extends AbstractCtrl {
    
    /**
     * Metoda process() zpracuje požadavek na odhlášení. Po odhlášení zobrazí 
     * přihlašovací dialog.
     * URL: /lougout
     */
    public function process($params){
        $userHelper = new UserHelper();
        $userHelper->logout();
        $this->redirectTo("login");
    }
}

?>