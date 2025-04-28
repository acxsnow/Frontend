<?php
////////////////////////////////////////////////////////////////////////////////
//BOCA Online Contest Administrator
//    Copyright (C) 2003-2012 by BOCA Development Team
//    (license omitted)
// Last modified 05/aug/2012 by cassio@ime.usp.br
////////////////////////////////////////////////////////////////////////////////

require 'header.php';

// Si se pide crear uno nuevo
if (isset($_GET["new"]) && $_GET["new"]=="1") {
    $n = DBNewContest();
    ForceLoad("contest.php?contest=$n");
}

// Determina el concurso actual
if (isset($_GET["contest"]) && is_numeric($_GET["contest"])) {
    $contest = $_GET["contest"];
} else {
    $contest = $_SESSION["usertable"]["contestnumber"];
}

// Carga info del concurso
if (($ct = DBContestInfo($contest)) == null) {
    ForceLoad("../index.php");
}
$main = ($ct["contestlocalsite"] == $ct["contestmainsite"]);

// Manejo de envío del formulario
if (
    isset($_POST["Submit3"])
    && isset($_POST["penalty"])           && is_numeric($_POST["penalty"])
    && isset($_POST["maxfilesize"])
    && isset($_POST["mainsite"])         && is_numeric($_POST["mainsite"])
    && isset($_POST["localsite"])        && is_numeric($_POST["localsite"])
    && isset($_POST["name"])             && $_POST["name"] != ""
    && isset($_POST["lastmileanswer"])   && is_numeric($_POST["lastmileanswer"])
    && isset($_POST["lastmilescore"])    && is_numeric($_POST["lastmilescore"])
    && isset($_POST["duration"])         && is_numeric($_POST["duration"])
    && isset($_POST["startdateh"])       && $_POST["startdateh"] >= 0 && $_POST["startdateh"] <= 23
    && isset($_POST["startdatemin"])     && $_POST["startdatemin"] >= 0 && $_POST["startdatemin"] <= 59
    && isset($_POST["startdated"])
    && isset($_POST["startdatem"])
    && isset($_POST["startdatey"])       && checkdate($_POST["startdatem"], $_POST["startdated"], $_POST["startdatey"])
    && isset($_POST["contest"])          && is_numeric($_POST["contest"])
) {
    if ($_POST["confirmation"] == "confirm") {
        $t = mktime(
            $_POST["startdateh"], $_POST["startdatemin"], 0,
            $_POST["startdatem"], $_POST["startdated"], $_POST["startdatey"]
        );
        $ac = ($_POST["Submit3"] == "Activate") ? 1 : 0;
        $param = [
            'number'           => $_POST["contest"],
            'name'             => $_POST["name"],
            'startdate'        => $t,
            'duration'         => $_POST["duration"] * 60,
            'lastmileanswer'   => $_POST["lastmileanswer"] * 60,
            'lastmilescore'    => $_POST["lastmilescore"] * 60,
            'penalty'          => $_POST["penalty"] * 60,
            'maxfilesize'      => $_POST["maxfilesize"] * 1000,
            'active'           => $ac,
            'mainsite'         => $_POST["mainsite"],
            'localsite'        => $_POST["localsite"],
            'mainsiteurl'      => $_POST["mainsiteurl"]
        ];
        DBUpdateContest($param);

        if ($ac == 1 && $_POST["contest"] != $_SESSION["usertable"]["contestnumber"]) {
            $cf = globalconf();
            if ($cf["basepass"] == "")
                MSGError("You must log in the new contest. The standard admin password is empty.");
            else
                MSGError("You must log in the new contest. The standard admin password is " . $cf["basepass"] . ".");
            ForceLoad("../index.php");
        }
    }
    ForceLoad("contest.php?contest=".$_POST["contest"]);
}

