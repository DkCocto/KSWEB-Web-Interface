<?php
require_once ("includes/functions/functions.php");

class ContentGenerator {

	const PAGE_1 = "1";
	const PAGE_2 = "2";
	const PAGE_3 = "3";
	const PAGE_4 = "4";
	const PAGE_5 = "5";
	const PAGE_6 = "6";
	const PAGE_7 = "7";
	const PAGE_8 = "8";

	var $page_names_array = array(
		"Home",
		"Restart KSWEB",
		"KSWEB settings",
		"Server statistics",
		"Server settings",
		"MySQL settings",
		"PHP settings",
		"System settings",
		"GPS statistics"
	);

	function getContent($page){

		switch ($page) {
		case self::PAGE_1:
			$this->showRestartServerPage();
			break;

		case self::PAGE_2:
			$this->showKSWEBSettingsPage();
			break;

		case self::PAGE_3:
			$this->showServerStatistics();
			break;

		case self::PAGE_4:
		    $serverType = getServerType();
		    $configType = Config::getConfigType($serverType);
		    $config = new Config($configType);
			$this->showConfig($config->getConfigFullPath());
			break;

		case self::PAGE_5:
			$config = new Config(ConfigType::MYSQL);
			$this->showConfig($config->getConfigFullPath());
			break;

		case self::PAGE_6:
			$config = new Config(ConfigType::PHP);
			$this->showConfig($config->getConfigFullPath());
			break;

		case self::PAGE_7:
			$this->showSystemConfig();
			break;

		default:
			if (getServerType() == Server::LIGHTTPD) $this->showServerStatistics();
			if (getServerType() == Server::NGINX) $this->showGeneralStatisticNginx();
            if (getServerType() == Server::APACHE) $this->showServerStatistics();
		}
	}

	function generateLink($page){
		if ($page == self::PAGE_1) return "<a href = 'index.php?page=$page'>" . $this->page_names_array[self::PAGE_1] . "</a>";
		if ($page == self::PAGE_2) return "<a href = 'index.php?page=$page'>" . $this->page_names_array[self::PAGE_2] . "</a>";
		if ($page == self::PAGE_3) return "<a href = 'index.php?page=$page'>" . $this->page_names_array[self::PAGE_3] . "</a>";
		if ($page == self::PAGE_4) return "<a href = 'index.php?page=$page'>" . $this->page_names_array[self::PAGE_4] . "</a>";
		if ($page == self::PAGE_5) return "<a href = 'index.php?page=$page'>" . $this->page_names_array[self::PAGE_5] . "</a>";
		if ($page == self::PAGE_6) return "<a href = 'index.php?page=$page'>" . $this->page_names_array[self::PAGE_6] . "</a>";
		if ($page == self::PAGE_7) return "<a href = 'index.php?page=$page'>" . $this->page_names_array[self::PAGE_7] . "</a>";
		return "<a href = 'index.php'>" . $this->page_names_array[0] . "</a>";
	}

	function showSystemConfig(){
?>
	<script type="text/javascript" src="assets/js/jquery.base64.min.js"></script>
	<script type="text/javascript">
		$(document).ready(function() {  

		    $("#yes_button").click(function(){
		        var current_password = $.base64.encode($('#current_password').val());
		        var new_password = $.base64.encode($('#new_password').val());
		        var confirm_password = $.base64.encode($('#confirm_password').val());
			
		        if (new_password.toString() == confirm_password.toString()) {
		            $('#result').css('display', 'none');
		            $.post('includes/ajax/handler.php', {act: "save_system_settings", current_password: current_password, new_password: new_password, confirm_password: confirm_password}, function(data) {
		                $('#result').html(data);
		                $('#result').css('display', 'block');
		            });
			
		        } else {
		            Materialize.toast('New password must be confirmed correctly.', 4000);
		        }

		    });

		    $("#no_button").click(function(){
		        document.location.href = "<?php getFullRootAddress(); ?>?page=0";
		    });


		});	
	</script>
    <div class="row">
        <div class="col s12 m12 l8 offset-l2">
        <div id="result" style="display:none;"></div>
          <div class="card white">
            <div class="card-content grey-text text-darken-3">
              <span class="card-title">Administrator password</span>

              <div class="row">
                <div class="input-field col s12">
                  <input id="current_password" type="password" class="validate">
                  <label for="current_password">Current password</label>
                </div>
              </div>
              <div class="row">
                <div class="input-field col s12">
                  <input id="new_password" type="password" class="validate">
                  <label for="new_password">New password</label>
                </div>
              </div>
              <div class="row">
                <div class="input-field col s12">
                  <input id="confirm_password" type="password" class="validate">
                  <label for="confirm_password">Confirm password</label>
                </div>
              </div>
            </div>
            <div class="card-action">
              <a class="linker blue-text" id="no_button">Cancel</a>
              <a class="linker blue-text" id="yes_button">Change</a>
            </div>
          </div>
        </div>
    </div>
	<?php
	}

