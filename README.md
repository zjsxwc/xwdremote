readme

环境：
archlinux/manjaro
```bash
sudo pacman -S xorg-xwininfo xorg-xwd xsel xdotool netpbm php php-imagick
sudo vim /etc/php/php.ini #add line `extension=imagick`
```

当前代码，我用了websocket+比较图片的差异数据来传输界面，但实际效果太差了，

完全不如最开始的1.0版本，无脑传http jpg图片的方式流畅。
