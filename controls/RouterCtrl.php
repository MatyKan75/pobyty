<?php

/**
 * Třída RouterCtrl reprezentuje směrovač, který na základě předané URL adresy spustí 
 * potřebný kontroler pro obsloužení požadavku uživatele. 
 */
class RouterCtrl extends AbstractCtrl {
    
    /**
     * Kontroler, který zpracuje požadavek uživatele.
     */
    protected $controler;
    
    /**
     * Metoda process() zpracuje zadanou URL adresu a zavolá příslušný kontroler, který 
     * požadavek obslouží.
     */
    public function process($params){
        $parsedUrl = $this->parseURL($params[0]);
        if(empty($parsedUrl)){
            $this->redirectTo("intro");
        }
        $ctrlClass = $this->dash2CamelText(array_shift($parsedUrl)).'Ctrl';
        if(file_exists('controls/'.$ctrlClass.'.php')){
            $this->controler = new $ctrlClass;
        } else {
            if(!$_SESSION['user']){
                $this->redirectTo("login");
            } else {
                $this->redirectTo("error");
            }
        }
        $this->controler->process($parsedUrl);
        $this->data['title'] = $this->controler->header['title'];
        $this->data['description'] = $this->controler->header['description'];
        $this->data['keywords'] = $this->controler->header['keywords'];
        $this->view = "layout";
        $this->data['messages']=$this->getMessages();
    }

    /**
     * Metoda parseURL(), zpracuje zadanou URL, oddělí část s protokolem a doménou a vrátí 
     * pole obsahující části cesty.
     * Např.: http://www.domena.cz/clanek/prvni-clanek
     *          vrátí ('clanek','prvni-clanek')  
     */
    private function parseURL($url) {
        $parsedUrl = parse_url($url);
        $parsedUrl["path"] = ltrim($parsedUrl["path"], "/");
        $parsedUrl["path"] = trim($parsedUrl["path"], "/");
        $splitedPath = explode("/", $parsedUrl["path"]);
        return $splitedPath;
    }

    /**
     * Metoda dash2CamelText() převede text, ve kterém jsou slova oddělaná pomlčkou na řetězec 
     * slov bez mezer, kdy jednotlivá slova začínají velkým písmenem.
     * Např.: toto-je-veta převede na TotoJeVeta.
     */
    private function dash2CamelText($text){
        $sentence = str_replace('-',' ',$text);
        $sentence = ucwords($sentence);
        $sentence = str_replace(' ','',$sentence);
        return $sentence;
    }
}

?>