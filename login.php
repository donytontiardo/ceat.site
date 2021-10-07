<!DOCTYPE html>
<?php
include "head.php";
include "database.php";
?>

<html>

<body>
    <main style="background-color: #141413;">
        <div class="container">
            <div style="flex-direction: row; display:flex;justify-content:space-between">
                <div style="display:flex;flex:1;padding:100px"><img src="images/logo.png" style="width: 100%;height:fit-content"></div>
                <div style="display:flex;flex:1;background-color: white;margin-top:3%;margin-bottom:3%;padding-left:2%;padding-right:3%;padding-bottom:3%;border-radius:30px;margin-right:5%">

                    <form style="width: 100%;" action="./auth_process.php" method="post">
                        <h1>Masuk</h1>
                        <li>NIM</li>
                        <li><input style="width:100%" type="number" name="nim" required autocomplete="off" /></li>
                        <br>
                        <li>Kata Sandi</li>
                        <li><input style="width:100%" type="password" name="password" required autocomplete="off" /></li>
                        <br>
                        <div style="text-align:center">
                            <input type="submit" class="start" name="login" value="Masuk" style="background-color:#23a0f1;color:white;font-size:1rem;width:100%">
                        </div>
                        <div style="text-align:center;margin-top:2%"> Belum punya akun ? <a href="regis.php">Daftar</a>
                        </div>
                    </form>
                </div>
            </div>


        </div>
    </main>
    <?php
    include "footer.php";
    ?>

</body>

</html>