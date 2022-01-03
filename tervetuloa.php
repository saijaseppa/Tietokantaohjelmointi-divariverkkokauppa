<?php
// Tervetuloa-sivu verkkokauppaan. Etusivulta on mahdollista kirjautua sivustolle tai uuden käyttäjän
// on mahdollista rekisteröityä.

// Aloitetaan sessio.
session_start();

//Luodaan tietokantayhteys
require 'tietokantayhteys.php';
require 'turvallisuus.php';

//Kirjautumistietojen muuttujat
$email_syote = $salasana_syote = "";
$email_err = $salasana_err = "";


if (isset($_POST['kirjaudu']) && $_SERVER["REQUEST_METHOD"] == "POST") {
  //suojataan merkkijonot
  $unsafemail = $_POST['email'];
  $unsafepwd = $_POST['salasana'];
  $email_syote = tarkista_syote($unsafemail);
  $salasana_syote = tarkista_syote($unsafepwd);
  echo $salasana_syote;

  // Tarkistetaan tietokannasta yhtenäväisyydet ja jos tiedot  ok, siirrytään etusivulle.

  // Jos sähköpostiosoitetta ei ole syötetty, annetaan virheviesti.
  if (empty($_POST["email"])) {
    $email_err = "Ole hyvä ja kirjoita sähköpostiosoitteesi.";
  }
  // Jos salasanaa ei ole syötetty, annetaan virheviesti.
  elseif (empty($_POST["salasana"])) {
    $salasana_err = "Ole hyvä ja kirjoita salasanasi.";
  } else {

    // Kysely tietokantaan, joka tarkastaa, löytyykö annettu sähköposti tietokannasta.
    $sql = 'SELECT * FROM keskusdivari.kayttaja WHERE kayttaja_email = \'' . $email_syote . '\'';
    $email_query = pg_query($sql);
    $rowCount = pg_num_rows($email_query);

    if (!$email_query) {
      die("Jokin meni pieleen..");
    }

    // Check if email exist
    if ($rowCount <= 0) {
      $email_err = '<div class="alert alert-danger">
      Käyttäjätiliä ei löytynyt annetulla sähköpostilla.
      </div>';
    } else {
      // Kun sähköposti löytyy tietokannasta, tarkastetaan salasanan oikeellisuus.
      // Fetch user data and store in php session
      $tulos = pg_query($sql);
      while ($rivi = pg_fetch_array($tulos)) {
        $id = $rivi['kayttaja_id'];
        $nimi = $rivi['kayttaja_nimi'];
        $osoite = $rivi['kayttaja_osoite'];
        $email = $rivi['kayttaja_email'];
        $puhelin = $rivi['kayttaja_puhelin'];
        $salasana = $rivi['kayttaja_salasana'];
      }

      // Testataan onko salasana sama kuin tietokantaan sähköpostille tallennettu. Jos
      // kirjautumistiedot on ollut oikein, kirjaudutaan sisäään, eli siirrytään etusivulle.
      if ($email_syote == $email && $salasana_syote == $salasana) {
        header('Location: etusivu.php');

        //Talletetaan sessiomuuttujat kirjautumistiedoista:
        $_SESSION['id'] = $id;
        $_SESSION['nimi'] = $nimi;
        $_SESSION['osoite'] = $osoite;
        $_SESSION['email'] = $email;
        $_SESSION['puhelin'] = $puhelin;
        $_SESSION['ostoskori'] = [];

      } else {
        $login_err = "Sähköpostiosoite tai salasana väärin!";
      }
    }
  }
}

if (isset($_POST['rekisteroidy']) && $_SERVER["REQUEST_METHOD"] == "POST") {
  // Kun käyttäjä klikkaa rekisteröidy-nappia, siirrytään sivulle,
  // jolla rekisteröityminen tehdään.
  header('Location: rekisteroityminen.php');
}
?>

<!DOCTYPE HTML>
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
  <title>Keskusdivari</title>
</head>

<body id="page-container">
  <div id="content-wrap">
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <a class="navbar-brand">Tervetuloa Keskusdivarin verkkokauppaan!</a>
    </nav>
    <div class="col-sm-8">
      <h4 class="col-sm-10 mt-3"><small>Kirjaudu sisään tai rekisteröidy.</small></h4>
      <br><br>

    </div>
    <div>
      <?php
      if(isset($login_err)) {
        echo
        '<div class="alert alert-danger">
        Sähköposti tai salasana väärin. Kokeile uudelleen.
        </div>';
      }
        ?>
    </div>

    <form class="mt-3" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
      <div class="mb-3 col-sm-8">
        <label for="email" class="form-label">Sähköposti</label>
        <input type="email" id="email" name="email" aria-describedby="emailHelp" class="form-control
            <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $email_syote; ?>">
        <span class="invalid-feedback"><?php echo $email_err; ?></span>
      </div>
      <div class="mb-3 col-sm-8">
        <label for="salasana" class="form-label">Salasana</label>
        <input type="password" id="salasana" name="salasana" class="form-control
            <?php echo (!empty($salasana_err)) ? 'is-invalid' : ''; ?>">
        <span class="invalid-feedback"><?php echo $salasana_err; ?></span>
      </div>

      <div class="mb-3 col-sm-8">
        <input type="hidden" name="kirjaudu" value="jep" />
        <button type="submit" class="btn btn-primary">Kirjaudu</button>
      </div>



      <div class="mb-3 col-sm-8">
        <p>Uusi asiakas? Rekisteröidy tästä!</p>
        <button type="submit" class="btn btn-primary" id="rekisteroidy" name="rekisteroidy">Rekisteröidy</button>
      </div>
    </form>
  </div>

  <?php include 'footer.php'; ?>

  <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

</body>

</html>

<?php
//Suljetaan tietokantayhteys.
pg_close($yhteys); ?>
