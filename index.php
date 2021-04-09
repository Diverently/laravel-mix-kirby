<?php
/**
 * Laravel Mix helper for the Kirby CMS
 */

if (! function_exists('mix')) {
    /**
     * Get the appropriate HTML tag with the right path for the (versioned) Mix file.
     *
     * @param string|array $path Path as it appears in the mix-manifest.json or an
     * array of paths to look for
     * @param string|bool|array $options Pass an array of attributes for the tag 
     * or a string/bool. A string behaves in the same way as in Kirby's `css()` 
     * and `js()` helper functions: for css files it will be used as the value 
     * of the media attribute, for js files it will determine wether or not the 
     * script is async.
     * @return string Either a <link> or a <script> tag, depending on the $path
     *
     * @throws \Exception
     */
    function mix($path, $options = null)
    {
        $kirby = kirby();

        // Handle arrays
        if (is_array($path)) {
            $assets = [];

            foreach($path as $p) {
                $assets[] = mix($p, $options);
            }

            return implode(PHP_EOL, $assets) . PHP_EOL;
        }

        static $manifest = [];

        $isAuto = Str::contains($path, '@auto');

        // Get the correct $path
        if (!Str::startsWith($path, '/') && !$isAuto) {
            $path = "/{$path}";
        }

        // Get the correct $manifestPath
        $manifestPath = option('diverently.laravel-mix-kirby.manifestPath', 'assets/mix-manifest.json');

        if (Str::startsWith($manifestPath, '/')) {
            $manifestPath = Str::substr($manifestPath, 1);
        }

        // Get the correct $assetsDirectory
        $assetsDirectory = option('diverently.laravel-mix-kirby.assetsDirectory', '/assets');

        // Get the manifest contents
        if (!$manifest) {
            if (! F::exists($manifestPath)) {
                if (option('debug')) {
                    throw new Exception('The Mix manifest does not exist.');
                } else {
                    return false;
                }
            }

            $manifest = json_decode(F::read($manifestPath), 'json');
        }

        // Get auto templates
        if ($isAuto) {
            if ($path == '@autocss') {
                $type = 'css';
            } else if($path == '@autojs') {
                $type = 'js';
            } else {
                if(option('debug')) {
                    throw new Exception("File type not recognized");
                } else {
                    return false;
                }
            }

            $path = '/'.$type.'/templates/'.$kirby->site()->page()->intendedTemplate().'.'.$type;
        }

        // Check if the manifest contains the given $path
        if (!array_key_exists($path, $manifest)) {
            if (option('debug') && !$isAuto) {
                throw new Exception("Unable to locate Mix file: {$path}.");
            } else {
                return false;
            }
        }

        // Check if Mix is in hmr mode
        $mixHmrFile = $kirby->root('index') . $assetsDirectory . '/hot';

        // Get the actual file path for the given $path
        if (F::exists($mixHmrFile)) {
            // Remove white space from string and remove ending forward slash
            $hmrHost = preg_replace('/\s+/', '', F::read($mixHmrFile));
            $hmrHost = substr($hmrHost, 0, -1);
            $mixFilePath = $hmrHost . $manifest[$path];
        } else {
            $mixFilePath = $assetsDirectory . $manifest[$path];
        }

        // Use the appropriate Kirby helper method to get the correct HTML tag
        $pathExtension = F::extension($mixFilePath);

        if (Str::contains($pathExtension, 'css')) {
            $mixFileLink = css($mixFilePath, $options);
        } elseif (Str::contains($pathExtension, 'js')) {
            $mixFileLink = js($mixFilePath, $options);
        } else {
            if (option('debug')) {
                throw new Exception("File type not recognized");
            } else {
                return false;
            }
        }

        return $mixFileLink;
    }
}
