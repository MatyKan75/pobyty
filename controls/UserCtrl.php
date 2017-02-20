<?php

/**
 * Třída SettingsCtrl umožňuje upravovat nastavení aplikace.
 */
class UserCtrl extends AbstractCtrl {
    
    /**
     * Metoda process() zpracuje požadavek uživatele na nastavení chování aplikace.
     */
    public function process($params){
        $this->checkUser();
        $userHelper = new UserHelper();
        $new_user = true;
        if(empty($params[0])){
            $this->redirectTo("error");
        } else {
            if($params[0]=='create'){
                $this->data['uid']=$userHelper->getUserMaxID();
                $this->data['jmeno']='';
                $this->data['admin']='';
            } else {
                $new_user = false;
                $user = $userHelper->getUser($params[0]);
                $this->data['uid']=$user['uid'][0];
                $this->data['jmeno']=$user['jmeno'];
                $this->data['admin']=$user['admin'];
            }
        }
        if($_POST){
            if($_POST['Action']=='Delete'){
                $userHelper->deleteUser($this->data['uid']);
                $this->addMessage("Uživatel <b>".$this->data['jmeno']."</b> byl odstraněn.");
                $this->redirectTo('config');
            } else {
                if($new_user){
                    $userHelper->createUser($_POST['user'],$_POST['password'],($_POST['admin'])?'Ano':'Ne');
                    $this->addMessage("Nový uživatel byl vytvořen.");
                    $this->redirectTo('config');
                } else {
                    $userHelper->updateUser($this->data['uid'],$_POST['user'],$_POST['password'],($_POST['admin'])?'Ano':'Ne');
                    $this->addMessage("Změny informací o uživateli byly uloženy.");
                    $this->redirectTo('config');
                }
            }
        }
        $this->view = "user";
        $_SESSION['current-page']="config";
    }
}

?>