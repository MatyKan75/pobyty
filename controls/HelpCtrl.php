<?php

/**
 * Třída SettingsCtrl umožňuje upravovat nastavení aplikace.
 */
class HelpCtrl extends AbstractCtrl {
    
    /**
     * Metoda process() zpracuje požadavek uživatele na nastavení chování aplikace.
     */
    public function process($params){
        $this->checkUser();
        $this->view = "help";
        $_SESSION['current-page']="help";
    }
}

?>