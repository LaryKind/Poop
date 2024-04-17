<?php
session_start();
 
// Проверка, входил ли ранее пользователь в аккаунт (если нет - редирект на страницу 'login')
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true)
{
    header("location: login.php");
    exit;
}
 
// подключение конфига
require_once "config.php";

$new_password = $confirm_password = "";
$new_password_err = $confirm_password_err = "";

if($_SERVER["REQUEST_METHOD"] == "POST")
{
    // проверка нового пароля
    if(empty(trim($_POST["new_password"])))
	{
        $new_password_err = "Введите новый пароль.";     
    }
	elseif(strlen(trim($_POST["new_password"])) < 6)
	{
        $new_password_err = "Пароль должен содержать минимум 6 символов.";
    }
	else
	{
        $new_password = trim($_POST["new_password"]);
    }
    
    // проверка подтверждения пароля
    if(empty(trim($_POST["confirm_password"])))
	{
        $confirm_password_err = "Введите пароль еще раз.";
    }
	else
	{
        $confirm_password = trim($_POST["confirm_password"]);
        if(empty($new_password_err) && ($new_password != $confirm_password))
		{
            $confirm_password_err = "Пароли не совпадают.";
        }
    }
        
    // проверка на ошибки перед обновлением БД
    if(empty($new_password_err) && empty($confirm_password_err))
	{
        $sql = "UPDATE users SET password = ? WHERE id = ?";
        
        if($stmt = $mysqli->prepare($sql))
		{
            $stmt->bind_param("si", $param_password, $param_id);
            
            $param_password = password_hash($new_password, PASSWORD_DEFAULT);
            $param_id = $_SESSION["id"];
            
            if($stmt->execute())
			{
                // пароль успешно обновлён, удаление сессии, редирект на страницу 'login'
                session_destroy();
                header("location: login.php");
                exit();
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
    <title>Reset Password</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body{ font: 14px sans-serif; }
        .wrapper{ width: 360px; padding: 20px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <h2>Сбросить пароль</h2>
        <p>Заполните все поля для сброса пароля.</p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post"> 
            <div class="form-group">
                <label>Новый пароль</label>
                <input type="password" name="new_password" class="form-control <?php echo (!empty($new_password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $new_password; ?>">
                <span class="invalid-feedback"><?php echo $new_password_err; ?></span>
            </div>
            <div class="form-group">
                <label>Подтвердить пароль</label>
                <input type="password" name="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>">
                <span class="invalid-feedback"><?php echo $confirm_password_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Подтвердить">
                <a class="btn btn-link ml-2" href="welcome.php">Отмена</a>
            </div>
        </form>
    </div>    
</body>
</html>