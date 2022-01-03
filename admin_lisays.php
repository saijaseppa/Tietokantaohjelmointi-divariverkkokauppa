<?php
// Tällä sivulla admin-roolin omaava käyttäjä voi lisätä uusia ylläpitäjiä
// verkkokauppaan. Keskustietokannan ylläpitäjä voi lisätä uuden ylläpitäjän 
// mille divarilel tahansa, mutta muiden divarien ylläpitäjät voivat lisätä '
// uuden ylläpitäjän vain omalle divarillensa. 

session_start();
//Luodaan tietokantayhteys ja tarkistetaan kirjautumistiedot.
require 'tietokantayhteys.php';
include 'logged_in.php';

//Muuttujien alustus.
$meili = $divari = $nimi = $puhe = $osoite_ = $ssana = $salasanaTark = $adminin_divari = "";
$salasanaTark_err = $email_err = $talletus = "" ;
$yliadmin = false;

//Kirjautuneen käyttäjän divari on nimessä (D1 tai D2)
$adminin_divari = $_nimi;
// Jos ylläpitäjä on koko keskustietokannan ylläpitäjä D0, hän voi päättää itse
// mille divarille lisää uuden ylläpitäjän. Tämä lippumuuttuja määrittää
// näytettävän html-lomakkeen sisältöä.
if($_nimi === "D0"){
    $yliadmin = true;
}

