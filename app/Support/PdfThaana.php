<?php

namespace App\Support;

class PdfThaana
{
    /**
     * Thaana Unicode block (U+0780–U+07BF).
     */
    public static function containsThaana(?string $text): bool
    {
        return $text !== null && $text !== '' && (bool) preg_match('/[\x{0780}-\x{07BF}]/u', $text);
    }

    /**
     * Render Dhivehi/Thaana for mPDF (proper RTL support).
     */
    public static function html(?string $text, bool $block = true, string $variant = 'default'): string
    {
        if ($text === null || $text === '') {
            return '';
        }

        $escaped = e($text);

        if (! self::containsThaana($text)) {
            return $escaped;
        }

        [$fontSize, $lineHeight, $marginTop] = match ($variant) {
            'indicator' => ['15px', '1.9', '6px'],
            default => ['14px', '1.85', '2px'],
        };

        $textStyle = "font-family: faruma; font-size: {$fontSize}; line-height: {$lineHeight}; text-align: right; direction: rtl; unicode-bidi: embed;";

        if (! $block) {
            return '<span dir="rtl" lang="dv" style="'.$textStyle.'">'.$escaped.'</span>';
        }

        return '<table width="100%" cellpadding="0" cellspacing="0" style="margin: '.$marginTop.' 0 0 0; border: none;">'
            .'<tr><td align="right" dir="rtl" lang="dv" style="'.$textStyle.' border: none; padding: 0;">'
            .$escaped
            .'</td></tr></table>';
    }
}
