<?php
require_once("../functions/functions.php");
require_once("../class/Config.php");
require_once("../class/ConfigType.php");
require_once("../class/Server.php");

$act = $_POST["act"];

switch ($act) {

    case "get_cpu_load":
		$cpuInfo = getCPUInfo();
		echo $cpuInfo["usage"];
        break;

    case "restart_server":

        $ourFileName = "/mnt/sdcard/ksweb/tmp/restart";
        $ourFileHandle = fopen($ourFileName, 'w') or die("Can't create server restart marker on sdcard!");
        fclose($ourFileHandle);
        echo "<script type=\"text/javascript\">Materialize.toast('Server restarted.', 4000);</script>";
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
		
		if ($settings["is_start_min_old"] != $settings["is_start_min"]) {
			setKSWEBSetting("enableStartMinimized", $settings["is_start_min"], $settings);
		}

		if ($settings["auto_start_old"] != $settings["auto_start"]) {
			setKSWEBSetting("enableAutoStart", $settings["auto_start"], $settings);
		}

		if ($settings["move_inis_old"] != $settings["move_inis"]) {
			if($settings["move_inis"] == "true") {
				$output = Config::copyAllConfigFiles();
				$output .= Config::copyAllHosts();
				echo $output;
			}
			setKSWEBSetting("externalINI", $settings["move_inis"], $settings);
		}

		if ($settings["wifiLock_old"] != $settings["wifiLock"]) {
			setKSWEBSetting("wifiLock", $settings["wifiLock"], $settings);
		}

        break;

    case "save_config":

        $configFile = $_POST["configFile"];
        $config_text = $_POST["config_text"];
        
        unlink(TMP_FILE_CONFIG);
        $fp = fopen(TMP_FILE_CONFIG, "a");
        fwrite($fp, $config_text);
        fclose($fp);

        shell_exec("chmod 644 " . TMP_FILE_CONFIG);
        if (Config::testConfig(Config::defineType($configFile), TMP_FILE_CONFIG)) {
            echo "Testing done! All is OK!<br>";
            Config::saveConfig(TMP_FILE_CONFIG, $configFile);
            echo "<script>Materialize.toast('".basename($configFile)." saved', 4000);
                $('#result').removeClass('card-panel red darken-1 white-text');
                $('#result').addClass('card-panel green darken-1 white-text').fadeIn(1500).delay(5000).fadeOut(1500);
                </script>";
        } else {
            echo "Testing done! Something wrong!<br>";
            echo "<script>Materialize.toast('Error occured while saving ".basename($configFile)."', 4000);
                $('#result').removeClass('card-panel green darken-1 white-text');
                $('#result').addClass('card-panel red darken-1 white-text').fadeIn(1500).delay(5000).fadeOut(1500);
                </script>";
        }

        break;

    case "save_system_settings":

        $settings["current_password"] = base64_decode($_POST["current_password"]);
        $settings["new_password"] = base64_decode($_POST["new_password"]);
        $settings["confirm_password"] = base64_decode($_POST["confirm_password"]);

        saveSystemSettings($settings);

        break;
}
?>