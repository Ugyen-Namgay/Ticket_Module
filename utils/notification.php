<?php



$notifications=[["This event Happened","2020-01-01"],["That also happened","2022-01-01"]];
$notification_count=count($notifications);
?>

<a class="nav-link count-indicator dropdown-toggle d-flex align-items-center justify-content-center" id="notificationDropdown" href="#" data-bs-toggle="dropdown">
  <i class="mdi mdi-bell mx-0 text-white"></i>
  <?php if ($notification_count>0) {
    ?> 
      <span class="count bg-success"><?php echo $notification_count; ?></span>
    <?php
  }
  ?>         
  </a>
  <?php if ($notification_count>0) {
    ?>               
                <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list" aria-labelledby="notificationDropdown">
                  <p class="mb-0 font-weight-normal float-left dropdown-header bg-info text-dark">Announcements</p>
                  <?php 
                   foreach ($notifications as $n) {
                    ?>

                  <a class="dropdown-item preview-item">
                    <div class="preview-thumbnail">
                        <div class="preview-icon">
                          <i class="mdi mdi-adjust mx-0"></i>
                        </div>
                    </div>
                    <div class="preview-item-content">
                        <h6 class="preview-subject font-weight-normal"><?php echo $n[0]; ?></h6>
                        <p class="font-weight-light small-text mb-0 text-muted">
                          <?php echo $n[1]; ?>
                        </p>
                    </div>
                  </a>

                    <?php
                   }
                   ?>
                </div>
<?php
  }
?> 