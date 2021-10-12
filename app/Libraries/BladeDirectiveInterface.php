<?php

namespace App\Libraries;

interface BladeDirectiveInterface
{
    public function openingTag();

    public function openingHandler($expression);

    public function closingTag();

    public function closingHandler($expression);

    public function alternatingTag();

    public function alternatingHandler($expression);
}
