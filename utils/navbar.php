        <div class="container-fluid">
          <div class="navbar-menu-wrapper d-flex align-items-center justify-content-between" style="position: relative; display: flex; padding-top: 1rem; padding-bottom: 0.5rem;align-items: flex-start; align-content: center; flex-wrap: wrap; height: 40px">
            <ul class="navbar-nav navbar-nav-left">
              <li class="nav-item ms-0 me-5 d-lg-flex d-none">
              </li>
              <!-- <li class="nav-item dropdown">
                <?php include("utils/notification.php"); ?>
              </li> -->
            </ul>
            <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-center">
            </div>
            <ul class="navbar-nav navbar-nav-right">
                <li class="nav-item nav-profile dropdown">
                  <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" id="profileDropdown">
                    <span class="text-white"><?php echo $name; ?></span>
                    <span class="online-status"></span>
                    <i class="mdi mdi-account-network text-white" style=""></i>
                  </a>
                  <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="profileDropdown">
                      <form action="/" method="POST">
                        <input type="hidden" name="logout">
                      <button class="dropdown-item" type="submit">
                        <i class="mdi mdi-logout text-primary"></i>
                        Logout
                      </button>
                      </form>
                      <!-- <button class="dropdown-item" type="button">Configurations</button> -->
                  </div>
                </li>
            </ul>
            <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-toggle="horizontal-menu-toggle">
              <span class="mdi mdi-menu text-white"></span>
            </button>
          </div>
        </div>