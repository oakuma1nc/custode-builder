<?php

declare(strict_types=1);

namespace Custode\Helpers;

use Custode\App;

final class View
{
    /**
     * @param array<string, mixed> $data
     */
    public static function render(string $template, array $data = [], ?string $layout = 'layout'): void
    {
        $root = App::$config['paths']['root'];
        extract($data, EXTR_SKIP);
        ob_start();
        require $root . '/templates/' . $template . '.php';
        $content = ob_get_clean();
        if ($layout !== null && $layout !== '') {
            require $root . '/templates/' . $layout . '.php';
        } else {
            echo $content;
        }
    }
}
