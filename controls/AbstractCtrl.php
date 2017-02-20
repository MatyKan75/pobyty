<?php
/**
 * Třída AbstractCtrl je společným předkem, pro všechny kontrolery použité ve webové
 * aplikaci.
 */
abstract class AbstractCtrl {

	/**
	 * Pole, kam bude kontroler ukládat data získaná od modelu. 
	 */
	protected $data = array();

	/**
	 * Název pohledu (view), který se má zobrazit.
	 */
    protected $view = "";

	/**
	 * Hlavička stránky (titulek, klíčová slova a popis).
	 */
	protected $header = array('title' => '', 'keywords' => '', 'description' => '');
	
	/**
	 * Metoda process() zajistí zpracování požadavku a parametrů od uživatele.
	 */	
	abstract function process($params);

	/**
	 * Metoda displayView() zajistí zobrazení dat uživateli pomocí pohledu (view).
	 */
	public function displayView() {
		if($this->view){
			extract($this->data);
			require("views/".$this->view.".phtml");
		}
	}

	/**
	 * Metoda redirectTo() přesměruje na zadanou URL adresu.
	 */
	public function redirectTo($url) {
		header("Location: /$url");
        header("Connection: close");
        exit;
	}

	/**
	 * Metoda addMessage() přidá zprávu, která bude zobrazena uživateli. 
	 * Např.: Neplatné heslo a jméno, atd.
	 */
	public function addMessage($msg) {
		if (isset($_SESSION['messages']))
            $_SESSION['messages'][] = $msg;
        else
            $_SESSION['messages'] = array($msg);
	}

	/**
	 * Metoda getMessages() vrátí seznam zpráv evidovaných v aplikaci. Tyto zprávy 
	 * pak považuje za zobrazené a vymaže seznam zpráv.
	 */
	public function getMessages(){
		if (isset($_SESSION['messages'])) {
            $messages = $_SESSION['messages'];
            unset($_SESSION['messages']);
            return $messages;
        }
        else
            return array();
	}

	/**
	 * Metoda checkUser() provede test, zda je uživatel přihlášený. Pokud ne, pak ho 
	 * přesměruje na přihlášení.
	 */
	public function checkUser(){
		$userHelper = new UserHelper();
		$user = $userHelper->getLoggedInUser();
		if(!$user){
			$this->addMessage("Nedostatečná oprávnění.");
			$this->redirectTo("login");
		}
	}
}

?>