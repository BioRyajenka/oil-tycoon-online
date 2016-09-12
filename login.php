<?php
/**
 * Created by PhpStorm.
 * User: Jackson
 * Date: 06.09.2016
 * Time: 16:55
 */
require "scripts/base_layout.php";
require "scripts/authentication_script.php";
require "scripts/util.php";

class LoginPage extends BaseLayout {
    protected function printHead() {
        self::printDefaultHead("Login");
    }

    protected function printBody() {
        $authStatus = AuthStatus::UNAUTHORIZED;
        if (isset($_POST['login']) && isset($_POST['password'])) {
            $login = $_POST['login'];
            $password = $_POST['password'];
            $authStatus = login($login, $password);
        }
        // if not isset, then it's first run
        if (isset($login) && isset($password) && $authStatus == AuthStatus::OPERATION_SUCCESS) {
            redirect('/map.php');
            die;
        }
        ?>
        <tr>
            <td colspan="4" class="td_body">
                <img src="res/welcome_image.jpg" class="wide_picture"><br><br>
                <form action="login.php" method=POST>
                    <table align="center">
                        <tr>
                            <td colspan='2'><span style='color: red;'>
                            <?php
                            if (isset($_GET['session_expired'])) {
                                //echo "Session expired";
                                echo "You must log in first.";
                            }
                            if ($authStatus == AuthStatus::OPERATION_FAILURE || $authStatus == AuthStatus::INCORRECT_SPELLING) {
                                echo "Wrong authentication pair.";
                            }
                            ?>
                            </span></td>
                        </tr>
                        <tr>
                            <td><label for="login">Login:</label></td>
                            <td>
                                <input name=login type=text size='18' id="login"
                                    <?php
                                    if (isset($login)) {
                                        echo "value=\"$login\"";
                                    }
                                    ?>
                                >
                            </td>
                        </tr>
                        <tr>
                            <td><label for="pass">Password:</label></td>
                            <td>
                                <input name=password type=password size="18" id="pass">
                            </td>
                        </tr>
                        <tr>
                            <td colspan=2>
                                <div style="text-align:center;">
                                    <input class=button type=submit value="Login">
                                    &nbsp;&nbsp;
                                    <input class=button type=button value="Back" onClick="parent.location='index.php'">
                                </div>
                            </td>
                        </tr>
                    </table>
                </form>
            </td>
        </tr>
        <?php
    }

    protected function printFootage() {
        self::printDefaultFootage(null);
    }
}

(new LoginPage())->printHtml();