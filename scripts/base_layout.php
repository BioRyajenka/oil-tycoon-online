<?php

/**
 * Created by PhpStorm.
 * User: Jackson
 * Date: 06.09.2016
 * Time: 2:41
 */
abstract class BaseLayout {
    private static function frameWithTag($args, $tagname) {
        $res = "";
        $arrLen = count($args);
        foreach ($args as $k => $v) {
            $res .= "<" . $tagname;
            if ($k == $arrLen - 1) {
                $res .= " colspan='".(5 - $arrLen)."'";
            }
            $res .= ">" . $v . "</" . $tagname . ">";
        }
        return $res;
    }

    protected static function printDefaultHead(...$titles) {
        echo self::frameWithTag($titles, "th");
    }

    protected static function printDefaultFootage(...$titles) {
        echo self::frameWithTag($titles, "td");
    }

    protected abstract function printHead();

    protected abstract function printBody();

    protected abstract function printFootage();

    private function printHtmlHead() {
        ?>
        <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0' name='viewport'/>
        <meta content="text/html; charset=ansi">
        <title>Oil Tycoon Online</title>
        <link rel="stylesheet" type="text/css" href="/css/base_style.css">
        <?php
    }

    private function printHtmlBody() {
        ?>
        <table border='1' class='main_table'>
            <thead>
            <tr>
                <?php $this->printHead(); ?>
            </tr>
            </thead>

            <tbody>
            <?php $this->printBody() ?>
            </tbody>

            <tfoot>
            <tr>
                <?php $this->printFootage(); ?>
            </tr>
            </tfoot>
        </table>
        <?php
    }

    public function printHtml() {
        echo '<html><head>';
        $this->printHtmlHead();
        echo '</head><body>';
        $this->printHtmlBody();
        echo '</body></html>';
    }
}