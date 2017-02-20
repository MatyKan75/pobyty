<?php

/**
 * Třída DatabaseHelper je tzv. util class, která poskytuje webové aplikaci připojení k databázi,
 * provádění dotazů a změn v databázi. Je to statická třída, není tedy potřeba vytvářet její 
 * instanci. Po připojení načte číselníky z databáze. 
 */
class DatabaseHelper {
    
    /**
     * Spojení z databází.
     */
    private static $conn;

    
    /**
     * Nastavení PDO.
     */
    private static $options = array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
        PDO::ATTR_EMULATE_PREPARES => false,
        );
    
    /**
     * Metoda connect() vytvoří spojení k databázi.
     */
    public static function connect(){
        $GLOBALS['Logger']->debug("DatabaseHelper::connect() ...");
        $ini_file = fopen("db.ini","r");
        $line = fgets($ini_file);
        setcookie("databaseHost", "", time() - 3600);
        if(!empty($line)){
            list($name,$host,$port,$user,$password,$database) = explode(":",$line);
            if (!isset(self::$conn)) {
                self::$conn = new PDO("mysql:host=$host;dbname=$database;port=$port",$user,$password,self::$options);
                setcookie("databaseHost",$name);
            } else {
                $GLOBALS['Logger']->debug("  unable connect to database.");
            }
        } else {
            $GLOBALS['Logger']->error("  unable read db.ini file.");
        }
        $GLOBALS['Logger']->debug("  Succefully connected.");
    }

    /**
     * Metoda queryAll() provede zadaný sql dotaz a vrátí všechny vyhovující záznamy.
     *  $sql        SQL dotaz,
     *  $params     parametry.
     */
    public static function queryAll($sql,$params=array()) {
        $result = self::$conn->prepare($sql);
        $result->execute($params);
        return $result->fetchAll();
    }

    /**
     * Metoda queryOne() provede zadaný sql dotaz a vrátí pouze první záznam. Tato metoda 
     * se používá zejména pro dotazy typu SELECT count(*), SELECT max(*), atd.
     *  $sql        SQL dotaz,
     *  $params     parametry.
     */
    public static function queryOne($sql,$params=array()) {
        $result = self::$conn->prepare($sql);
        $result->execute($params);
        return $result->fetch();
    }

    /**
     * Metoda query() provede zadaný sql dotaz a vrátí pouze první záznam. Tato metoda 
     * se používá zejména pro dotazy typu SELECT count(*), SELECT max(*), atd.
     *  $sql        SQL dotaz,
     *  $params     parametry.
     */
    public static function query($sql,$params=array()) {
        $result = self::$conn->prepare($sql);
        $result->execute($params);
        return $result->rowCount();
    }

    /**
     * Metoda insert() vloží hodnoty uložené v poli (sloupec,hodnota) do dané tabulky.
     *  $table      název tabulky,
     *  $values     pole obsahující hodnoty (sloupec,hodnota).
     */
    public static function insert($table, $values=array()) {
        $sql = "INSERT INTO $table (".implode(', ', array_keys($values)).") VALUES ("
            .str_repeat('?,', sizeOf($values)-1)."?)";
        $result = self::$conn->prepare($sql);
        $result->execute(array_values($values));
        return $result;
    }

    /**
     * Metoda update() zaktualizuje hodnoty v poli (sloupec,hodnota) do dané tabulky v záznamech 
     * vyhovujících dané podmínce.
     *  $table      název tabulky,
     *  $values     pole změněných hodnot (sloupec, honota),
     *  $where      podmínka
     *  $params     parametry dotazu.
     */
    public static function update($table, $values, $where, $params=array()) {
        $sql = "UPDATE $table SET ".implode(' = ?, ', array_keys($values))." = ? " . $where;
        $result = self::$conn->prepare($sql);
        $result->execute(array_merge(array_values($values), $params));
        return $result;
    }

    /**
     * Metoda delete() odstraní z dané tabulky záznamy vyhovující zadané podmínce.
     *  $table  název tabulky,
     *  $where  podmínka.
     */
    public static function delete($table, $where){
        $sql = "DELETE FROM $table WHERE $where";
        $result = self::$conn->prepare($sql);
        $result->execute();
        return $result;
    }

    /**
     * Metoda getServerInfo() vrátí informace o databazovém serveru (zejména jeho IP adresu).
     */
    public static function getServerInfo(){
        return self::$conn->getAttribute(constant(PDO::ATTR_SERVER_INFO));
    }
}

?>