<?php
require_once("../functions/functions.php");
require_once("../class/Config.php");
require_once("../class/ConfigType.php");
require_once("../class/Server.php");

$act = $_POST["act"];

switch ($act) {

    case "download_gps_log":

        if (file_exists(GPS_LOG_FILE)) {
            $settings = getKSWEBSettings();
            $file = (GPS_LOG_FILE);
            header("Content-Type: application/octet-stream");
            header("Accept-Ranges: bytes");
            header("Content-Length: " . filesize($file));
            header("Content-Disposition: attachment; filename=log.txt");
            readfile($file);
        }

        break;

    case "restart_server":

        $ourFileName = "/mnt/sdcard/ksweb/tmp/restart";
        $ourFileHandle = fopen($ourFileName, 'w') or die("can't open file");
        fclose($ourFileHandle);
        echo "Command \"server restart\" executed successfully...";
        break;

    case "save_ksweb_settings":

        $settings["enable_root_func"] = trim($_POST["enable_root_func"]);
        $settings["enable_root_func_old"] = trim($_POST["enable_root_func_old"]);
        $settings["is_start_min"] = trim($_POST["is_start_min"]);
        $settings["is_start_min_old"] = trim($_POST["is_start_min_old"]);
        $settings["auto_start"] = trim($_POST["auto_start"]);
        $settings["auto_start_old"] = trim($_POST["auto_start_old"]);
        $settings["move_inis"] = trim($_POST["move_inis"]);
        $settings["move_inis_old"] = trim($_POST["move_inis_old"]);
        $settings["wifiLock"] = trim($_POST["wifiLock"]);
        $settings["wifiLock_old"] = trim($_POST["wifiLock_old"]);

        if (checkSettings($settings)) {

            if ($settings["is_start_min_old"] != $settings["is_start_min"]) {
                echo "Switching KSWEB \"is start minimized\" function...<br>";
                setKSWEBSetting("enableStartMinimized", $settings["is_start_min"], $settings);
            }

            if ($settings["auto_start_old"] != $settings["auto_start"]) {
                echo "Switching KSWEB auto starting...<br>";
                setKSWEBSetting("enableAutoStart", $settings["auto_start"], $settings);
            }

            if ($settings["move_inis_old"] != $settings["move_inis"]) {
                echo "Switching to another group of the INI files...<br>";
                setKSWEBSetting("externalINI", $settings["move_inis"], $settings);
            }

            if ($settings["wifiLock_old"] != $settings["wifiLock"]) {
                echo "Switching Wi-Fi lock...<br>";
                setKSWEBSetting("wifiLock", $settings["wifiLock"], $settings);
            }

            echo "Settings saved!";
        } else {
            echo "<br>There are some errors in settings. Settings were not saved!";
        }

        break;

    case "move_inis_click_handler":

        $is_move_inis = $_POST["move_inis"];
            Config::copyAllConfigFiles();
            Config::copyAllHosts();
        if ($is_move_inis == "true") {
            
        } else {
            
        }
     
        break;
    case "replace_inis_in_sdcard":
        echo "Copying process started...<br>";

        Config::copyConfFiles(true);

        break;

    case "do_not_replace_inis_in_sdcard":

        echo "Copying process started...<br>";

        Config::copyConfFiles(false);

        break;

    case "save_config":

        $configFile = $_POST["configFile"];
        $config_text = $_POST["config_text"];
        
        @unlink(TMP_FILE_CONFIG);
        $fp = fopen(TMP_FILE_CONFIG, "a");
        fwrite($fp, $config_text);
        fclose($fp);
        
        shell_exec("chmod 644 " . TMP_FILE_CONFIG);
        if (Config::testConfig(Config::defineType($configFile), TMP_FILE_CONFIG)) {
            echo "Testing done! All is OK!<br>";
            Config::saveConfig(TMP_FILE_CONFIG, $configFile);
            echo "<script>Materialize.toast('".basename($configFile)." file saved successfully', 4000)</script>";
        } else {
            echo "Testing done! Something wrong!<br>";
        }

        break;

    case "save_system_settings":

        $settings["old_password"] = base64_decode($_POST["old_password"]);
        $settings["new_password"] = base64_decode($_POST["new_password"]);
        $settings["repeat_password"] = base64_decode($_POST["repeat_password"]);

        saveSystemSettings($settings);

        break;
}
?>
