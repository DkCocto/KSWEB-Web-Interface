<?php

const SERVER_LIGHTTPD_CONF_SDCARD_DIR = "/mnt/sdcard/ksweb/conf/lighttpd";
const SERVER_NGINX_CONF_SDCARD_DIR = "/mnt/sdcard/ksweb/conf/nginx";
const LIGHTTPD_FOLDER_PASS = "/data/data/ru.kslabs.ksweb/components/etc/.pass";
const NGINX_FOLDER_PASS = "/data/data/ru.kslabs.ksweb/components/etc/.pass_nginx";
const APACHE_FOLDER_PASS = "/data/data/ru.kslabs.ksweb/components/etc/.pass_apache";
const ANDROID_VERSION_MARKER = "/data/data/ru.kslabs.ksweb/components/etc/androidVer";
const KSWEB_UTIL_BATTERY_INFO_CMD = "-b";
const KSWEB_UTIL_CPU_INFO_CMD = "-c";
const KSWEB_UTIL_MEM_INFO_CMD = "-m";
const KSWEB_UTIL_WI_FI_INFO_CMD = "-w";
const KSWEB_PREFERENCES_XML_CMD = "/data/data/ru.kslabs.ksweb/shared_prefs/ru.kslabs.ksweb_preferences.xml";
const TMP_FILE_CONFIG = "/data/data/ru.kslabs.ksweb/tmp/tempConfig.ini";
const VERSION = "2.11";

function showLighttpdConfigHref() {
    $config = new Config(ConfigType::SERVER_LIGHTTPD);
?>
        <span class="card-title">Lighttpd config</span>
        <p>
        <?php
    echo "<a class='blue-text text-darken-4' href = '?page=4&server=" . Server::LIGHTTPD . "'>Config file: " . $config->getConfigFullPath() . "</a><br>"; ?>
    </p><br>
    <?php
}

function showNginxConfigHref() {
    $config = new Config(ConfigType::SERVER_NGINX);
?>
        <span class="card-title">Nginx config</span>
        <p>
        <?php
    echo "<a class='blue-text text-darken-4' href = '?page=4&server=" . Server::NGINX . "'>Config file: " . $config->getConfigFullPath() . "</a><br>";
?>
    </p><br>
    <?php
}?>

<?php function showApacheConfigHref() { ?>
    <?php $config = new Config(ConfigType::SERVER_APACHE); ?>
    <span class="card-title">Apache config</span>
    <p>
        <?= "<a class='blue-text text-darken-4' href = '?page=4&server=" . Server::APACHE . "'>Config file: " . $config->getConfigFullPath() . "</a><br>"; ?>
    </p>
    <br>
<?php } ?>

<?php function showHostListLighttpd() {
?>
        <span class="card-title">Lighttpd host files list</span>
        <p>
        <?php
    $settings = getKSWEBSettings();
    $move_inis = $settings["move_inis"];
    $f = @scandir(($move_inis == "true") ? Config::SERVER_LIGHTTPD_CONF_SDCARD_DIR : Config::SERVER_LIGHTTPD_CONF_DIR);
    if ($f != false) {
        foreach($f as $file) {
            if (preg_match('/\_(host)/', $file)) {
                echo "<a class='blue-text text-darken-4' href = '?page=4&server=" . Server::LIGHTTPD . "&hostFile=$file'>Open host: " . $file . "</a><br/>";
            }
        }
    }

?>
    </p>
    <?php
}

function showHostListNginx() {
?>
        <span class="card-title">Nginx host files list</span>
        <p>
        <?php
    $settings = getKSWEBSettings();
    $move_inis = $settings["move_inis"];
    $f = scandir(($move_inis == "true") ? Config::SERVER_NGINX_CONF_SDCARD_DIR : Config::SERVER_NGINX_CONF_DIR);
    if ($f != false) {
        foreach($f as $file) {
            if (preg_match('/\_(host)/', $file)) {
                echo "<a class='blue-text text-darken-4' href = '?page=4&server=" . Server::NGINX . "&hostFile=$file'>Open host: " . $file . "</a><br/>";
            }
        }
    }

?>
    </p>
    <?php
}?>