	function showServerStatistics(){
        if (getServerType() == Server::LIGHTTPD) {
            $authInfo = getAuthInfoLighttpd();
        } elseif (getServerType() == Server::APACHE) {
            $authInfo = getAuthInfoApache();
        } else {
            $authInfo = getAuthInfoLighttpd();
        }

		$context = stream_context_create(array(
			'http' => array(
				'header' => "Authorization: Basic " . base64_encode($authInfo["login"] . ":" . $authInfo["password"])
			)
		));

        $html = file_get_contents("http://127.0.0.1:".$_SERVER['SERVER_PORT']."/server-status", false, $context);
?>
    <div class="row">
        <div class="col s12 m12 l12">
        <div class="card white" style="overflow-y: scroll">
            <div class="card-content grey-text text-darken-3">
              <p>
                <?php
				$search = array(
					'<h1>',
					'</h1>',
					'<h2>',
					'</h2>',
					'<table summary="status" class="status">'
				);
				$replace = array(
					'<h4>',
					'</h4>',
					'<h4>',
					'</h4>',
					'<table class="striped">'
				);
				$html = str_replace($search, $replace, $html);
				echo $html; 
				?>
              </p>
            </div>
          </div>
        </div>
    </div>
    </div>
	<?php
	}

	function showConfig($configFile)
    {

        $hostFile = $_GET["hostFile"];
        $server = $_GET["server"];
        if ($server == Server::LIGHTTPD) {
            $config = new Config(ConfigType::SERVER_LIGHTTPD);
            $configFile = $config->getConfigFullPath();
        }

        if ($server == Server::NGINX) {
            $config = new Config(ConfigType::SERVER_NGINX);
            $configFile = $config->getConfigFullPath();
        }

        if ($server == Server::APACHE) {
            $config = new Config(ConfigType::SERVER_APACHE);
            $configFile = $config->getConfigFullPath();
        }

        $settings = getKSWEBSettings();
        $move_inis = $settings["move_inis"];
        if (isset($hostFile) && isset($server)) {
            if ($server == Server::LIGHTTPD) {
                $configFile = (($move_inis == "true") ? Config::SERVER_LIGHTTPD_CONF_SDCARD_DIR : Config::SERVER_LIGHTTPD_CONF_DIR) . "/" . $hostFile;
            }

            if ($server == Server::NGINX) {
                $configFile = (($move_inis == "true") ? Config::SERVER_NGINX_CONF_SDCARD_DIR : Config::SERVER_NGINX_CONF_DIR) . "/" . $hostFile;
            }

            if ($server == Server::APACHE) {
                $configFile = (($move_inis == "true") ? Config::SERVER_APACHE_CONF_SDCARD_DIR : Config::SERVER_APACHE_CONF_DIR) . "/" . $hostFile;
            }
        }

        ?>
        <script type="text/javascript">
            $(document).ready(function () {
                $("#save-config").click(function () {
                    var configTxt = editor.getValue();
                    $('#result').css('display', 'none');
                    $.post('includes/ajax/handler.php', {
                        act: "save_config",
                        configFile: "<?php echo $configFile; ?>",
                        config_text: configTxt
                    }, function (data) {
                        $('#result').html(data);
                    });
                });
            });
        </script>
        <?php if ($_GET["page"] == ContentGenerator::PAGE_4) { ?>
        <div class="row">
            <div class="col s12 m12 l8 offset-l2">
                <div class="card white">
                    <div class="card-content grey-text text-darken-3">
                        <?php
                        showLighttpdConfigHref();
                        showHostListLighttpd();
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col s12 m12 l8 offset-l2">
                <div class="card white">
                    <div class="card-content grey-text text-darken-3">
                        <?php
                        showNginxConfigHref();
                        showHostListNginx();
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col s12 m12 l8 offset-l2">
                <div class="card white">
                    <div class="card-content grey-text text-darken-3">
                        <?php
                        showApacheConfigHref();
                        showHostListApache();
                        ?>
                    </div>
                </div>
            </div>
        </div>

    <?php } ?>
        <?php if (file_exists($configFile)) { ?>
        <div class="row">
            <div class="col s12 m12 l8 offset-l2">
                <div id="result" class="card-panel" style="display:none;"></div>
                <div class="card white">
                    <div class="card-content grey-text text-darken-3">
                        <span class="card-title">Text Editor</span>
                        <div class="right-align" style="margin-top: -45px;">
                            <a id="save-config" onclick="return false;"
                               class="btn-floating waves-effect waves-light-grey white" style="box-shadow:none;"><i
                                        class="material-icons grey-text text-darken-3">&#xE161;</i></a>
                        </div>
                        <div class="input-field">
<textarea id="config-file-content">
<?php echo file_get_contents($configFile); ?>
</textarea>
                            <label style="padding-bottom:4px;"
                                   for="config-file-content"><?php echo basename($configFile); ?></label>
                        </div>
                        <script type='text/javascript'>
                            var editor = CodeMirror.fromTextArea(document.getElementById("config-file-content"), {
                                mode: "properties",
                                lineNumbers: true,
                                lineWrapping: true,
                                viewportMargin: Infinity
                            });
                        </script>
                    </div>
                </div>
            </div>

        </div>
    <?php } else { ?>

        <div class="row">
            <div class="col s12 m12 l8 offset-l2">
                <div id="result" class="card-panel" style="display:none;"></div>
            </div>
        </div>
        <?php echo "<script>
                $('#result').text('File \'$configFile\' not found!');
                $('#result').removeClass('card-panel green darken-1 white-text');
                $('#result').addClass('card-panel red darken-1 white-text').fadeIn(1500).delay(5000).fadeOut(1500);
                </script>";
    }
    }

