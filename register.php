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
require "scripts/logger.php";

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

function checkCaptcha() {
    if (!isset($_POST['captcha'])) {
        return false;
    }
    global $captchaResult;
    $captchaResult = intval($_POST['captcha']);
    return $captchaResult === $_SESSION['captcha'];
}

class RegisterPage extends BaseLayout {
    protected function printHead() {
        self::printDefaultHead("Login");
    }

    const AUTH_STATUS_EULA_UNACCEPTED = "EULA_UNACCEPTED";
    const AUTH_STATUS_WRONG_CAPTCHA = "WRONG_CAPTCHA";

    protected function printBody() {
        $authStatus = AuthStatus::UNAUTHORIZED;

        if (isset($_POST['login']) && isset($_POST['password']) && isset($_POST['nickname'])
            && isset($_POST['email']) && isset($_POST['gender'])
        ) {
            $login = $_POST['login'];
            $password = $_POST['password'];
            $nickname = $_POST['nickname'];
            $email = $_POST['email'];
            $gender = $_POST['gender'];

            if (isset($_POST["eula"])) {
                $eula = true;
            } else {
                $eula = false;
            }

            if ($eula == false) {
                $authStatus = self::AUTH_STATUS_EULA_UNACCEPTED;
            } else {
                $authStatus = register($login, $password, $nickname, $email, $gender);
            }

            if (!checkCaptcha()) {
                $authStatus = self::AUTH_STATUS_WRONG_CAPTCHA;
            }
        }
        if ($authStatus == AuthStatus::OPERATION_SUCCESS) {
            redirect('/login.php');
            die;
        }
        ?>
        <tr>
            <td colspan="4" class="td_body">
                <form action="register.php" method=POST>
                    <table align="center">
                        <tr>
                            <td colspan='2'><span style='color: red;'>
                            <?php
                            if ($authStatus == self::AUTH_STATUS_WRONG_CAPTCHA) {
                                echo "Wrong captcha<br>";
                            }
                            if ($authStatus == AuthStatus::INCORRECT_SPELLING) {
                                echo "Login should be at least 5 characters, must start with letter 
                                and should contain only numbers and letters.<br>
                                Password should contain 6 to 31 characters<br>";
                            }
                            if ($authStatus == AuthStatus::OPERATION_FAILURE) {
                                echo "Wrong login or password.";
                            }
                            if ($authStatus == self::AUTH_STATUS_EULA_UNACCEPTED) {
                                echo "You must accept rules.";
                            }
                            ?>
                            </span></td>
                        </tr>
                        <tr>
                            <td><label for=login>Login:</label></TD>
                            <td><input type=text name=login id=login size="18"></td>
                        </tr>
                        <tr>
                            <td><label for=password>Password:</label></td>
                            <td><input type=password name=password id=password size="18"></td>
                        </tr>
                        <tr>
                            <td><label for=nickname>Nickname:</label></td>
                            <td><input type=text name=nickname id=nickname size="18"></td>
                        </tr>
                        <tr>
                            <td> Gender:</td>
                            <td>
                                <label><input type="radio" name=gender value="male" style="cursor:hand" checked>
                                    male </label>
                                <label><input type="radio" name=gender value="female" style="cursor:hand">
                                    female</label>
                            </td>
                        </tr>
                        <tr>
                            <td><label for=email>E-mail:</label></td>
                            <td><input type=text name=email id=email size="18"></td>
                        </tr>
                        <tr>
                            <td>
                                <label for=captcha>
                                    Captcha
                                </label>
                            </td>
                            <td>
                                <input type=text name=captcha id=captcha size="18">
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" style="text-align: center">
                                <?php
                                $_SESSION['captcha'] = mt_rand(100000, 999999);

                                $captchaUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/scripts/captcha.php';
                                //$captchaUrl = 'http://localhost/scripts/captcha.php';
                                $code = $_SESSION['captcha'];

                                $pic = file_get_contents($captchaUrl . "?code=$code");

                                echo "<img src='data:image/jpeg;base64,$pic'>";
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td colspan=2>
                                <label><input type=checkbox name=eula style="cursor:hand">
                                    I'am agree with Oil Tycoon's <a href="rules.php">rules</a>
                                </label>
                                <br>
                            </td>
                        </tr>
                        <tr>
                            <td colspan=2>
                                <div style="text-align:center;">
                                    <input class=button type=submit value="Register">
                                    &nbsp;&nbsp;
                                    <input class=button type=button value="Back" onClick="parent.location='index.php'">
                                </div>
                            </td>
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

(new RegisterPage())->printHtml();