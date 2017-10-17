<?php

class Config {
    const SDCARD_CONF_PATH                  = "/mnt/sdcard/ksweb/conf";

    const PHP_CONF_PATH                     = "/data/data/ru.kslabs.ksweb/components/php/conf/php.ini";
    const MYSQL_CONF_PATH                   = "/data/data/ru.kslabs.ksweb/components/mysql/conf/my.ini";

    const SERVER_LIGHTTPD_CONF_PATH         = "/data/data/ru.kslabs.ksweb/components/lighttpd/conf/lighttpd.conf";
    const SERVER_LIGHTTPD_CONF_DIR          = "/data/data/ru.kslabs.ksweb/components/lighttpd/conf";

    const SERVER_NGINX_CONF_PATH            = "/data/data/ru.kslabs.ksweb/components/nginx/conf/nginx.conf";
    const SERVER_NGINX_CONF_DIR             = "/data/data/ru.kslabs.ksweb/components/nginx/conf";

    const PHP_CONF_SDCARD_PATH              = "/mnt/sdcard/ksweb/conf/php/php.ini";
    const MYSQL_CONF_SDCARD_PATH            = "/mnt/sdcard/ksweb/conf/mysql/my.ini";
    const SERVER_LIGHTTPD_CONF_SDCARD_PATH  = "/mnt/sdcard/ksweb/conf/lighttpd/lighttpd.conf";
    const SERVER_LIGHTTPD_CONF_SDCARD_DIR   = "/mnt/sdcard/ksweb/conf/lighttpd";

    const SERVER_NGINX_CONF_SDCARD_PATH     = "/mnt/sdcard/ksweb/conf/nginx/nginx.conf";
    const SERVER_NGINX_CONF_SDCARD_DIR      = "/mnt/sdcard/ksweb/conf/nginx";

    const MYSQL_LANGUAGE_FILE_PATH          = "/data/data/ru.kslabs.ksweb/components/mysql/sbin/share/mysql/english";

    const LIGHTTPD_BIN_PATH                 = "/data/data/ru.kslabs.ksweb/components/lighttpd/sbin/lighttpd";
    const NGINX_BIN_PATH                    = "/data/data/ru.kslabs.ksweb/components/nginx/sbin/nginx";
	const NGINX_LIB_PATH                    = "/data/data/ru.kslabs.ksweb/components/nginx/lib";
    const MYSQLD_BIN_PATH                   = "/data/data/ru.kslabs.ksweb/components/mysql/sbin/mysqld";

    private $currentConfig;
    private $type;

    public function __construct($configType) {

        $settings = getKSWEBSettings();
        $move_inis = $settings["move_inis"];

        if ($configType == ConfigType::SERVER_LIGHTTPD) {
            $this->currentConfig = ($move_inis == "true") ? Config::SERVER_LIGHTTPD_CONF_SDCARD_PATH : Config::SERVER_LIGHTTPD_CONF_PATH;
        }

        if ($configType == ConfigType::SERVER_NGINX) {
            $this->currentConfig = ($move_inis == "true") ? Config::SERVER_NGINX_CONF_SDCARD_PATH : Config::SERVER_NGINX_CONF_PATH;
        }

        if ($configType == ConfigType::PHP) {
            $this->currentConfig = ($move_inis == "true") ? Config::PHP_CONF_SDCARD_PATH : Config::PHP_CONF_PATH;
        }

        if ($configType == ConfigType::MYSQL) {
            $this->currentConfig = ($move_inis == "true") ? Config::MYSQL_CONF_SDCARD_PATH : Config::MYSQL_CONF_PATH;
        }

        $this->type = $configType;
    }

    public function getConfigFullPath() {
        return $this->currentConfig;
    }

    public function getConfigFileName() {
        echo basename($this->currentConfig);
    }

    public static function testConfig($configType, $configFile) {
        if ($configType == ConfigType::SERVER_LIGHTTPD) {
            $output = str_replace("\n", "<br>", shell_exec(Config::LIGHTTPD_BIN_PATH . " -t -f ".$configFile." 2>&1"));
            echo "============<br>Testing config...<br>" . $output . "============<br>";
            if (strpos($output, "yntax OK"))
                return true;
        }

        if ($configType == ConfigType::PHP) {
            return true;
        }

        if ($configType == ConfigType::MYSQL) {
            $output = str_replace("\n", "<br>", shell_exec(Config::MYSQLD_BIN_PATH . " --defaults-file=".$configFile." --lc-messages-dir=".Config::MYSQL_LANGUAGE_FILE_PATH." --help --verbose 2>&1 1>/dev/null")); //1>/dev/null
            echo "============<br>Testing config...<br>" . $output . "============<br>";
            if (strpos($output, "[ERROR]")) {
                return false;
            } else
                return true;
        }
        if ($configType == ConfigType::SERVER_NGINX) {
            $output = str_replace("\n", "<br>", shell_exec("LD_LIBRARY_PATH=" . Config::NGINX_LIB_PATH . " " . Config::NGINX_BIN_PATH . " -t -c ".$configFile." 2>&1"));
            echo "============<br>Testing config...<br>" . $output . "============<br>";
            if (strpos($output, "syntax is ok")) return true;
        }
        if ($configType == -1) {
            return true;
        }
        return false;
    }

