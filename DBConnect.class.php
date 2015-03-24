<?php
/**
 * DBConnect class
 * Singleton. Simple database connection facade using PDO.
 * Pulls connection info from .ini (in this case named settings.ini)
 *
 * @author     Eric Christenson (EricChristenson.com)
 * @copyright  2015
 * @version    1.1
 * @license    MIT Public License (http://opensource.org/licenses/MIT)
 *
 * @see  DBException
 */
class DBConnect {
    # databse connection info. Customize value as needed
    const SETTINGS_FILE = 'settings.ini';

    # connection instance
    static $connection = null;


    /* enforce singleton */
    public function __construct() { }
    public function __clone() { }


    /**
     * @param   string  $user
     * @return  PDO
     */
    public static function connect($user) {
        if (self::$connection === null) {
            $settings = self::getSettings($user);
            self::$connection = self::getConnection($settings);
        }
        return self::$connection;
    }

    /**
     * @param  string  $new_user
     */
    public static function changeUser($new_user) {
        self::$connection = self::connect($new_user);
    }

    /**
     * Reset $connection to null.
     */
    public static function closeConnection() {
        self::$connection = null;
    }



    //------------------------------ GETTERS ------------------------------//
    /**
     * Loads connection information from .ini file.
     *
     * @param   string  $user
     * @throws  DBException
     * @return  array [string|string]
     */
    private static function getSettings($user) {
        try {
            $settings = parse_ini_file(self::SETTINGS_FILE, true);
            $current_user = $settings[$user];
        } catch (Exception $e) {
            throw new DBException("Could not load settings: {$e->getMessage()}");
        }

        return $current_user;
    }

    /**
     * @param   array [string|strting]  $settings
     * @throws  DBException
     * @return  PDO (database connection)
     */
    private static function getConnection(array $settings) {
        try {
            $connection = new PDO(
                "mysql:host={$settings['host']};dbname={$settings['database']}",
                $settings['username'],
                $settings['password'],
                array(PDO::ATTR_PERSISTENT => true)
            );

            $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new DBException($e->getMessage());
        }

        return $connection;
    }
}

/**
 * Class DBException
 * Custom exception
 *
 * @extends Exception
 */
class DBException extends Exception { }