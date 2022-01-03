<?php
session_start();

//Luodaan tietokantayhteys ja tarkistetaan kirjautumistiedot.
require 'tietokantayhteys.php';
include 'logged_in.php';

$_tab = 'Divarin etusivu';

$nimi = 'teos_nimi';
$tekija = 'teos_tekija';
$julkaisuvuosi = 'teos_vuosi';
$tyyppi = 'teos_tyyppi';
$luokka = 'teos_luokka';

$query = "SELECT DISTINCT teos_nimi, teos_tekija, teos_vuosi, teos_tyyppi, teos_luokka" ."
          FROM keskusdivari.mkpl_tiedot"."
          WHERE mkpl_tila ='1'";

$tulos = pg_query($query);

$teokset_taulukko = pg_fetch_all($tulos);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet"
     href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css"
     integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm"
     crossorigin="anonymous">
    <style>
        #page-container {
            position: relative;
            min-height: 100vh;
        }

        #content-wrap {
            padding-bottom: 3rem;
        }

        #footer {
            position: absolute;
            bottom: 0;
            width: 100%;
            height: 3rem;
        }
    </style>
    <title>Divarin etusivu</title>
</head>

<body id="page-container">
    <div id="content-wrap">
        <?php include 'header.php';?>
        <div class="first-div">
            <blockquote class="blockquote text-center mt-4">
                <h2 class="mb-0"><small>Tervetuloa Divarin etusivulle!</small></h2>
            </blockquote>
        </div>
        <?php if (isset($tulos)) : ?>
        <div class='container'>
        <h3 class="mb-0"><small> Teoksia tietokannassamme </small></h3>
        <table class="table">
          <thead>
            <th>Nimi</th>
            <th>Tekijä</th>
            <th>Julkaisuvuosi</th>
            <th>Tyyppi</th>
            <th>Luokka</th>
          </thead>
          <tbody>
            <?php
              foreach ($teokset_taulukko as $rivi) {
              echo
                  '<tr>
                    <td>'.$rivi[$nimi].'</td>
                    <td>'.$rivi[$tekija].'</td>
                    <td>'.$rivi[$julkaisuvuosi].'</td>
                    <td>'.$rivi[$tyyppi].'</td>
                    <td>'.$rivi[$luokka].'</td>
                    <td>
                  </tr>';
             }?>
          </tbody>
        </table>
        </div>
        <?php endif; ?>
    </div>

    <?php include 'footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"
     integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN"
     crossorigin="anonymous">
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"
     integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q"
     crossorigin="anonymous">
    </script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"
     integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl"
     crossorigin="anonymous">
    </script>
  </body>
</html>
