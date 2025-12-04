<?php
session_start();
$host="localhost"; $user="root"; $pass=""; $db="praktikum";
$conn=new mysqli($host,$user,$pass,$db);

if(isset($_POST['login'])){
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Cek di tabel dosen
    $stmt = $conn->prepare("SELECT * FROM dosen WHERE email=?");
    $stmt->bind_param("s",$email);
    $stmt->execute();
    $res = $stmt->get_result();

    if($res->num_rows > 0){
        $user = $res->fetch_assoc();
        if(password_verify($password, $user['password'])){
            $_SESSION['user'] = $user;
            $_SESSION['role'] = 'dosen';
            header("Location: dashboard_dosen.php"); exit;
        } else { $error = "Password salah!"; }
    } else {
        // Cek di tabel mahasiswa
        $stmt = $conn->prepare("SELECT * FROM mahasiswa WHERE email=?");
        $stmt->bind_param("s",$email);
        $stmt->execute();
        $res = $stmt->get_result();
        if($res->num_rows > 0){
            $user = $res->fetch_assoc();
            if(password_verify($password, $user['password'])){
                $_SESSION['user'] = $user;
                $_SESSION['role'] = 'mahasiswa';
                header("Location: dashboard_mahasiswa.php"); exit;
            } else { $error = "Password salah!"; }
        } else {
            $error = "Email tidak ditemukan!";
        }
    }
}
?>
<form method="POST">
    Email: <input type="email" name="email" required><br>
    Password: <input type="password" name="password" required><br>
    <button type="submit" name="login">Login</button>
</form>
<?php if(isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
