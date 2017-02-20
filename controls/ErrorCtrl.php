<?php

/**
 * Třída ErrorCtrl zpracuje chybový stav aplikace. Např.: uživatel požaduje 
 * neexistující stránku nebo stránku, ke které nemá dostatečná oprávnění.
 */
class ErrorCtrl extends AbstractCtrl {

    /**
     * Metoda proces() zobrazí chybovou stránku uživateli.
     */
    public function process($params) {
        Logger::debug("ErrorCtrl->process($params) ...");
        // Hlavička požadavku
        header("HTTP/1.0 404 Not Found");
        // Hlavička stránky
        $this->header['title'] = 'Chyba 404';
        // Nastavení šablony
        $this->view = 'error';
    }
}

?>