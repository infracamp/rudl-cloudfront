<?php
/**
 * Created by PhpStorm.
 * User: matthes
 * Date: 26.07.18
 * Time: 16:00
 */

namespace Rudl;


use Leuffen\TextTemplate\TextTemplate;

class CloudTemplate extends TextTemplate
{

    public function __construct($text = "")
    {
        parent::__construct($text);
        $this->addPlugin(new __CloudTemplateExtension());
    }

}