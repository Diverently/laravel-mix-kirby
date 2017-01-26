<?php
/**
 * Laravel Mix helper for the Kirby CMS
 *
 *
 *
 * @version   1.0.0
 * @author    Robert Cordes <robert@diverently.com>
 */

if (! function_exists('mix')) {
  /**
   * Get the path to a versioned Mix file.
   *
   * @param  string  $path
   */
  function mix($path)
  {
    static $manifest;
    static $mixFilePath;
    static $pathExtension;
    static $mixFileLink;

    $manifest_path = c::get('mixManifestPath', 'assets/mix-manifest.json');
    $assets_path = c::get('mixAssetsPath', '/assets/');

    if (str::startsWith($manifest_path, '/')) {
      $manifest_path = str::substr($manifest_path, 1);
    }

    if (! str::startsWith($assets_path, '/')) {
      $assets_path = "/{$assets_path}";
    }

    if (! str::endsWith($assets_path, '/')) {
      $assets_path = "{$assets_path}/";
    }

    if (! $manifest) {
      if (! f::exists($manifest_path)) {
        // @TODO Throw an error in debug mode
        // return response::error("The Mix manifest does not exist.", 404);
        return false;
      }

      $manifest = str::parse(f::read($manifest_path), 'json');
    }

    if (! array_key_exists($path, $manifest)) {
      // @TODO Throw an error in debug mode
      // "Unable to locate Mix file: {$path}. Please check your ".
      // "webpack.mix.js output paths and try again."
      return false;
    }

    $mixFilePath = $assets_path . $manifest[$path];
    $pathExtension = f::extension($mixFilePath);

    if ('css' === $pathExtension) {
      $mixFileLink = css($mixFilePath);
    } elseif ('js' === $pathExtension) {
      $mixFileLink = js($mixFilePath);
    } else {
      // @TODO Throw an error
      // "File type not recognized"
      return false;
    }

    return $mixFileLink;
  }
}
