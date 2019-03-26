<?php

/**
 * 接口文件
 */

// 加载类库
include './phpqrcode/qrlib.php';

// error_reporting(0);


// 获取调用者参数
$text = isset($_GET['text']) ? trim($_GET['text']) : 'free.ss-ssr.com';
$w = isset($_GET['w']) ? trim($_GET['w']) : 5;
$logo = isset($_GET['logo']) ? trim($_GET['logo']) : false;
$el = isset($_GET['el']) ? trim($_GET['el']) : 'h';


// 执行生成
generateQRcode($text, $w, $logo, $el);


/**
 * 二维码生成器
 * @param  string  $text [description]
 * @param  integer $w    [description]
 * @param  string  $logo [description]
 * @param  string  $el   [description]
 * @return [type]        [description]
 * 2017-01-18T15:01:40+0800
 */
function generateQRcode($text='free.ss-ssr.com', $w=10, $logo_img=false, $el='h')
{
    // 将纠错级别转成大写
    $el = strtoupper($el);

    // 1)判断是否有logo需要加载
    if ($logo_img !== false) {
        // 先生成二维码
        QRcode::png($text, 'qrcode.png', $el, $w, 2);
        // 创建大画布
        $qr = imagecreatefromstring(file_get_contents('qrcode.png'));
        // 创建小画布
        $logo = imagecreatefromstring(file_get_contents($logo_img));

        // 获取大画布的宽高
        list($qr_w, $qr_h) = getimagesize('qrcode.png');
        // 获取小画布的宽高
        list($logo_w, $logo_h) = getimagesize($logo_img);

        // 创建空白画布
        // 定义logo最终的宽高,为了让logo能够自适应
        // logo覆盖的面积为二维码的三分之一
        $width = $qr_w/3;
        $height = $qr_h/3;

        $white_logo = imagecreatetruecolor($width, $height);
        $white = imagecolorallocate($white_logo, 255, 255, 255);
        imagefill($white_logo, 0, 0, $white);

        // 等比例缩放logo
        $ratio = $logo_w/$logo_h;
        if ($width/$height > $ratio) {
            $width = $height*$ratio;
        } else {
            $height = $width/$ratio;
        }


        // 重新采样
        imagecopyresampled($qr, $logo, ($qr_w-$width)/2, ($qr_h-$height)/2, 0, 0, $width, $height, $logo_w, $logo_h);


        // 输出图片
        Header("Content-type: image/png");
        /**
         * 图片输出,二选一,gd库自定义的函数imagepng()或者
         * 像PHPQRCode类库一样使用ImagePng()这在Windows系统下没有问题
         * 但是移植到了Linux系统下,问题就暴露了,严格区分大小写
         *
         */
        imagepng($qr);

        // 销毁画布
        imagedestroy($qr);
        imagedestroy($logo);
        imagedestroy($white_logo);

    } else {
        // 没有logo的情况
        return QRcode::png($text, false, $el, $w, 2);
    }
}