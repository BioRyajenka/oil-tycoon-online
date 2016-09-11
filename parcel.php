<?php
/**
 * Created by PhpStorm.
 * User: Jackson
 * Date: 11.09.2016
 * Time: 0:49
 */

require "scripts/session_guard.php";
require "scripts/base_layout.php";

$x = $_GET['x'];
$y = $_GET['y'];

class ParcelPage extends BaseLayout {
    protected function printHead() {
        self::printDefaultHead("some default header");
    }

    protected function printBody() {
        global $x;
        global $y;
        ?>
        <script src="scripts/map.js"></script>

        <tr style="height: 70%">
            <td colspan="4" class="td_body">
                <table style="width: 100%; height: 100%">
                    <tr>
                        <td rowspan="2" style="vertical-align: top; width: 60%">
                            <?php
                            foreach ($this->actionButtons as $name) {
                                echo "<div class='button' style='
                                    margin-top: 5px; text-align: center; height: 3em; line-height:3em'>$name</div>";
                            }
                            ?>
                        </td>
                        <td style="position:relative; text-align: center; width:40%; height: 1%; border: 1px solid black;" id="image_holder">
                        </td>
                        <script type="application/javascript">
                            document.getElementById("image_holder").innerHTML = createParcelHtml("parcel_image");
                            initParcelFunctions("parcel_image");
                        </script>
                    </tr>
                    <tr>
                        <td style="text-align: center"><img src="/res/facilities.jpg"></td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr style="height: 30%">
            <td colspan="4" style="vertical-align: top; position: relative">
                oil stored<br>
                oil left<br>
                other info

                <div
                    style="position: absolute; left: 100%; top: 100%; transform: translate(-100%, -100%); white-space: nowrap"
                    class="button" id="view_parcel_button"
                    onclick="window.location.href = `/map.php?x=<?php echo $x?>&y=<?php echo $y?>`">
                    Back to the map
                </div>
            </td>
        </tr>

        <script type="application/javascript">
            document.getElementById("parcel_image").downloadData(<?php echo $x?>, <?php echo $y?>);
            //downloadParcelInfoAndUpdateItsImage(document.getElementById("image_id"), );
        </script>
        <?php
    }

    protected function printFootage() {
    }

    private $actionButtons = array();

    public function addActionButton($name) {
        $this->actionButtons[] = $name;
    }
}

$parcelPage = new ParcelPage();
$parcelPage->addActionButton("Scout");
$parcelPage->addActionButton("Spy");
$parcelPage->addActionButton("Transfer");
$parcelPage->addActionButton("Trade");
$parcelPage->printHtml();