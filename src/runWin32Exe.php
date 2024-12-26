<?php

include_once __DIR__."/parameters.php";

global $windowTitle;
global $startWindowCmd;

while (1) {
    $shellout = shell_exec('xwininfo -name "'.$windowTitle.'"');
    if (strpos("error", $shellout) !== false) {
        exec($startWindowCmd);
    }
    sleep(10);
}

