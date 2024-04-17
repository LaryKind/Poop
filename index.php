<?php

	$db = new PDO("mysql:host=localhost;dbname=MoviesDB",
	"root","");
	
	$info = [];
	
	if ($query = $db->query("SELECT * FROM MovieTable")){
		$info = $query->fetchAll(PDO::FETCH_ASSOC);
	} else {
		print_r($db->errorInfo());
	}

	// Параметры пагинации
    $items_per_page = 10;
    $total_movies = count($info);
    $total_pages = ceil($total_movies / $items_per_page);

    // Определение текущей страницы
    $current_page = isset($_GET['page']) ? max(1, min($_GET['page'], $total_pages)) : 1;
    $offset = ($current_page - 1) * $items_per_page;

    $movies_on_current_page = array_slice($info, $offset, $items_per_page);

	if (isset($_GET['search_query']) && !empty($_GET['search_query'])) {
    $search_query = $_GET['search_query'];
    
	$query = $db->prepare("SELECT * FROM MovieTable WHERE Name LIKE ?");
    $query->execute(array($search_query));
    $search_results = $query->fetchAll(PDO::FETCH_ASSOC);
	}
	
	$movies_on_current_page = !empty($search_results) ? [] : array_slice($info, $offset, $items_per_page);

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Библиотека фильмов</title>

    <!--задаю фавиконки для браузера, мобилок и пк
    беру иконку от дизайнера в svg и отправляю на генератор фавиконок
    https://realfavicongenerator.net
    там уже можно настроить размеры, фон и отображение
    там же выдаётся код для html, что экономит время-->
    <link rel="apple-touch-icon" sizes="180x180" href="icon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="icon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="icon/favicon-16x16.png">
    <link rel="manifest" href="icon/site.webmanifest">
    <link rel="mask-icon" href="/safari-pinned-tab.svg" color="#5bbad5">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet">
    <meta name="msapplication-TileColor" content="#00aba9">
    <meta name="theme-color" content="#ffffff">

    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="header">
        <div class="header_logo">
            <img src="logo.png">
        </div>
        <div class="header_name_site">Библиотека фильмов</div> <!--Логотип, или название сайта -->
        <div class="header_search">
			 <form action="" method="GET">
            <input class="input_search" type="search" name="search_query" placeholder="Поиск фильма.." />
            <button type="submit" class="search_button">
                <img src="image.jpg" alt="" class="search_icon" width=16px; height=16px;>
            </button>
			 </form>
        </div>
        <!-- Кнопка триггер "Войти в профиль"-->
        <div class="header_profile">
            <!-- окно "Войти в профиль" Пытался сделать всплывающим (модальным) окном, не получилось
            в итоге оставил только кнопку(ссылку) -->
			 <?php
			session_start();
			if(isset($_SESSION['username'])) {
			$username = $_SESSION['username'];
			echo '<span class="user-info">Привет, '.$username.'!</span>';
			echo '<a href="logout.php" class="logout">Выйти из аккаунта</a>';
			echo '<a href="reset-password.php"> Сбросить пароль</a>';
		} else {
			echo '<a class="login-trigger" href="login.php">Войти в профиль</a>';
		}
		?>
        </div>
    </header>
    <main class="main">
	<div class="container">
        <div class="menu">
            <div class="title_menu"><h2>Фильмы</h2></div>
            <div class="movie-list">
              <?php if (!empty($search_results)): ?>
            <!-- Отображение найденного фильма -->
            <?php foreach ($search_results as $movie): ?>
                <div class="movie-item">
                    <img src="<?php echo $movie['PosterLink']; ?>" alt="<?php echo $movie['Name']; ?> Poster">
                    <h3><?php echo $movie['Name']; ?></h3>
                    <p>Жанры: <?php echo $movie['Genres']; ?></p>
                    <p>Режисер: <?php echo $movie['Director']; ?></p>
                    <p>Год выпуска: <?php echo $movie['Year']; ?></p>
					<p>Страна: <?php echo $movie['Country']; ?></p>
                    <p>Рейтинг: <?php echo $movie['RatingValue']; ?></p>
                    <p>Краткое описание: <?php echo $movie['Description']; ?></p>
                </div>
            <?php endforeach; ?>
		 <?php elseif (isset($_GET['search_query']) && !empty($_GET['search_query'])): ?>
        <p>По вашему запросу ничего не найдено.</p>
        <?php else: ?>
            <!-- Отображение всех фильмов из базы данных -->
            <?php foreach ($movies_on_current_page as $movie): ?>
                <div class="movie-item">
                    <img src="<?php echo $movie['PosterLink']; ?>" alt="<?php echo $movie['Name']; ?> Poster">
                    <h3><?php echo $movie['Name']; ?></h3>
                    <p>Жанры: <?php echo $movie['Genres']; ?></p>
                    <p>Режисер: <?php echo $movie['Director']; ?></p>
                    <p>Год выпуска: <?php echo $movie['Year']; ?></p>
					<p>Страна: <?php echo $movie['Country']; ?></p>
                    <p>Рейтинг: <?php echo $movie['RatingValue']; ?></p>
                    <p>Краткое описание: <?php echo $movie['Description']; ?></p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
       <!-- Пагинация -->
            <div class="pagination">
                <?php if ($current_page > 1): ?>
                    <a href="?page=<?php echo $current_page - 1; ?>">Предыдущая</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <?php if ($i == 1 || $i == $total_pages || abs($i - $current_page) < 3): ?>
                        <a href="?page=<?php echo $i; ?>" <?php echo $i == $current_page ? 'class="active"' : ''; ?>><?php echo $i; ?></a>
                    <?php elseif ($i == 2 && $current_page > 4): ?>
                        <span>...</span>
                    <?php elseif ($i == $total_pages - 1 && $current_page < $total_pages - 3): ?>
                        <span>...</span>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($current_page < $total_pages): ?>
                    <a href="?page=<?php echo $current_page + 1; ?>">Следующая</a>
                <?php endif; ?>
            </div>
        </div>
            <div class="list_menu">
                <div class="faq">
                    <div class="faq-item">
                        <input class="faq-input" type="checkbox" name="faq_1" id="faq_1">
                        <label class="faq-title" for="faq_1">Жанры</label>
                        <div class="faq-text">
                            <p>
                                <input type="checkbox" id="biography" name="genres">
                                <label for="biography">Биография</label>
                                <br>
                                <input type="checkbox" id="action_film" name="genreses">
                                <label for="action_film">Боевик</label>
                                <br>
                                <input type="checkbox" id="western_film" name="genres">
                                <label for="western_film">Вестерн</label>
                                <br>
                                <input type="checkbox" id="war_film" name="genres">
                                <label for="war_film">Военный</label>
                                <br>
                                <input type="checkbox" id="detective_movies" name="genres">
                                <label for="detective_movies">Детектив</label>
                                <br>
                                <input type="checkbox" id="drama" name="genres">
                                <label for="drama">Драма</label>
                                <br>
                                <input type="checkbox" id="historical_film" name="genres">
                                <label for="historical_film">История</label>
                                <br>
                                <input type="checkbox" id="comedy" name="genres">
                                <label for="comedy">Комедия</label>
                                <br>
                                <input type="checkbox" id="crime_film" name="genres">
                                <label for="crime_film">Криминал</label>
                                <br>
                                <input type="checkbox" id="romance" name="genres">
                                <label for="romance">Мелодрама</label>
                                <br>
                                <input type="checkbox" id="cartoon" name="genres">
                                <label for="cartoon">Мультфильм</label>
                                <br>
                                <input type="checkbox" id="music" name="genres">
                                <label for="music">Музыка</label>
                                <br>
                                <input type="checkbox" id="musical" name="genres">
                                <label for="musical">Мюзикл</label>
                                <br>
                                <input type="checkbox" id="adventure" name="genres">
                                <label for="adventure">Приключения</label>
                                <br>
                                <input type="checkbox" id="family" name="genres">
                                <label for="family">Семейное</label>
                                <br>
                                <input type="checkbox" id="sport" name="genres">
                                <label for="sport">Спорт</label>
                                <br>
                                <input type="checkbox" id="thriller" name="genres">
                                <label for="thriller">Триллер</label>
                                <br>
                                <input type="checkbox" id="horror" name="genres">
                                <label for="horror">Ужасы</label>
                                <br>
                                <input type="checkbox" id="science_fiction" name="genres">
                                <label for="science_fiction">Фантастика</label>
                                <br>
                                <input type="checkbox" id="fantasy" name="genres">
                                <label for="fantasy">Фэнтези</label>
                            </p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <input class="faq-input" type="checkbox" name="faq_2" id="faq_2">
                        <label class="faq-title" for="faq_2">Страны</label>
                        <div class="faq-text">
                            <p>
                                <input type="checkbox" id="russia" name="countries">
                                <label for="russia">Россия</label>
                                <br>
                                <input type="checkbox" id="usa" name="countries">
                                <label for="usa">США</label>
                                <br>
                                <input type="checkbox" id="uk" name="countries">
                                <label for="uk">Великобритания</label>
                                <br>
                                <input type="checkbox" id="france" name="countries">
                                <label for="france">Франция</label>
                                <br>
                                <input type="checkbox" id="canada" name="countries">
                                <label for="canada">Канада</label>
                                <br>
                                <input type="checkbox" id="italy" name="countries">
                                <label for="italy">Италия</label>
                                <br>
                                <input type="checkbox" id="turky" name="countries">
                                <label for="turky">Турция</label>
                            </p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <input class="faq-input" type="checkbox" name="faq_3" id="faq_3">
                        <label class="faq-title" for="faq_3">Годы</label>
                        <div class="faq-text">
                            <p>
                                <input type="checkbox" id="70th" name="decade">
                                <label for="70th">1970-1979</label>
                                <br>
                                <input type="checkbox" id="80th" name="decade">
                                <label for="80th">1980-1989</label>
                                <br>
                                <input type="checkbox" id="90th" name="decade">
                                <label for="90th">1990-1999</label>
                                <br>
                                <input type="checkbox" id="00th" name="decade">
                                <label for="four">2000-2009</label>
                                <br>
                                <input type="checkbox" id="10th" name="decade">
                                <label for="10th">2010-2019</label>
                                <br>
                                <input type="checkbox" id="20th" name="decade">
                                <label for="20th">2020-2029</label>
                            </p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <input class="faq-input" type="checkbox" name="faq_4" id="faq_4">
                        <label class="faq-title" for="faq_4">Рейтинг</label>
                        <div class="faq-text">
                            <p>
                                <input type="checkbox" id="one" name="estimate">
                                <label for="one">1</label>
                                <br>
                                <input type="checkbox" id="two" name="estimate">
                                <label for="two">2</label>
                                <br>
                                <input type="checkbox" id="three" name="estimate">
                                <label for="three">3</label>
                                <br>
                                <input type="checkbox" id="four" name="estimate">
                                <label for="four">4</label>
                                <br>
                                <input type="checkbox" id="five" name="estimate">
                                <label for="five">5</label>
                                <br>
                                <input type="checkbox" id="six" name="estimate">
                                <label for="six">6</label>
                                <br>
                                <input type="checkbox" id="seven" name="estimate">
                                <label for="seven">7</label>
                                <br>
                                <input type="checkbox" id="eight" name="estimate">
                                <label for="eight">8</label>
                                <br>
                                <input type="checkbox" id="nine" name="estimate">
                                <label for="nine">9</label>
                                <br>
                                <input type="checkbox" id="ten" name="estimate">
                                <label for="ten">10</label>
                            </p>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </main>
    <footer class="footer">
        <div class="row">
            <ul>
                <li><a href="#">Свяжитесь с нами</a></li>
                <li><a href="#">Наши сервисы</a></li>
                <li><a href="#">Политика конфидициальности</a></li>
                <li><a href="#">Правила и условия</a></li>
                <li><a href="#">Работа</a></li>
            </ul>
        </div>

        <div class="row">
            Poop Copyright © 2024 Poop - Все права защищены || Разработано: 4.4_TulaHack.W&M
        </div>
    </footer>
    <script src="script.js"></script>
</body>
</html>
