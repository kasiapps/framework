<?php

namespace Kasi\Support\Facades;

/**
 * @method static array preloadedAssets()
 * @method static string|null cspNonce()
 * @method static string useCspNonce(string|null $nonce = null)
 * @method static \Kasi\Foundation\Vite useIntegrityKey(string|false $key)
 * @method static \Kasi\Foundation\Vite withEntryPoints(array $entryPoints)
 * @method static \Kasi\Foundation\Vite mergeEntryPoints(array $entryPoints)
 * @method static \Kasi\Foundation\Vite useManifestFilename(string $filename)
 * @method static \Kasi\Foundation\Vite createAssetPathsUsing(callable|null $resolver)
 * @method static string hotFile()
 * @method static \Kasi\Foundation\Vite useHotFile(string $path)
 * @method static \Kasi\Foundation\Vite useBuildDirectory(string $path)
 * @method static \Kasi\Foundation\Vite useScriptTagAttributes(callable|array $attributes)
 * @method static \Kasi\Foundation\Vite useStyleTagAttributes(callable|array $attributes)
 * @method static \Kasi\Foundation\Vite usePreloadTagAttributes(callable|array|false $attributes)
 * @method static \Kasi\Foundation\Vite prefetch(int|null $concurrency = null, string $event = 'load')
 * @method static \Kasi\Foundation\Vite useWaterfallPrefetching(int|null $concurrency = null)
 * @method static \Kasi\Foundation\Vite useAggressivePrefetching()
 * @method static \Kasi\Foundation\Vite usePrefetchStrategy(string|null $strategy, array $config = [])
 * @method static \Kasi\Support\HtmlString|void reactRefresh()
 * @method static string asset(string $asset, string|null $buildDirectory = null)
 * @method static string content(string $asset, string|null $buildDirectory = null)
 * @method static string|null manifestHash(string|null $buildDirectory = null)
 * @method static bool isRunningHot()
 * @method static string toHtml()
 * @method static void macro(string $name, object|callable $macro)
 * @method static void mixin(object $mixin, bool $replace = true)
 * @method static bool hasMacro(string $name)
 * @method static void flushMacros()
 *
 * @see \Kasi\Foundation\Vite
 */
class Vite extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return \Kasi\Foundation\Vite::class;
    }
}