// Prepara la lista de concursos y marca si es "fake" (nuevo)
$cs = DBAllContestInfo();
$isfake = false;
foreach ($cs as $c) {
    if ($contest == $c["contestnumber"] && $c["contestnumber"] == 0) {
        $isfake = true;
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>BOCA – Contest Settings</title>

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
    .navbar-nav .nav-link {
      font-weight: bold;
      color: #000;
      padding: .5rem 1rem;
    }
    .navbar-nav .nav-link:hover {
      text-decoration: underline;
    }
    .form-container {
      border: 2px solid #ffff66;
      padding: 2rem;
      max-width: 800px;
      margin: 3rem auto;
    }
    .btn-custom {
      border: 1px solid #ffff66;
      background-color: #fff;
      color: #000;
      min-width: 100px;
      padding: .375rem .75rem;
    }
    .btn-custom:hover {
      background-color: #ffffcc;
    }
  </style>
</head>
<body>



  <div class="form-container">
    <script>
      function conf() {
        if (confirm("Confirm?")) {
          document.form1.confirmation.value = 'confirm';
        }
      }
      function newcontest() {
        document.location = 'contest.php?new=1';
      }
      function contestch(n) {
        if (n == null) {
          let k = document.form1.contest[document.form1.contest.selectedIndex].value;
          if (k == 'new') newcontest();
          else document.location = 'contest.php?contest=' + k;
        } else {
          document.location = 'contest.php?contest=' + n;
        }
      }
    </script>

    <?php if ($isfake) { ?>
      <h2 class="text-center mb-4">Select a contest or create a new one.</h2>
      <form name="form1" method="get" action="contest.php">
        <div class="row mb-3 align-items-center">
          <label class="col-sm-4 col-form-label text-end">Contest number:</label>
          <div class="col-sm-4">
            <select name="contest"
                    class="form-select d-inline-block"
                    style="width:auto"
                    onchange="contestch()">
              <?php foreach ($cs as $c) {
                echo '<option value="'.$c["contestnumber"].'">'
                     .$c["contestnumber"]
                     .($c["contestactive"]=="t"?"*":"")
                     ."</option>\n";
              } ?>
              <option value="new">new</option>
            </select>
          </div>
        </div>
      </form>
    <?php } else { ?>
      <form name="form1" enctype="multipart/form-data" method="post" action="contest.php">
        <input type="hidden" name="confirmation" value="noconfirm">

        <table class="table table-bordered mb-4">
          <tbody>
            <tr>
              <th>Contest number:</th>
              <td>
                <select name="contest"
                        class="form-select d-inline-block"
                        style="width:auto"
                        onchange="contestch()">
                  <?php foreach ($cs as $c) {
                    $sel = ($contest == $c["contestnumber"] ? "selected" : "");
                    echo '<option value="'.$c["contestnumber"].'" '.$sel.'>'
                         .$c["contestnumber"]
                         .($c["contestactive"]=="t"?"*":"")
                         ."</option>\n";
                  } ?>
                  <option value="new">new</option>
                </select>
              </td>
            </tr>
            <tr>
              <th>Name:</th>
              <td>
                <input
                  type="text"
                  name="name"
                  class="form-control"
                  value="<?php echo htmlspecialchars($ct["contestname"]); ?>"
                  maxlength="50"
                  <?php if (!$main) echo 'readonly'; ?>>
              </td>
            </tr>
            <tr>
              <th>Start date:</th>
              <td>
                hh:mm
                <input type="text" name="startdateh" class="form-control d-inline-block mx-1" style="width:3rem"
                  value="<?php echo date("H", $ct["conteststartdate"]); ?>"
                  maxlength="2" <?php if (!$main) echo 'readonly'; ?>>
                :
                <input type="text" name="startdatemin" class="form-control d-inline-block mx-1" style="width:3rem"
                  value="<?php echo date("i", $ct["conteststartdate"]); ?>"
                  maxlength="2" <?php if (!$main) echo 'readonly'; ?>>
                &nbsp; dd/mm/yyyy
                <input type="text" name="startdated" class="form-control d-inline-block mx-1" style="width:3rem"
                  value="<?php echo date("d", $ct["conteststartdate"]); ?>"
                  maxlength="2" <?php if (!$main) echo 'readonly'; ?>>
                /
                <input type="text" name="startdatem" class="form-control d-inline-block mx-1" style="width:3rem"
                  value="<?php echo date("m", $ct["conteststartdate"]); ?>"
                  maxlength="2" <?php if (!$main) echo 'readonly'; ?>>
                /
                <input type="text" name="startdatey" class="form-control d-inline-block mx-1" style="width:5rem"
                  value="<?php echo date("Y", $ct["conteststartdate"]); ?>"
                  maxlength="4" <?php if (!$main) echo 'readonly'; ?>>
              </td>
            </tr>
            <tr>
              <th>Duration (in minutes):</th>
              <td>
                <input type="text" name="duration" class="form-control"
                  value="<?php echo $ct["contestduration"]/60; ?>"
                  <?php if (!$main) echo 'readonly'; ?>>
              </td>
            </tr>
            <tr>
              <th>Stop answering (in minutes):</th>
              <td>
                <input type="text" name="lastmileanswer" class="form-control"
                  value="<?php echo $ct["contestlastmileanswer"]/60; ?>"
                  <?php if (!$main) echo 'readonly'; ?>>
              </td>
            </tr>
            <tr>
              <th>Stop scoreboard (in minutes):</th>
              <td>
                <input type="text" name="lastmilescore" class="form-control"
                  value="<?php echo $ct["contestlastmilescore"]/60; ?>"
                  <?php if (!$main) echo 'readonly'; ?>>
              </td>
            </tr>
            <tr>
              <th>Penalty (in minutes):</th>
              <td>
                <input type="text" name="penalty" class="form-control"
                  value="<?php echo $ct["contestpenalty"]/60; ?>"
                  <?php if (!$main) echo 'readonly'; ?>>
              </td>
            </tr>
            <tr>
              <th>Max file size allowed for teams (KB):</th>
              <td>
                <input type="text" name="maxfilesize" class="form-control"
                  value="<?php echo $ct["contestmaxfilesize"]/1000; ?>"
                  <?php if (!$main) echo 'readonly'; ?>>
              </td>
            </tr>
            <tr>
              <th>Your PHP config allows at most:</th>
              <td>
                <?php echo ini_get('post_max_size').'B (max post) and '.ini_get('upload_max_filesize').'B (max file)'; ?>
              </td>
            </tr>
            <tr>
              <th>Contest main site URL:</th>
              <td>
                <input type="text" name="mainsiteurl" class="form-control"
                  value="<?php echo htmlspecialchars($ct["contestmainsiteurl"]); ?>">
              </td>
            </tr>
            <tr>
              <th>Contest main site number:</th>
              <td>
                <input type="text" name="mainsite" class="form-control"
                  value="<?php echo $ct["contestmainsite"]; ?>">
              </td>
            </tr>
            <tr>
              <th>Contest local site number:</th>
              <td>
                <input type="text" name="localsite" class="form-control"
                  value="<?php echo $ct["contestlocalsite"]; ?>">
              </td>
            </tr>
          </tbody>
        </table>

        <div class="text-center">
          <button type="submit" name="Submit3" value="Send" class="btn btn-custom mx-2" onclick="conf()">Send</button>
          <button type="submit" name="Submit3" value="Activate" class="btn btn-custom mx-2" onclick="conf()">Activate</button>
          <button type="reset" class="btn btn-custom mx-2">Clear</button>
        </div>
      </form>
    <?php } // end if !$isfake ?>
  </div>

  <!-- Bootstrap JS Bundle (opcional) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
