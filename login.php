<?php
session_start();

// Proses login saat form dikirim
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Login sederhana (username: admin, password: 123)
    if ($username === 'admin' && $password === '123') {
        $_SESSION['username'] = $username;
        $_SESSION['role'] = 'Dosen';
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Username atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
     <title>Login</title>
     <style>
       :root  {
--bg:  #f5f7fb;
--card:  #ffffff;
--accent:  #1f6feb;
--muted:  #6b7280;
--border:  #e5e7eb;
}


*  {
box-sizing:  border-box;
font-family:  Inter,  "Segoe UI",  Roboto,  Arial,  sans-serif
}


body   {
margin:  0;
background:  var(--bg);
display:  flex;
align-items:  center; justify-content:  center; height:  100vh;

color:  #111827;
}


.login-card  {
background:  var(--card);
width:  100%;
max-width:  380px;
padding:  32px;
border-radius:  16px;
box-shadow:  0  8px  24px   rgba(0,  0,  0,  0.06);
border:  1px  solid  var(--border);
}


.login-card  h2  {
text-align:  center; margin-bottom:  20px; color:  var(--accent);
letter-spacing:  1px;
}


.form-group  {
margin-bottom:  16px;
}


label  {
display:  block; font-weight:  600; margin-bottom:  6px; color:  #374151;
font-size:  14px;
}


input[type="text"], input[type="password"]  {
width:  100%;
padding:  10px   12px;
border:  1px  solid  var(--border);
border-radius:  8px; font-size:  14px; outline:  none;
transition:  0.2s;
}


input:focus  {
border-color:  var(--accent);
box-shadow:  0  0  0  3px  rgba(31,  111,  235,  0.15);
}


.btn  {
width:  100%; padding:  10px   0; border:  none;
border-radius:  8px;
background:  var(--accent);

color:  white;
font-weight:  600; font-size:  15px; cursor:  pointer; transition:  0.2s;
}


.btn:hover  {
background:  #155cc1;
}


.btn-reset  { width:  100%; padding:  10px   0;
border:  1px  solid  var(--border);
border-radius:  8px; background:  transparent; color:  #374151;
font-size:  14px;
cursor:  pointer;
margin-top:  10px;
}


.error  {
background:  #fee2e2; color:  #b91c1c; padding:  10px;
border-radius:  8px;
font-size:  14px;
margin-bottom:  16px;
border:  1px  solid  #fecaca;
}


.footer  {
text-align:  center; margin-top:  20px; color:  var(--muted);
font-size:  13px;
}
</style>

</head>


<body>
<div  class="login-card">
<h2>POLGAN  MART</h2>
<?php  if (!empty($error))  echo   "<div  class='error'>$error</div>";  ?>
<form  method="post">
<div  class="form-group">
<label  for="username">Username</label>
<input  type="text"  name="username"  required>
</div>
<div  class="form-group">
<label  for="password">Password</label>
<input  type="password"  name="password"  required>

</div>
<button  type="submit"  class="btn">Login</button>
<button  type="reset"  class="btn-reset">Batal</button>
</form>
<div  class="footer">
<p>©  2026   POLGAN   MART</p>
</div>
</div>
</body>
</html>
