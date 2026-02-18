<?php

namespace App\Enums;

enum ItemUnit: string
{
    case Piece = 'pcs';
    case Box = 'box';
    case Pack = 'pack';
    case Set = 'set';


    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return [
            self::Piece->value => 'Pieces (pcs)',
            self::Box->value => 'Box',
            self::Pack->value => 'Pack',
            self::Set->value => 'Set',
        ];
    }
}
