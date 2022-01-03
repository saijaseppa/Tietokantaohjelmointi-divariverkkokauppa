<?php
// Tällä sivulla on ylläpitäjän sivu, jolta voi siirtyä lisäämään uusia 
// ylläpitäjiä ja myyntikappaleita. Lisäksi myyntikappaleiden poistaminen onnistuu.

//Luodaan tietokantayhteys ja tarkistetaan kirjautumistiedot.
require 'tietokantayhteys.php';
include 'logged_in.php';

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
    <title>Ylläpitäjän toiminnot muokkaus</title>
</head>

<body id="page-container">
    <div id="content-wrap">
        <?php include 'header.php'; ?>
        <br><br>
        <div class="card w-50">
            <div class="card-body">
                <h5 class="card-title">Uuden ylläpitäjän lisäys</h5>
                <p class="card-text">Tästä voit lisätä uuden ylläpitäjän haluamallesi divarille.</p>
                <a href="admin_lisays.php" class="btn btn-primary">Lisäämään</a>
            </div>
        </div>
        <br><br>
        <div class="card w-50">
            <div class="card-body">
                <h5 class="card-title">Lisää uusi myyntikappale</h5>
                <p class="card-text">Tästä voit lisätä uusia teoksia ja myyntikappaleita verkkokauppaan.</p>
                <a href="teoksen_lisays.php" class="btn btn-primary">Lisäämään</a>
            </div>
        </div>
        <br><br>
        <!--<div>
            <p>Haluatko muuttaa salasanaasi?</p>
        </div>-->

        <?php include 'footer.php'; ?>

        <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
</body>

</html>