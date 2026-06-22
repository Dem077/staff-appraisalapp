<?php

namespace App\Console\Commands;

use FontLib\Font;
use Illuminate\Console\Command;

class LoadFarumaFont extends Command
{
    protected $signature = 'font:load-faruma';

    protected $description = 'Load Faruma font for DOMPDF';

    public function handle(): int
    {
        $fontPath = public_path('fonts/Faruma.otf');
        $fontStoragePath = storage_path('fonts');

        if (! file_exists($fontStoragePath)) {
            mkdir($fontStoragePath, 0755, true);
            $this->info("Created fonts directory: {$fontStoragePath}");
        }

        if (! file_exists($fontPath)) {
            $this->error("Font file not found: {$fontPath}");

            return self::FAILURE;
        }

        try {
            copy($fontPath, $fontStoragePath.'/faruma.otf');
            $this->info('Copied font to storage/fonts/faruma.otf');

            $font = Font::load($fontPath);
            $font->parse();

            $fontHash = md5_file($fontPath);
            $basename = 'faruma_normal_'.$fontHash;

            $font->saveAdobeFontMetrics($fontStoragePath.'/'.$basename.'.ufm');
            $this->info("Generated font metrics: {$basename}.ufm");

            copy($fontPath, $fontStoragePath.'/'.$basename.'.ttf');
            $this->info("Copied font binary: {$basename}.ttf");

            $installedFontsFile = $fontStoragePath.'/installed-fonts.json';
            $installedFonts = file_exists($installedFontsFile)
                ? json_decode(file_get_contents($installedFontsFile), true)
                : [];

            $installedFonts['faruma'] = [
                'normal' => $basename,
            ];

            file_put_contents($installedFontsFile, json_encode($installedFonts, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $this->info('Updated installed-fonts.json');
            $this->info('Faruma font ready — use font-family: faruma in PDF views.');

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error loading font: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