    public static function defineType($configFile) {
        if ($configFile == Config::PHP_CONF_PATH || $configFile == Config::PHP_CONF_SDCARD_PATH) {
            return ConfigType::PHP;
        }
        if ($configFile == Config::MYSQL_CONF_PATH || $configFile == Config::MYSQL_CONF_SDCARD_PATH) {
            return ConfigType::MYSQL;
        }
        if ($configFile == Config::SERVER_LIGHTTPD_CONF_PATH || $configFile == Config::SERVER_LIGHTTPD_CONF_SDCARD_PATH) {
            return ConfigType::SERVER_LIGHTTPD;
        }
        if ($configFile == Config::SERVER_NGINX_CONF_PATH || $configFile == Config::SERVER_NGINX_CONF_SDCARD_PATH) {
            return ConfigType::SERVER_NGINX;
        }
        return -1;
    }

    public function getConfigFileContent() {
        return file_get_contents($this->currentConfig);
    }

    public function save($tmpConfigFile) {
        @unlink($this->getFullConfigPath());
        copy($tmpConfigFile, $this->getFullConfigPath());
        shell_exec("chmod 644 ".$this->getFullConfigPath());
        echo "Saved file: ".$this->getFullConfigPath();
    }

    public static function saveConfig($tmpConfigFile, $configFile) {
        @unlink($configFile);
        copy($tmpConfigFile, $configFile);
        shell_exec("chmod 644 ".$configFile);
        echo "Saved file: ".$configFile;
    }

    /*public static function copyConfFiles($isReplace) {
        //php.ini
        @mkdir(Config::SDCARD_CONF_PATH);
        if (!file_exists(Config::PHP_CONF_SDCARD_PATH) || $isReplace) {
            if (@unlink(Config::PHP_CONF_SDCARD_PATH)) {
                echo "Deleted file: " . Config::PHP_CONF_SDCARD_PATH . "<br>";
            }
            if (copy(Config::PHP_CONF_PATH, Config::PHP_CONF_SDCARD_PATH)) {
                echo "Copied file: " . Config::PHP_CONF_PATH . " -> " . Config::PHP_CONF_SDCARD_PATH . "<br>";
            } else {
                echo "Error copying file: " . Config::PHP_CONF_PATH . " -> " . Config::PHP_CONF_SDCARD_PATH . "<br>";
            }
        }
        //---
        //my.ini
        if (!file_exists(Config::MYSQL_CONF_SDCARD_PATH) || $isReplace) {
            if (@unlink(Config::MYSQL_CONF_SDCARD_PATH)) {
                echo "Deleted file: " . Config::MYSQL_CONF_SDCARD_PATH . "<br>";
            }
            if (copy(Config::MYSQL_CONF_PATH, Config::MYSQL_CONF_SDCARD_PATH)) {
                echo "Copied file: " . Config::MYSQL_CONF_PATH . " -> " . Config::MYSQL_CONF_SDCARD_PATH . "<br>";
            } else {
                echo "Error copying file: " . Config::MYSQL_CONF_PATH . " -> " . Config::MYSQL_CONF_SDCARD_PATH . "<br>";
            }
        }
        //---
        //lighttpd.conf
        if (!file_exists(Config::SERVER_LIGHTTPD_CONF_SDCARD_PATH) || $isReplace) {
            if (@unlink(Config::SERVER_LIGHTTPD_CONF_SDCARD_PATH)) {
                echo "Deleted file: " . Config::SERVER_LIGHTTPD_CONF_SDCARD_PATH . "<br>";
            }
            if (copy(Config::SERVER_LIGHTTPD_CONF_PATH, Config::SERVER_LIGHTTPD_CONF_SDCARD_PATH)) {
                echo "Copied file: " . Config::SERVER_LIGHTTPD_CONF_PATH . " -> " . Config::SERVER_LIGHTTPD_CONF_SDCARD_PATH . "<br>";
            } else {
                echo "Error copying file: " . Config::SERVER_LIGHTTPD_CONF_PATH . " -> " . Config::SERVER_LIGHTTPD_CONF_SDCARD_PATH . "<br>";
            }
        }
        //---
        //---
        //nginx.conf
        if (isNginxInstalled()) {
            if (!file_exists(Config::SERVER_NGINX_CONF_SDCARD_PATH) || $isReplace) {
                if (@unlink(Config::SERVER_NGINX_CONF_SDCARD_PATH)) {
                    echo "Deleted file: " . Config::SERVER_NGINX_CONF_SDCARD_PATH . "<br>";
                }
                if (copy(Config::SERVER_NGINX_CONF_PATH, Config::SERVER_NGINX_CONF_SDCARD_PATH)) {
                    echo "Copied file: " . Config::SERVER_NGINX_CONF_PATH . " -> " . Config::SERVER_NGINX_CONF_SDCARD_PATH . "<br>";
                } else {
                    echo "Error copying file: " . Config::SERVER_NGINX_CONF_PATH . " -> " . Config::SERVER_NGINX_CONF_SDCARD_PATH . "<br>";
                }
            }
        }
        //---
        echo "Done!<br>";
    }*/

