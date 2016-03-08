<?php

namespace Zoco;

/**
 * 表单处理器
 * 用于生成HTML表单项
 * Class Form
 *
 * @package Zoco
 */
class Form {
    /**
     * checkbox的分隔符
     *
     * @var string
     */
    static $checkboxValueSplit = ',';

    /**
     * 默认的帮助选项
     *
     * @var string
     */
    static $defaultHelpOption = '请选择';

    /**
     * 根据数据，生成表单
     *
     * @param $formArray
     * @return array
     */
    static public function autoForm($formArray) {
        $forms = array();
        foreach ($formArray as $key => $value) {
            /**
             * 表单选项
             */
            $func = $value['type'];

            /**
             * 表单值
             */
            $formValue = '';

            if (isset($value['value'])) {
                $formValue = $value['value'];
            }
            unset($value['type'], $value['value']);

            if ($func == 'input' || $func == 'password' || $func == 'text') {
                $forms[$key] = self::$func($key, $formValue, $value);
            } else {
                $option     = $value['option'];
                $self       = $value['self'];
                $labelClass = $value['label_class'];

                unset($value['option'], $value['self'], $self['label_class']);

                $forms[$key] = self::$func($key, $option, $formValue, $self, $value, $labelClass);
            }
        }

        return $forms;
    }

    /**
     * @param           $name      :此select的name标签
     * @param           $option    :要制作select的数
     * @param null      $default   :如果要设定默认选择哪个数据，就在此填入默认的数据的值
     * @param null      $self      :设置为true，option的值等于$value
     * @param null      $attrArray :html标签的属性，就是这个select的属性标签，例如，class="x1"
     * @param bool|true $addHelp   :增加一个值为空的，请选择项
     * @return string
     */
    static public function select($name, $option, $default = null, $self = null, $attrArray = null, $addHelp = true) {
        $htmlStr = "<select name=\"$name\" id=\"$name\"";
        $htmlStr .= self::inputAttr($attrArray) . ">\n";

        if ($addHelp) {
            if ($addHelp === true) {
                $htmlStr .= "<option value=\"\">" . self::$defaultHelpOption . "</option>\n";
            } else {
                $htmlStr .= "<option value=\"\">$addHelp</option>\n";
            }
        }

        foreach ($option as $key => $value) {
            if ($self) {
                $key = $value;
            }

            if ($key === $default) {
                $htmlStr .= "<option value=\"{$key}\" selected=\"selected\">{$value}</option>\n";
            } else {
                $htmlStr .= "<option value=\"{$key}\">{$value}</option>\n";
            }
        }
        $htmlStr .= "</select>\n";

        return $htmlStr;
    }

    /**
     * 元素选项处理
     *
     * @param $attr
     * @return string
     */
    static public function inputAttr(&$attr) {
        $str = ' ';
        if (!empty($attr) && is_array($attr)) {
            foreach ($attr as $key => $value) {
                $str .= "$key=\"$value\" ";
            }
        }

        return $str;
    }

    /**
     * 单选按钮
     *
     * @param            $name      :此radio的name标签
     * @param            $option    :要制作radio的数
     * @param null       $default   :如果要设定默认选择哪个数据，就在此填入默认的数据的值
     * @param bool|false $self      :设置为true，option的值等于$value
     * @param null       $attrArray :html的属性，例如class="x1"
     * @param string     $labelClass
     */
    static public function radio($name, $option, $default = null, $self = false, $attrArray = null, $labelClass = '') {
        $htmlStr = '';
        $attrStr = self::inputAttr($attrArray);

        foreach ($option as $key => $value) {
            if ($self) {
                $key = $value;
            }

            if ($key == $default) {
                $htmlStr .= "<label class='$labelClass'><input type=\"radio\" name=\"$name\" id=\"{$name}_{$key}\" value=\"$key\" checked=\"checked\" {$attrStr} />" . $value . "</label>";
            } else {
                $htmlStr .= "<label class='$labelClass'><input type=\"radio\" name=\"$name\" id=\"{$name}_{$key}\" value=\"$key\"  {$attrStr} />&nbsp;" . $value . "</label>";
            }
        }

        return $htmlStr;
    }

