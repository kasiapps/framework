<?php

namespace Kasi\Support\Facades;

/**
 * @method static \Kasi\Validation\Validator make(array $data, array $rules, array $messages = [], array $attributes = [])
 * @method static array validate(array $data, array $rules, array $messages = [], array $attributes = [])
 * @method static void extend(string $rule, \Closure|string $extension, string|null $message = null)
 * @method static void extendImplicit(string $rule, \Closure|string $extension, string|null $message = null)
 * @method static void extendDependent(string $rule, \Closure|string $extension, string|null $message = null)
 * @method static void replacer(string $rule, \Closure|string $replacer)
 * @method static void includeUnvalidatedArrayKeys()
 * @method static void excludeUnvalidatedArrayKeys()
 * @method static void resolver(\Closure $resolver)
 * @method static \Kasi\Contracts\Translation\Translator getTranslator()
 * @method static \Kasi\Validation\PresenceVerifierInterface getPresenceVerifier()
 * @method static void setPresenceVerifier(\Kasi\Validation\PresenceVerifierInterface $presenceVerifier)
 * @method static \Kasi\Contracts\Container\Container|null getContainer()
 * @method static \Kasi\Validation\Factory setContainer(\Kasi\Contracts\Container\Container $container)
 *
 * @see \Kasi\Validation\Factory
 */
class Validator extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'validator';
    }
}
