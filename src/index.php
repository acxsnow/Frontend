<?php
////////////////////////////////////////////////////////////////////////////////
// BOCA Online Contest Administrator
//    Copyright (C) 2003-2012 by BOCA Development Team
//    (license text omitted for brevity)
// Last modified 05/aug/2012 by cassio@ime.usp.br
////////////////////////////////////////////////////////////////////////////////

ob_start();
header ("Expires: " . gmdate("D, d M Y H:i:s") . " GMT");
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");
header ("Pragma: no-cache");
header ("Content-Type: text/html; charset=utf-8");
session_start();

$_SESSION["loc"]  = dirname($_SERVER['PHP_SELF']);
if ($_SESSION["loc"] == "/") $_SESSION["loc"] = "";
$_SESSION["locr"] = dirname(__FILE__);
if ($_SESSION["locr"] == "/") $_SESSION["locr"] = "";

require_once("globals.php");
require_once("db.php");

// Si no vienen credenciales, destruimos sesión y regeneramos
if (!isset($_GET["name"])) {
    if (ValidSession()) {
        DBLogOut(
            $_SESSION["usertable"]["contestnumber"],
            $_SESSION["usertable"]["usersitenumber"],
            $_SESSION["usertable"]["usernumber"],
            $_SESSION["usertable"]["username"] == 'admin'
        );
    }
    session_unset();
    session_destroy();
    session_start();
    $_SESSION["loc"]  = dirname($_SERVER['PHP_SELF']);
    if ($_SESSION["loc"] == "/") $_SESSION["loc"] = "";
    $_SESSION["locr"] = dirname(__FILE__);
    if ($_SESSION["locr"] == "/") $_SESSION["locr"] = "";
}

// Responde con session_id si lo piden desde JS
if (isset($_GET["getsessionid"])) {
    echo session_id();
    exit;
}

// Cookie “biscoitobocabombonera”
$coo = [];
if (isset($_COOKIE['biscoitobocabombonera'])) {
    $coo = explode('-', $_COOKIE['biscoitobocabombonera']);
    if (count($coo) != 2
        || strlen($coo[1]) != strlen(myhash('xxx'))
        || !is_numeric($coo[0])
        || !ctype_alnum($coo[1])
    ) {
        $coo = [];
    }
}
if (count($coo) != 2) {
    setcookie(
        'biscoitobocabombonera',
        time() . '-' . myhash(time() . rand() . time() . rand()),
        time() + 240 * 3600
    );
}

ob_end_flush();

// Procesa login si vienen por GET name + password
if (
    function_exists("globalconf") &&
    function_exists("sanitizeVariables") &&
    isset($_GET["name"]) && $_GET["name"] != ""
) {
    $name     = $_GET["name"];
    $password = $_GET["password"];
    $usertable = DBLogIn($name, $password);
    if (!$usertable) {
        ForceLoad("index.php");
    } else {
        // Verifica concurso y permisos
        if (( $ct = DBContestInfo($_SESSION["usertable"]["contestnumber"]) ) === null) {
            ForceLoad("index.php");
        }
        $main = ($ct["contestlocalsite"] == $ct["contestmainsite"]);
        if ($main && $_SESSION["usertable"]["usertype"] == 'site') {
            MSGError('Direct login of this user is not allowed');
            unset($_SESSION["usertable"]);
            ForceLoad("index.php");
            exit;
        }
        // Redirige al dashboard según tipo de usuario
        echo "<script>location.href='" . $_SESSION["usertable"]["usertype"] . "/index.php';</script>";
        exit;
    }
} elseif (!function_exists("globalconf") || !function_exists("sanitizeVariables")) {
    echo "<script>alert('Unable to load config files. Check BOCA directory permissions.');</script>";
}

require_once('version.php');
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>BOCA – Login</title>

  <!-- Bootstrap 5 CSS -->
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
    rel="stylesheet"
  >

  <style>
    body {
      font-family: serif;
      background-color: #fff;
    }
    header {
      background-color: #ffff66;
      padding: 1rem;
      text-align: center;
    }
    header h1 {
      margin: 0;
      font-size: 2.5rem;
      letter-spacing: 2px;
    }
    .navbar {
      background-color: #fff;
      border-bottom: 1px solid #ccc;
    }
    .navbar-nav .nav-link.disabled {
      color: #999;
      pointer-events: none;
      text-decoration: none;
    }
    .form-container {
      border: 2px solid #ccff00;
      padding: 2rem;
      max-width: 600px;
      margin: 3rem auto;
    }
    .form-container h2 {
      text-align: center;
      margin-bottom: 2rem;
      font-weight: bold;
    }
    .form-group {
      display: flex;
      align-items: center;
      margin-bottom: 1.5rem;
    }
    .form-group label {
      flex: 0 0 120px;
      margin-bottom: 0;
    }
    .form-group .form-control {
      flex: 1;
      border: 1px solid #ccff00;
    }
    .btn-login {
      background-color: #ccff00;
      color: #000;
      border: none;
      padding: .5rem 2rem;
      border-radius: .25rem;
      font-weight: bold;
      min-width: 120px;
    }
    .btn-login:hover {
      background-color: #e6ff99;
    }
  </style>

  <!-- JS hashing -->
  <script src="sha256.js"></script>
  <script>
    function computeHASH() {
      const user = document.getElementById('username').value;
      const pass = document.getElementById('password').value;
      const sid  = '<?php echo session_id(); ?>';
      const passHASH = js_myhash(js_myhash(pass) + sid);
      // Limpia
      document.getElementById('username').value = '';
      document.getElementById('password').value = '';
      // Redirige con GET
      window.location = 'index.php?name='
        + encodeURIComponent(user)
        + '&password=' + passHASH;
    }
  </script>
</head>
<body onload="document.getElementById('username').focus()">

  <!-- Header -->
  <header>
    <h1>BOCA</h1>
  </header>

  <!-- Navbar deshabilitada -->
  <nav class="navbar navbar-expand-sm justify-content-center">
    <ul class="navbar-nav">
      <li class="nav-item"><a class="nav-link disabled" href="#">Contest</a></li>
      <li class="nav-item"><a class="nav-link disabled" href="#">Options</a></li>
      <li class="nav-item"><a class="nav-link disabled" href="#">Logout</a></li>
    </ul>
  </nav>

  <!-- Login form -->
  <div class="form-container">
    <h2>BOCA Login</h2>
    <form id="login-form" name="form1" onsubmit="computeHASH(); return false;">
      <div class="form-group">
        <label for="username">Name</label>
        <input
          type="text"
          id="username"
          name="name"
          class="form-control"
          required
        >
      </div>
      <div class="form-group">
        <label for="password">Password</label>
        <input
          type="password"
          id="password"
          name="password"
          class="form-control"
          required
        >
      </div>
      <div class="text-center">
        <button type="submit" class="btn-login">Login</button>
      </div>
    </form>
  </div>

  <?php include('footnote.php'); ?>

  <!-- Bootstrap JS bundle (opcional) -->
  <script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
  ></script>
</body>
</html>
