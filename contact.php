<?php require "includes/header.php"; ?>
<?php require "config/config.php"; ?>

<?php if (isset($_GET['sent'])): ?>
  <?php if ($_GET['sent'] == 1): ?>
    <div class="alert alert-success text-center mt-3">Your message was sent successfully!</div>
  <?php else: ?>
    <div class="alert alert-danger text-center mt-3">Sorry—something went wrong, please try again.</div>
  <?php endif; ?>
<?php endif; ?>

<section class="mb-4">

    <!--Section heading-->
    <h2 class="h1-responsive font-weight-bold text-center my-4">Contact us</h2>
    <!--Section description-->
    <p class="text-center w-responsive mx-auto mb-5">Do you have any questions? Please do not hesitate to contact us directly. Our team will come back to you within
        a matter of hours to help you.</p>

    <div class="row">

        <!--Grid column-->
        <div class="col-md-9 mb-md-0 mb-5">
            <form id="contact-form" name="contact-form" action="send_email.php" method="POST">

                <!--Grid row-->
                <div class="row">

                    <!--Grid column-->
                    <div class="col-md-6">
                        <div class="md-form mb-0">
                            <label for="name" class="">Your name</label>

                            <input type="text" id="name" name="name" class="form-control">
                        </div>
                    </div>
                    <!--Grid column-->

                    <!--Grid column-->
                    <div class="col-md-6">
                        <div class="md-form mb-0">
                            <label for="email" class="">Your email</label>

                            <input type="text" id="email" name="email" class="form-control">
                        </div>
                    </div>
                    <!--Grid column-->

                </div>
                <!--Grid row-->

                <!--Grid row-->
                <div class="row">
                    <div class="col-md-12">
                        <div class="md-form mb-0">
                            <label for="subject" class="">Subject</label>

                            <input type="text" id="subject" name="subject" class="form-control">
                        </div>
                    </div>
                </div>
                <!--Grid row-->

                <!--Grid row-->
                <div class="row">

                    <!--Grid column-->
                    <div class="col-md-12">

                        <div class="md-form">
                            <label for="message">Your message</label>

                            <textarea type="text" id="message" name="message" rows="2" class="form-control md-textarea"></textarea>
                        </div>

                    </div>
                </div>
                <!--Grid row-->

            </form>

            <div class="text-center text-md-left mt-4">
                <a class="btn btn-primary" onclick="document.getElementById('contact-form').submit();">Send</a>
            </div>
            <div class="status"></div>
        </div>
        <!--Grid column-->

        <!--Grid column-->
        <div class="col-md-3 text-center">
            <ul class="list-unstyled mb-0">
                <li><i class="fas fa-map-marker-alt fa-2x"></i>
                    <p>Lebanon , Elchouf, Barja</p>
                </li>

                <li><i class="fas fa-phone mt-4 fa-2x"></i>
                    <p>+961 03 36 74 47</p>
                </li>

                <li><i class="fas fa-envelope mt-4 fa-2x"></i>
                    <p>projecti439fr@gmail.com</p>
                </li>
            </ul>
        </div>
        <!--Grid column-->

    </div>

</section>
</div>
       
<footer class="bg-dark text-white text-center text-lg-start" style="margin-top: 40px">
    <!-- Grid container -->
    <div class="container p-4">
        <!--Grid row-->
        <div class="row">
        <!--Grid column-->
        <div class="col-lg-6 col-md-12 mb-4 mb-md-0">
            <h5 class="text-uppercase"></h5>

            <p>
            This BookStore is developped by Omar Chebbo, Ahmad Chebbo ,Muhamad El Tahesh.
            It's just for University Project
            </p>
        </div>
        <!--Grid column-->

        <!--Grid column-->
        <div class="col-lg-3 col-md-6 mb-4 mb-md-0">
            <h5 class="text-uppercase">Links</h5>

            <ul class="list-unstyled mb-0">
            <li>
                <a href="https://www.instagram.com/omar.chebbo?igsh=anp4dTRsc3J4bTgw" class="text-white">Omar Chebbo</a>
            </li>
            <li>
                <a href="https://www.instagram.com/ahmadkchebbo?igsh=MXJwemFjbmJrMnVlYQ==" class="text-white">Ahmad Chebbo</a>
            </li>
            <li>
                <a href="https://www.instagram.com/mohamed_tahech?igsh=NmdidXRlMnJzcWZt" class="text-white">Muhamad Tahech</a>
            </li>
            
            </ul>
        </div>
        <!--Grid column-->

        <!--Grid column-->
        
        <!--Grid column-->
        </div>
        <!--Grid row-->
    </div>
    <!-- Grid container -->

    <!-- Copyright -->
    <div class="text-center p-3" style="background-color: rgba(0, 0, 0, 0.2);">
         
        <a class="text-white" href="https://mdbootstrap.com/"></a>
    </div>
    <!-- Copyright -->
</footer>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="" crossorigin="anonymous"></script>
</body>

</html>
