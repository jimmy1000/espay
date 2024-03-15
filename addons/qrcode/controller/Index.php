<?php

namespace addons\qrcode\controller;

use Endroid\QrCode\QrCode;
use think\addons\Controller;
use think\Response;

/**
 * 二维码生成
 *
 */
class Index extends Controller
{

    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
    }

    // 
    public function index()
    {
        return $this->fetch();
    }

    // 生成二维码
    public function build()
    {
        $text = $this->request->get('text', 'hello');
        $size = $this->request->get('size', 250);
        $padding = $this->request->get('padding', 15);
        $errorcorrection = $this->request->get('errorcorrection', 'medium');
        $foreground = $this->request->get('foreground', "#ffffff");
        $background = $this->request->get('background', "#000000");
        $logo = $this->request->get('logo','');
        $label = $this->request->get('label','');
        $labelfontsize = $this->request->get('labelfontsize');
        $labelhalign = $this->request->get('labelhalign');
        $labelvalign = $this->request->get('labelvalign');


        // 前景色
        list($r, $g, $b) = sscanf($foreground, "#%02x%02x%02x");
        $foregroundcolor = ['r' => $r, 'g' => $g, 'b' => $b];

        // 背景色
        list($r, $g, $b) = sscanf($background, "#%02x%02x%02x");
        $backgroundcolor = ['r' => $r, 'g' => $g, 'b' => $b];

        $qrCode = new QrCode();
        $qrCode
            ->setText($text)
            ->setSize($size)
            ->setPadding($padding)
            ->setErrorCorrection($errorcorrection)
            ->setForegroundColor($foregroundcolor)
            ->setBackgroundColor($backgroundcolor)
            ->setLabelFontPath(ROOT_PATH . 'public/assets/fonts/fzltxh.ttf')
            ->setLabel($label)
            ->setLabelFontSize($labelfontsize)
            ->setLabelHalign($labelhalign)
            ->setLabelValign($labelvalign)
            ->setImageType(QrCode::IMAGE_TYPE_PNG);



        if ($logo!='') {

            $logoPath = ROOT_PATH . 'public/assets/img/ewm/use_'.$logo.'.png';
            if (is_file($logoPath)){
                $qrCode->setLogoSize(40);
                $qrCode->setLogo(ROOT_PATH . 'public/assets/img/ewm/use_'.$logo.'.png');
            }
        }


        //也可以直接使用render方法输出结果
        //$qrCode->render();
        return new Response($qrCode->get(), 200, ['Content-Type' => $qrCode->getContentType()]);
        exit('123');
    }

}
