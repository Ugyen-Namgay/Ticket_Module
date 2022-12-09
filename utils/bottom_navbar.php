        <div class="container">
            <ul class="nav page-navigation">
              <li class="nav-item <?php echo (strpos($_SERVER["REQUEST_URI"],"/home")!==false)?"active":""?>">
                <a class="nav-link" href="/">
                  <i class="mdi mdi-file-document-box menu-icon"></i>
                  <span class="menu-title">Dashboard</span>
                </a>
              </li>
              <!-- <li class="nav-item">
                  <a href="/luckydraw" target="_blank" class="nav-link">
                    <i class="mdi mdi-ticket menu-icon"></i>
                    <span class="menu-title">Lucky Draw</span>
                  </a>
              </li> -->
              
              <!-- <li class="nav-item <?php echo (strpos($_SERVER["REQUEST_URI"],"/quizwinner")!==false)?"active":""?>">
                  <a href="/quizwinner" target="_blank" class="nav-link">
                    <i class="mdi mdi-medal menu-icon"></i>
                    <span class="menu-title">Select Winner</span>
                  </a>
              </li>
              <li class="nav-item <?php echo (strpos($_SERVER["REQUEST_URI"],"/questions")!==false)?"active":""?>">
                  <a href="/questions" class="nav-link">
                    <i class="mdi mdi-timetable menu-icon"></i>
                    <span class="menu-title">Manage Questions</span>
                  </a>
              </li> -->
              <li class="nav-item <?php echo (strpos($_SERVER["REQUEST_URI"],"/eventluckydrawsettings")!==false)?"active":""?>">
                  <a href="/eventluckydrawsettings" target="_blank" class="nav-link">
                    <i class="mdi mdi-star mdi-spin menu-icon"></i>
                    <span class="menu-title">Lucky Draw</span>
                  </a>
              </li>
              <li class="nav-item <?php echo (strpos($_SERVER["REQUEST_URI"],"/events")!==false)?"active":""?>">
                  <a href="/events" class="nav-link">
                    <i class="mdi mdi-timetable menu-icon"></i>
                    <span class="menu-title">Manage Events</span>
                  </a>
              </li>
              <li class="nav-item <?php echo (strpos($_SERVER["REQUEST_URI"],"/users")!==false)?"active":""?>">
                  <a href="/users" class="nav-link">
                    <i class="mdi mdi-account-multiple-plus menu-icon"></i>
                    <span class="menu-title">Manage Users</span>
                  </a>
              </li>
              <!-- <li class="nav-item">
                  <a href="pages/forms/basic_elements.html" class="nav-link">
                    <i class="mdi mdi-chart-areaspline menu-icon"></i>
                    <span class="menu-title">Form Elements</span>
                    <i class="menu-arrow"></i>
                  </a>
              </li>
              <li class="nav-item">
                  <a href="pages/charts/chartjs.html" class="nav-link">
                    <i class="mdi mdi-finance menu-icon"></i>
                    <span class="menu-title">Charts</span>
                    <i class="menu-arrow"></i>
                  </a>
              </li>
              <li class="nav-item">
                  <a href="#" class="nav-link">
                    <i class="mdi mdi-codepen menu-icon"></i>
                    <span class="menu-title">Sample Pages</span>
                    <i class="menu-arrow"></i>
                  </a>
                  <div class="submenu">
                      <ul class="submenu-item">
                          <li class="nav-item"><a class="nav-link" href="pages/samples/login.html">Login</a></li>
                          <li class="nav-item"><a class="nav-link" href="pages/samples/login-2.html">Login 2</a></li>
                          <li class="nav-item"><a class="nav-link" href="pages/samples/register.html">Register</a></li>
                          <li class="nav-item"><a class="nav-link" href="pages/samples/register-2.html">Register 2</a></li>
                          <li class="nav-item"><a class="nav-link" href="pages/samples/lock-screen.html">Lockscreen</a></li>
                      </ul>
                  </div>
              </li> -->
            </ul>
        </div>
