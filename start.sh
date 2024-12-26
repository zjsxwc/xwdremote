#!/bin/bash

php src/channelServer.php start &
php src/runWin32Exe.php &
php src/server.php start


