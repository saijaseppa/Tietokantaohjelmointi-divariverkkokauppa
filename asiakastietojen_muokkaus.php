<?php
// Kun kirjautuneena on asiakas, voi tarkastella ja muokata omia tietojaan.

//Luodaan tietokantayhteys ja tarkistetaan kirjautumistiedot.
require 'tietokantayhteys.php';
include 'logged_in.php';

// Tänne lomake, jolla asiakas voi tarkastella omia tietoja ja muokata niitä? Samalla kertaa vai? 
// Muutokset muihin kuin id on mahdollisia. 
// Asiakkaalle ei muita toimintoja? Vai olisiko tilin poistomahdollisuus hyvä ekstra asiakkaalle? <- täytyisi muistaa mainita
// ominaisuus-lomakkeella.

// Käyttäjän tiedot joita voisi muuttaa: kayttaja_nimi, kayttaja_email, kayttaja_osoite, kayttaja_puhelin, kayttaja_salasana
// [id tai roolia ei voi muuttaa.]





?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
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
    <title>Asiakastietojen muokkaus</title>
</head>

<body id="page-container">
    <div id="content-wrap">
      <?php include 'header.php';?>
        <div class="first-div">
            <blockquote class="blockquote text-center mt-4">
                <h2 class="mb-0"><small>Omien tietojen tarkastelu ja muokkaus</small></h2>
            </blockquote>
        </div>        
        <div class="first-div">
            <blockquote class="blockquote text-center mt-4">
            <p>Tälle sivulle tulossa asiakkaalle mahdollisuus muokata omia asiakastietojaan.</p>
            </blockquote>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
</body>

</html>
