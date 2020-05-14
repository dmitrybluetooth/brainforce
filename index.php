<?php
ini_set('display_errors', 0);

function debug($data){
    echo '<pre>' . print_r($data,true) . '</pre>';
}
?>

<html>
    <head>
        <meta charset='utf-8'>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>

    <?php
    $host = 'localhost';
    $database = 'brainforce';
    $user = 'root';
    $password = '';

    if(isset($_POST["excel_Reader"]))
    {
        require 'phpExcelReader/Excel/reader.php';

        $data = new Spreadsheet_Excel_Reader();
        $data->setOutputEncoding('UTF-8');
        // Файл xls
        $data->read('pricelist.xls');
        // Первый лист
        $sheet = $data->sheets[0]['cells'];
        array_shift($sheet);

        $link = mysqli_connect($host, $user, $password, $database)
        or die("Ошибка " . mysqli_error($link));

        foreach ($sheet as $row)
        {
            if ($row[2] !== 'Стоимость')
            {
                $sql = "INSERT INTO `pricelist` (`name`,`cost`,`cost_opt`,`storage_1`,`storage_2`,`country`) 
                        VALUES ('$row[1]','$row[2]','$row[3]','$row[4]','$row[5]','$row[6]')";
                $query = mysqli_query($link, $sql) or die('Ошибка чтения записи: '.mysqli_error($link));
            }
        }
        mysqli_close($link);
    }

    if(isset($_POST["truncate_table"]))
    {
        $link = mysqli_connect($host, $user, $password, $database)
        or die("Ошибка " . mysqli_error($link));

        $sql = "TRUNCATE TABLE `pricelist`";
        $query = mysqli_query($link, $sql) or die('Ошибка чтения записи: '.mysqli_error($link));

        mysqli_close($link);
    }
    ?>

    <form action="" method="POST">
        <input type="submit" name="excel_Reader" value="Парсить файл xls в БД">
    </form>

    <form action="" method="POST">
        <input type="submit" name="truncate_table" value="Очистить таблицу">
    </form><br /><br />

    <form action="table.php" id="filters-form" role="form" method="post">
        <div>
            Показать товары, у которых
            <select id="cost" name="cost">
                <option value="cost" <?php if($_POST['cost'] == 'cost') echo 'selected'?>>Розничная цена</option>
                <option value="cost_opt" <?php if($_POST['cost'] == 'cost_opt') echo 'selected'?>>Оптовая цена</option>
            </select>
            от
            <input type="number" id="min-price" name="min_price" value="<?= ($_POST['min_price']) ?: '1000'?>" min="1">
            до
            <input type="number" id="max-price" name="max_price" value="<?= ($_POST['max_price']) ?: '3000'?>">
            рублей и на складе
            <select id="min_max" name="min_max">
                <option value="max" <?php if($_POST['min_max'] == 'max') echo 'selected'?>>Более</option>
                <option value="min" <?php if($_POST['min_max'] == 'min') echo 'selected'?>>Менее</option>
            </select>
            <input type="number" id="count" name="count" value="<?= ($_POST['count']) ?: '20'?>" min="1">
            штук.
            <input type="submit" name="filters-form" value="Показать товары">
        </div>
    </form>
    <br><br>

    <div id="table">
        <?php
        require 'table.php';
        ?>
    </div>

    <script src="jquery-3.5.0.min.js"></script>

    <script type="text/javascript">
        $("#filters-form").submit(function (event) {
            // Предотвращаем обычную отправку формы
            event.preventDefault();
            $.ajax({
                url: $(this).attr('action')+"?ajax=1",
                data: $(this).serialize(),
                type: 'POST',
                success: function (data) {
                    $('#table').html(data);
                }
            });
        });
    </script>
    </body>
</html>