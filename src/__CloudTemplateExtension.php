<?php
/**
 * Created by PhpStorm.
 * User: matthes
 * Date: 26.07.18
 * Time: 16:10
 */

namespace Rudl;


use Leuffen\TextTemplate\TextTemplate;
use Leuffen\TextTemplate\TextTemplatePlugin;

class __CloudTemplateExtension implements TextTemplatePlugin
{

    public function resolve($paramArr, $command, &$context) : string
    {
        $ips = gethostbynamel($paramArr["name"]);
        if ($ips === false) {
            throw new \InvalidArgumentException("resolve(name = '{$paramArr["name"]}''): Does not resolve.");
        }
        sort($ips);
        return $ips[0];
    }

    public function resolvel($paramArr, $command, &$context) : array
    {
        $ips = gethostbynamel($paramArr["name"]);
        if ($ips === false) {
            throw new \InvalidArgumentException("resolve(name = '{$paramArr["name"]}''): Does not resolve.");
        }
        sort($ips);
        return $ips;
    }


    public function registerPlugin(TextTemplate $textTemplate)
    {
        $textTemplate->addFunction("resolve", [$this, "resolve"]);
        $textTemplate->addFunction("resolvel", [$this, "resolvel"]);

        $textTemplate->addSection("section", function($content) {
            return $content;
        });
    }
}