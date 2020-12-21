<?php
    $hostname = "localhost";
    $username = "root";
    $password = "123";
    $dbName = "familyBudget";
?>

<html>

    <head>
        <link rel="stylesheet" href="css/bootstrap.min.css">

        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Семейный бюджет</title>
    </head>

    <body>
        <br/> <br/>
        <?
        $connection = mysqli_connect($hostname, $username, $password, $dbName);

        if (!$connection->set_charset("utf8")) {
            printf("Error loading character set utf8: %s\n", $mysqli->error);
            exit();
        }

        if (mysqli_connect_errno()) {
            printf("Подключение к серверу MySQL невозможно. Код ошибки: %s\n", mysqli_connect_error());
            exit;
        }

        ?>
        <!------------------------------------------------ 1 БЛОК ------------------------------------------------>

        <div class="container">
            <p class="h1 text-center text-dark">Семейный бюджет</p>
            <br/> <br/> <br/>
            <div class="row">
                <div class="col text-center">
                    <div class="row"
                        <!-- БЛОК С КОЛИЧЕСТВОМ ДЕНЕГ И ПОТРАЧЕННЫМИ ДЕНЬГАМИ -->
                        <?
                            echo '<ul class="list-group">';
                            $result = $connection->query('SELECT SUM(moneyAmount) FROM Person');

                            if (!$result) {
                                echo 'Error';
                            } else {
                                echo "<li class='list-group-item border-0'><p>Количество денег - ";
                                $row = mysqli_fetch_array($result);
                                echo number_format($row[0], 2, '.', '');
                                echo "<br></p></li>";
                            }

                            $result = $connection->query('SELECT SUM(moneySpent) FROM Spending');

                            if (!$result) {
                                echo 'Error';
                            } else {
                                echo "<li class='list-group-item border-0'><p>Потрачено денег - ";
                                $row = mysqli_fetch_array($result);
                                echo number_format($row[0], 2, '.', '');
                                echo "<br></p></li>";
                            }
                            echo '</ul>';
                        ?>
                    </div>
                    <div class="row">
                        <div class="col text-center">
                            <!-- ТАБЛИЦА С КОЛИЧЕСТВОМ ДЕНЕГ НА КАЖДОГО ЧЛЕНА СЕМЬИ -->
                            <?
                            $result = $connection->query('SELECT name, surname, moneyAmount FROM Person ORDER BY id');
                            if (!$result) {
                                echo 'Error';
                            } else {
                                $numberOfRows = $result->num_rows;

                                if($numberOfRows > 0) {
                                    echo '<p class="h4 text-center">Количество денег на члена семьи</p>';
                                    echo "<table class='table text-center'><thead class='thead-dark'>";
                                    echo "<tr>";
                                    echo "<th>Имя</th>";
                                    echo "<th>Фамилия</th>";
                                    echo "<th>Количество денег</th>";
                                    echo "</tr></thead>";

                                    echo "<tbody>";

                                    while ($field = $result -> fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<th>".$field["name"]."</th>";
                                        echo "<th>".$field["surname"]."</th>";
                                        echo "<th>".number_format(floatval($field["moneyAmount"]), 2, '.', '')."</th>";
                                        echo "</tr>";
                                    }

                                    echo "</tbody></table>";
                                }
                            }
                            ?>
                        </div>

                    </div>
                </div>
                <div class="col text-center">
                    <!-- РАЗДЕЛ С КОПИЛКОЙ-->
                    <p class="h4">Копилка</p>
                    <?
                    $result = $connection->query('SELECT p.name, pig.moneyPut FROM Person p RIGHT JOIN PiggyBank pig ON pig.personId = p.id;');
                    if (!$result) {
                        echo 'Error';
                    } else {
                        $numberOfRows = $result->num_rows;

                        if($numberOfRows > 0) {
                            echo '<p>Последние пополнения:</p>';
                            echo "<table class='table text-center w-auto' style='margin: 0 auto'><thead class='thead-dark'>";
                            echo "<tr>";
                            echo "<th>Имя</th>";
                            echo "<th>Сумма пополнения</th>";
                            echo "</tr></thead>";

                            echo "<tbody>";

                            while ($field = $result -> fetch_assoc()) {
                                echo "<tr>";
                                echo "<th>".$field["name"]."</th>";
                                echo "<th>".number_format($field["moneyPut"], 2, '.', '')."</th>";
                                echo "</tr>";
                            }

                            echo "</tbody></table>";

                            $result = $connection->query('SELECT SUM(moneyPut) FROM PiggyBank');
                            $row = mysqli_fetch_array($result);
                            echo "<p class='font-weight-bold'>Всего денег отложено - ".number_format($row[0], 2, '.', '')."</p>";
                        }
                    }
                    ?>
                </div>
            </div>
            <br/> <br/><br/> <br/>

        <!------------------------------------------------ 2 БЛОК ------------------------------------------------>

            <div class="row">
                <div class="col">
                    <div class="col text-center">
                        <!-- ТАБЛИЦА С ТРАТАМИ -->
                        <?
                        echo '<p class="h4 text-center">Траты</p>';
                        $result = $connection->query('SELECT * FROM Person ORDER BY id');
                        if (!$result) {
                            echo 'Error';
                        } else {
                            echo "<form action='index.php' method='post' name='spending_form'>";
                            echo "Член семьи:  ";
                            echo "<select name='person' style='margin-left: 50px'>";

                            $selectedPerson = $_POST["person"]==""?0:$_POST["person"];

                            $numberOfRows = $result->num_rows;
                            $i=0;
                            if ($numberOfRows > 0) {
                                while ($field = $result -> fetch_assoc()) {
                                    echo "<option value='".$field['id']."'".($i+1==$selectedPerson?" selected>":">");
                                    echo $field['name']."</option>";
                                    $i++;
                                }
                            }
                            echo "<option value='0'".(0==$selectedPerson?" selected>":">");
                            echo "Все"."</option>";

                            echo "<input type='submit' class='btn btn-primary' style='margin-left: 50px' value='Применить'/>";
                            echo "</select>";
                            echo "</form>";


                            if ($_POST["person"]!="") {
                                $section_id = $_POST["person"];
                            } else {
                                $section_id = 0;
                            }
                        }

                        if ($section_id==0) {
                            $result = $connection->query('SELECT p.name, p.surname, s.moneySpent, sm.spentOn FROM Person p RIGHT JOIN Spending s ON s.personId = p.id LEFT JOIN SpendMoney sm ON s.spentOnId = sm.id');
                        } else {
                            $stmt = $connection->prepare("SELECT p.name, p.surname, s.moneySpent, sm.spentOn FROM Person p RIGHT JOIN Spending s ON s.personId = p.id LEFT JOIN SpendMoney sm ON s.spentOnId = sm.id WHERE p.id=?");

                            $stmt->bind_param("i", $selectedPerson);
                            $stmt->execute();
                            $result = $stmt->get_result();
                        }

                        if (!$result) {
                            echo 'Error';
                        } else {
                            $numberOfRows = $result->num_rows;

                            if($numberOfRows > 0) {
                                echo "<table class='table text-center'><thead class='thead-dark'>";
                                echo "<tr>";
                                echo "<th>Имя</th>";
                                echo "<th>Фамилия</th>";
                                echo "<th>Потрачено</th>";
                                echo "<th>Статья расхода</th>";
                                echo "</tr></thead>";

                                echo "<tbody>";

                                while ($field = $result -> fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<th>".$field["name"]."</th>";
                                    echo "<th>".$field["surname"]."</th>";
                                    echo "<th>".number_format($field["moneySpent"], 2, '.', '')."</th>";
                                    echo "<th>".$field["spentOn"]."</th>";
                                    echo "</tr>";
                                }

                                echo "</tbody></table>";
                            }
                        }
                        ?>
                    </div>
                </div>

                <div class="col">
                    <div class="col text-center">
                        <!-- ТАБЛИЦА С ЗАРАБОТКАМИ -->
                        <?
                        echo '<p class="h4 text-center">Заработки</p>';
                        $result2 = $connection->query('SELECT * FROM Person ORDER BY id');
                        if (!$result2) {
                            echo 'Error';
                        } else {
                            echo "<form action='index.php' method='post' name='getting_form'>";
                            echo "Член семьи:  ";
                            echo "<select name='person2' style='margin-left: 50px'>";

                            $selectedPerson2 = $_POST["person2"]==""?0:$_POST["person2"];

                            $numberOfRows2 = $result2->num_rows;

                            $i2 = 0;
                            if ($numberOfRows2 > 0) {
                                while ($field2 = $result2 -> fetch_assoc()) {
                                    echo "<option value='".$field2['id']."'".($i2+1==$selectedPerson2?" selected>":">");
                                    echo $field2['name']."</option>";
                                    $i2++;
                                }
                            }
                            echo "<option value='0'".(0==$selectedPerson2?" selected>":">");
                            echo "Все"."</option>";

                            echo "<input type='submit' class='btn btn-primary' style='margin-left: 50px' value='Применить'/>";
                            echo "</select>";
                            echo "</form>";


                            if ($_POST["person2"]!="") {
                                $section_id2 = $_POST["person2"];
                            } else {
                                $section_id2 = 0;
                            }
                        }

                        if ($section_id2==0) {
                            $result2 = $connection->query('SELECT p.name, p.surname, g.moneyReceived FROM Person p RIGHT JOIN Getting g ON g.personId = p.id');
                        } else {
                            $stmt2 = $connection->prepare("SELECT p.name, p.surname, g.moneyReceived FROM Person p RIGHT JOIN Getting g ON g.personId = p.id WHERE p.id=?");

                            $stmt2->bind_param("i", $selectedPerson2);
                            $stmt2->execute();
                            $result2 = $stmt2->get_result();
                        }

                        if (!$result2) {
                            echo 'Error';
                        } else {
                            $numberOfRows2 = $result2->num_rows;

                            if($numberOfRows2 > 0) {
                                echo "<table class='table text-center'><thead class='thead-dark'>";
                                echo "<tr>";
                                echo "<th>Имя</th>";
                                echo "<th>Фамилия</th>";
                                echo "<th>Заработано</th>";
                                echo "</tr></thead>";

                                echo "<tbody>";

                                while ($field2 = $result2 -> fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<th>".$field2["name"]."</th>";
                                    echo "<th>".$field2["surname"]."</th>";
                                    echo "<th>".number_format($field2["moneyReceived"], 2, '.', '')."</th>";
                                    echo "</tr>";
                                }

                                echo "</tbody></table>";
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>
            <br/> <br/><br/> <br/>

        <!------------------------------------------------ 3 БЛОК ------------------------------------------------>

            <div class="row">
                <div class="col">
                    <div class="col">
                        <!-- ДОБАВЛЕНИЕ НОВОЙ ТРАТЫ -->
                        <?
                            echo '<p class="h4 text-center">Ввести новую трату</p>';
                            echo '<br/><form action="index.php" method="post" name="newSpending">';
                            echo '<div class="form-group row">';
                                echo '<label for="idInput" class="col-md-4">Ваш id: </label>';
                                echo '<div class="col-md-8"> <input type="text" class="form-control" 
                                           name="idInput" placeholder="1000"></div>';
                            echo '</div>';
                            echo '<div class="form-group row">';
                                echo '<label for="amountInput" class="col-md-4">Сумму: </label>';
                                echo '<div class="col-md-8"> <input type="text" class="form-control" 
                                           name="amountInput" placeholder="9999.99"></div>';
                            echo '</div>';
                            echo '<div class="form-group row">';
                                echo '<label for="spentIdInput" class="col-md-4">id расхода: </label>';
                                echo '<div class="col-md-8"> <input type="text" class="form-control" 
                                           name="spentIdInput" placeholder="Интервал 10-16"></div>';
                            echo '</div>';
                            echo '<div class="form-group row">';
                                echo '<div class="col-sm-10">';
                                    echo '<button type="submit" class="btn btn-primary">Подтвердить</button>';
                                echo '</div>';
                            echo '</div>';
                            echo '</form>';

                            $person_id_spend = $_POST["idInput"];
                            $amount_spend = $_POST["amountInput"];
                            $id_spend_on = $_POST["spentIdInput"];

                            if ($person_id_spend != "" && $amount_spend != "" && $id_spend_on != "") {
                                if (is_numeric($person_id_spend) && is_numeric($amount_spend) && is_numeric($id_spend_on)) {

                                    $stmt_spending = $connection->prepare("CALL spendMoney(?,?,?)");
                                    $stmt_spending->bind_param("iii", $person_id_spend, $amount_spend, $id_spend_on);

                                    if ($stmt_spending->execute() == TRUE) {
                                        echo "<div class='alert alert-success' role='alert'>Успешно!</div>";
                                        $person_id_spend = "";
                                        $amount_spend = "";
                                        $id_spend_on = "";
                                    } else {
                                        echo "<div class='alert alert-danger' role='alert'>Ошибка!</div>";
                                    }
                                }
                            }
                        ?>
                    </div>
                </div>

                <div class="col">
                    <div class="col text-center">
                        <!-- ПЕРЕВОД -->
                        <?
                            echo '<p class="h4 text-center">Перевести деньги</p>';
                            echo '<br/><form action="index.php" method="post" name="transfer">';
                                echo '<div class="form-group row">';
                                    echo '<label for="idFromInput" class="col-md-4">Ваш id: </label>';
                                    echo '<div class="col-md-8"> <input type="text" class="form-control" 
                                                       name="idFromInput" placeholder="1000"></div>';
                                echo '</div>';
                                echo '<div class="form-group row">';
                                    echo '<label for="idToInput" class="col-md-4">id кому: </label>';
                                    echo '<div class="col-md-8"> <input type="text" class="form-control" 
                                                       name="idToInput" placeholder="1000"></div>';
                                echo '</div>';
                                echo '<div class="form-group row">';
                                    echo '<label for="sumTransfer" class="col-md-4">Сумма: </label>';
                                    echo '<div class="col-md-8"> <input type="text" class="form-control" 
                                               name="sumTransfer" placeholder="9999.99"></div>';
                                echo '</div>';
                                echo '<div class="form-group row">';
                                    echo '<div class="col-sm-5">';
                                    echo '<button type="submit" class="btn btn-primary">Подтвердить</button>';
                                echo '</div>';
                                echo '</div>';
                            echo '</form>';

                            $id_from_transfer = $_POST["idFromInput"];
                            $id_to_transfer = $_POST["idToInput"];
                            $sum_transfer = $_POST["sumTransfer"];

                            if ($id_from_transfer != "" && $id_to_transfer != "" && $sum_transfer != "") {
                                if (is_numeric($id_from_transfer) && is_numeric($id_to_transfer) && is_numeric($sum_transfer)) {

                                    $stmt_transfer = $connection->prepare("CALL transfer(?,?,?)");
                                    $stmt_transfer->bind_param("iii", $id_from_transfer, $id_to_transfer, $sum_transfer);

                                    if ($stmt_transfer->execute() == TRUE) {
                                        echo "<div class='alert alert-success' role='alert'>Успешно!</div>";
                                        $id_from_transfer = "";
                                        $id_to_transfer = "";
                                        $sum_transfer = "";
                                    } else {
                                        echo "<div class='alert alert-danger' role='alert'>Ошибка!</div>";
                                    }
                                }
                            }
                        ?>
                    </div>
                </div>
            </div>

            <br/> <br/><br/><br/><br/>

        <!------------------------------------------------ 4 БЛОК ------------------------------------------------>

            <div class="row">
                <div class="col">
                    <div class="col">
                        <!-- БЛОК ОТЛОЖИТЬ В КОПИЛКУ -->
                        <?
                        echo '<p class="h4 text-center">Отложить</p>';
                        echo '<br/><form action="index.php" method="post" name="putToPiggy">';
                        echo '<div class="form-group row">';
                        echo '<label for="idInputPiggy" class="col-md-4">Ваш id: </label>';
                        echo '<div class="col-md-8"> <input type="text" class="form-control" 
                                           name="idInputPiggy" placeholder="1000"></div>';
                        echo '</div>';
                        echo '<div class="form-group row">';
                        echo '<label for="amountInputPiggy" class="col-md-4">Сумма: </label>';
                        echo '<div class="col-md-8"> <input type="text" class="form-control" 
                                           name="amountInputPiggy" placeholder="9999.99"></div>';
                        echo '</div>';
                        echo '<div class="form-group row">';
                        echo '<div class="col-sm-10">';
                        echo '<button type="submit" class="btn btn-primary">Подтвердить</button>';
                        echo '</div>';
                        echo '</div>';
                        echo '</form>';

                        $id_input_piggy_bank = $_POST["idInputPiggy"];
                        $amount_put_to_piggy_bank = $_POST["amountInputPiggy"];

                        if ($id_input_piggy_bank != "" && $amount_put_to_piggy_bank != "") {
                            if (is_numeric($id_input_piggy_bank) && is_numeric($amount_put_to_piggy_bank)) {

                                $stmt_piggy = $connection->prepare("CALL addToPiggyBank(?,?)");
                                $stmt_piggy->bind_param("ii", $id_input_piggy_bank, $amount_put_to_piggy_bank);

                                if ($stmt_piggy->execute() == TRUE) {
                                    echo "<div class='alert alert-success' role='alert'>Успешно!</div>";
                                    $id_input_piggy_bank = "";
                                    $amount_put_to_piggy_bank = "";
                                } else {
                                    echo "<div class='alert alert-danger' role='alert'>Ошибка!</div>";
                                }
                            }
                        }
                        ?>
                    </div>
                </div>

                <div class="col">
                    <div class="col text-center">
                        <!-- ВВЕСТИ ЗАРАБОТОК -->
                        <?
                            echo '<p class="h4 text-center">Ввести заработок</p>';
                            echo '<br/><form action="index.php" method="post" name="getting">';
                            echo '<div class="form-group row">';
                            echo '<label for="idGettingInput" class="col-md-4">Ваш id: </label>';
                            echo '<div class="col-md-8"> <input type="text" class="form-control" 
                                                           name="idGettingInput" placeholder="1000"></div>';
                            echo '</div>';
                            echo '<div class="form-group row">';
                            echo '<label for="sumGetting" class="col-md-4">Сумма: </label>';
                            echo '<div class="col-md-8"> <input type="text" class="form-control" 
                                                   name="sumGetting" placeholder="9999.99"></div>';
                            echo '</div>';
                            echo '<div class="form-group row">';
                            echo '<div class="col-sm-5">';
                            echo '<button type="submit" class="btn btn-primary">Подтвердить</button>';
                            echo '</div>';
                            echo '</div>';
                            echo '</form>';

                            $id_getting = $_POST["idGettingInput"];
                            $sum_getting = $_POST["sumGetting"];

                            if ($id_getting != "" && $sum_getting != "") {
                                if (is_numeric($id_getting) && is_numeric($sum_getting)) {

                                    $stmt_getting = $connection->prepare("CALL getMoney(?,?)");
                                    $stmt_getting->bind_param("ii", $id_getting, $sum_getting);

                                    if ($stmt_getting->execute() == TRUE) {
                                        echo "<div class='alert alert-success' role='alert'>Успешно!</div>";
                                        $id_getting = "";
                                        $sum_getting = "";
                                    } else {
                                        echo "<div class='alert alert-danger' role='alert'>Ошибка!</div>";
                                    }
                                }
                            }
                        ?>
                    </div>
                </div>
            </div>
            <br/><br/><br/>

        <!------------------------------------------------ 5 БЛОК ------------------------------------------------>

            <div class="row">
                <div class="col" style="margin: 15%">
                    <!-- РЕГИСТРАЦИЯ -->
                    <?
                        $result = $connection->query('SELECT MAX(id) FROM Person');
                        $row = mysqli_fetch_array($result);
                        $newId = $row[0] + 1;

                        echo '<p class="h4 text-center">Регистрация члена семьи</p><br/>';

                        echo '<br/><form action="index.php" method="post" name="registration">';

                            echo '<div class="form-group row">';
                            echo '<label for="newId" class="col-md-4">Ваш id: </label>';
                            echo '<div class="col-md-8"> <input type="text" class="form-control" 
                                                                readonly class="form-control-plaintext" 
                                                                name="newId" value='.$newId.'></div>';
                            echo '</div>';

                            echo '<div class="form-group row">';
                            echo '<label for="newName" class="col-md-4">Имя: </label>';
                            echo '<div class="col-md-8"> <input type="text" class="form-control" 
                                                                name="newName" placeholder="Mikhail"></div>';
                            echo '</div>';

                            echo '<div class="form-group row">';
                            echo '<label for="newSurname" class="col-md-4">Фамилия: </label>';
                            echo '<div class="col-md-8"> <input type="text" class="form-control" 
                                                                name="newSurname" placeholder="Vilkin"></div>';
                            echo '</div>';

                            echo '<div class="form-group row">';
                            echo '<label for="newEmail" class="col-md-4">E-mail: </label>';
                            echo '<div class="col-md-8"> <input type="email" class="form-control" 
                                                                name="newEmail" placeholder="vvvvvv@vv.vv"></div>';
                            echo '</div>';

                            echo '<div class="form-group row">';
                            echo '<label for="newMoneyAmount" class="col-md-4">Сколько у вас денег: </label>';
                            echo '<div class="col-md-8"> <input type="text" class="form-control" 
                                                                name="newMoneyAmount" placeholder="9999.99"></div>';
                            echo '</div>';

                            echo '<div class="form-group row">';
                            echo '<div class="col-sm-5">';
                            echo '<button type="submit" class="btn btn-primary">Зарегистрироваться</button>';
                            echo '</div>';

                        echo '</form>';

                        $new_name = $_POST["newName"];
                        $new_surname = $_POST["newSurname"];
                        $new_email = $_POST["newEmail"];
                        $new_money_amount = $_POST["newMoneyAmount"];

                        if ($new_name != "" && $new_surname != "" && $new_email != "" && $new_money_amount != "") {
                            if (is_numeric($new_money_amount)) {

                                $stmt_getting = $connection->prepare("INSERT INTO Person VALUES(?,?,?,?,?)");
                                $stmt_getting->bind_param("isssi", $newId, $new_name, $new_surname, $new_email, $new_money_amount);

                                if ($stmt_getting->execute() == TRUE) {
                                    echo "<div class='alert alert-success' role='alert'>Успешно!</div>";
                                    $new_name = "";
                                    $new_surname = "";
                                    $new_email = "";
                                    $new_money_amount = "";
                                } else {
                                    echo "<div class='alert alert-danger' role='alert'>Ошибка!</div>";
                                }
                            }
                        }
                    ?>
                    <br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/>
                </div>
            </div>

        </div>
    </body>

</html>


