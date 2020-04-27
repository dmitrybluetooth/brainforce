<?php
$host = 'localhost';
$database = 'brainforce';
$user = 'root';
$password = '';

$link = mysqli_connect($host, $user, $password, $database) or die("Ошибка " . mysqli_error($link));

if($_GET['ajax'] == 1)
{
    $cost = mysqli_real_escape_string($link, $_POST['cost']);
    $min_price = (int)$_POST["min_price"];
    $max_price = (int)$_POST["max_price"];
    $min_max = (mysqli_real_escape_string($link, $_POST['min_max']) == 'min') ? '<' : '>';
    $count = (int)$_POST["count"];

    $query1 = "
    SELECT * FROM `pricelist` 
    WHERE 
          {$cost} BETWEEN {$min_price} AND {$max_price} 
      AND 
          (storage_1 {$min_max} {$count} OR storage_2 {$min_max} {$count})";

    $query2 = "
    SELECT 
           max(cost) as max_cost, 
           min(cost_opt) as min_cost_opt, 
           avg(cost) as avg_cost, 
           avg(cost_opt) as avg_cost_opt, 
           sum(storage_1) as sum_storage_1, 
           sum(storage_2) as sum_storage_2 
    FROM `pricelist` 
    WHERE 
          {$cost} BETWEEN {$min_price} AND {$max_price} 
      AND 
          (storage_1 {$min_max} {$count} OR storage_2 {$min_max} {$count})";
}
else
{
    $query1 ="SELECT * FROM `pricelist`";

    $query2 ="
        SELECT 
               max(cost) as max_cost, 
               min(cost_opt) as min_cost_opt, 
               avg(cost) as avg_cost, 
               avg(cost_opt) as avg_cost_opt, 
               sum(storage_1) as sum_storage_1, 
               sum(storage_2) as sum_storage_2 
        FROM `pricelist`";
}

$sql1 = mysqli_query($link, $query1) or die("Ошибка " . mysqli_error($link));
$sql2 = mysqli_query($link, $query2) or die("Ошибка " . mysqli_error($link));

$avg_cost = 0;
$avg_cost_opt = 0;
$sum_storage_1 = 0;
$sum_storage_2 = 0;
$max_cost = 0;
$min_cost_opt = 0;

while ($result = mysqli_fetch_array($sql2))
{
    $avg_cost = $result['avg_cost'];
    $avg_cost_opt = $result['avg_cost_opt'];
    $sum_storage_1 = $result['sum_storage_1'];
    $sum_storage_2 = $result['sum_storage_2'];
    $max_cost = $result['max_cost'];
    $min_cost_opt = $result['min_cost_opt'];
}
?>
<table>
    <thead>
    <tr>
        <th>#</th>
        <th>Наименование товара</th>
        <th>Стоимость, руб</th>
        <th>Стоимость опт, руб</th>
        <th>Наличие на складе 1, шт</th>
        <th>Наличие на складе 2, шт</th>
        <th>Страна производства</th>
        <th>Примечание</th>
    </tr>
    </thead>
    <tbody>
    <?php
    while ($result = mysqli_fetch_array($sql1)): ?>
        <tr
            <?php
            if ($result['cost'] == $max_cost) echo 'style="background-color: red; color: white"';
            if ($result['cost_opt'] == $min_cost_opt) echo 'style="background-color: green; color: white"';
            ?>
        >
            <td style="text-align: center"><?php echo $result['id'] ?></td>
            <td><?php echo $result['name'] ?></td>
            <td style="text-align: right"><?php echo str_replace('.', ',', $result['cost']) ?></td>
            <td style="text-align: right"><?php echo $result['cost_opt'] ?></td>
            <td style="text-align: right"><?php echo $result['storage_1'] ?></td>
            <td style="text-align: right"><?php echo $result['storage_2'] ?></td>
            <td style="text-align: center"><?php echo $result['country'] ?></td>
            <td style="text-align: center">
                <?php
                if ($result['storage_1'] < 20 or $result['storage_2'] < 20) echo 'Осталось мало!! Срочно докупите!!!';
                else echo '';
                ?>
            </td>
        </tr>
    <?php endwhile;
    mysqli_free_result($sql1);
    mysqli_free_result($sql2);
    mysqli_close($link);
    ?>
    </tbody>
</table><br>
<?php
if(!empty($sum_storage_1) and !empty($sum_storage_2) and !empty($avg_cost) and !empty($avg_cost_opt))
    echo 'Общее количество товаров на Складе1: ' . $sum_storage_1 . '<br />' .
         'Общее количество товаров на Складе2: ' . $sum_storage_2 . '<br />' .
         'Средняя стоимость розничной цены товара: ' . round($avg_cost, 2) . '<br />' .
         'Средняя стоимость оптовой цены товара: ' . round($avg_cost_opt, 2) . '<br />'
    ;
?>