<?php


namespace App\Services\CommonMail\Template;

class TemplateEngine
{
    /**
     * 应用模板
     * @param String $content 模板
     * @param array $data 绑定的值
     * @return mixed|String 绑定值以后的内容
     */
    public static function make(String $content, array $data)
    {
        foreach ($data as $key => $value) {
            $content = str_replace("#{{$key}}", $value, $content);
        }
        return $content;
    }
}
