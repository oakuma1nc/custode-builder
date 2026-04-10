<?php

declare(strict_types=1);

namespace Custode\Helpers;

final class HtmlShell
{
    public static function centeredPage(string $title, string $heading, string $message = '', string $linkHref = '/', string $linkLabel = 'Home'): string
    {
        $t = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $h = htmlspecialchars($heading, ENT_QUOTES, 'UTF-8');
        $m = $message !== '' ? '<p class="cas-subtitle cas-subtitle--mb" style="max-width:26rem;margin-left:auto;margin-right:auto;">'
            . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</p>' : '';
        $href = htmlspecialchars($linkHref, ENT_QUOTES, 'UTF-8');
        $lab = htmlspecialchars($linkLabel, ENT_QUOTES, 'UTF-8');

        return '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8">'
            . '<meta name="viewport" content="width=device-width,initial-scale=1">'
            . '<title>' . $t . '</title>'
            . '<link rel="stylesheet" href="/public/css/custode-app-shell.css">'
            . '</head><body class="cas-body">'
            . '<div class="cas-page-center"><div class="cas-page-inner">'
            . '<h1 class="cas-title cas-title--center">' . $h . '</h1>'
            . $m
            . '<a class="cas-link" href="' . $href . '">' . $lab . '</a>'
            . '</div></div></body></html>';
    }
}
