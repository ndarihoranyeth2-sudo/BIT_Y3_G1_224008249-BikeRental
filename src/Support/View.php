<?php
declare(strict_types=1);

namespace App\Support;

/** Renders a template inside the shared layout. Views only render; no SQL here. */
final class View
{
    public static function render(string $view, array $data = [], ?string $layout = 'layout/layout'): void
    {
        $file = __DIR__ . '/../Views/' . $view . '.php';
        extract($data, EXTR_SKIP);
        ob_start();
        require $file;
        $content = ob_get_clean();

        if ($layout === null) {
            echo $content;
            return;
        }
        $flash = Flash::pull();
        $user  = Auth::user();
        require __DIR__ . '/../Views/' . $layout . '.php';
    }
}