    /**
     * 多选按钮
     *
     * @param            $name      :此radio的name标签
     * @param            $option    :要制作radio的数
     * @param null       $default   :如果要设定默认选择哪个数据，就在此填入默认的数据的值
     * @param bool|false $self      :设置为true，option的值等于$value
     * @param null       $attrArray :html的属性，例如class="x1"
     * @param string     $labelClass
     */
    static public function checkbox(
        $name,
        $option,
        $default = null,
        $self = false,
        $attrArray = null,
        $labelClass = ''
    ) {
        $htmlStr = '';
        $attrStr = self::inputAttr($attrArray);
        $default = array_flip(explode(self::$checkboxValueSplit, $default));

        foreach ($option as $key => $value) {
            if ($self) {
                $key = $value;
            }

            if (isset($default[$key])) {
                $htmlStr .= "<label class='$labelClass'><input type=\"checkbox\" name=\"{$name}[]\" id=\"{$name}_$key\" value=\"$key\" checked=\"checked\" {$attrStr} />" . $value . '</label>';
            } else {
                $htmlStr .= "<label class='$labelClass'><input type=\"checkbox\" name=\"{$name}[]\" id=\"{$name}_$key\" value=\"$key\"  {$attrStr} />" . $value . '</label>';
            }
        }

        return $htmlStr;
    }

    /**
     * 文件上传表单
     *
     * @param        $name      :表单名称
     * @param string $value     :查看文件的地址
     * @param int    $size      :表单的大小
     * @param null   $attrArray :html的属性，例如class="x1"
     * @return string
     */
    static public function upload($name, $value = '', $size = 50, $attrArray = null) {
        $attrStr = self::inputAttr($attrArray);
        $form    = '';
        if (!empty($value)) {
            $form = "<a href='$value' target='_blank'>查看文件</a><br />\n重新上传";
        }

        return $form . "<input type='file' name='$name' id='{$name}' size='{$size}' {$attrStr} />";
    }

    /**
     * 单行文本输入框
     *
     * @param        $name
     * @param string $value
     * @param null   $attrArray
     * @return string
     */
    static public function input($name, $value = '', $attrArray = null) {
        $attrStr = self::inputAttr($attrArray);

        return "<input type='text' name='{$name}' id='{$name}' value='{$value}' {$attrStr} />";
    }

    /**
     * 按钮
     *
     * @param        $name
     * @param string $value
     * @param null   $attrArray
     * @return string
     */
    static public function button($name, $value = '', $attrArray = null) {
        if (empty($attrArray['type'])) {
            $attrArray['type'] = 'button';
        }
        $attrStr = self::inputAttr($attrArray);

        return "<input name='{$name}' id='{$name}' value='{$value}' {$attrStr} />";
    }

    /**
     * 密码输入框
     *
     * @param        $name
     * @param string $value
     * @param null   $attrArray
     * @return string
     */
    static public function password($name, $value = '', $attrArray = null) {
        $attrStr = self::inputAttr($attrArray);

        return "<input type='password' name='{$name}' id='{$name}' value='{$value}' {$attrStr} />";
    }

    /**
     * 多行文本输入框
     *
     * @param        $name
     * @param string $value
     * @param null   $attrArray
     * @return string
     */
    static public function text($name, $value = '', $attrArray = null) {
        if (!isset($attrArray['cols'])) {
            $attrArray['cols'] = 60;
        }
        if (!isset($attrArray['rows'])) {
            $attrArray['rows'] = 3;
        }

        $attrStr = self::inputAttr($attrArray);

        $forms = "<textarea name='{$name}' id='{$name}' $attrStr>$value</textarea>";

        return $forms;
    }

    /**
     * 隐藏项
     *
     * @param        $name
     * @param string $value
     * @param null   $attrArray
     * @return string
     */
    static public function hidden($name, $value = '', $attrArray = null) {
        $attrStr = self::inputAttr($attrArray);

        return "<input type='hidden' name='{$name}' id='{$name}' value='{$value}' {$attrStr} />";
    }

    /**
     * 表单头部
     *
     * @param            $name
     * @param string     $method
     * @param string     $action
     * @param bool|false $ifUpload
     * @param null       $attrArray
     * @return string
     */
    static public function head($name, $method = 'post', $action = '', $ifUpload = false, $attrArray = null) {
        if ($ifUpload) {
            $attrArray['enctype'] = "multipart/form-data";
        }
        $attrStr = self::inputAttr($attrArray);

        return "action='$action' method='$method' name='$name' id='$name' $attrStr";
    }

    /**
     * 设置Form Secret防止，非当前页面提交数据
     *
     * @param string     $pageName
     * @param int        $length
     * @param bool|false $return
     * @return string
     */
    static public function secret($pageName = '', $length = 32, $return = false) {
        $secret = uniqid(RandomKey::produceString($length));
        if ($return) {
            return $secret;
        } else {
            $k = 'form_' . $pageName;
            setcookie($k, $secret, 0, '/');
            if (!isset($_SESSION)) {
                session_start();
            }
            $_SESSION[$k] = $secret;
        }

        return true;
    }

    /**
     * @param            $formName
     * @param bool|false $each
     * @return bool|string
     */
    static public function js($formName, $each = false) {
        $js = "window.onload = function(){\n validator(\"$formName\");\n";
        if ($each) {
            $js .= "validator_each(\"$formName\");\n";
        }

        $js .= "};\n";

        return JS::echojs($js, true);
    }
}