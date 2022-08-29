<?php

namespace PhpLiteAdmin;

class GetParameters {

    private $_fields;

    public function __construct(array $defaults = array()) {
        $this->_fields = $defaults;
    }

    public function __set($key, $value) {
        $this->_fields[$key] = $value;
    }

    public function __isset($key) {
        return isset($this->_fields[$key]);
    }

    public function __unset($key) {
        unset($this->_fields[$key]);
    }

    public function __get($key) {
        return $this->_fields[$key];
    }

    public function getURL(array $assoc = array(), $html = true, $prefix = '?') {
        $arg_sep = ($html ? '&amp;' : '&');
        $assoc['page'] = 'explorer';
        return $prefix . http_build_query(array_merge($this->_fields, $assoc), '', $arg_sep);
    }

    public function getLink(array $assoc = array(), $content = '[ link ]', $class = '', $title = '', $target = '') {
        return '<a href="' . $this->getURL($assoc) . '"'
                . ($class != '' ? ' class="' . $class . '"' : '')
                . ($title != '' ? ' title="' . $title . '"' : '')
                . ($target != '' ? ' target="' . $target . '"' : '')
                . '>' . $content . '</a>';
    }

    public function getForm(array $assoc = array(), $method = 'post', $upload = false, $name = '', $csrf = true) {
        $hidden = '';
        if ($method == 'get') {
            $url = '';
            foreach (array_merge($this->_fields, $assoc) as $key => $value) {
                if (!is_null($value))
                    $hidden .= '<input type="hidden" name="' . htmlencode($key) . '" value="' . htmlencode($value) . '" /> ';
            }
        } else
            $url = $this->getURL($assoc);

        if ($csrf && $method == 'post')
            $hidden .= '<input type="hidden" name="token" value="' . $_SESSION[COOKIENAME . 'token'] . '" />';

        if (!empty($_GET['page'])) {
            $hidden .= '<input type="hidden" name="page" value="'.$_GET['page'].'" />';
        }
        
        return "<form action='" . $url . "' method='" . $method . "'" .
                ($name != '' ? " name='" . $name . "'" : '') .
                ($upload ? " enctype='multipart/form-data'" : '') . ">" .
                $hidden;
    }

    public function redirect(array $assoc = array(), $message = "") {
        if ($message != "") {
            $_SESSION[COOKIENAME . 'messages'][md5($message)] = $message;
            $url = $this->getURL(array_merge($assoc, array('message' => md5($message))), false);
        } else
            $url = $this->getURL($assoc, false);

        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http');

        //header("Location: " . $protocol . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . $url, true, 302);
        //var_dump($url);
        echo '<script>window.location.href = "'. $url .'";</script>';
        //wp_redirect($url);
        exit;
    }

}
