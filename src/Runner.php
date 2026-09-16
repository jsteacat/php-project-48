<?php

namespace Gendiff;

class Runner
{
    private const string VERSION = '1.0.0';

    private const string DOC = <<<'DOC'
Generate diff

Usage:
  gendiff (-h|--help)
  gendiff (-v|--version)
  gendiff [-f|--format <fmt>] <firstFile> <secondFile>

Options:
  -h --help         Show this screen
  -v --version      Show version
  -f --format <fmt>    Report format [default: stylish]
DOC;

    /**
     * Разбирает аргументы командной строки и запускает сравнение файлов.
     *
     * @param string[] $argv Аргументы в формате $_SERVER['argv'] — с именем скрипта в начале.
     * @param resource $stdout Поток для основного вывода.
     * @param resource $stderr Поток для сообщений об ошибках.
     *
     * @return int Код возврата процесса: 0 — успех, 1 — ошибка.
     */
    public static function run(array $argv, $stdout = STDOUT, $stderr = STDERR): int
    {
        // help/version => false, чтобы docopt не завершал процесс сам (иначе падают тесты),
        // exit => false, чтобы разбор заканчивался возвратом Response, а не exit().
        $response = \Docopt::handle(self::DOC, [
            'argv' => array_slice($argv, 1),
            'help' => false,
            'version' => false,
            'exit' => false,
        ]);

        if ($response->status !== 0) {
            fwrite($stderr, $response->output . PHP_EOL);

            return $response->status;
        }

        $data = $response->args;

        if ($data['--help'] === true) {
            fwrite($stdout, self::DOC . PHP_EOL);

            return 0;
        }

        if ($data['--version'] === true) {
            fwrite($stdout, self::VERSION . PHP_EOL);

            return 0;
        }

        // docopt возвращает значение опции массивом: ['--format' => ['stylish']].
        $format = (string) ($data['--format'][0] ?? 'stylish');
        $firstFilePath = (string) $data['<firstFile>'];
        $secondFilePath = (string) $data['<secondFile>'];

        try {
            fwrite($stdout, genDiff($firstFilePath, $secondFilePath, $format) . PHP_EOL);

            return 0;
        } catch (\Throwable $e) {
            fwrite($stderr, $e->getMessage() . PHP_EOL);

            return 1;
        }
    }
}
