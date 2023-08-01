<?php
session_start(); // Đảm bảo rằng session đã được khởi tạo

// Kiểm tra xem biến $_SESSION["admin"] có tồn tại và có giá trị true hay không
if (isset($_SESSION["admin"]) && $_SESSION["admin"] === true) {
    // Biến $_SESSION["admin"] tồn tại và có giá trị true
    // Hiển thị danh sách người tố cáo
?>
    <!DOCTYPE html>
    <html>

    <head>
        <!-- Load Bootstrap -->
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/css/bootstrap.min.css" integrity="sha384-r4NyP46KrjDleawBgD5tp8Y7UzmLA05oM1iAEQ17CSuDqnUK2+k9luXQOfXJCJ4I" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous">
        </script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/js/bootstrap.min.js" integrity="sha384-oesi62hOLfzrys4LxRF63OJCXdXDipiYWBnvTl9Y9/TRlw5xlKIEHpNyvvDShgf/" crossorigin="anonymous">
        </script>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
        <style>
            
            .list-group-item,
            .list-group-item p {
                font-size: 20px;
                font-weight: bold;
                color: #FF007F;
                margin: 3px;
            }

            ul {
                margin: auto;
                width: 100%;
            }

            #filterName {
                width: 50%;
                margin: auto;
                font-size: 20px;
                font-weight: bold;
                color: #FF007F;
            }

            .btn {
                margin: 10px;
                margin-top: 15px;
            }

            .img {
                height: 200px;
                float: right;
                padding: 10px;
            }

            .report-container {
                margin: auto;
                width: 90%;
                height: 200px;
                background-color: #66B2FF;
                position: relative;
                border-radius: 20px;
            }

            .noidung {
                position: absolute;
                text-align: center;
                margin: 10px;
                font-size: 20px;
                color: red;
                font-weight: bold;
            }

            .report-content {
                margin-top: 30px;
                float: left;
                margin: 30px;
                color: aliceblue;
                font-size: 25px;
            }

            .report-image {
                float: right;
                position: relative;
            }

            .report-image:hover {
                cursor: pointer;
            }

            summary {
                border-radius: 20px;
            }

            .dulieu {
                color: black;
            }

            .luc {
                color: #FF007F;
            }
            br{
                display: none;
            }
            b{
                display: none;
            }
        </style>
    </head>

    <body class="text-center">
        <div class="container mt-3">
            <h2 class="text-success">Danh sách người tố cáo</h2>
            <form method="post" action="">
                <div class="mb-3">
                    <label for="filterName" class="form-label">Tên người bị tố cáo:</label>
                    <input type="text" class="form-control" id="filterName" name="filterName" placeholder="Nhập tên người bị tố cáo">
                </div>
                <a class="btn btn-success" href="tocao.php">Back</a>
                <button type="submit" class="btn btn-primary">Lọc</button>
                <a class="btn btn-info" href="">Reset</a>
            </form>
            <ul class="list-group list-group-flush">
                <?php
                // Kết nối tới CSDL
                require_once 'config.php';
                $conn = new mysqli($servername, $username, $password, $dbname);
                mysqli_set_charset($conn, 'UTF8');
                if ($conn->connect_error) {
                    die("Kết nối thất bại: " . $conn->connect_error);
                }

                // Xử lý yêu cầu POST để lọc danh sách tố cáo nếu có
                if ($_SERVER["REQUEST_METHOD"] === "POST") {
                    if (isset($_POST["filterName"])) {
                        $filterName = $_POST['filterName'];
                        $sql = "SELECT r.id_report, u1.fullname AS reporter, u2.fullname AS accused, r.report_date, r.report_content,r.reportImage
                        FROM reports r
                        INNER JOIN report_relationships rr ON r.id_report_relationships = rr.id_report_relationships
                        INNER JOIN users u1 ON rr.reporter_id = u1.user_id
                        INNER JOIN users u2 ON rr.accused_id = u2.user_id
                        ";

                        // Nếu tên được nhập vào, thêm điều kiện lọc theo tên vào câu truy vấn
                        if (!empty($filterName)) {
                            $sql .= " WHERE u2.fullname LIKE '%$filterName%'";
                        }
                    }
                } else {
                    // Nếu không có yêu cầu POST, hiển thị danh sách tố cáo ban đầu (không lọc)
                    $sql = "SELECT r.id_report, u1.fullname AS reporter, u2.fullname AS accused, r.report_date, r.report_content,r.reportImage
                    FROM reports r
                    INNER JOIN report_relationships rr ON r.id_report_relationships = rr.id_report_relationships
                    INNER JOIN users u1 ON rr.reporter_id = u1.user_id
                    INNER JOIN users u2 ON rr.accused_id = u2.user_id
                    ORDER BY r.report_date DESC
                    ";
                }

                $result = $conn->query($sql);
                 
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        // Tạo một id duy nhất cho mỗi phần tử details để dùng trong JavaScript
                        $reportDateTime = $row["report_date"]; // Lấy ngày và giờ từ cơ sở dữ liệu

                        // Tách ngày và giờ thành hai phần riêng biệt
                        $date = substr($reportDateTime, 0, 8); // Lấy 10 ký tự đầu tiên (ngày)
                        $time = substr($reportDateTime, 9);
                        $uniqueId = uniqid();
                        echo '<form method="post" action="">';
                        echo '<input type="hidden" name="id_report" value="' . $row["id_report"] . '">';

                        echo '<details id="' . $uniqueId . '">';
                        echo '<summary class="list-group-item list-group-item-primary">'
                            . $row["reporter"] . ' đã tố cáo ' . $row["accused"] .
                            '<p>vào ngày<span class="dulieu" > ' . $time  . '<span class="luc" > lúc ' . '<span class="dulieu"> ' . $date . ' </span > </span>' . ' </span></p>    </summary>';

                        // Thêm nút xóa và xử lý xóa trực tiếp từ danh sách
                        echo '<button type="submit" name="delete_report" class="btn btn-danger">Xóa</button>';

                        echo '<div class="report-container">';
                        echo '<p class="noidung" > Nội dung tố cáo: </p>';
                        echo '<div class="report-content">';
                        echo '<p>- ' . $row["report_content"] . '</p>';
                        echo '</div>';

                        if (!empty($row["reportImage"])) {
                            echo '<div class="report-image">';
                            echo '<img class="img" src="' . $row["reportImage"] . '" data-image-url="' . $row["reportImage"] . '" alt="huy""' . $row["report_content"] . '">';
                            echo '</div>';
                        }
                        echo '</div>';
                        echo '</details>';
                        echo '</form>';
                    }
                } else {
                    echo '<li class="list-group-item list-group-item-danger">Không có bản ghi nào.</li>';
                }

                $conn->close();

                // Xử lý xóa dữ liệu nếu có yêu cầu POST
                if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_report"])) {
                    $id_report_to_delete = $_POST["id_report"];

                    // Kết nối tới CSDL
                    require_once 'config.php';
                    $conn = new mysqli($servername, $username, $password, $dbname);
                    mysqli_set_charset($conn, 'UTF8');
                    if ($conn->connect_error) {
                        die("Kết nối thất bại: " . $conn->connect_error);
                    }

                    // Xóa bản ghi từ bảng report_relationships
                    $sql_delete_relationships = "DELETE FROM report_relationships WHERE id_report_relationships IN (SELECT id_report_relationships FROM reports WHERE id_report = '$id_report_to_delete')";
                    $conn->query($sql_delete_relationships);

                    // Xóa bản ghi từ bảng reports
                    $sql_delete_report = "DELETE FROM reports WHERE id_report = '$id_report_to_delete'";
                    $conn->query($sql_delete_report);

                    $conn->close();

                    // Làm mới trang sau khi xóa thành công
                    echo '<script>window.location.href = window.location.href;</script>';
                }
                ?>
            </ul>
        </div>
    </body>
    </html>
<?php
} else {
    // Biến $_SESSION["admin"] không tồn tại hoặc có giá trị false
    // Hiển thị thông báo "Bạn không có quyền truy cập"
    echo '<div class="container mt-3">';
    echo '<h2 class="text-danger">Bạn không có quyền truy cập hãy liên hệ huy nhé hihi</h2>';
    echo '</div>';
}
?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const images = document.querySelectorAll(".img");
        images.forEach(img => {
            img.addEventListener("click", function() {
                const imageUrl = this.dataset.imageUrl;
                window.open(imageUrl, "_blank"); // Mở ảnh trong trang mới
            });
        });
        const deleteButtons = document.querySelectorAll(".btn-danger");
        deleteButtons.forEach(button => {
            button.addEventListener("click", function(event) {
                const isConfirmed = confirm("Bạn có chắc chắn muốn xóa báo cáo này?");
                if (!isConfirmed) {
                    event.preventDefault(); // Hủy bỏ việc gửi yêu cầu xóa nếu không xác nhận
                }
            });
        });
    });
</script>
