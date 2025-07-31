<?php require "../includes/header.php"; ?>
<?php require "../config/config.php"; ?>
<?php
        //get the data about the product to add it to cart
        $backLink = APPURL . '/index.php';
if (!empty($_SERVER['QUERY_STRING'])) {
    $query = $_SERVER['QUERY_STRING'];
    parse_str($query, $queryParams);
    unset($queryParams['id']);
    if (!empty($queryParams)) {
        $backLink = APPURL . '/index.php?' . http_build_query($queryParams);
    }
}

        if(isset($_POST['submit'])){
            $pro_id=$_POST['pro_id'];
            $pro_name=$_POST['pro_name'];
            $pro_image=$_POST['pro_image'];
            $pro_price=$_POST['pro_price'];
            $pro_amount=$_POST['pro_amount'];
            $pro_file=$_POST['pro_file'];
            $user_id=$_POST['user_id'];

            $insert=$conn->prepare("INSERT INTO cart(pro_id,pro_name,pro_image,pro_price,pro_amount,pro_file,user_id)
            VALUES(:pro_id, :pro_name, :pro_image, :pro_price, :pro_amount, :pro_file, :user_id)");

            $insert->execute([
                ':pro_id'=>$pro_id,
                ':pro_name'=>$pro_name,
                ':pro_image'=>$pro_image,
                ':pro_price'=>$pro_price,
                ':pro_amount'=>$pro_amount,
                ':pro_file'=>$pro_file,
                ':user_id'=>$user_id,
            ]);



        }

        // get the info of the product to show it 
        if(isset($_GET['id'])){
            $id=$_GET['id'];

            if (isset($_SESSION['user_id'])){
            //checking if product in cart
            $select=$conn->query("SELECT * FROM cart WHERE pro_id ='$id' AND user_id='$_SESSION[user_id]' ");
            $select->execute();
            }

            if (isset($_SESSION['user_id'])){
            //getting id for wishlist
            $select_wishlist = $conn->query("SELECT * FROM wishlist WHERE pro_id ='$id' AND user_id='$_SESSION[user_id]' ");
            $select_wishlist->execute();

            $fetch = $select_wishlist->fetch(PDO::FETCH_OBJ);
            }




            //getting data for every product
            $row=$conn->query("SELECT * FROM products WHERE status =1 AND id='$id'");

            $row->execute();

            $product=$row->fetch(PDO::FETCH_OBJ);




        }else{
            header("location: ".APPURL."/404.php");
        }
        $backLink = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : APPURL . '/';

?>



        <div class="row d-flex justify-content-center mt-4">
            <div class="col-md-10">
                <div class="card">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="images p-3">
                                <div class="text-center p-4"> <img id="main-image" src="../admin-panel/products-admins/images/<?php echo $product->image; ?>" width="250" /> </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="product p-4">
                                <div class="d-flex justify-content-between align-items-center">
                                <!-- Your Back button in single.php is hardcoded:
                                 This always takes the user to the homepage (/), even if they came from a filtered search.
                                ou can dynamically take the user back to the previous page like this: -->
                                    <div class="d-flex align-items-center"> <a href="<?php echo htmlspecialchars($backLink); ?>" class="ml-1 btn btn-primary">
  <i class="fa fa-long-arrow-left"></i> Back
</a> </div> <i class="fa fa-shopping-cart text-muted"></i>
                                </div>
                                <div class="mt-4 mb-3"> 
                                    <h5 class="text-uppercase"><?php echo $product->name;?></h5>
                                    <div class="price d-flex flex-row align-items-center"> <span class="act-price"><?php echo $product->price;?> $</span>
                                    </div>
                                </div>
                                <p class="about"><?php echo $product->description;?></p>
                              <form method="post" id="form-data">
                                <div class="">
                                     <input type="hidden"  name='pro_id' value="<?php echo $product->id;?>" class="form-control" >
                                </div>
                                <div class="">
                                     <input type="hidden"  name='pro_name' value="<?php echo $product->name;?>" class="form-control" >
                                </div>
                                <div class="">
                                     <input type="hidden"  name='pro_image' value="<?php echo $product->image;?>" class="form-control" >
                                </div>
                                <div class="">
                                     <input type="hidden"  name='pro_price' value="<?php echo $product->price;?>" class="form-control" >
                                </div>
                                <div class="">
                                     <input type="hidden"  name='pro_amount' value="1" class="form-control" >
                                </div>
                                <div class="">
                                     <input type="hidden"  name='pro_file' value="<?php echo $product->file;?>" class="form-control" >
                                </div>
                                <?php if(isset($_SESSION['user_id'])) : ?>
                                <div class="">
                                     <input type="hidden"  name='user_id' value="<?php echo $_SESSION['user_id'];?>" class="form-control" >
                                </div>
                                <?php endif;?>

                                <div class="cart mt-4 align-items-center">
                                <?php if(isset($_SESSION['user_id'])) : ?>
                                    <?php if($select->rowCount() > 0) : ?>
                                        <button id="submit" name="submit" type="submit" disabled  class="btn btn-primary text-uppercase mr-2 px-4"><i class="fas fa-shopping-cart"></i> Added to cart</button> </div>

                                    <?php else : ?>                             
                                         <button id="submit" name="submit" type="submit"  class="btn btn-primary text-uppercase mr-2 px-4"><i class="fas fa-shopping-cart"></i> Add to cart</button> </div>
                                    <?php endif ;?>
                                <?php endif ;?>
                            </div>
                            <?php if(isset($_SESSION['user_id'])) : ?>
                                <?php if($select_wishlist->rowCount() > 0) : ?>
                            <button  name="submit"  value="<?php echo $fetch->id;?>"  class="btn-delete-wishlist btn btn-primary text-uppercase mr-2 px-4"><i class="fas fa-heart"></i> Added to wishlist</button> 
                                    <?php else:?>
                            <button  name="submit"  class="wishlist-btn btn btn-primary text-uppercase mr-2 px-4"><i class="fas fa-heart"></i> Add to wishlist</button> 
                            <?php endif;?>
                            <?php endif;?>
                        </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
  </div>
  <?php require "../includes/footer.php";?>
 
<script> 
// add product to cart by JQuerry
    $(document).ready(function(){

        $(document).on("submit",function(e) {
            //to prevent the refresh of the page, without her every refresh will add to cart
           e.preventDefault();
           var formdata=$("#form-data").serialize()+'&submit=submit';
           $.ajax({
                    type: "post",
                    url: "single.php?id=<?php echo $id; ?>",
                    data: formdata,

                    success: function(){
                        alert("added to cart successfully");
                        // only add prodcut once
                        $("#submit").html("<i class='fas fa-shopping-cart'></i> Added to cart").prop("disabled",true);
                        ref();
                    }
           });
           function ref() {

               
                    $("body").load("single.php?id=<?php echo $id; ?>");
               
              }

        });

        $(".wishlist-btn").on("click",function(e) {
            //to prevent the refresh of the page, without her every refresh will add to cart
           e.preventDefault();
           var formdata=$("#form-data").serialize()+'&submit=submit';
           $.ajax({
                    type: "post",
                    url: "wishlist.php",
                    data: formdata,

                    success: function(){
                        alert("added to wishlist successfully");
                        $(".wishlist-btn").html("<i class='fas fa-heart'></i> Added to wishlist").addClass("btn-delete-wishlist").removeClass("wishlist-btn");
                        ref();
                    }
           });
           

        });

    
    });

    function ref() {

               
                    $("body").load("single.php?id=<?php echo $id; ?>");
               
              }

    $(".btn-delete-wishlist").on('click', function(e) {
      e.preventDefault();
      var id = $(this).val();


      $.ajax({
        type: "POST",
        url: "delete-item-wishlist.php",
        data: {
          delete: "delete",
          id: id,
        },

        success: function() {
          alert("Product deleted successfully from wishlist");
          $(".btn-delete-wishlist").html("<i class='fas fa-heart'></i> Added to wishlist").addClass("wishlist-btn").removeClass("btn-delete-wishlist");
          ref();
        }
        
      });

    });
</script>
