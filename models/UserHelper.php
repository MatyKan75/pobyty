<?php 

/**
 * 
 */
class UserHelper {
    
    public function getImprint($password){
        $salt = "fd16sdfd2ew#$%";
        return hash("sha256",$password.$salt);
    }

    public function createUser($user_name,$password,$admin){
        $user_id = $this->getUserMaxID()[0]+1;
        $values = array(
            'uid'   => $user_id,
            'jmeno' => $user_name,
            'heslo' => $this->getImprint($password),
            'admin' => $admin,
        );
        DatabaseHelper::insert('uzivatel',$values);
    }

    public function deleteUser($uid){
        DatabaseHelper::delete("uzivatel","uid=$uid");
    }

    public function updateUser($uid,$user_name,$password,$admin){
        $values = array(
            'jmeno' => $user_name,
            'heslo' => $this->getImprint($password),
            'admin' => $admin,
        );
        DatabaseHelper::update('uzivatel',$values,'WHERE uid=?',array($uid,));
    }

    /**
     * Metoda login() provede přihlášení uživatele do aplikace. Pokud je uživatel administrátor, 
     * nastaví příznak o admin přístupu k aplikaci.
     *  $user_name  uživatelské jméno,
     *  $password   heslo uživatele.
     */
    public function login($user_name,$password){
        $user = DatabaseHelper::queryOne(
            'SELECT uid, jmeno, admin, akce FROM uzivatel WHERE jmeno=? AND heslo=?',
            array($user_name,$this->getImprint($password)));
        if(!$user){
            throw new UserError("Neplatné jméno nebo heslo.");
            unset($_SESSION['user']);
        }
        $_SESSION['user']=$user;
        if($user['admin']=='Ano'){
            $_SESSION['admin'] = true;
        } else {
            $_SESSION['admin'] = false;
        }
        return $user['akce'];
    }

    /**
     * Provede odhlášení uživatele z aplikace.
     */
    public function logout(){
        if (isset($_SESSION['user']))
            unset($_SESSION['user']);
    }

    /**
     * Vrátí aktuálně přihlášeného uživatele.
     */
    public function getLoggedInUser(){
        if (isset($_SESSION['user']))
            return $_SESSION['user'];
        return null;
    }

    /**
     * Metoda getUsers() vrátí uživatele registrované v databázi.
     */
    public function getUsers(){
        $users = DatabaseHelper::queryAll('SELECT uid, jmeno, admin FROM uzivatel',array());
        return $users;
    }

    /**
     * Metoda getUser() vrátí záznam o uživateli daného ID.
     *  $uid    identifikátor uživatele.
     */
    public function getUser($uid){
        $user = DatabaseHelper::queryOne('SELECT uid, jmeno, admin FROM uzivatel WHERE uid=?',array($uid,));
        return $user;
    }

    /**
     * Metoda getUserMaxID() vrátí nejvetší ID uživatele.
     */
    public function getUserMaxID(){
        $sql = 'SELECT max(uid) FROM uzivatel';
        $max_id = DatabaseHelper::queryOne($sql,array());
        return $max_id;
    }
}

?>