	function showKSWEBSettingsPage(){
		$settings = getKSWEBSettings();
?>
		<script type="text/javascript">
		    $(document).ready(function() {
		        var is_start_min_old, auto_start_old, wifiLock_old, move_inis_old;
			
		        function initVars() {

		            $('#is_start_min').is(':checked') ? is_start_min_old = "true" : is_start_min_old = "false";
		            $('#auto_start').is(':checked') ? auto_start_old = "true" : auto_start_old = "false";
		            $('#move_inis').is(':checked') ? move_inis_old = "true" : move_inis_old = "false";
		            $('#wifiLock').is(':checked') ? wifiLock_old = "true" : wifiLock_old = "false";
				
		        }

		        initVars();

		        $("#save_btn").click(function(){
		            
					var is_start_min;
					$('#is_start_min').is(':checked') ? is_start_min = "true" : is_start_min = "false";		
				
					var auto_start;
					$('#auto_start').is(':checked') ? auto_start = "true" : auto_start = "false";		
				
					var move_inis;
					$('#move_inis').is(':checked') ? move_inis = "true" : move_inis = "false";
				
					var wifiLock;
					$('#wifiLock').is(':checked') ? wifiLock = "true" : wifiLock = "false";
				
					$('#result').css('display', 'none');
					$.post('includes/ajax/handler.php', {act: "save_ksweb_settings", wifiLock: wifiLock, wifiLock_old: wifiLock_old, is_start_min: is_start_min, is_start_min_old: is_start_min_old, auto_start: auto_start, auto_start_old: auto_start_old, move_inis: move_inis, move_inis_old: move_inis_old}, function(data) {
						Materialize.toast('Settings saved.', 4000);
						if (data.length > 0) {
							$('#result').html(data);
							$('#result').removeClass('card-panel red darken-1 white-text');
							$('#result').addClass('card-panel green darken-1 white-text').fadeIn(1500).delay(5000).fadeOut(1500);
						}
						initVars();
					});
		        });
			
		        function isNumber(number){
		            if (number == 0) return true;
		            return res = (number / number) ? true : false;
		        }
			
			
		    });		
		</script>
        <div class="row">
            <div class="col s12 m12 l8 offset-l2">
            <div id="result" class="card-panel" style="display:none;"></div>
              <div class="card white">
                <div class="card-content grey-text text-darken-3">
                  <span class="card-title">KSWEB Settings</span>
                    <br />
                  <p>
                    <input type="checkbox" class="filled-in" id="enable_root_func" disabled <?php if ($settings["enable_root_func"] == 'true') echo "checked"; ?>>
                    <label for="enable_root_func">Enable root functions</label>
                    </p>
                    <br />
                    <p>
                    <input type="checkbox" class="filled-in checkbox-blue" id="is_start_min" <?php if ($settings["is_start_min"] == 'true') echo "checked"; ?>>
                    <label class="label-text" for="is_start_min">Start minimized</label>
                    </p>
                    <br />
                    <p>
                    <input type="checkbox" class="filled-in checkbox-blue" id="auto_start" <?php if ($settings["auto_start"] == 'true') echo "checked"; ?>>
                    <label class="label-text" for="auto_start">Start KSWEB on system start</label>
                    </p>
                    <br />
                    <p>
                    <input type="checkbox" class="filled-in checkbox-blue" id="move_inis" <?php if ($settings["move_inis"] == 'true') echo "checked"; ?>>
                    <label class="label-text" for="move_inis">Use external ini files</label>
                    </p>
                    <br />
                    <p>
                    <input type="checkbox" class="filled-in checkbox-blue" id="wifiLock" <?php if ($settings["wifiLock"] == 'true') echo "checked"; ?>>
                    <label class="label-text" for="wifiLock">Lock Wi-Fi</label>
                    </p>
                </div>
                <div class="card-action">
                  <a class="linker blue-text nos" id="save_btn">Save</a>
                </div>
              </div>
            </div>
        </div>
        <?php
	}

