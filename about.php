<?php
require "scripts/base_layout.php";

class AboutPage extends BaseLayout {
    protected function printHead() {
        self::printDefaultHead("About");
    }

    protected function printBody() {
        ?>
        <tr>
            <td colspan="4" class="td_body">
                This is about window.
                <br><br>
                <input class=button type=button value="ok :(" onClick="history.back()">
            </td>
        </tr>
        <?php
    }

    protected function printFootage() {
        self::printDefaultFootage(null);
    }
}

(new AboutPage())->printHtml();