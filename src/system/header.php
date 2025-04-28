<?php
////////////////////////////////////////////////////////////////////////////////
//BOCA Online Contest Administrator
//    Copyright (C) 2003-2012 by BOCA Development Team (bocasystem@gmail.com)
//
//    This program is free software: you can redistribute it and/or modify
//    it under the terms of the GNU General Public License as published by
//    the Free Software Foundation, either version 3 of the License, or
//    (at your option) any later version.
//
//    This program is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU General Public License for more details.
//    You should have received a copy of the GNU General Public License
//    along with this program.  If not, see <http://www.gnu.org/licenses/>.
////////////////////////////////////////////////////////////////////////////////
// Last modified 05/aug/2012 by cassio@ime.usp.br
ob_start();
header ("Expires: " . gmdate("D, d M Y H:i:s") . " GMT");
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");
header ("Pragma: no-cache");
header ("Content-Type: text/html; charset=utf-8");
session_start();
ob_end_flush();
require_once('../version.php');

require_once("../globals.php");
require_once("../db.php");

echo "<html><head><title>System's Page</title>\n";
echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">\n";
echo "<link href=\"https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css\" rel=\"stylesheet\">\n";
echo "<style>\n";
echo "body {\n";
echo "  font-family: serif;\n";
echo "  background-color: #fff;\n";
echo "}\n";
echo "header {\n";
echo "  background-color: #ffff66;\n";
echo "  padding: 1rem;\n";
echo "  text-align: center;\n";
echo "}\n";
echo "header h1 {\n";
echo "  margin: 0;\n";
echo "  font-size: 2.5rem;\n";
echo "  letter-spacing: 2px;\n";
echo "}\n";
echo ".navbar {\n";
echo "  background-color: #fff;\n";
echo "  border-bottom: 1px solid #ccc;\n";
echo "}\n";
echo ".navbar-nav .nav-link {\n";
echo "  font-weight: bold;\n";
echo "  color: #000;\n";
echo "  padding: .5rem 1rem;\n";
echo "}\n";
echo ".navbar-nav .nav-link:hover {\n";
echo "  text-decoration: underline;\n";
echo "}\n";
echo ".form-container {\n";
echo "  border: 2px solid #ffff66;\n";
echo "  padding: 2rem;\n";
echo "  max-width: 600px;\n";
echo "  margin: 3rem auto;\n";
echo "}\n";
echo ".form-container h2 {\n";
echo "  text-align: center;\n";
echo "  margin-bottom: 2rem;\n";
echo "  font-weight: bold;\n";
echo "}\n";
echo ".input-inline {\n";
echo "  display: inline-block;\n";
echo "  width: 4rem;\n";
echo "}\n";
echo "</style>\n";

echo "</head><body>\n";

echo "<!-- Header -->\n";
echo "<header>\n";
echo "  <a href=\"../index.php\" style=\"text-decoration: none; color: inherit;\">\n";
echo "    <h1>BOCA</h1>\n";
echo "  </a>\n";
echo "</header>\n";

echo "<!-- Menú -->\n";
echo "<nav class=\"navbar navbar-expand-sm justify-content-center\">\n";
echo "  <ul class=\"navbar-nav\">\n";
echo "    <li class=\"nav-item\">\n";
echo "      <a class=\"nav-link\" href=\"contest.php\">Contest</a>\n";
echo "    </li>\n";
echo "    <li class=\"nav-item\">\n";
echo "      <a class=\"nav-link\" href=\"option.php\">Options</a>\n";
echo "    </li>\n";
echo "    <li class=\"nav-item\">\n";
echo "      <a class=\"nav-link\" href=\"../index.php\">Logout</a>\n";
echo "    </li>\n";
echo "  </ul>\n";
echo "</nav>\n";

?>
