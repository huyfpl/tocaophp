<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<title>Bootstrap Elegant Modal Login Modal Form with Avatar Icon</title>
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto|Varela+Round">
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
<link href="style.css" rel="stylesheet">
</head>
<body>
<div class="text-center">
    <!-- Button HTML (to Trigger Modal) -->
    
</div>

<!-- Modal HTML -->
<div id="myModal" class="modal fade">
    <div class="modal-dialog modal-login">
        <div class="modal-content">
            <div class="modal-header">
                <div class="avatar">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/2/21/Flag_of_Vietnam.svg/225px-Flag_of_Vietnam.svg.png" alt="Avatar">
                </div>              
                <h4 class="modal-title">Đăng nhập</h4>    
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            </div>
            <div class="modal-body">
                <form action="login.php" method="post">
                    <div class="form-group">
                        <input type="text" class="form-control" name="username" placeholder="Username" required="required"  id="username">        
                    </div>
                    <div class="form-group">
                        <input type="password" class="form-control" name="password" placeholder="Password" required="required"  id="password">    
                    </div>        
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-lg btn-block login-btn">Login</button>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <a href="#">Forgot Password?</a>
            </div>
        </div>
    </div>
</div>     
<script>
    $(document).ready(function(){
        $("#myModal").modal("show");

        // Xử lý khi đăng nhập thành công
        <?php if(isset($_GET['login_status']) && $_GET['login_status'] === "success"): ?>
            setTimeout(function() {
                $("#myModal").modal("hide");
            }, 1000); // 2 seconds delay before hiding modal
        <?php endif; ?>
    });
</script>

</body>
</html>
