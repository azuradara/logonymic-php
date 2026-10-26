<?php

namespace Logonymic;

final class Nymic
{
    private const TYPE = [
        ['navigator', ['system', 'vendor', 'userAgentData']],
        ['cssMedia', ['pointer']],
    ];

    private const FAMILY = [
        ['navigator', ['platform', 'deviceMemory', 'hardwareConcurrency', 'maxTouchPoints']],
        ['screen', []],
        ['canvasWebgl', ['gpu']],
    ];

    private const SIGNALS = [
        ['navigator', ['platform', 'system', 'vendor', 'oscpu']],
        ['navigator', ['deviceMemory', 'hardwareConcurrency', 'maxTouchPoints']],
        ['navigator', ['userAgentData']],
        ['language', []],
        ['timezone', []],
        ['screen', []],
        ['cssMedia', []],
        ['css', []],
        ['canvas2d', ['paint']],
        ['canvas2d', ['text']],
        ['canvas2d', ['emoji']],
        ['canvas2d', ['textMetricsSystemSum', 'emojiSet']],
        ['canvasWebgl', ['gpu']],
        ['canvasWebgl', ['extensions']],
        ['canvasWebgl', ['parameters']],
        ['offlineAudioContext', []],
        ['fonts', []],
        ['media', []],
    ];

    public function __construct(
        public readonly string $protonymic,
        public readonly string $tribunymic,
        public readonly string $neonymic,
        public readonly string $egonymic,
        public readonly string $paleonymic,
    ) {}

    /**
     * @param  string|object|array<string, mixed>  $worker
     */
    public static function of(string|object|array $worker): self
    {
        if (is_string($worker)) {
            $worker = json_decode($worker, flags: JSON_THROW_ON_ERROR);
        }

        return new self(
            self::hash(self::signals($worker, self::TYPE)),
            self::hash(self::signals($worker, self::FAMILY)),
            self::hash($worker),
            self::hash(self::egonymic($worker)),
            implode('', array_map(fn (mixed $signal) => substr(self::hash($signal), 0, 2), self::signals($worker, self::SIGNALS))),
        );
    }

    /**
     * @param  array{protonymic: string, tribunymic: string, neonymic: string, egonymic: string, paleonymic: string}  $nymic
     */
    public static function from(array $nymic): self
    {
        return new self($nymic['protonymic'], $nymic['tribunymic'], $nymic['neonymic'], $nymic['egonymic'], $nymic['paleonymic']);
    }

    public static function hash(mixed $value): string
    {
        return hash('sha256', Canonical::encode($value));
    }

    public static function similarity(string $a, string $b): float
    {
        $pairs = max(strlen($a), strlen($b)) / 2;
        $same = 0;
        for ($i = 0; $i < min(strlen($a), strlen($b)); $i += 2) {
            $same += substr($a, $i, 2) === substr($b, $i, 2) ? 1 : 0;
        }

        return $pairs ? $same / $pairs : 0.0;
    }

    public function match(self $other, float $threshold = 0.8): MatchResult
    {
        $similarity = self::similarity($this->paleonymic, $other->paleonymic);

        return new MatchResult(match (true) {
            $this->neonymic === $other->neonymic => Level::Browser,
            $this->egonymic === $other->egonymic => Level::Device,
            $similarity >= $threshold => Level::Similar,
            $this->tribunymic === $other->tribunymic => Level::Model,
            default => Level::None,
        }, $similarity);
    }

    /**
     * @return array{protonymic: string, tribunymic: string, neonymic: string, egonymic: string, paleonymic: string}
     */
    public function toArray(): array
    {
        return [
            'protonymic' => $this->protonymic,
            'tribunymic' => $this->tribunymic,
            'neonymic' => $this->neonymic,
            'egonymic' => $this->egonymic,
            'paleonymic' => $this->paleonymic,
        ];
    }

    private static function egonymic(mixed $worker): object
    {
        $fields = (array) $worker;
        unset($fields['offlineAudioContext']);
        if (isset($fields['canvas2d'])) {
            $fields['canvas2d'] = (object) array_intersect_key((array) $fields['canvas2d'], array_flip(['text', 'emoji', 'textMetricsSystemSum', 'emojiSet']));
        }

        return (object) $fields;
    }

    /**
     * @param  list<array{string, list<string>}>  $signals
     * @return list<mixed>
     */
    private static function signals(mixed $worker, array $signals): array
    {
        return array_map(function (array $signal) use ($worker) {
            [$section, $keys] = $signal;
            $value = self::field($worker, $section);

            return $keys ? array_map(fn (string $key) => self::field($value, $key), $keys) : $value;
        }, $signals);
    }

    private static function field(mixed $value, string $key): mixed
    {
        return match (true) {
            is_array($value) => $value[$key] ?? null,
            is_object($value) => $value->$key ?? null,
            default => null,
        };
    }
}
