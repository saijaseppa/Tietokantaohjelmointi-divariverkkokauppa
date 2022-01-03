<?php
  // Tämän tiedoston koodilla muodostetaan yhteys tietokantaan.
  require_once(__DIR__ . '/dotenv.php');

  $dotenv = new DevCoder\DotEnv(__DIR__ . '/.env');
  $dotenv->load();

  $dbname = getenv('dbname');
  $user = getenv('user');
  $password = getenv('password');

  $y_tiedot = "dbname=$dbname user=$user password=$password";

  if (!$yhteys = pg_connect($y_tiedot)) {
      die("Tietokantayhteyden luominen epäonnistui.");
  }
?>
