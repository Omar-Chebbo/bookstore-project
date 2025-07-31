<?php require "../config/config.php"; ?>
<?php require "../includes/header.php"; ?>

<!DOCTYPE html>
<html>
<head>
  <title>Thank You</title>
  </head>
  <body>
    <h2 style="text-align:center;">Thank you for your purchase!</h2>
      <p style="text-align:center;">Your download should start shortly. You will be redirected to the home page...</p>

        <!-- Hidden iframe triggers the download -->
          <iframe src="<?php echo APPURL; ?>/download.php" style="display:none;"></iframe>

            <!-- Redirect after 5 seconds -->
              <script>
                  setTimeout(function() {
                        window.location.href = "<?php echo APPURL; ?>";
                            }, 5000);
                              </script>
                              </body>
                              </html>

                              <?php require "../includes/footer.php"; ?>