<?php function showHostListApache() { ?>
    <span class="card-title">Apache host files list</span>
    <p>
        <?php
        $settings = getKSWEBSettings();
        $move_inis = $settings["move_inis"];
        $f = scandir(($move_inis == "true") ? Config::SERVER_APACHE_CONF_SDCARD_DIR : Config::SERVER_APACHE_CONF_DIR);
        if ($f != false) {
            foreach($f as $file) {
                if (preg_match('/\_(host)/', $file)) {
                    echo "<a class='blue-text text-darken-4' href = '?page=4&server=" . Server::APACHE . "&hostFile=$file'>Open host: " . $file . "</a><br/>";
                }
            }
        }
        ?>
    </p>
    <?php
}

function getBatteryInfo() {
    $output = shell_exec(getKSWEBUtilFilePath() . " " . KSWEB_UTIL_BATTERY_INFO_CMD);
    $data_array = explode(';', $output);
    $batt_array["capacity"] = $data_array[0];
    $batt_array["voltage"] = ($data_array[1] == "ERROR") ? "ERROR" : $data_array[1][0] . "." . $data_array[1][1] . $data_array[1][2] . $data_array[1][3];
    $batt_array["status"] = $data_array[2];
    $batt_array["temp"] = ($data_array[3] == "ERROR") ? "ERROR" : $data_array[3][0] . $data_array[3][1];
    $batt_array["health"] = $data_array[4];
    return $batt_array;
}

function getCPUInfo() {
    $f = fopen("/proc/stat", "r");
    $line = fgets($f);
    fclose($f);
    $toks = explode(" ", $line);
    $idle1 = floatval($toks[5]);
    $cpu1 = floatval($toks[2]) + floatval($toks[3]) + floatval($toks[4]) + floatval($toks[6]) + floatval($toks[7]) + floatval($toks[8]);
    sleep(1);
    $f = fopen("/proc/stat", "r");
    $line = fgets($f);
    fclose($f);
    $toks = explode(" ", $line);
    $idle2 = floatval($toks[5]);
    $cpu2 = floatval($toks[2]) + floatval($toks[3]) + floatval($toks[4]) + floatval($toks[6]) + floatval($toks[7]) + floatval($toks[8]);
    $output = shell_exec(getKSWEBUtilFilePath() . " " . KSWEB_UTIL_CPU_INFO_CMD);
    $data_array = explode("\n", $output);
    $processor_info = explode(":", preg_replace('/ {2,}/', ' ', trim($data_array[1])));
    if ((($cpu2 + $idle2) - ($cpu1 + $idle1)) != 0) {
        $cpu["usage"] = floor(100 * ($cpu2 - $cpu1) / (($cpu2 + $idle2) - ($cpu1 + $idle1)));
    }
    else {
        $cpu["usage"] = "N/A";
    }

    $cpu["name"] = $processor_info[1];
    return $cpu;
}

function getMemInfo() {
    $output = shell_exec(getKSWEBUtilFilePath() . " " . KSWEB_UTIL_MEM_INFO_CMD);
    $output = preg_replace('/ {2,}/', ' ', $output);
    $data_array = explode(' ', $output);
    $mem_array["total"] = $data_array[1];
    $mem_array["free"] = $data_array[3];
    $mem_array["filled"] = $data_array[1] - $data_array[3];
    return $mem_array;
}

function getWIFIInfo() {
    $output = shell_exec(getKSWEBUtilFilePath() . " " . KSWEB_UTIL_WI_FI_INFO_CMD);
    $output = preg_replace('/ {2,}/', ' ', trim($output));
    $data_array = explode(' ', $output);
    $wifi_array["quality"] = str_replace(".", "", $data_array[2]);
    $wifi_array["discarded_packets"] = str_replace(".", "", $data_array[3]);
    $wifi_array["missed_packets"] = str_replace(".", "", $data_array[4]);
    return $wifi_array;
}

