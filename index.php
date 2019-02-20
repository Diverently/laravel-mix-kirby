<?php
/**
 * Laravel Mix helper for the Kirby CMS
 */

if (! function_exists('mix')) {
    /**
     * Get the appropriate HTML tag with the right path for the (versioned) Mix file.
     *
     * @param string $path Path as it appears in the mix-manifest.json
     *
     * @return string Either a <link> or a <script> tag, depending on the $path
     *
     * @throws \Exception
     */
    function mix($path)
    {
        static $manifest = [];

        // Get the correct $path
        if (!Str::startsWith($path, '/')) {
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
                    throw new Exception('The Mix manifest does not exists.');
                } else {
                    return false;
                }
            }

            $manifest = json_decode(F::read($manifestPath), 'json');
        }

        // Check if the manifest contains the given $path
        if (! array_key_exists($path, $manifest)) {
            if (option('debug')) {
                throw new Exception("Unable to locate Mix file: {$path}.");
            } else {
                return false;
            }
        }

        // Get the actual file path for the given $path
        $mixFilePath = $assetsDirectory . $manifest[$path];

        // Use the appropriate Kirby helper method to get the correct HTML tag
        $pathExtension = F::extension($mixFilePath);

        if (Str::contains($pathExtension, 'css')) {
            $mixFileLink = css($mixFilePath);
        } elseif (Str::contains($pathExtension, 'js')) {
            $mixFileLink = js($mixFilePath);
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
