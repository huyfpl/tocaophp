<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}

require_once 'config.php';

$conn = new mysqli($servername, $username, $password, $dbname);
mysqli_set_charset($conn, 'UTF8');
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
$isAdmin = false;

// Kiểm tra xem user_id có trong bảng admin và có role là 1 (admin) hay không
$user_id = $_SESSION["user_id"];
$sql_check_admin = "SELECT role FROM admin WHERE user_id = ?";
$stmt = $conn->prepare($sql_check_admin);
$stmt->bind_param("i", $user_id);

if ($stmt->execute()) {
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        if ($row["role"] == 1) {
            $isAdmin = true;
        }
    }
} else {
    die("Lỗi khi thực hiện câu truy vấn: " . $conn->error);
}
$_SESSION["admin"] = $isAdmin;

$stmt->close();

$sql = "SELECT * FROM users WHERE user_id != '{$_SESSION['user_id']}'";
$result = $conn->query($sql);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $reported_user_id = $_POST["reported_user_id"];
    $report_content = $_POST["reportContent"];
    $report_datetime = $_POST["reportDateTime"];
    $report_Image = $_POST["reportImage"];
    $reported_fullname = $_POST["reported_fullname"];
    $_SESSION['reported_fullname'] = $reported_fullname;
    // Xử lý lưu thông tin tố cáo vào bảng report_relationships
    $sql_insert_relationship = "INSERT INTO report_relationships (reporter_id, accused_id) 
                                VALUES ('{$_SESSION['user_id']}', '$reported_user_id')";

    if ($conn->query($sql_insert_relationship) === TRUE) {
        // Lấy ID report_relationships vừa thêm
        $relationship_id = $conn->insert_id;

        // Tiếp theo, lưu thông tin báo cáo vào bảng reports
        $sql_insert_report = "INSERT INTO reports (report_content, report_date, id_report_relationships,reportImage) 
                             VALUES ('$report_content', '$report_datetime', '$relationship_id','$report_Image')";

        if ($conn->query($sql_insert_report) === TRUE) {
            // Thành công: Điều hướng người dùng đến trang thành công
            $_SESSION['report_success'] = true;
            header("Location: tocao.php"); // Thay 'success.php' bằng đường dẫn của trang thành công
            exit();
        } else {
            // Lỗi: Điều hướng người dùng đến trang báo lỗi
            header("Location: error.php"); // Thay 'error.php' bằng đường dẫn của trang báo lỗi
            exit();
        }
    } else {
        // Lỗi: Điều hướng người dùng đến trang báo lỗi
        header("Location: error.php"); // Thay 'error.php' bằng đường dẫn của trang báo lỗi
        exit();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://www.gstatic.com/firebasejs/8.6.8/firebase-app.js"></script>
    <script src="https://www.gstatic.com/firebasejs/8.6.8/firebase-storage.js"></script>
    <link rel="stylesheet" href="tocao.css">
    <style>
        /* CSS cho progress bar */
        .modal-content {
            position: relative;
            /* Cần có vị trí tương đối để dùng để canh giữa */
        }

        .progressBar {
            display: flex;
            align-items: center;
            /* Căn giữa dọc */
            justify-content: center;
            /* Căn giữa ngang */
            position: absolute;
            /* Đặt vị trí tuyệt đối so với modal-content */
            top: 0;
            /* Đặt top là 50% để căn giữa theo chiều dọc */
            left: 0;
            /* Đặt left là 0 để căn giữa theo chiều ngang */
            right: 0;
            /* Đặt right là 0 để căn giữa theo chiều ngang */
            transform: translateY(-50%);
            /* Đặt lại top thành -50% để căn giữa hoàn toàn theo chiều dọc */
            background-color: #f1f1f1;
            /* Màu nền */
            padding: 10px;
            /* Khoảng cách xung quanh progress bar */
            border-radius: 4px;
            /* Bo viền */
        }

        .progress {
            width: 0;
            height: 20px;
            background-color: #4CAF50;
            border-radius: 4px;
        }

        .bouncing-text {
            display: flex;
            position: absolute;
            top: -35%;
            left: 42%;


        }

        .bouncing-text>span {
            animation: shake 0.5s infinite;
            font-size: 2rem;

            color: yellow;
        }

        @keyframes shake {
            0% {
                transform: translate(0);
            }

            25% {
                transform: translate(-5px, -5px) rotate(-5deg);
            }

            50% {
                transform: translate(5px, 5px) rotate(5deg);
            }

            75% {
                transform: translate(-5px, -5px) rotate(-5deg);
            }

            100% {
                transform: translate(0);
            }
        }
    </style>
    <title>Tố Cáo</title>
</head>

<body>
    <?php if ($isAdmin) : ?>
        <!-- Nếu có quyền admin, hiển thị nút để mở file danhsachtocao.php -->
        <div style="margin:auto;text-align: center;margin-top: 20px;" ;>
            <a href="danhsachtocao.php" class="btn btn-primary">Danh sách đã tố cáo</a>
        </div>

    <?php else : ?>
        <!-- Nếu không có quyền admin, ẩn nút -->
        <p style="margin: auto; text-align: center; font-size:25px">Chào mừng đến với ban có thưởng </p>
    <?php endif; ?>
    <h2 style="margin:auto;text-align: center;margin-top: 20px;">Danh sách người dùng </h2>
    <h2 style="margin:auto;text-align: center;margin-top: 20px; color:aqua">Hi <?php echo $_SESSION["fullname"]; ?></h2>
    <a style=" font-size: 20px;
            font-weight: bold;  margin:auto;text-align: center;margin-top: 20px; display: block;" href="index.php"><i class="fa fa-sign-out" aria-hidden="true"></i> Đăng xuất</a>
    <div class="container">
        <div class="table-responsive">
            <table class="table table-bordered table-striped" style="margin-top:50px">
                <thead class="table__head">
                    <tr class="winner__table">
                        <th><i class="fa fa-trophy" aria-hidden="true"></i> <strong>id</strong></th>
                        <th><i class="fa fa-user" aria-hidden="true"></i> <strong>Họ và tên</strong></th>
                        <th><i class="fa fa-bullhorn" aria-hidden="true"></i> <strong>Tố cáo</strong></th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) : ?>
                        <tr>
                            <td><?php echo $row["user_id"]; ?></td>
                            <td><?php echo $row["fullname"]; ?></td>
                            <td>
                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#reportModal<?php echo $row["user_id"]; ?>" data-backdrop="false">Tố cáo</button>
                                <!-- Modal Tố Cáo -->

                                <?php
                                if (isset($_SESSION['report_success']) && $_SESSION["report_success"] === true) {
                                    echo "<script>
        $(window).on('load', function(){
            $('#myModalsucess').modal('show');
        });
    </script>";
                                    unset($_SESSION['report_success']); // Unset the session variable after using it
                                }
                                ?>
                                <div id='myModalsucess' class='modal fade'>
                                    <div class='modal-dialog modal-confirm'>
                                        <div class='modal-content'>
                                            <div class='modal-header'>
                                                <div class='icon-box'>
                                                    <i class="fa fa-snowflake-o" aria-hidden="true"></i>
                                                </div>
                                                <button type='button' class='close' data-dismiss='modal' aria-hidden='true'>&times;</button>
                                            </div>
                                            <div class='modal-body text-center'>
                                                <div class="bouncing-text">
                                                    <span>Ố là la !</span>
                                                </div>
                                                <p style="font-weight: bold; color:red">Tí tớ bảo "<?php echo $_SESSION['reported_fullname']; ?>" ná nêu nêu... dám tố cáo bn ấy ak</p>
                                                <button class='btn btn-success' data-dismiss='modal'><span>Xong </span> <i class="fa fa-gift" aria-hidden="true"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Modal Tố Cáo -->
                                <div class="modal fade" id="reportModal<?php echo $row["user_id"]; ?>" tabindex="-1" role="dialog" aria-labelledby="reportModalLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="reportModalLabel">Tố Cáo <?php echo $row["fullname"]; ?></h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <!-- Phần upload ảnh -->

                                                <div class="progressBar" style="display: none;">
                                                    <div class="progress"></div>
                                                </div>
                                                <div style="text-align: center;"><span class="bangchung">Bằng chứng(có thể bỏ qua)</span></div>
                                                <div class="imageUpload">

                                                    <input type="file" class="inp" accept="image/*" onchange="getImageData(event)" />
                                                    <button onclick="selectImage()" class="selectImage" type="button">Chọn ảnh</button>
                                                    <span class="filedata"></span>

                                                    <span class="loading">Loading...</span>
                                                    <img class="img" />
                                                    <button onclick="deleteImage()" class="delete">Xóa Ảnh</button>
                                                </div>

                                                <form id="reportForm<?php echo $row["user_id"]; ?>" method="post">
                                                    <input type="hidden" name="reported_user_id" value="<?php echo $row["user_id"]; ?>">
                                                    <input type="hidden" name="reported_fullname" value="<?php echo $row["fullname"]; ?>">
                                                    <div class="form-group">
                                                        <label for="reportContent">Nội dung tố cáo:</label>
                                                        <textarea class="form-control" name="reportContent" id="reportContent" rows="4" required placeholder="Hãy nhập nội dung cần tố cáo "></textarea>
                                                    </div>
                                                    <!-- Trường ẩn để lưu URL của ảnh -->
                                                    <input type="text" class="reportImage" id="reportImage" name="reportImage" />
                                                    <div class="form-group">
                                                        <label for="reportDateTime">Ngày giờ tố cáo:</label>
                                                        <input type="text" class="form-control reportDateTime" id="reportDateTime" name="reportDateTime" readonly>
                                                    </div>
                                                    <button type="submit" class="btn btn-primary">Gửi tố cáo</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- End Modal Tố Cáo -->

                                <!-- End Modal Tố Cáo -->
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Hiển thị ngày giờ tự động với cập nhật mỗi giây
        $(document).ready(function() {
            function updateDateTime() {
                var now = new Date();
                var formattedDateTime = now.toLocaleString();
                $(".reportDateTime").val(formattedDateTime);
            }

            // Bắt đầu cập nhật mỗi giây
            setInterval(updateDateTime, 1000);

            // Xử lý sự kiện khi mở modal
            $('#reportModal').on('shown.bs.modal', function() {
                updateDateTime();
            });
        });
    </script>
    <script>
        const firebaseConfig = {
            apiKey: "AIzaSyDnH-AUvjneLbHV9D_QqCFhoDxl7NBBN1Y",
            authDomain: "tocao-7112d.firebaseapp.com",
            projectId: "tocao-7112d",
            storageBucket: "tocao-7112d.appspot.com",
            messagingSenderId: "849708041064",
            appId: "1:849708041064:web:f298e34c91fa63c2b9ccdd",
            measurementId: "G-09DQLQLQ9K"
        };
        const app = firebase.initializeApp(firebaseConfig);

        const storage = firebase.storage();

        const inp = document.querySelector(".inp");
        const bangchung = document.querySelector(".bangchung");
        const reportImage = document.querySelector(".reportImage");
        const progressbar = document.querySelector(".progress");
        const img = document.querySelector(".img");
        const fileData = document.querySelector(".filedata");
        const loading = document.querySelector(".loading");
        let file;
        let fileName;
        let progress;
        let isLoading = false;
        let uploadedFileName;
        const selectImage = () => {
            inp.click();
        };
        const getImageData = (e) => {
            file = e.target.files[0];
            fileName = Math.round(Math.random() * 9999) + file.name;
            if (fileName) {
                fileData.style.display = "none";
            }
            fileData.innerHTML = fileName;
            console.log(file, fileName);

            uploadImage();

        };

        const progressBar = document.querySelector(".progressBar");
        const uploadImage = () => {
            loading.style.display = "block";
            const storageRef = storage.ref().child("myimages");
            const folderRef = storageRef.child(fileName);
            const uploadtask = folderRef.put(file);
            uploadtask.on(
                "state_changed",
                (snapshot) => {
                    console.log("Snapshot", snapshot.ref.name);
                    progress = (snapshot.bytesTransferred / snapshot.totalBytes) * 100;
                    progress = Math.round(progress);
                    progressBar.style.display = "block";
                    progressbar.style.width = progress + "%";
                    progressbar.innerHTML = progress + "%";
                    uploadedFileName = snapshot.ref.name;
                },
                (error) => {
                    console.log(error);
                },
                () => {
                    storage
                        .ref("myimages")
                        .child(uploadedFileName)
                        .getDownloadURL()
                        .then((url) => {
                            console.log("URL", url);
                            if (!url) {
                                img.style.display = "none";
                            } else {
                                img.style.display = "block";
                                loading.style.display = "none";
                            }
                            img.setAttribute("src", url);
                            reportImage.setAttribute("value", url);
                            bangchung.style.display = "none";
                            fileData.innerHTML = "";
                            inp.value = "";
                            clearTimeout();
                        });
                    console.log("File Uploaded Successfully");
                }
            );
        };
        // delete image
        const deleteImage = () => {
            if (uploadedFileName) {
                const storageRef = storage.ref().child("myimages");
                const imageRef = storageRef.child(uploadedFileName);
                imageRef
                    .delete()
                    .then(() => {
                        console.log("File deleted successfully");
                        progressBar.style.display = "none";
                        img.style.display = "none";
                        fileData.style.display = "none";
                        progressbar.style.width = "0%";
                        progressbar.innerHTML = "0%";
                        uploadedFileName = null;
                        bangchung.style.display = "block";
                    })
                    .catch((error) => {
                        console.log("Error deleting file:", error);
                    });
            } else {
                console.log("No file to delete.");
            }
        };
    </script>
</body>

</html>