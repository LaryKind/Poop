<?php
// подключение конфига
require_once "config.php";
 
$username = $password = $confirm_password = "";
$username_err = $password_err = $confirm_password_err = "";

if($_SERVER["REQUEST_METHOD"] == "POST")
{
    // проверка юзернейма
    if(empty(trim($_POST["username"])))
	{
        $username_err = "Введите имя пользователя.";
    }
	elseif(!preg_match('/^[a-zA-Z0-9_]+$/', trim($_POST["username"])))
	{
        $username_err = "Имя пользователя может содержать только буквы, цифры и символы.";
    }
	else
	{
        $sql = "SELECT id FROM users WHERE username = ?";
        
        if($stmt = $mysqli->prepare($sql))
		{
            $stmt->bind_param("s", $param_username);
            
            $param_username = trim($_POST["username"]);
            
            if($stmt->execute())
			{
                $stmt->store_result();
                
                if($stmt->num_rows == 1)
				{
                    $username_err = "Имя пользователя уже занято.";
                }
				else
				{
                    $username = trim($_POST["username"]);
                }
            }
			else
			{
                echo "Ой! Что-то пошло не так! Попробуйте позже.";
            }

            $stmt->close();
        }
    }
    
    // проверка пароля
    if(empty(trim($_POST["password"]))){
        $password_err = "Введите пароль.";     
    } elseif(strlen(trim($_POST["password"])) < 6){
        $password_err = "Пароль должен содержать минимум 6 символов.";
    } else{
        $password = trim($_POST["password"]);
    }
    
    // проверка подтверждения пароля
    if(empty(trim($_POST["confirm_password"]))){
        $confirm_password_err = "Введите пароль еще раз.";     
    } else{
        $confirm_password = trim($_POST["confirm_password"]);
        if(empty($password_err) && ($password != $confirm_password)){
            $confirm_password_err = "Пароли не совпадают.";
        }
    }
    
    // проверка на ошибки перед сохранением в БД
    if(empty($username_err) && empty($password_err) && empty($confirm_password_err))
	{
        $sql = "INSERT INTO users (username, password) VALUES (?, ?)";
         
        if($stmt = $mysqli->prepare($sql))
		{
            $stmt->bind_param("ss", $param_username, $param_password);
            
            $param_username = $username;
			// создание хэша пароля
            $param_password = password_hash($password, PASSWORD_DEFAULT);
            
            if($stmt->execute())
			{
                // Редирект на страницу с логином
                header("location: login.php");
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
    <title>Sign Up</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body{ font: 14px sans-serif; }
        .wrapper{ width: 360px; padding: 20px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <h2>Создать аккаунт</h2>
        <p>Заполните все поля для создания аккаунта.</p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label>Имя пользователя</label>
                <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>">
                <span class="invalid-feedback"><?php echo $username_err; ?></span>
            </div>    
            <div class="form-group">
                <label>Пароль</label>
                <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $password; ?>">
                <span class="invalid-feedback"><?php echo $password_err; ?></span>
            </div>
            <div class="form-group">
                <label>Подтвердить пароль</label>
                <input type="password" name="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $confirm_password; ?>">
                <span class="invalid-feedback"><?php echo $confirm_password_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Подтвердить">
                <input type="reset" class="btn btn-secondary ml-2" value="Сбросить">
            </div>
            <p>Уже есть аккаунт? <a href="login.php">Войти</a>.</p>
        </form>
    </div>    
</body>
</html>