if (isset($_POST['lisaa']) && $_SERVER["REQUEST_METHOD"] == "POST") {
    //Muuttujat talletettavaksi tietokantaan. Osa on vakiotietoa, osa otetaan lomakkeelta.
    $rooli_ = "admin";
    $ssana = $_POST["ssana"];
    $meili = $_POST["meili"];
    $puhe = $_POST["puhelin"];

    // Tietokannan ylläpitäjä voi täyttää divarinimen lomakkeelle
    if ($yliadmin) {
        $divari = $_POST["divari"];
    } else {
        // Divarien ylläpitäjät voivat lisätä ylläpitäjän vain omalle divarillensa. 
        $divari = $adminin_divari;
    }

    // Tarkistetaan vastaavatko kahden salasanakentän tekstit toisiaan, jos eivät
    // annetaan virheviesti (tässä vielä käytettävyysongelma: koko lomake tyhjenee jos salasanat
    // ei vastaa toisiaan). 
    if ($_POST["salasanaTark"] !== $_POST["ssana"]) {
        $salasanaTark_err = "Salasanat eivät vastanneet toisiaan.";

        // Jos salasanat vastasivat toisiaan, tallenetaan tiedot tietokantaan ja siirrytään etusivulle. 
    } else {

        // Haetaan tietokannasta onko sähköposti jo talletettu tietokantaan.
        $email_query = pg_query('SELECT * FROM keskusdivari.kayttaja WHERE kayttaja_email = \'' . $meili . '\'');
        $rowCount = pg_num_rows($email_query);

        //Edetään, jos mitään kenttää ei ole jätetty tyhjäksi.
        if (!empty($meili) && !empty($divari) && !empty($puhe) && !empty($ssana) && !empty($_POST["salasanaTark"])) {
            // Katsotaan annetun sähköpostin olemassaolo aiemman kyselyn tuloksesta.
            if ($rowCount > 0) {
                $email_err = "Sähköpostiosoite on jo käytössä!";
            } else {
                //Käyttäjän antaman divarinimen perusteella haetaan divari-taulusta kyseisen divarin osoite.
                $osoite_query = pg_query('SELECT divari_osoite FROM keskusdivari.divari WHERE divari_nimi LIKE \'' . $divari . '%' . '\'');
                $osoite_taulu = pg_fetch_all_columns($osoite_query);

                // Suojataan tiedot ennen niiden lähettämistä tietokantaan. 
                $_meili = pg_escape_string($meili);
                $_nimi_ = pg_escape_string($divari);
                $_osoite_ = pg_escape_string($osoite_taulu[0]);
                $_puhelin_ = pg_escape_string($puhe);
                $_ssana = pg_escape_string($ssana);
                $_rooli_ = pg_escape_string($rooli_);

                //Talletetaan tiedot tietokantaan.
                $sql_talletus = pg_query('INSERT INTO keskusdivari.kayttaja (kayttaja_nimi, kayttaja_email, kayttaja_osoite, kayttaja_puhelin, kayttaja_rooli, kayttaja_salasana)
                            VALUES(\'' . $_nimi_ . '\', \'' . $_meili . '\', \'' . $_osoite_ . '\', \'' . $_puhelin_ . '\', \'' . $_rooli_ . '\', \'' . $_ssana . '\')');

                if (!$sql_talletus) {
                    die('Tietojen talletus epäonnistui! ');
                } else {
                    $talletus_onnistui = "Onnistui!";
                }
            }
        }
        else {
            $tietoja_puuttuu ="Tietoja puuttuu!";
        }
    }
}
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
    <title>Ylläpitäjän lisäys</title>
</head>

<body id="page-container">
    <div id="content-wrap">
        <?php include 'header.php'; ?>
        <div class="first-div">
            <blockquote class="blockquote text-left mt-4">
                <h2 class="mb-0"><small>Lisää uusi ylläpitäjä tästä:</small></h2>
                <h6 class="mb-0"><small>Kaikki kentät ovat pakollisia.</small></h6>
            </blockquote>
        </div>
        <div>
            <?php 
            if (isset($tietoja_puuttuu)) {
                echo
                '<div class="alert alert-danger" role="alert">
                Tietoja puuttui! Kaikki kentät ovat pakollisia. 
                </div>';
            } 
            elseif (isset($talletus_onnistui)) {
                echo
                '<div class="alert alert-success" role="alert">
                Uuden ylläpitäjän tallettaminen onnistui!  
                </div>';
            }
            ?>
        </div>
        <form class="row g-3 col-sm-8 mt-4" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">

            <div class="col-md-6 mt-2">
                <label for="meili" class="form-label">Sähköposti</label>
                <input type="email" id="meili" name="meili" class="form-control
                <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>">
                <span class="invalid-feedback"><?php echo $email_err; ?></span>
            </div>
            <div class="col-md-6 mt-2">
            <?php 
            // Keskustietokannan ylläpitäjälle näytettävä lomakeosuus:
            if ($yliadmin) { 
                echo 
                '<label for="divari" class="form-label">Divari, jolle ylläpitäjä lisätään (D1, D2, D3 ..)</label>
                <input type="text" class="form-control" id="divari" name="divari">';
            }
            else {
                // Divarien ylläpitäjille näytettävä lomakeosuus:
                echo 
                '<label for="divari" class="form-label">Divari, jolle ylläpitäjä lisätään (D1, D2, D3 ..)</label>
                <input type="text" class="form-control" placeholder="' . $adminin_divari . '" id="divari" name="divari" readonly>';
            }
            ?>
            </div>
            <div class="col-12 mt-3">
                <label for="puhelin" class="form-label">Puhelinnumero</label>
                <input type="text" class="form-control" id="puhelin" name="puhelin">
            </div>
            <div class="col-md-6 mt-2">
                <label for="ssana" class="form-label">Salasana</label>
                <input type="password" class="form-control" id="ssana" name="ssana" aria-describedby="passwordHelpBlock">
                <div id="passwordHelpBlock" class="form-text">
                    Salasanan tulee olla 8-15 merkkiä pitkä.
                </div>
            </div>
            <div class="col-md-6 mt-2">
                <label for="salasanaTark" class="form-label">Salasana uudelleen</label>
                <input type="password" id="salasanaTark" name="salasanaTark" class="form-control
            <?php echo (!empty($salasanaTark_err)) ? 'is-invalid' : ''; ?>">
                <span class="invalid-feedback"><?php echo $salasanaTark_err; ?></span>
            </div>

            <br><br>
            <div class="col-md-6 col-sm-8 mt-3">
                <button type="submit" class="btn btn-primary" id="lisaa" name="lisaa">Lisää uusi ylläpitäjä</button>
            </div>
            <div class="col-md-6 mt-3">
                <input type="reset" class="btn btn-primary" id="tyhjenna" name="tyhjenna"></input>
            </div>
        </form>

    </div>


    <?php include 'footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous">
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous">
    </script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous">
    </script>
</body>

</html>