function getServerInfo() {
    if (Server::LIGHTTPD == 0) {
    }
}

/*Server Version: Apache (compiled for KSWEB)/2.4.28 (Unix) mod_fastcgi/mod_fastcgi-SNAP-0910052141
Server MPM: prefork
Server Built: Nov 1 2017 14:56:45
Current Time: Monday, 04-Dec-2017 13:05:00 MSK
Restart Time: Monday, 04-Dec-2017 12:50:38 MSK
Parent Server Config. Generation: 1
Parent Server MPM Generation: 0
Server uptime: 14 minutes 22 seconds
Server load: -1.00 -1.00 -1.00
Total accesses: 251 - Total Traffic: 1020 kB
CPU Usage: u2.22 s4.08 cu0 cs0 - .731% CPU load
.291 requests/sec - 1211 B/second - 4161 B/request
2 requests currently being processed, 3 idle workers*/


function getServerInfoApache() {
    $serverInfo = array();
    $authInfo = getAuthInfoApache();
    $context = stream_context_create(array(
        'http' => array(
            'header' => "Authorization: Basic " . base64_encode($authInfo["login"] . ":" . $authInfo["password"])
        )
    ));
    $html = file_get_contents("http://" . str_replace("localhost", "127.0.0.1", $_SERVER["HTTP_HOST"]) . "/server-status", false, $context);

    $dom = new DOMDocument;
    $dom->loadHTML($html);
    $dt = $dom->getElementsByTagName('dt');
    foreach($dt as $dt) {

        if (strpos($dt->nodeValue, 'Server Version: Apache') !== false) {
            $serverInfo["serverVersion"] = $dt->nodeValue;
        }

        if (strpos($dt->nodeValue, 'Server uptime') !== false) {
            $arr = explode(": ", $dt->nodeValue);
            $serverInfo["uptime"] = $arr;
        }
        if (strpos($dt->nodeValue, 'Server load') !== false) {
            $arr = explode(": ", $dt->nodeValue);
            $serverInfo["load"] = $arr[1];
        }
        if (strpos($dt->nodeValue, 'Total accesses') !== false) {
            $serverInfo["totalAccesses"] = $dt->nodeValue;
            $serverInfo["totalAccesses"] = str_replace("Total accesses:","<b>Total accesses:</b>", $serverInfo["totalAccesses"]);
            $serverInfo["totalAccesses"] = str_replace("Total Traffic:","<b>Total Traffic:</b>", $serverInfo["totalAccesses"]);
        }
        if (strpos($dt->nodeValue, 'CPU Usage') !== false) {
            $arr = explode(": ", $dt->nodeValue);
            $serverInfo["cpuUsage"] = $arr[1];
        }
        if (strpos($dt->nodeValue, 'requests/sec') !== false) {
            $parts = explode(" - ", $dt->nodeValue);
            $serverInfo["traffic"] = $parts[0]."<br>".$parts[1]."<br>".$parts[2];
        }
    }
    return $serverInfo;

}

function getServerInfoLighttpd() {
    $serverInfo = array();
    $authInfo = getAuthInfoLighttpd();
    $context = stream_context_create(array(
        'http' => array(
            'header' => "Authorization: Basic " . base64_encode($authInfo["login"] . ":" . $authInfo["password"])
        )
    ));
    $html = file_get_contents("http://" . str_replace("localhost", "127.0.0.1", $_SERVER["HTTP_HOST"]) . "/server-status", false, $context);
    $dom = new DOMDocument;
    $dom->loadHTML($html);
    $tds = $dom->getElementsByTagName('td');
    $count = 0;
    foreach($tds as $td) {
        if ($td->getAttribute("class") == "string") {
            switch ($count) {
            case 0:
                $serverInfo["hostname"] = $td->nodeValue;
                break;

            case 1:
                $serverInfo["uptime"] = $td->nodeValue;
                break;

            case 2:
                $serverInfo["started_at"] = $td->nodeValue;
                break;

            case 3:
                $serverInfo["requests"] = $td->nodeValue;
                break;

            case 4:
                $serverInfo["traffic"] = $td->nodeValue;
                break;

            case 5:
                $serverInfo["requests_avr"] = $td->nodeValue;
                break;

            case 6:
                $serverInfo["traffic_avr"] = $td->nodeValue;
                break;
            }

            $count++;
        }
    }

    return $serverInfo;
}

