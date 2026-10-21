<?php

namespace App\Enums;

enum ProductUnit: string
{
    case Pcs = 'pcs';
    case Portion = 'portion';
    case Pack = 'pack';
    case Box = 'box';
    case Bottle = 'bottle';
    case Cup = 'cup';
    case Glass = 'glass';
    case Plate = 'plate';
    case Bowl = 'bowl';
    case Bundle = 'bundle';
    case Pair = 'pair';
    case Set = 'set';
    case Sheet = 'sheet';
    case Roll = 'roll';
    case Piece = 'piece';
    case Jar = 'jar';
    case Can = 'can';
    case Kg = 'kg';
    case Gram = 'gram';
    case Liter = 'liter';

    /**
     * Label in Indonesian and English for admin/owner dropdown option.
     */
    public function dropdownLabel(): string
    {
        return match ($this) {
            self::Pcs => 'Pcs / Buah (pcs)',
            self::Portion => 'Porsi (portion)',
            self::Pack => 'Bungkus / Pack (pack)',
            self::Box => 'Kotak / Box (box)',
            self::Bottle => 'Botol (bottle)',
            self::Cup => 'Cup / Cangkir (cup)',
            self::Glass => 'Gelas (glass)',
            self::Plate => 'Piring (plate)',
            self::Bowl => 'Mangkuk (bowl)',
            self::Bundle => 'Ikat (bundle)',
            self::Pair => 'Pasang (pair)',
            self::Set => 'Set (set)',
            self::Sheet => 'Lembar (sheet)',
            self::Roll => 'Gulung (roll)',
            self::Piece => 'Butir / Biji (piece)',
            self::Jar => 'Toples / Jar (jar)',
            self::Can => 'Kaleng (can)',
            self::Kg => 'Kilogram (kg)',
            self::Gram => 'Gram (g)',
            self::Liter => 'Liter (L)',
        };
    }

    /**
     * Localized label for public display.
     */
    public function label(?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();

        return match ($this) {
            self::Pcs => 'pcs',
            self::Portion => $locale === 'en' ? 'portion' : 'porsi',
            self::Pack => $locale === 'en' ? 'pack' : 'bungkus',
            self::Box => $locale === 'en' ? 'box' : 'kotak',
            self::Bottle => $locale === 'en' ? 'bottle' : 'botol',
            self::Cup => $locale === 'en' ? 'cup' : 'cangkir',
            self::Glass => $locale === 'en' ? 'glass' : 'gelas',
            self::Plate => $locale === 'en' ? 'plate' : 'piring',
            self::Bowl => $locale === 'en' ? 'bowl' : 'mangkuk',
            self::Bundle => $locale === 'en' ? 'bundle' : 'ikat',
            self::Pair => $locale === 'en' ? 'pair' : 'pasang',
            self::Set => 'set',
            self::Sheet => $locale === 'en' ? 'sheet' : 'lembar',
            self::Roll => $locale === 'en' ? 'roll' : 'gulung',
            self::Piece => $locale === 'en' ? 'piece' : 'butir',
            self::Jar => $locale === 'en' ? 'jar' : 'toples',
            self::Can => $locale === 'en' ? 'can' : 'kaleng',
            self::Kg => 'kg',
            self::Gram => $locale === 'en' ? 'g' : 'gram',
            self::Liter => $locale === 'en' ? 'L' : 'liter',
        };
    }

    /**
     * Normalize an input value (Indonesian or English or case-variant) into a standard unit value.
     */
    public static function normalize(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $normalized = strtolower(trim($value));

        $aliases = [
            'pcs' => self::Pcs->value,
            'pc' => self::Pcs->value,
            'buah' => self::Pcs->value,
            'piece' => self::Piece->value,
            'pieces' => self::Pcs->value,
            'item' => self::Pcs->value,
            'porsi' => self::Portion->value,
            'portion' => self::Portion->value,
            'portions' => self::Portion->value,
            'bungkus' => self::Pack->value,
            'pack' => self::Pack->value,
            'paket' => self::Pack->value,
            'packet' => self::Pack->value,
            'kotak' => self::Box->value,
            'box' => self::Box->value,
            'boxes' => self::Box->value,
            'dus' => self::Box->value,
            'botol' => self::Bottle->value,
            'bottle' => self::Bottle->value,
            'bottles' => self::Bottle->value,
            'cup' => self::Cup->value,
            'cups' => self::Cup->value,
            'cangkir' => self::Cup->value,
            'gelas' => self::Glass->value,
            'glass' => self::Glass->value,
            'glasses' => self::Glass->value,
            'piring' => self::Plate->value,
            'plate' => self::Plate->value,
            'mangkuk' => self::Bowl->value,
            'mangkok' => self::Bowl->value,
            'bowl' => self::Bowl->value,
            'ikat' => self::Bundle->value,
            'bundle' => self::Bundle->value,
            'pasang' => self::Pair->value,
            'pair' => self::Pair->value,
            'pairs' => self::Pair->value,
            'set' => self::Set->value,
            'sets' => self::Set->value,
            'lembar' => self::Sheet->value,
            'sheet' => self::Sheet->value,
            'sheets' => self::Sheet->value,
            'gulung' => self::Roll->value,
            'roll' => self::Roll->value,
            'butir' => self::Piece->value,
            'biji' => self::Piece->value,
            'toples' => self::Jar->value,
            'jar' => self::Jar->value,
            'jars' => self::Jar->value,
            'kaleng' => self::Can->value,
            'can' => self::Can->value,
            'cans' => self::Can->value,
            'kg' => self::Kg->value,
            'kilogram' => self::Kg->value,
            'gram' => self::Gram->value,
            'g' => self::Gram->value,
            'liter' => self::Liter->value,
            'l' => self::Liter->value,
        ];

        return $aliases[$normalized] ?? (self::tryFrom($normalized)?->value ?? $value);
    }

    /**
     * Get the localized label for a raw unit string.
     */
    public static function labelFor(?string $unit, ?string $locale = null): string
    {
        if ($unit === null || trim($unit) === '') {
            return '';
        }

        $normalized = self::normalize($unit);
        $case = self::tryFrom((string) $normalized);

        if ($case) {
            return $case->label($locale);
        }

        return $unit;
    }
}