	function showRestartServerPage(){
?>	
	<script type="text/javascript">
	    $(document).ready(function() {
	        $("#do_restart_button").click(function(){
	            $('#tootip').css('display', 'none');
	            $.post('includes/ajax/handler.php', {act: "restart_server"}, function(data) {
	                $('#tootip').html(data);
	                $('#tootip').css('display', 'block');
	            });
	        });
	        $("#go_back_button").click(function(){			
	            document.location.href = "<?php getFullRootAddress(); ?>?page=0";
	        });
	    });

	</script>
        <div class="row">
            <div class="col s12 m12 l8 offset-l2">
            <div id="tootip" style="display:none;"></div>
			

			
			
              <div class="card white">
                <div class="card-content grey-text text-darken-3">
                  <span class="card-title">Restart KSWEB</span><br>
                  <b>You should know the following before you restart the server:</b><br><br>
						The errors in config files of the server, PHP or MySQL can lead that they did not start again. In this case you can't use KSWEB Web Interface until you correct the errors.<br><br>
						The server will be restarted only in case if KSWEB service is started on your Android device.<br><br>
						You may need to confirm using root rights on the device in case "root functions" was enabled.
                </div>
                <div class="card-action">
                  <a class="linker blue-text nos" id="go_back_button">No</a>
                  <a class="linker blue-text nos" id="do_restart_button">Yes, Restart Now</a>
                </div>
              </div>
            </div>
        </div>
        <?php
	}

