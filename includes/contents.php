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
			$config = new Config(getServerType());
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
			if (getServerType() == Server::LIGHTTPD) $this->showGeneralStatisticLighttpd();
			if (getServerType() == Server::NGINX) $this->showGeneralStatisticNginx();
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
		        var old_password = $.base64.encode($('#old_password').val());
		        var new_password = $.base64.encode($('#new_password').val());
		        var repeat_password = $.base64.encode($('#repeat_password').val());
			
		        if (new_password.toString() == repeat_password.toString()) {
		            $('#tootip').css('display', 'none');
		            $.post('includes/ajax/handler.php', {act: "save_system_settings", old_password: old_password, new_password: new_password, repeat_password: repeat_password}, function(data) {
		                $('#tootip').html(data);
		                $('#tootip').css('display', 'block');
		            });
			
		        } else {
		            Materialize.toast('Your new password must be confirmed correctly.', 4000);
		        }

		    });

		    $("#no_button").click(function(){
		        document.location.href = "<?php getFullRootAddress(); ?>?page=0";
		    });


		});	
	</script>
    <div class="row">
        <div class="col s12 m12 l8 offset-l2">
        <div id="tootip" style="display:none;"></div>
          <div class="card white">
            <div class="card-content grey-text text-darken-3">
              <span class="card-title">Administrator password</span>

              <div class="row">
                <div class="input-field col s12">
                  <input id="old_password" type="password" class="validate">
                  <label for="old_password">Current password</label>
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
                  <input id="repeat_password" type="password" class="validate">
                  <label for="repeat_password">Confirm password</label>
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

		$authInfo = getAuthInfoLighttpd();
		$context = stream_context_create(array(
			'http' => array(
				'header' => "Authorization: Basic " . base64_encode($authInfo["login"] . ":" . $authInfo["password"])
			)
		));
		$html = file_get_contents("http://" . str_replace("localhost", "127.0.0.1", $_SERVER["HTTP_HOST"]) . "/server-status", false, $context);
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

	function showConfig($configFile){

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

		$settings = getKSWEBSettings();
		$move_inis = $settings["move_inis"];
		if (isset($hostFile) && isset($server)) {
			if ($server == Server::LIGHTTPD) {
				$configFile = (($move_inis == "true") ? Config::SERVER_LIGHTTPD_CONF_SDCARD_DIR : Config::SERVER_LIGHTTPD_CONF_DIR) . "/" . $hostFile;
			}

			if ($server == Server::NGINX) {
				$configFile = (($move_inis == "true") ? Config::SERVER_NGINX_CONF_SDCARD_DIR : Config::SERVER_NGINX_CONF_DIR) . "/" . $hostFile;
			}
		}

?>
	<script type="text/javascript" src="assets/js/xedit.js"></script>
	<script type="text/javascript">
		$(document).ready(function() {
		    xedit.bind($("#config-file-content"), function (el) {
		        saveConfig($("#config-file-content").val());
		    });

		    $("#save-config").click(function(){
		        saveConfig($("#config-file-content").val());
		    });

		    function saveConfig(configText) {
		        var editors = editor.getValue();
		        $('#result').css('display', 'none');
		        $.post('includes/ajax/handler.php', {act: "save_config", configFile: "<?php echo $configFile; ?>", config_text: editors}, function(data) {
		            $('#result').html(data);
		        });
		    }
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
<?php } ?>
<?php if (file_exists($configFile)) { ?>
<div class="row">
    <div class="col s12 m12 l8 offset-l2">
    <div id="result" class="card-panel" style="display:none;"></div>
        <div class="card white">
            <div class="card-content grey-text text-darken-3">
			<span class="card-title">Text Editor</span>
                <div class="right-align" style="margin-top: -45px;">
                    <a id="save-config" onclick ="return false;" class="btn-floating waves-effect waves-light-grey white" style="box-shadow:none;"><i class="material-icons grey-text text-darken-3">&#xE161;</i></a>
                </div>
				<div class="input-field">
<textarea id="config-file-content">
<?php echo file_get_contents($configFile); ?>
</textarea>
<label style="padding-bottom:4px;" for="config-file-content"><?php echo basename($configFile); ?></label>
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
<?php } else echo "File \"$configFile\" not found!";  ?>
        <?php
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

		        $("#do_restart_button").click(function(){
		            if (checkAllFields()) {	
		                var is_start_min;
		                $('#is_start_min').is(':checked') ? is_start_min = "true" : is_start_min = "false";		
					
		                var auto_start;
		                $('#auto_start').is(':checked') ? auto_start = "true" : auto_start = "false";		
					
		                var move_inis;
		                $('#move_inis').is(':checked') ? move_inis = "true" : move_inis = "false";
					
		                var wifiLock;
		                $('#wifiLock').is(':checked') ? wifiLock = "true" : wifiLock = "false";
					
		                $('#tootip').css('display', 'none');
		                $.post('includes/ajax/handler.php', {act: "save_ksweb_settings", wifiLock: wifiLock, wifiLock_old: wifiLock_old, is_start_min: is_start_min, is_start_min_old: is_start_min_old, auto_start: auto_start, auto_start_old: auto_start_old, move_inis: move_inis, move_inis_old: move_inis_old}, function(data) {
		                    $('#tootip').html(data);
		                    $('#tootip').css('display', 'block');
		                    initVars();
		                });
		            }
		        });
			
		        $("#go_back_button").click(function(){			
		            document.location.href = "<?php getFullRootAddress(); ?>?page=0";
		        });
			
		        $("#move_inis").click(function(){			
		            var move_inis;
		            $('#move_inis').is(':checked') ? move_inis = "true" : move_inis = "false";
				
		            if (move_inis == "true") {
		                $('#tootip').css('display', 'none');
		                $.post('includes/ajax/handler.php', {act: "move_inis_click_handler", move_inis: move_inis}, function(data) {
		                    $('#tootip').html(data);
		                    $('#tootip').css('display', 'block');
		                });
		            }
				
		        });
			
		        function checkAllFields(){
		            return true;
		        }
			
		        function isNumber(number){
		            if (number == 0) return true;
		            return res = (number / number) ? true : false;
		        }
			
			
		    });		
		</script>
        <div class="row">
            <div class="col s12 m12 l8 offset-l2">
            <div id="tootip" style="display:none;"></div>
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
                  <a class="linker blue-text nos" id="go_back_button">Cancel</a>
                  <a class="linker blue-text nos" id="do_restart_button">Save</a>
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
                  <span class="card-title">Restart KSWEB</span>
                  <p>You must know the following before you restart the server:<br />

                    <li>The errors in config files of the server, PHP or MySQL can lead that they did not start again. In this case you can't use KSWEB Web Interface until you correct the errors.</li>
                    <li>The server will be restarted only in case if KSWEB service is started on your Android device.</li>
                    <li>You may need to confirm using root rights on the device in case "root functions" was enabled.</li>

                  </p>
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
	<li class="tab"><a <?php if($_GET['page'] == 0){ echo "class=\"active\""; }else{ ""; } ?> target="_self" id="home_page_button">Home</a></li>
	<?php if (getServerType() != Server::NGINX) { ?>
	<li class="tab"><a <?php if($_GET['page'] == 3){ echo "class=\"active\""; }else{ ""; } ?> target="_self" id="server_statistics_button">Server Statistics</a></li>
	<?php } ?>
	<li class="tab"><a <?php if($_GET['page'] == 4){ echo "class=\"active\""; }else{ ""; } ?> target="_self" id="server-settings">Server Settings</a></li>
	<li class="tab"><a <?php if($_GET['page'] == 5){ echo "class=\"active\""; }else{ ""; } ?> target="_self" id="mysql-settings">MySQL Settings</a></li>
	<li class="tab"><a <?php if($_GET['page'] == 6){ echo "class=\"active\""; }else{ ""; } ?> target="_self" id="php-settings">PHP Settings</a></li>
	<li class="tab"><a <?php if($_GET['page'] == 2){ echo "class=\"active\""; }else{ ""; } ?> target="_self" id="ksweb_settings_button">KSWEB Settings</a></li>
	<li class="tab"><a <?php if($_GET['page'] == 7){ echo "class=\"active\""; }else{ ""; } ?> target="_self" id="system-settings">System Settings</a></li>
	<li class="tab"><a <?php if($_GET['page'] == 1){ echo "class=\"active\""; }else{ ""; } ?> target="_self" id="restart_button">Restart KSWEB</a></li>
	<?php
	}

	function showGeneralStatisticLighttpd(){
		$cpu = getCPUInfo();
		$memoryInfo = getMemInfo();
		$batteryInfo = getBatteryInfo();
		$wifiInfo = getWIFIInfo();
		$serverInfo = getServerInfoLighttpd();
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
              <th colspan="2" class="center-align">
                <svg style="width:50px;height:50px" viewBox="0 0 24 24">
                    <path fill="#424242" d="M17,17H7V7H17M21,11V9H19V7C19,5.89 18.1,5 17,5H15V3H13V5H11V3H9V5H7C5.89,5 5,5.89 5,7V9H3V11H5V13H3V15H5V17A2,2 0 0,0 7,19H9V21H11V19H13V21H15V19H17A2,2 0 0,0 19,17V15H21V13H19V11M13,13H11V11H13M15,9H9V15H15V9Z" />
                </svg>
              </th>
              <th colspan="2" class="center-align">
                <svg style="width:60px" viewBox="0 0 375 225" fill="#424242">
                    <g transform="translate(-68.5,-143.5)"><rect height="37.5" width="56.3" y="331" x="87.2"/><rect height="37.5" width="56.3" y="331" x="181"/><rect height="37.5" width="56.3" y="331" x="274.7"/><rect height="37.5" width="56.3" y="331" x="368.5"/><path d="m443.5 199.8v-56.3h-375v56.3c20.7 0 37.5 16.8 37.5 37.5 0 20.7-16.8 37.5-37.5 37.5v37.6h375v-37.5c-20.7 0-37.5-16.8-37.5-37.5 0-20.7 16.8-37.6 37.5-37.6zm-281.3 75H124.7V181h37.5zm75.1 0H199.8V181h37.5zm75 0H274.8V181h37.5zm75.1 0H349.9V181h37.5z"/></g>
                </svg>
              </th>
              <th colspan="2" class="center-align">
                <svg style="width:50px;height:50px" viewBox="0 0 24 24">
                    <path fill="#424242" d="M23,11H20V4L15,14H18V22M12.67,4H11V2H5V4H3.33A1.33,1.33 0 0,0 2,5.33V20.67C2,21.4 2.6,22 3.33,22H12.67C13.4,22 14,21.4 14,20.67V5.33A1.33,1.33 0 0,0 12.67,4Z" />
                </svg>
              </th>
              <th colspan="2" class="center-align">
                <svg style="width:50px;height:50px" fill="#424242" viewBox="0 0 24 24">
                    <path d="M12.01 21.49L23.64 7c-.45-.34-4.93-4-11.64-4C5.28 3 .81 6.66.36 7l11.63 14.49.01.01.01-.01z" fill-opacity=".3"/>
                    <path d="M0 0h24v24H0z" fill="none"/>
                    <path d="M3.53 10.95l8.46 10.54.01.01.01-.01 8.46-10.54C20.04 10.62 16.81 8 12 8c-4.81 0-8.04 2.62-8.47 2.95z"/>
                </svg>
              </th>
          </tr>
        </thead>
        <tbody>
            <tr>
                <td class="right-align"><b>Hostname:</b></td>
                <td class="left-align"><?php echo $serverInfo["hostname"]; ?></td>

                <td colspan="2"><?php echo $cpu["name"]; ?></td>

                <td class="right-align"><b>Total:</b></td>
                <td class="left-align"><?php echo $memoryInfo["total"] . " kb"; ?></td>

                <td class="right-align"><b>Capacity:</b></td>
                <td class="left-align"><?php echo $batteryInfo["capacity"] . "%"; ?></td>

                <td class="right-align"><b>Signal quality:</b></td>
                <td class="left-align"><?php echo $wifiInfo["quality"]; ?></td>
            </tr>
            <tr>
                <td class="right-align"><b>Uptime:</b></td>
                <td class="left-align"><?php echo $serverInfo["uptime"]; ?></td>

                <td class="right-align"><b>CPU usage:</b></td>
                <td class="left-align">
					<?php
					if ($cpu["usage"] != "N/A") {
						echo $cpu["usage"] . "%";
					}
					else {
						echo "N/A";
					}
					?>
                </td>

                <td class="right-align"><b>Free:</b></td>
                <td class="left-align"><?php echo $memoryInfo["free"] . " kb"; ?></td>

                <td class="right-align"><b>Voltage:</b></td>
                <td class="left-align"><?php echo $batteryInfo["voltage"] . " v"; ?></td>

                <td class="right-align"><b>Discarded packets:</b></td>
                <td class="left-align"><?php echo $wifiInfo["discarded_packets"]; ?></td>
            </tr>
            <tr>
                <td class="right-align"><b>Started at:</b></td>
                <td class="left-align"><?php echo $serverInfo["started_at"]; ?></td>

                <td class="right-align"></td>
                <td class="left-align"></td>

                <td class="right-align"><b>Filled:</b></td>
                <td class="left-align"><?php echo $memoryInfo["filled"] . " kb"; ?></td>

                <td class="right-align"><b>Status:</b></td>
                <td class="left-align"><?php echo $batteryInfo["status"]; ?></td>

                <td class="right-align"><b>Missed packets:</b></td>
                <td class="left-align"><?php echo $wifiInfo["missed_packets"]; ?></td>
            </tr>
            <tr>
                <td class="right-align"><b>Requests:</b></td>
                <td class="left-align"><?php echo $serverInfo["requests"]; ?></td>

                <td class="right-align"></td>
                <td class="left-align"></td>

                <td class="right-align"></td>
                <td class="left-align"></td>

                <td class="right-align"><b>Temperature:</b></td>
                <td class="left-align"><?php echo $batteryInfo["temp"] . " C"; ?></td>

                <td class="right-align"></td>
                <td class="left-align"></td>
            </tr>
            <tr>
                <td class="right-align"><b>Traffic:</b></td>
                <td class="left-align"><?php echo $serverInfo["traffic"]; ?></td>

                <td class="right-align"></td>
                <td class="left-align"></td>

                <td class="right-align"></td>
                <td class="left-align"></td>

                <td class="right-align"><b>Health:</b></td>
                <td class="left-align"><?php echo $batteryInfo["health"]; ?></td>

                <td class="right-align"></td>
                <td class="left-align"></td>
            </tr>
            <tr>
                <td class="right-align"><b>Requests average:</b></td>
                <td class="left-align"><?php echo $serverInfo["requests_avr"]; ?></td>

                <td class="right-align"></td>
                <td class="left-align"></td>

                <td class="right-align"></td>
                <td class="left-align"></td>

                <td class="right-align"></td>
                <td class="left-align"></td>

                <td class="right-align"></td>
                <td class="left-align"></td>
            </tr>
            <tr>
                <td class="right-align"><b>Traffic average:</b></td>
                <td class="left-align"><?php echo $serverInfo["traffic_avr"]; ?></td>

                <td class="right-align"></td>
                <td class="left-align"></td>

                <td class="right-align"></td>
                <td class="left-align"></td>

                <td class="right-align"></td>
                <td class="left-align"></td>

                <td class="right-align"></td>
                <td class="left-align"></td>
            </tr>
        </tbody>
    </table>

            </div>
          </div>
        </div>
    </div>
        <?php
	}

	function showGeneralStatisticNginx(){
		$cpu = getCPUInfo();
		$memoryInfo = getMemInfo();
		$batteryInfo = getBatteryInfo();
		$wifiInfo = getWIFIInfo();
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
              <th colspan="2" class="center-align">
                <svg style="width:50px;height:50px" viewBox="0 0 24 24">
                    <path fill="#424242" d="M17,17H7V7H17M21,11V9H19V7C19,5.89 18.1,5 17,5H15V3H13V5H11V3H9V5H7C5.89,5 5,5.89 5,7V9H3V11H5V13H3V15H5V17A2,2 0 0,0 7,19H9V21H11V19H13V21H15V19H17A2,2 0 0,0 19,17V15H21V13H19V11M13,13H11V11H13M15,9H9V15H15V9Z" />
                </svg>
              </th>
              <th colspan="2" class="center-align">
                <svg style="width:60px" viewBox="0 0 375 225" fill="#424242">
                    <g transform="translate(-68.5,-143.5)"><rect height="37.5" width="56.3" y="331" x="87.2"/><rect height="37.5" width="56.3" y="331" x="181"/><rect height="37.5" width="56.3" y="331" x="274.7"/><rect height="37.5" width="56.3" y="331" x="368.5"/><path d="m443.5 199.8v-56.3h-375v56.3c20.7 0 37.5 16.8 37.5 37.5 0 20.7-16.8 37.5-37.5 37.5v37.6h375v-37.5c-20.7 0-37.5-16.8-37.5-37.5 0-20.7 16.8-37.6 37.5-37.6zm-281.3 75H124.7V181h37.5zm75.1 0H199.8V181h37.5zm75 0H274.8V181h37.5zm75.1 0H349.9V181h37.5z"/></g>
                </svg>
              </th>
              <th colspan="2" class="center-align">
                <svg style="width:50px;height:50px" viewBox="0 0 24 24">
                    <path fill="#424242" d="M23,11H20V4L15,14H18V22M12.67,4H11V2H5V4H3.33A1.33,1.33 0 0,0 2,5.33V20.67C2,21.4 2.6,22 3.33,22H12.67C13.4,22 14,21.4 14,20.67V5.33A1.33,1.33 0 0,0 12.67,4Z" />
                </svg>
              </th>
              <th colspan="2" class="center-align">
                <svg style="width:50px;height:50px" fill="#424242" viewBox="0 0 24 24">
                    <path d="M12.01 21.49L23.64 7c-.45-.34-4.93-4-11.64-4C5.28 3 .81 6.66.36 7l11.63 14.49.01.01.01-.01z" fill-opacity=".3"/>
                    <path d="M0 0h24v24H0z" fill="none"/>
                    <path d="M3.53 10.95l8.46 10.54.01.01.01-.01 8.46-10.54C20.04 10.62 16.81 8 12 8c-4.81 0-8.04 2.62-8.47 2.95z"/>
                </svg>
              </th>
          </tr>
        </thead>
        <tbody>
            <tr>
                <td class="right-align"><b>Active connections:</b></td>
                <td class="left-align"><?php echo $serverInfo["activeConnections"]; ?></td>

                <td colspan="2" class="center-align"><?php echo $cpu["name"]; ?></td>

                <td class="right-align"><b>Total:</b></td>
                <td class="left-align"><?php echo $memoryInfo["total"] . " kb"; ?></td>

                <td class="right-align"><b>Capacity:</b></td>
                <td class="left-align"><?php echo $batteryInfo["capacity"] . "%"; ?></td>

                <td class="right-align"><b>Signal quality:</b></td>
                <td class="left-align"<?php echo $wifiInfo["quality"]; ?></td>
            </tr>
            <tr>
                <td class="right-align"><b>Accepted connections:</b></td>
                <td class="left-align"><?php echo $serverInfo["accepts"]; ?></td>

                <td class="right-align"><b>CPU usage:</b></td>
                <td class="left-align"><?php echo $cpu["usage"] . "%"; ?></td>

                <td class="right-align"><b>Free:</b></td>
                <td class="left-align"><?php echo $memoryInfo["free"] . " kb"; ?></td>

                <td class="right-align"><b>Voltage:</b></td>
                <td class="left-align"><?php echo $batteryInfo["voltage"] . " v"; ?></td>

                <td class="right-align"><b>Discarded packets:</b></td>
                <td class="left-align"><?php echo $wifiInfo["discarded_packets"]; ?></td>
            </tr>
            <tr>
                <td class="right-align"><b>Handled connections:</b></td>
                <td class="left-align"><?php echo $serverInfo["handled"]; ?></td>

                <td class="right-align"><b></b></td>
                <td class="left-align"></td>

                <td class="right-align"><b>Filled:</b></td>
                <td class="left-align"><?php echo $memoryInfo["filled"] . " kb"; ?></td>

                <td class="right-align"><b>Status:</b></td>
                <td class="left-align"><?php echo $batteryInfo["status"]; ?></td>

                <td class="right-align"><b>Missed packets:</b></td>
                <td class="left-align"><?php echo $wifiInfo["missed_packets"]; ?></td>
            </tr>
            <tr>
                <td class="right-align"><b>Handled requests:</b></td>
                <td class="left-align"><?php echo $serverInfo["requests"]; ?></td>

                <td class="right-align"><b></b></td>
                <td class="left-align"></td>

                <td class="right-align"><b></b></td>
                <td class="left-align"></td>

                <td class="right-align"><b>Temperature:</b></td>
                <td class="left-align"><?php echo $batteryInfo["temp"] . " C"; ?></td>

                <td class="right-align"><b></b></td>
                <td class="left-align"></td>
            </tr>
            <tr>
                <td class="right-align"><b>Read request headers:</b></td>
                <td class="left-align"><?php echo $serverInfo["reading"]; ?></td>

                <td class="right-align"><b></b></td>
                <td class="left-align"></td>

                <td class="right-align"><b></b></td>
                <td class="left-align"></td>

                <td class="right-align"><b>Health:</b></td>
                <td class="left-align"><?php echo $batteryInfo["health"]; ?></td>

                <td class="right-align"><b></b></td>
                <td class="left-align"></td>
            </tr>
            <tr>
                <td class="right-align"><b>Wrote responses:</b></td>
                <td class="left-align"><?php echo $serverInfo["writing"]; ?></td>

                <td class="right-align"><b></b></td>
                <td class="left-align"></td>

                <td class="right-align"><b></b></td>
                <td class="left-align"></td>

                <td class="right-align"><b></b></td>
                <td class="left-align"></td>

                <td class="right-align"><b></b></td>
                <td class="left-align"></td>
            </tr>
            <tr>
                <td class="right-align"><b>Keep-alive connections:</b></td>
                <td class="left-align"><?php echo $serverInfo["waiting"]; ?></td>

                <td class="right-align"><b></b></td>
                <td class="left-align"></td>

                <td class="right-align"><b></b></td>
                <td class="left-align"></td>

                <td class="right-align"><b></b></td>
                <td class="left-align"></td>

                <td class="right-align"><b></b></td>
                <td class="left-align"></td>
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
