<?php

namespace App\Libraries;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

class Form
{
    public $id;
    public $ajax = FALSE;
    public $action = FALSE;
    public $method = 'POST';
    public $fields = [];
    public $buttons = [];
    public $tabs = FALSE;
    public $prefix;
    public $suffix;
    public $title;
    public $body;
    public $modal = FALSE;
    public $button_submit_text;
    public $button_submit_class;

    public function __construct($attributes = [])
    {
        $_attr = (object)array_merge([
            'id'     => NULL,
            'class'  => NULL,
            'action' => NULL,
            'title'  => NULL,
            'body'   => NULL,
            'tabs'   => FALSE,
            'prefix' => NULL,
            'suffix' => NULL,
        ], $attributes);
        foreach ($_attr as $_id => $_value) {
            $this->{$_id} = $_value;
        }
    }

    public function setAjax()
    {
        $this->ajax = TRUE;
    }

    public function setModal()
    {
        $this->modal = TRUE;
    }

    public function setMethod($method)
    {
        $this->method = $method;
    }

    public function setButtons($buttons)
    {
        if (is_array($buttons)) {
            foreach ($buttons as $button) {
                $this->buttons[] = $button;
            }
        }
    }

    public function setFields($fields)
    {
        if (is_array($fields)) {
            if ($this->tabs) {
                $this->fields[] = '<div><ul class="uk-tab uk-tab-top" uk-tab="connect: #uk-tab-file-info-body; animation: uk-animation-fade; swiping: false;">';
                $_first = TRUE;
                foreach ($fields as $tab) {
                    $this->fields[] = '<li class="' . ($_first ? 'uk-active' : NULL) . '"><a href="#">' . $tab['title'] . '</a></li>';
                    $_first = FALSE;
                }
                $this->fields[] = '</ul><ul id="uk-tab-file-info-body" class="uk-switcher uk-margin">';
                $_first = TRUE;
                foreach ($fields as $tab) {
                    $this->fields[] = '<li class="' . ($_first ? 'uk-active' : NULL) . '">';
                    foreach ($tab['content'] as $name => $field) {
                        if (is_string($name)) {
                            $field['form_id'] = $this->id;
                            $_field = new Fields($name, $field);
                            $this->fields[] = $_field->_render();
                        } else {
                            $this->fields[] = $field;
                        }
                    }
                    $this->fields[] = '</li>';
                    $_first = FALSE;
                }
                $this->fields[] = '</ul></div>';
            } else {
                foreach ($fields as $name => $field) {
                    if (is_string($name)) {
                        $field['form_id'] = $this->id;
                        $_field = new Fields($name, $field);
                        $this->fields[] = $_field->_render();
                    } else {
                        $this->fields[] = $field;
                    }
                }
            }
        }
    }

    public function setButtonSubmitText($text)
    {
        $this->button_submit_text = $text;
    }

    public function setButtonSubmitClass($class)
    {
        $this->button_submit_class = $class;
    }

    public function _render()
    {
        global $wrap;
        if (!count($this->fields)) return NULL;

        return View::first([
            "frontend.{$wrap['device']['template']}.forms.generate",
            'frontend.default.forms.generate',
            'backend.forms.generate',
        ], [
            '_form' => $this
        ])
            ->render(function ($view, $content) {
                return clear_html($content);
            });
    }
}

