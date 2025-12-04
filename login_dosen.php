<?php
session_start();
$conn = new mysqli("localhost","root","","praktikum");

if(isset($_POST['login'])){
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM dosen WHERE email=? AND password=?");
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $res = $stmt->get_result();

    if($res->num_rows > 0){
        $user = $res->fetch_assoc();
        $_SESSION['user'] = $user;
        $_SESSION['role'] = 'dosen';
        header("Location: dashboard_dosen.php"); exit;
    } else {
        $error = "Email atau password salah!";
    }
}
?>
<h2>Login Dosen</h2>
<form method="POST">
    Email: <input type="email" name="email" required><br>
    Password: <input type="password" name="password" required><br>
    <button type="submit" name="login">Login</button>
</form>
<?php if(isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
<a href="login_mahasiswa.php">Login Mahasiswa</a>
