<?php
require "scripts/base_layout.php";

class RulesPage extends BaseLayout {
    protected function printHead() {
        self::printDefaultHead("Rules");
    }

    protected function printBody() {
        ?>
        <tr>
            <td colspan="4" class="td_body">
                Правил нет! Беспредел!
                <img src="res/pirate.gif" title="Тысяча чертей!">
                <input class=button type=button value="Back" onClick="history.back()">
            </td>
        </tr>
        <?php
    }

    protected function printFootage() {
        self::printDefaultFootage(null);
    }
}

(new RulesPage())->printHtml();