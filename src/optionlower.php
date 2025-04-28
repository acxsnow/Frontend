<?php
////////////////////////////////////////////////////////////////////////////////
//BOCA Online Contest Administrator
//    (license omitted for brevity)
// Last modified 05/aug/2012 by cassio@ime.usp.br
////////////////////////////////////////////////////////////////////////////////

require_once("globals.php");

// Valida sesión
if (!ValidSession()) {
    InvalidSession("optionlower.php");
    ForceLoad("index.php");
}
$loc = $_SESSION['loc'];

// Procesa actualización del usuario si vienen los parámetros
if (
    isset($_GET["username"])    &&
    isset($_GET["userfullname"])&&
    isset($_GET["userdesc"])    &&
    isset($_GET["passwordo"])   &&
    isset($_GET["passwordn"])
) {
    if ($_SESSION["usertable"]["usertype"] == 'team') {
        MSGError('Updates are not allowed');
        ForceLoad("option.php");
    }

    $username     = myhtmlspecialchars($_GET["username"]);
    $userfullname = myhtmlspecialchars($_GET["userfullname"]);
    $userdesc     = myhtmlspecialchars($_GET["userdesc"]);
    $passwordo    = $_GET["passwordo"];
    $passwordn    = $_GET["passwordn"];

    DBUserUpdate(
        $_SESSION["usertable"]["contestnumber"],
        $_SESSION["usertable"]["usersitenumber"],
        $_SESSION["usertable"]["usernumber"],
        $_SESSION["usertable"]["username"], // no permitimos cambiar username
        $userfullname,
        $userdesc,
        $passwordo,
        $passwordn
    );
    ForceLoad("option.php");
}

// Carga datos del usuario
$a = DBUserInfo(
    $_SESSION["usertable"]["contestnumber"],
    $_SESSION["usertable"]["usersitenumber"],
    $_SESSION["usertable"]["usernumber"]
);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>BOCA – User Profile</title>

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
    /* Header amarillo */
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
    /* Navbar sencilla */
    .navbar {
      background-color: #fff;
      border-bottom: 1px solid #ccc;
    }
    .navbar-nav .nav-link {
      font-weight: bold;
      color: #000;
      padding: .5rem 1rem;
    }
    .navbar-nav .nav-link:hover {
      text-decoration: underline;
    }
    /* Contenedor del formulario */
    .form-container {
      border: 2px solid #ffff66;
      padding: 2rem;
      max-width: 700px;
      margin: 3rem auto;
    }
    .form-container table {
      margin: 0 auto 2rem;
    }
    .table-bordered th,
    .table-bordered td {
      padding: .5rem;
      vertical-align: middle;
    }
    /* Botón send */
    .btn-send {
      background-color: #ffff66;
      color: #000;
      border: none;
      padding: .5rem 2rem;
      border-radius: .25rem;
      font-weight: bold;
      min-width: 120px;
    }
    .btn-send:hover {
      background-color: #ffffcc;
    }
  </style>
</head>
<body>


  <!-- Formulario de usuario -->
  <div class="form-container">

    <!-- Carga librerías JS para hashing -->
    <script src="<?php echo $loc; ?>/sha256.js"></script>
    <script src="<?php echo $loc; ?>/hex.js"></script>

    <script>
      function computeHASH() {
        // Si las nuevas contraseñas no coinciden o son iguales a la vieja, abortar
        if (document.form1.passwordn1.value !== document.form1.passwordn2.value) return;
        if (document.form1.passwordn1.value === document.form1.passwordo.value) return;

        let username = document.form1.username.value;
        let userdesc = document.form1.userdesc.value;
        let userfull = document.form1.userfull.value;

        let passHASHo = js_myhash(
          js_myhash(document.form1.passwordo.value)
          + '<?php echo session_id(); ?>'
        );
        let passHASHn = bighexsoma(
          js_myhash(document.form1.passwordn2.value),
          js_myhash(document.form1.passwordo.value)
        );

        // Limpia los inputs originales
        document.form1.passwordo.value = '';
        document.form1.passwordn1.value = '';
        document.form1.passwordn2.value = '';

        // Redirige con GET al mismo option.php
        document.location = 'option.php'
          + '?username='   + encodeURIComponent(username)
          + '&userfullname='+ encodeURIComponent(userfull)
          + '&userdesc='    + encodeURIComponent(userdesc)
          + '&passwordo='   + passHASHo
          + '&passwordn='   + passHASHn;
      }
    </script>

    <form name="form1" onsubmit="computeHASH(); return false;">
      <table class="table table-bordered">
        <tbody>
          <tr>
            <th>Username:</th>
            <td>
              <input
                type="text"
                readonly
                name="username"
                class="form-control"
                value="<?php echo htmlspecialchars($a["username"]); ?>"
              >
            </td>
          </tr>
          <tr>
            <th>User Full Name:</th>
            <td>
              <input
                type="text"
                readonly
                name="userfull"
                class="form-control"
                value="<?php echo htmlspecialchars($a["userfullname"]); ?>"
              >
            </td>
          </tr>
          <tr>
            <th>User Description:</th>
            <td>
              <input
                type="text"
                name="userdesc"
                class="form-control"
                value="<?php echo htmlspecialchars($a["userdesc"]); ?>"
              >
            </td>
          </tr>
          <tr>
            <th>Old Password:</th>
            <td>
              <input
                type="password"
                name="passwordo"
                class="form-control"
              >
            </td>
          </tr>
          <tr>
            <th>New Password:</th>
            <td>
              <input
                type="password"
                name="passwordn1"
                class="form-control"
              >
            </td>
          </tr>
          <tr>
            <th>Retype New Password:</th>
            <td>
              <input
                type="password"
                name="passwordn2"
                class="form-control"
              >
            </td>
          </tr>
        </tbody>
      </table>

      <div class="text-center">
        <button type="submit" class="btn-send">send</button>
      </div>
    </form>
  </div>

  <!-- Bootstrap JS (opcional) -->
  <script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
  ></script>
</body>
</html>
