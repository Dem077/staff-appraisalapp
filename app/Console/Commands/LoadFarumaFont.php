<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Dompdf\Dompdf;
use FontLib\Font;

class LoadFarumaFont extends Command
{
    protected $signature = 'font:load-faruma';
    protected $description = 'Load Faruma font for DOMPDF';

    public function handle()
    {
        $fontPath = public_path('fonts/Faruma.otf');
        $fontStoragePath = storage_path('fonts');

        // Ensure storage/fonts directory exists
        if (!file_exists($fontStoragePath)) {
            mkdir($fontStoragePath, 0755, true);
            $this->info("Created fonts directory: {$fontStoragePath}");
        }

        if (!file_exists($fontPath)) {
            $this->error("Font file not found: {$fontPath}");
            return 1;
        }

        try {
            // Copy the font to storage/fonts
            $destPath = $fontStoragePath . '/faruma.otf';
            copy($fontPath, $destPath);
            $this->info("Copied font to: {$destPath}");

            // Generate font metrics using FontLib
            $font = Font::load($destPath);
            $font->parse();

            // Save the font metrics
            $font->saveAdobeFontMetrics($fontStoragePath . '/faruma.ufm');
            $this->info("Generated font metrics: {$fontStoragePath}/faruma.ufm");

            // Create installed-fonts.json if it doesn't exist
            $installedFontsFile = $fontStoragePath . '/installed-fonts.json';
            $installedFonts = file_exists($installedFontsFile)
                ? json_decode(file_get_contents($installedFontsFile), true)
                : [];

            $installedFonts['faruma'] = [
                'normal' => 'faruma',
            ];

            file_put_contents($installedFontsFile, json_encode($installedFonts, JSON_PRETTY_PRINT));
            $this->info("Updated installed-fonts.json");

            $this->info("\nFaruma font successfully loaded!");
            $this->info("You can now use 'faruma' as font-family in your PDFs.");

            return 0;
        } catch (\Exception $e) {
            $this->error("Error loading font: " . $e->getMessage());
            return 1;
        }
    }
}
