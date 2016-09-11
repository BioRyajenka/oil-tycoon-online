<?php
// difference btw require and include is that include throws warnings
// if there any errors occurred, while require throws these errors
require "scripts/base_layout.php";

class WelcomePage extends BaseLayout {
    protected function printHead() {
        self::printDefaultHead("Welcome");
    }

    protected function printBody() {
        ?>
        <tr>
            <td colspan="4" class='td_body'>
                <img src="res/welcome_image.jpg" class="wide_picture">
                <br><br>
                <span style="margin-left:2em">This economical browser game is about oil rush, spying, alliances and dizzying money!</span>
                <br>
                <span style="margin-left:2em">Try to smash your opponents and go down in history!</span>
                <br><br>
                <div style="text-align:center;">
                    <input class=button type=button value="Login" onClick="window.location.href='login.php';">&nbsp;&nbsp;
                    <input class=button type=button value="Register" onClick="window.location.href='register.php';">
                </div>
            </td>
        </tr>
        <?php
    }

    protected function printFootage() {
        self::printDefaultFootage("<a href='rules.php'>Rules</a>", null, "<a href='about.php'>About</a>");
    }
}

(new WelcomePage())->printHtml();