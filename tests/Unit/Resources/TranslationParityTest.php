<?php

/*
 * This file is part of package:
 * Sylius RMA Plugin
 *
 * @copyright MADCODERS Team (www.madcoders.co)
 * @licence For the full copyright and license information, please view the LICENSE
 *
 * Architects of this package:
 * @author Leonid Moshko <l.moshko@madcoders.pl>
 * @author Piotr Lewandowski <p.lewandowski@madcoders.pl>
 */

declare(strict_types=1);

namespace Tests\Madcoders\SyliusRmaPlugin\Unit\Resources;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

/**
 * Every locale the plugin ships must define the same set of translation keys as English, so the UI
 * is never partly English (or shown as raw keys) in another locale.
 */
class TranslationParityTest extends TestCase
{
    private const LOCALES = ['pl', 'de', 'fr', 'it', 'es', 'sv', 'da'];

    /**
     * @dataProvider domains
     */
    public function testEveryLocaleHasTheSameKeysAsEnglish(string $domain): void
    {
        $dir = \dirname(__DIR__, 3) . '/src/Resources/translations/';
        $enKeys = array_keys($this->flatten(Yaml::parseFile($dir . $domain . '.en.yaml')));
        sort($enKeys);

        foreach (self::LOCALES as $locale) {
            $file = $dir . $domain . '.' . $locale . '.yaml';
            self::assertFileExists($file, sprintf('Missing catalogue %s.%s.yaml', $domain, $locale));

            $localeKeys = array_keys($this->flatten(Yaml::parseFile($file)));
            sort($localeKeys);

            $missing = array_values(array_diff($enKeys, $localeKeys));
            $orphan = array_values(array_diff($localeKeys, $enKeys));

            self::assertSame([], $missing, sprintf('%s.%s.yaml is missing keys: %s', $domain, $locale, implode(', ', $missing)));
            self::assertSame([], $orphan, sprintf('%s.%s.yaml has keys not in en: %s', $domain, $locale, implode(', ', $orphan)));
        }
    }

    /**
     * @return array<string, array{string}>
     */
    public static function domains(): array
    {
        return [
            'messages' => ['messages'],
            'validators' => ['validators'],
        ];
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    private function flatten(array $data, string $prefix = ''): array
    {
        $flat = [];
        foreach ($data as $key => $value) {
            $path = '' === $prefix ? (string) $key : $prefix . '.' . $key;
            if (\is_array($value)) {
                $flat += $this->flatten($value, $path);
            } else {
                $flat[$path] = $value;
            }
        }

        return $flat;
    }
}
