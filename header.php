<header>
  <nav class="navbar navbar-expand-lg navbar-light bg-light">
      <a class="navbar-brand" href="etusivu.php">Divari</a>
      <button class="navbar-toggler" type="button"
       data-toggle="collapse" data-target="#navbarNavDropdown"
       aria-controls="navbarNavDropdown" aria-expanded="false"
       aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
      </button>
      <div class="navbar-collapse collapse w-100 order-3 dual-collapse2"
       id="navbarNavDropdown">
          <ul class="navbar-nav ml-auto">
              <li class="nav-item">
                  <a class="nav-link" href="hakusivu.php">Haku</a>
              </li>
              <li class="nav-item">
                  <a class="nav-link" href="ostoskori.php">Ostoskori</a>
              </li>
              <li class="nav-item dropdown">
                  <a class="nav-link dropdown-toggle"
                   href="#" id="navbarDropdownMenuLink"
                   data-toggle="dropdown" aria-haspopup="true"
                   aria-expanded="false">
                  <?php echo $_nimi ?>
                  </a>
                  <div class="dropdown-menu dropdown-menu-right"
                   aria-labelledby="navbarDropdownMenuLink">
                      <a class="dropdown-item" href="tilaukset.php">
                        Tilaukset
                      </a>
                      <a class="dropdown-item" href="omat_asetukset.php">
                        Omien tietojen muokkaus
                      </a>
                      <a class="dropdown-item" href="log_out.php">
                        Kirjaudu ulos
                      </a>
                  </div>
              </li>
          </ul>
      </div>
  </nav>
</header>