    public static function copyAllConfigFiles() {
        //php.ini
        @mkdir(Config::SDCARD_CONF_PATH);
		$result = "";
        if (copy(Config::PHP_CONF_PATH, Config::PHP_CONF_SDCARD_PATH)) {
            $result .= "Copied file: " . Config::PHP_CONF_PATH . " -> " . Config::PHP_CONF_SDCARD_PATH . "<br>";
        } else {
            $result .= "Error copying file: " . Config::PHP_CONF_PATH . " -> " . Config::PHP_CONF_SDCARD_PATH . "<br>";
        }
        //---
        //my.ini
        if (copy(Config::MYSQL_CONF_PATH, Config::MYSQL_CONF_SDCARD_PATH)) {
            $result .= "Copied file: " . Config::MYSQL_CONF_PATH . " -> " . Config::MYSQL_CONF_SDCARD_PATH . "<br>";
        } else {
            $result .= "Error copying file: " . Config::MYSQL_CONF_PATH . " -> " . Config::MYSQL_CONF_SDCARD_PATH . "<br>";
        }
        //---
        //lighttpd.conf
        if (copy(Config::SERVER_LIGHTTPD_CONF_PATH, Config::SERVER_LIGHTTPD_CONF_SDCARD_PATH)) {
            $result .= "Copied file: " . Config::SERVER_LIGHTTPD_CONF_PATH . " -> " . Config::SERVER_LIGHTTPD_CONF_SDCARD_PATH . "<br>";
        } else {
            $result .= "Error copying file: " . Config::SERVER_LIGHTTPD_CONF_PATH . " -> " . Config::SERVER_LIGHTTPD_CONF_SDCARD_PATH . "<br>";
        }
        //---
        //---
        //nginx.conf
        if (isNginxInstalled()) {
            if (copy(Config::SERVER_NGINX_CONF_PATH, Config::SERVER_NGINX_CONF_SDCARD_PATH)) {
                $result .= "Copied file: " . Config::SERVER_NGINX_CONF_PATH . " -> " . Config::SERVER_NGINX_CONF_SDCARD_PATH . "<br>";
            } else {
                $result .= "Error copying file: " . Config::SERVER_NGINX_CONF_PATH . " -> " . Config::SERVER_NGINX_CONF_SDCARD_PATH . "<br>";
            }
        }
		return $result;
        //---
        //echo "Done!<br>";
    }

    public static function copyAllHosts() {
        Config::deleteAllHostsFromSDLighttpd();
		
		$result = "";
		
        foreach (glob(Config::SERVER_LIGHTTPD_CONF_DIR."/*_host.conf") as $file) {

            if (copy($file, Config::SERVER_LIGHTTPD_CONF_SDCARD_DIR ."/". basename($file))) {
                $result .= "Copied file: " . $file . " -> " . Config::SERVER_LIGHTTPD_CONF_SDCARD_DIR ."/". basename($file) . "<br>";
            } else {
                $result .= "Error copying file: " . $file . " -> " . Config::SERVER_LIGHTTPD_CONF_SDCARD_DIR ."/". basename($file) . "<br>";
            }

        }

        if (isNginxInstalled()) {
            Config::deleteAllHostsFromSDNginx();
            foreach (glob(Config::SERVER_NGINX_CONF_DIR."/*_host.conf") as $file) {

                if (copy($file, Config::SERVER_NGINX_CONF_SDCARD_DIR ."/". basename($file))) {
                    $result .= "Copied file: " . $file . " -> " . Config::SERVER_NGINX_CONF_SDCARD_DIR  ."/". basename($file) . "<br>";
                } else {
                    $result .= "Error copying file: " . $file . " -> " . Config::SERVER_NGINX_CONF_SDCARD_DIR  ."/". basename($file) . "<br>";
                }

            }

        }
		return $result;
	}

    public static function deleteAllHostsFromSDLighttpd() {
        foreach (glob(Config::SERVER_LIGHTTPD_CONF_SDCARD_DIR."/*_host.conf") as $file) {
            unlink($file);
        }
    }

    public static function deleteAllHostsFromSDNginx() {
        foreach (glob(Config::SERVER_NGINX_CONF_SDCARD_DIR."/*_host.conf") as $file) {
            unlink($file);
        }
    }
}

?>