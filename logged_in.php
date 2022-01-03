<?php
// Tällä tiedostolla tarkistetaan, onko käyttäjä kirjautuneena sisään.

//Luodaan tietokantayhteys
require 'tietokantayhteys.php';

//Haetaan sessiomuuttujista muuttujat tietona kirjautuneesta käyttäjästä
$_id = $_SESSION['id'];
$_nimi = $_SESSION['nimi'];
$_osoite = $_SESSION['osoite'];
$_email = $_SESSION['email'];
$_puhelin = $_SESSION['puhelin'];

//Tarkistetaan, onko käyttäjä kirjautuneena, jos ei, niin ohjataan
//kirjautumissivulle. Kirjautuneelle käyttäjälle etusivu näytetään.
if (empty($_nimi)) {
    header('Location: tervetuloa.php');
}

?>