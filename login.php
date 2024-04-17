<?php
session_start();
 
// Проверка, входил ли ранее пользователь в аккаунт (если да - редирект на страницу 'welcome')
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true)
{
    header("location: welcome.php");
    exit;
}
 
// Подключение конфига
require_once "config.php";

$username = $password = "";
$username_err = $password_err = $login_err = "";

if($_SERVER["REQUEST_METHOD"] == "POST")
{
    // проверка поля 'username' на пустоту
    if(empty(trim($_POST["username"])))
	{
        $username_err = "Введите имя пользователя.";
    }
	else
	{
        $username = trim($_POST["username"]);
    }
    
    // проверка поля 'password' на пустоту
    if(empty(trim($_POST["password"])))
	{
        $password_err = "Введите пароль.";
    }
	else
	{
        $password = trim($_POST["password"]);
    }
    
    if(empty($username_err) && empty($password_err))
	{
        $sql = "SELECT id, username, password FROM users WHERE username = ?";
        
        if($stmt = $mysqli->prepare($sql))
		{
            $stmt->bind_param("s", $param_username);
			
            $param_username = $username;
            
            if($stmt->execute())
			{
                $stmt->store_result();
                
                // если юзернейм существует, то начинается проверка пароля
                if($stmt->num_rows == 1)
				{                    
                    $stmt->bind_result($id, $username, $hashed_password);
                    if($stmt->fetch())
					{
                        if(password_verify($password, $hashed_password))
						{
                            // пароль верный, запуск новой сессии
                            session_start();
                            
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["username"] = $username;                            
                            
                            // редирект на страницу 'welcome'
                            header("location: index.php");
                        }
						else
						{
                            // пароль неверный, отображение сообщения с ошибкой
                            $login_err = "Invalid username or password.";
                        }
                    }
                }
				else
				{
                    // юзернейм неверный, отображение сообщения с ошибкой
                    $login_err = "Неверное имя пользователя или пароль.";
                }
            }
			else
			{
                echo "Ой! Что-то пошло не так. Попробуйте позже.";
            }

            $stmt->close();
        }
    }
    
    $mysqli->close();
}
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body{ font: 14px sans-serif; }
        .wrapper{ width: 360px; padding: 20px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <h2>Вход</h2>
        <p>Введите данные для входа.</p>

        <?php 
        if(!empty($login_err))
		{
            echo '<div class="alert alert-danger">' . $login_err . '</div>';
        }        
        ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label>Имя пользователя</label>
                <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>">
                <span class="invalid-feedback"><?php echo $username_err; ?></span>
            </div>    
            <div class="form-group">
                <label>Пароль</label>
                <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                <span class="invalid-feedback"><?php echo $password_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Войти">
            </div>
            <p>Нет аккаунта? <a href="register.php">Создать аккаунт</a>.</p>
        </form>
    </div>
</body>
</html>