function getServerInfoNginx() {
    $serverInfo = array();
    $authInfo = getAuthInfoNginx();
    $context = stream_context_create(array(
        'http' => array(
            'header' => "Authorization: Basic " . base64_encode($authInfo["login"] . ":" . $authInfo["password"])
        )
    ));
    $html = file_get_contents("http://" . str_replace("localhost", "127.0.0.1", $_SERVER["HTTP_HOST"]) . "/nginx_status", false, $context);
    $array = explode(' ', $html);
    $serverInfo["activeConnections"] = $array[2];
    $serverInfo["accepts"] = $array[7];
    $serverInfo["handled"] = $array[8];
    $serverInfo["requests"] = $array[9];
    $serverInfo["reading"] = $array[11];
    $serverInfo["writing"] = $array[13];
    $serverInfo["waiting"] = $array[15];
    return $serverInfo;
}

function getFullRootAddress() {
    return "http://" . str_replace("localhost", "127.0.0.1", $_SERVER["HTTP_HOST"]) . "/";
}

function getKSWEBSettings()
{
    $settings = array();
    $xml = file_get_contents(KSWEB_PREFERENCES_XML_CMD);
    $dom = new DOMDocument;
    $dom->loadXML($xml);
    $map = $dom->getElementsByTagName('string');
    $map = $dom->getElementsByTagName('boolean');
    foreach($map as $m) {
        if ($m->getAttribute("name") == "enableStartMinimized") $settings["is_start_min"] = $m->getAttribute("value");
        if ($m->getAttribute("name") == "enableAutoStart") $settings["auto_start"] = $m->getAttribute("value");
        if ($m->getAttribute("name") == "externalINI") $settings["move_inis"] = $m->getAttribute("value");
        if ($m->getAttribute("name") == "allowRoot") $settings["enable_root_func"] = $m->getAttribute("value");
        if ($m->getAttribute("name") == "wifiLock") $settings["wifiLock"] = $m->getAttribute("value");
    }
    return $settings;
}

function setKSWEBSetting($key, $value, $settings) {
    $settingsFileName = KSWEB_PREFERENCES_XML_CMD;
    $xml = file_get_contents($settingsFileName);
    $dom = new DOMDocument;
    $dom->formatOutput = true;
    $dom->loadXML($xml);
    $map = $dom->getElementsByTagName('string');
    foreach($map as $m) {
        if ($m->getAttribute("name") == $key) $m->nodeValue = $value;
    }

    $map = $dom->getElementsByTagName('boolean');
    foreach($map as $m) {
        if ($m->getAttribute("name") == $key) $m->setAttribute("value", $value);
    }

    unlink($settingsFileName);
    $dom->save($settingsFileName);
}

function checkSettings($settings) {
    return true;
}

function getAuthInfoLighttpd() {
    $authInfo = array();
    $file_handle = @fopen(LIGHTTPD_FOLDER_PASS, "r");
    if ($file_handle) {
        while (!feof($file_handle)) {
            $line = fgets($file_handle);
            $array = explode(':', $line);
            $authInfo["login"] = $array[0];
            $authInfo["password"] = $array[1];
            return $authInfo;
        }
    }
}

function getAuthInfoNginx() {
    $authInfo = array();
    $file_handle = @fopen(NGINX_FOLDER_PASS, "r");
    if ($file_handle) {
        while (!feof($file_handle)) {
            $line = fgets($file_handle);
            $array = explode(':{PLAIN}', $line);
            $authInfo["login"] = $array[0];
            $authInfo["password"] = $array[1];
            return $authInfo;
        }
    }
}