	function showMainMenu(){
?>
	<script type="text/javascript">
	$(document).ready(function() {
	    $("#home_page_button").click(function(){			
	        document.location.href = "<?php getFullRootAddress(); ?>?page=0";
	    });	
	    $("#restart_button").click(function(){			
	        document.location.href = "<?php getFullRootAddress(); ?>?page=1";
	    });
	    $("#ksweb_settings_button").click(function(){			
	        document.location.href = "<?php getFullRootAddress(); ?>?page=2";
	    });
	    $("#server_statistics_button").click(function(){			
	        document.location.href = "<?php getFullRootAddress(); ?>?page=3";
	    });
	    $("#server-settings").click(function(){			
	        document.location.href = "<?php getFullRootAddress(); ?>?page=4";
	    });
	    $("#mysql-settings").click(function(){			
	        document.location.href = "<?php getFullRootAddress(); ?>?page=5";
	    });
	    $("#php-settings").click(function(){			
	        document.location.href = "<?php getFullRootAddress(); ?>?page=6";
	    });
	    $("#system-settings").click(function(){			
	        document.location.href = "<?php getFullRootAddress(); ?>?page=7";
	    });
	    $("#gps_statistics").click(function(){			
	        document.location.href = "<?php getFullRootAddress(); ?>?page=8";
	    });
	});			
	</script>
	<li class="tab">
        <a
            <?php if($_GET['page'] == 0) {
                echo "class=\"active\"";
            }?>
                target="_self" id="home_page_button">Home
        </a>
    </li>


	<li class="tab"><a <?php if($_GET['page'] == 4){ echo "class=\"active\""; }else{ ""; } ?> target="_self" id="server-settings">Server Settings</a></li>
	<li class="tab"><a <?php if($_GET['page'] == 5){ echo "class=\"active\""; }else{ ""; } ?> target="_self" id="mysql-settings">MySQL Settings</a></li>
	<li class="tab"><a <?php if($_GET['page'] == 6){ echo "class=\"active\""; }else{ ""; } ?> target="_self" id="php-settings">PHP Settings</a></li>
	<li class="tab"><a <?php if($_GET['page'] == 2){ echo "class=\"active\""; }else{ ""; } ?> target="_self" id="ksweb_settings_button">KSWEB Settings</a></li>
	<li class="tab"><a <?php if($_GET['page'] == 7){ echo "class=\"active\""; }else{ ""; } ?> target="_self" id="system-settings">System Settings</a></li>
	<li class="tab"><a <?php if($_GET['page'] == 1){ echo "class=\"active\""; }else{ ""; } ?> target="_self" id="restart_button">Restart KSWEB</a></li>
	<?php
	}

	function showGeneralStatisticNginx(){
		$serverInfo = getServerInfoNginx();
?>
    <div class="row">
        <div class="col s12 m12 l12">
          <div class="card white" style="overflow-y: scroll">
            <div class="card-content grey-text text-darken-3">
              <span class="card-title">General statistic</span>


    <table class="striped">
        <thead>
          <tr>
              <th colspan="2" class="center-align">
                <svg style="width:50px;height:50px" viewBox="0 0 24 24">
                    <path fill="#424242" d="M4,1H20A1,1 0 0,1 21,2V6A1,1 0 0,1 20,7H4A1,1 0 0,1 3,6V2A1,1 0 0,1 4,1M4,9H20A1,1 0 0,1 21,10V14A1,1 0 0,1 20,15H4A1,1 0 0,1 3,14V10A1,1 0 0,1 4,9M4,17H20A1,1 0 0,1 21,18V22A1,1 0 0,1 20,23H4A1,1 0 0,1 3,22V18A1,1 0 0,1 4,17M9,5H10V3H9V5M9,13H10V11H9V13M9,21H10V19H9V21M5,3V5H7V3H5M5,11V13H7V11H5M5,19V21H7V19H5Z" />
                </svg>
              </th>
          </tr>
        </thead>
        <tbody>
            <tr>
                <td class="center-align"><b>Active connections: </b> <?php echo $serverInfo["activeConnections"];?></td>
            </tr>
            <tr>
                <td class="center-align"><b>Accepted connections: </b><?php echo $serverInfo["accepts"]; ?></td>
            </tr>
            <tr>
                <td class="center-align"><b>Handled connections: </b> <?php echo $serverInfo["handled"]; ?></td>
            </tr>
            <tr>
                <td class="center-align"><b>Handled requests: </b><?php echo $serverInfo["requests"]; ?></td>
            </tr>
            <tr>
                <td class="center-align"><b>Read request headers: </b><?php echo $serverInfo["reading"]; ?></td>
            </tr>
            <tr>
                <td class="center-align"><b>Wrote responses: </b><?php echo $serverInfo["writing"]; ?></td>
            </tr>
            <tr>
                <td class="center-align"><b>Keep-alive connections : </b><?php echo $serverInfo["waiting"]; ?></td>
            </tr>
        </tbody>
    </table>

            </div>
          </div>
        </div>
    </div>
        <?php
	}
}

?>
