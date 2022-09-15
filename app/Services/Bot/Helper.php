<?php namespace App\Services\Bot;

use GuzzleHttp\Exception\GuzzleException;

class Helper {

    // Метод для экранирование символом ///https://core.telegram.org/bots/api#formatting-options
    public static function escapingCharacter($text) {

        $replaceCharacter = [
            '_' => '\_',
            '*' => '\*',
            '[' => '\[',
            ']' => '\]',
            '(' => '\(',
            ')' => '\)',
            '~' => '\~',
            '`' => '\`',
            '>' => '\>',
            '#' => '\#',
            '+' => '\+',
            '-' => '\-',
            '=' => '\=',
            '|' => '\|',
            '{' => '\{',
            '}' => '\}',
            '.' => '\.',
            ',' => '\,',
            '!' => '\!',
            ':' => '\:',
            '"' => '\"'
        ];

        // проходимя по тексту заменяя символы
        return str_replace(array_keys($replaceCharacter), array_values($replaceCharacter), $text);
    }



}