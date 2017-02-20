<?php

/**
 * Třída OverviewCtrl zpracovává požadavky uživatele na zobrazení přehledů.
 * Např.: Účastníci do 26, zaměstnanci a účastnící nad 26 včetně pracovníků.
 */
class OverviewCtrl extends AbstractCtrl {

    public function process($params){
        $this->view = 'overview';
        $_SESSION['current-page']="overview";
    }
}

?>