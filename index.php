<?php
    require_once("includes/class/Server.php");
    require_once("includes/class/ConfigType.php");
    require_once("includes/class/Config.php");
    require_once("includes/contents.php");
    require_once("includes/html/head.php");
?>
<body class="grey lighten-3">
<nav class="nav-extended blue">
    <div class="nav-wrapper">
    <a href="./" class="left brand-logo"><span class="ksweb-title">KSWEB Web Interface&nbsp;<?= VERSION; ?></span></a>
      <ul class="right">
       <li><a class="nocover" href="info.php">
               <img src="assets/images/php.svg">
          </a></li>
      </ul>
    </div>
    <div class="nav-content">
      <ul class="tabs tabs-transparent" style="display:flex;">
        <?php
			$page = trim($_GET["page"]);
			$cg = new ContentGenerator();
            $cg->showMainMenu();
        ?>
      </ul>
    </div>
  </nav>
  <main>
    <div class="container">
      <?php $cg->getContent($page); ?>
    </div>
</main>
</body>
<?php require_once("includes/html/foot.php"); ?>