function getAuthInfoApache() {
    $authInfo = array();
    $file_handle = @fopen(APACHE_FOLDER_PASS, "r");
    if ($file_handle) {
        while (!feof($file_handle)) {
            $line = fgets($file_handle);
            $array = explode(':', $line);
            $authInfo["login"] = $array[0];
            $authInfo["password"] = $array[1];
            return $authInfo;
        }
    }
}

function saveSystemSettings($settings) {
    if (!empty($settings["new_password"])) {
        if ($settings["new_password"] == $settings["confirm_password"]) {
            $authInfo = NULL;
            $serverType = getServerType();
            if ($serverType == Server::LIGHTTPD) $authInfo = getAuthInfoLighttpd();
            if ($serverType == Server::NGINX) $authInfo = getAuthInfoNginx();
            if ($serverType == Server::APACHE) $authInfo = getAuthInfoApache();
			
            if ($settings["current_password"] == $authInfo["password"]) {
                $password = $settings["new_password"];

                $serverType = getServerType();
                if ($serverType == Server::LIGHTTPD ) {
                    unlink(LIGHTTPD_FOLDER_PASS);
                    $fp = fopen(LIGHTTPD_FOLDER_PASS, "a");
                    fwrite($fp, "admin:$password");
                    fclose($fp);
                }

                if ($serverType == Server::APACHE) {
                    unlink(APACHE_FOLDER_PASS);
                    $fp = fopen(APACHE_FOLDER_PASS, "a");
                    fwrite($fp, "admin:$password");
                    fclose($fp);
                }

                if ($serverType == Server::NGINX) {
                    unlink(NGINX_FOLDER_PASS);
                    $fp = fopen(NGINX_FOLDER_PASS, "a");
                    fwrite($fp, "admin:{PLAIN}$password");
                    fclose($fp);
                }

				echo "<script>Materialize.toast('Your password has been changed.', 4000);</script>";
            } else {
                echo "<script>Materialize.toast('The current password you\'ve entered is incorrect. Please enter a different password.', 4000);</script>";
            }
        } else {
            echo "<script>Materialize.toast('New password must be confirmed correctly.', 4000);</script>";
        }
    }
    else {
        echo "<script>Materialize.toast('You must enter a new password in order to change it.', 4000);</script>";
    }
}

/* function savePassword($password) {
    $serverType = getServerType();
    if ($serverType == Server::LIGHTTPD) {
        unlink(LIGHTTPD_FOLDER_PASS);
        $fp = fopen(LIGHTTPD_FOLDER_PASS, "a");
        fwrite($fp, "admin:$password");
        fclose($fp);
    }

    if ($serverType == Server::NGINX) {
        unlink(NGINX_FOLDER_PASS);
        $fp = fopen(NGINX_FOLDER_PASS, "a");
        fwrite($fp, "admin:{PLAIN}$password");
        fclose($fp);
    }
    echo "Your password has been changed.";
} */

function getServerType() {
    $serverSoftware = $_SERVER["SERVER_SOFTWARE"];
    if (strpos($serverSoftware, 'lighttpd') !== false) {
        return Server::LIGHTTPD;
    }

    if (strpos($serverSoftware, 'nginx') !== false) {
        return Server::NGINX;
    }

    if (strpos($serverSoftware, 'Apache') !== false) {
        return Server::APACHE;
    }
}

function isNginxInstalled() {
    return file_exists(Config::NGINX_BIN_PATH);
}

function getKSWEBUtilFilePath() {
    $KSWEB_UTIL = "/data/data/ru.kslabs.ksweb/components/bin/ksweb-util";
    $KSWEB_UTIL_PIE = "/data/data/ru.kslabs.ksweb/components/bin/ksweb-util-pie";
    if (getAndroidVersion() >= 20) {
        return $KSWEB_UTIL_PIE;
    }
    else return $KSWEB_UTIL;
}

function getAndroidVersion() {
    $file_handle = @fopen(ANDROID_VERSION_MARKER, "r");
    if ($file_handle) {
        while (!feof($file_handle)) {
            $line = fgets($file_handle);
            return $line;
        }
    }
}

?>