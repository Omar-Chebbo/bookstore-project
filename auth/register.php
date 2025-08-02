<?php require "../includes/header.php";?>
<?php require "../config/config.php";?>

<?php
            if(isset($_SESSION['username'])){
                header("loaction: ".APPURL."");
            }
       //check if information is correct then add the new user in database


       if(isset($_POST['submit'])){

        $uppercaseChars = 0;
           $specialChars = 0;

           for($i = 0;$i < strlen($_POST['password']);$i++){
            if($_POST['password'][$i] >= 'A' && $_POST['password'][$i] <= 'Z'){
                $uppercaseChars++;
            }
            else if($_POST['password'][$i] < 'a' || $_POST['password'][$i] > 'z'){
                $specialChars++;
            }
           }

        //check if any inputs are empty
           if(empty($_POST['username']) or empty($_POST['email']) or empty($_POST['password'])){
            echo "<script>alert ('one or more inputs are empty');</script>";
           }
           
           else if(strlen($_POST['password']) < 8 ){
            echo "<script>alert ('password must contain more than 8 characters');</script>";
           }

           else if($uppercaseChars == 0 || $specialChars == 0){
            echo "<script>alert ('password must contain at least one uppercase letter and one special character');</script>";
           }
           
           else {
            $username=$_POST['username'];
            $email=$_POST['email'];
            $password=$_POST['password'];

            //insert querry
            $insert =$conn->prepare("INSERT INTO users (username, email, mypassword) 
            VALUES (:username, :email, :mypassword)");

           $insert->execute([
                ':username'   => $username,
                ':email'      => $email,
                ':mypassword' => password_hash($password, PASSWORD_DEFAULT),
            ]);
            


            header("location:login.php");
           }
       }
?>


        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <!-- <form class="d-flex">
            <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search">
            <button class="btn btn-outline-success" type="submit">Search</button>
        </form> -->
        <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
            <li class="nav-item">
            <a class="nav-link active" aria-current="page" href="#">Home</a>
            </li>
            <!-- <li class="nav-item">
            <a class="nav-link" href="#">Link</a>
            </li> -->
            <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                Username
            </a>
            <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                <li><a class="dropdown-item" href="#">Action</a></li>
                <li><a class="dropdown-item" href="#">Another action</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="#">Something else here</a></li>
            </ul>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">Login</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">Register</a>
            </li>
        </ul>
       
        </div>
    </div>
    </nav>

    <div class="container">

        <div class="row justify-content-center">
            <div class="col-md-6">
                <form class="form-control mt-5" method="post" action="register.php">  
                    <h4 class="text-center mt-3"> Register </h4> 
                    <div class="">
                        <label for="" class="col-sm-2 col-form-label">Username</label>
                        <div class="">
                            <input type="text" name="username" class="form-control" >
                        </div>
                    </div>
                    <div class="">
                        <label for="staticEmail" class="col-sm-2 col-form-label">Email</label>
                        <div class="">
                            <input type="email" name="email" class="form-control" id="" value="">
                        </div>
                    </div>
                    <div class="">
                        <label for="inputPassword" class="col-sm-2 col-form-label">Password</label>
                        <div class="">
                            <input type="password" name="password" class="form-control" id="inputPassword">
                        </div>
                    </div>
                    <button name="submit" class="w-100 btn btn-lg btn-primary mt-4 mb-4" type="submit">register</button>

                </form>
            </div>
        </div>
 
   

        
<?php require "../includes/footer.php";?>
 
