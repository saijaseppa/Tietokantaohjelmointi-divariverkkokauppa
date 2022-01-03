<?php     
// Uloskirjautuminen, sessiomuuttujat tuhotaan ja siirrytään kirjautumissivulle.
    session_start();
    session_destroy();
      
    header("Location: tervetuloa.php")
;?>