<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class AiImageEnhancerService
{
    /**
     * @return array<string, array<string, string>>
     */
    public function presets(): array
    {
        return [
            'neon_studio' => [
                'name' => 'Estudio Neón JIHOVA',
                'description' => 'Fondo Midnight Obsidian con halo Cyber Cyan & Electric Violet y pedestal holográfico.',
                'tag' => 'Catálogo & Vitrina',
            ],
            'promo_banner' => [
                'name' => 'Banner Publicitario 16:9 (Home)',
                'description' => 'Composición publicitaria para la página principal con titular, precio y badge de oferta.',
                'tag' => 'Publicidad Home',
            ],
            'cyber_glow' => [
                'name' => 'Filtro Cyber Glow',
                'description' => 'Realce cromático hiper-saturado con resplandor ultravioleta en bordes y viñeta.',
                'tag' => 'Efecto Visual',
            ],
            'clean_hd' => [
                'name' => 'Realce Minimalista Editorial',
                'description' => 'Acabado de estudio limpio en alta definición con sombra de contacto y degradado sutil.',
                'tag' => 'Estudio Premium',
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $options
     */
    public function enhance(string $sourcePath, string $preset = 'neon_studio', array $options = []): string
    {
        $resolvedSource = $this->resolveSourceImagePath($sourcePath);
        $sourceGd = $this->loadGdImage($resolvedSource);

        $outDir = public_path('uploads/products');
        if (! File::isDirectory($outDir)) {
            File::makeDirectory($outDir, 0755, true, true);
        }

        $filename = 'ai_'.Str::slug($preset).'_'.Str::lower(Str::random(10)).'.jpg';
        $destPath = $outDir.DIRECTORY_SEPARATOR.$filename;

        switch ($preset) {
            case 'promo_banner':
                $canvas = $this->renderPromoBanner($sourceGd, $options);
                break;
            case 'cyber_glow':
                $canvas = $this->renderCyberGlow($sourceGd, $options);
                break;
            case 'clean_hd':
                $canvas = $this->renderCleanHd($sourceGd, $options);
                break;
            case 'neon_studio':
            default:
                $canvas = $this->renderNeonStudio($sourceGd, $options);
                break;
        }

        imagejpeg($canvas, $destPath, 92);
        imagedestroy($canvas);
        if (is_resource($sourceGd) || $sourceGd instanceof \GdImage) {
            imagedestroy($sourceGd);
        }

        return '/uploads/products/'.$filename;
    }

    /**
     * @param  array<string, mixed>  $options
     */
    public function enhanceProduct(Product $product, string $preset = 'neon_studio', array $options = []): string
    {
        $source = $product->image_url ?? '/images/catalog/moda.jpg';
        $options['product_name'] = $options['product_name'] ?? $product->name;
        $options['badge'] = $options['badge'] ?? ($product->badge ?? 'JIHOVA AI');
        $options['price'] = $options['price'] ?? (string) $product->base_price;
        $options['store_name'] = $options['store_name'] ?? ($product->store?->business_name ?? 'JIHOVA');

        $url = $this->enhance($source, $preset, $options);

        $product->update([
            'ai_enhanced_image_url' => $url,
        ]);

        return $url;
    }

    /**
     * @param  array<string, mixed>  $params
     */
    public function generateAdBanner(Store $store, ?Product $product = null, array $params = []): string
    {
        $outDir = public_path('uploads/ads');
        if (! File::isDirectory($outDir)) {
            File::makeDirectory($outDir, 0755, true, true);
        }

        $source = $params['source_image']
            ?? ($product?->ai_enhanced_image_url ?? ($product?->image_url ?? ($store->banner_url ?? '/images/catalog/hero-mall.jpg')));

        $resolvedSource = $this->resolveSourceImagePath($source);
        $sourceGd = $this->loadGdImage($resolvedSource);

        $options = [
            'headline' => $params['headline'] ?? ($product ? $product->name : $store->business_name),
            'subline' => $params['subline'] ?? ($product ? ($product->description ?? 'Disponible con delivery express') : $store->tagline),
            'badge' => $params['badge'] ?? '✦ OFERTA EXCLUSIVA',
            'tag' => $params['tag'] ?? ($product ? 'Desde $'.number_format((float) $product->base_price, 2) : 'Tienda Verificada'),
            'store_name' => $store->business_name,
            'accent_color' => $store->accent_color ?? '#00F0FF',
        ];

        $canvas = $this->renderPromoBanner($sourceGd, $options);

        $filename = 'ad_banner_'.Str::lower(Str::random(12)).'.jpg';
        $destPath = $outDir.DIRECTORY_SEPARATOR.$filename;

        imagejpeg($canvas, $destPath, 92);
        imagedestroy($canvas);
        if (is_resource($sourceGd) || $sourceGd instanceof \GdImage) {
            imagedestroy($sourceGd);
        }

        return '/uploads/ads/'.$filename;
    }

    private function renderNeonStudio(\GdImage $src, array $options): \GdImage
    {
        $w = 800;
        $h = 800;
        $dst = imagecreatetruecolor($w, $h);

        // Fondo Midnight Obsidian
        $bg = imagecolorallocate($dst, 11, 14, 23);
        imagefilledrectangle($dst, 0, 0, $w, $h, $bg);

        // Halos de luz de fondo
        $violet = imagecolorallocatealpha($dst, 108, 40, 254, 90);
        $cyan = imagecolorallocatealpha($dst, 0, 240, 255, 100);
        imagefilledellipse($dst, (int) ($w * 0.75), (int) ($h * 0.25), 450, 450, $violet);
        imagefilledellipse($dst, (int) ($w * 0.25), (int) ($h * 0.75), 400, 400, $cyan);

        // Pedestal holográfico elíptico en la base
        for ($i = 6; $i >= 1; $i--) {
            $glow = imagecolorallocatealpha($dst, 0, 240, 255, 115 + ($i * 2));
            imagefilledellipse($dst, (int) ($w * 0.5), (int) ($h * 0.82), 520 + ($i * 12), 140 + ($i * 8), $glow);
        }
        $baseViolet = imagecolorallocatealpha($dst, 108, 40, 254, 75);
        imagefilledellipse($dst, (int) ($w * 0.5), (int) ($h * 0.82), 480, 110, $baseViolet);

        // Escalar y centrar el producto en el pedestal
        $srcW = imagesx($src);
        $srcH = imagesy($src);
        $targetW = (int) ($w * 0.78);
        $targetH = (int) ($h * 0.70);

        $ratio = min($targetW / $srcW, $targetH / $srcH);
        $finalW = (int) ($srcW * $ratio);
        $finalH = (int) ($srcH * $ratio);
        $posX = (int) (($w - $finalW) / 2);
        $posY = (int) (($h - $finalH) / 2) - 20;

        imagecopyresampled($dst, $src, $posX, $posY, 0, 0, $finalW, $finalH, $srcW, $srcH);

        // Marco perimetral neón con esquinas tecnológicas
        $borderColor = imagecolorallocate($dst, 0, 240, 255);
        $borderDark = imagecolorallocatealpha($dst, 108, 40, 254, 70);
        imagerectangle($dst, 20, 20, $w - 20, $h - 20, $borderDark);
        imagerectangle($dst, 22, 22, $w - 22, $h - 22, $borderColor);

        // Acentos angulares en las 4 esquinas
        $accent = imagecolorallocate($dst, 0, 240, 255);
        imageline($dst, 14, 14, 50, 14, $accent);
        imageline($dst, 14, 14, 14, 50, $accent);
        imageline($dst, $w - 50, 14, $w - 14, 14, $accent);
        imageline($dst, $w - 14, 14, $w - 14, 50, $accent);
        imageline($dst, 14, $h - 14, 50, $h - 14, $accent);
        imageline($dst, 14, $h - 50, 14, $h - 14, $accent);
        imageline($dst, $w - 50, $h - 14, $w - 14, $h - 14, $accent);
        imageline($dst, $w - 14, $h - 50, $w - 14, $h - 14, $accent);

        // Badge y texto de IA
        $badgeText = $options['badge'] ?? 'JIHOVA AI STUDIO · ESTUDIO NEON';
        $this->drawText($dst, $badgeText, 36, 48, 11, [0, 240, 255]);

        if (! empty($options['store_name'])) {
            $this->drawText($dst, strtoupper((string) $options['store_name']), 36, $h - 40, 12, [243, 244, 246]);
        }

        return $dst;
    }

    private function renderPromoBanner(\GdImage $src, array $options): \GdImage
    {
        $w = 1200;
        $h = 675; // 16:9
        $dst = imagecreatetruecolor($w, $h);

        // Degradado de fondo horizontal de izquierda (oscuro) a derecha (resplandor violeta/cian)
        for ($x = 0; $x < $w; $x++) {
            $ratio = $x / $w;
            $r = (int) (11 + (18 * $ratio));
            $g = (int) (14 + (10 * $ratio));
            $b = (int) (23 + (45 * $ratio));
            $col = imagecolorallocate($dst, $r, $g, $b);
            imageline($dst, $x, 0, $x, $h, $col);
        }

        // Halos de fondo derecho
        $glowViolet = imagecolorallocatealpha($dst, 108, 40, 254, 85);
        $glowCyan = imagecolorallocatealpha($dst, 0, 240, 255, 105);
        imagefilledellipse($dst, (int) ($w * 0.78), (int) ($h * 0.45), 550, 550, $glowViolet);
        imagefilledellipse($dst, (int) ($w * 0.85), (int) ($h * 0.70), 450, 300, $glowCyan);

        // Rejilla futurista sutil
        $gridCol = imagecolorallocatealpha($dst, 255, 255, 255, 120);
        for ($x = 0; $x < $w; $x += 40) {
            imageline($dst, $x, 0, $x, $h, $gridCol);
        }
        for ($y = 0; $y < $h; $y += 40) {
            imageline($dst, 0, $y, $w, $y, $gridCol);
        }

        // Producto en el tercio derecho con pedestal
        $pedestal = imagecolorallocatealpha($dst, 0, 240, 255, 80);
        imagefilledellipse($dst, (int) ($w * 0.75), (int) ($h * 0.78), 420, 110, $pedestal);

        $srcW = imagesx($src);
        $srcH = imagesy($src);
        $boxW = 460;
        $boxH = 460;
        $ratio = min($boxW / $srcW, $boxH / $srcH);
        $finalW = (int) ($srcW * $ratio);
        $finalH = (int) ($srcH * $ratio);
        $posX = (int) (($w * 0.75) - ($finalW / 2));
        $posY = (int) (($h * 0.50) - ($finalH / 2));

        imagecopyresampled($dst, $src, $posX, $posY, 0, 0, $finalW, $finalH, $srcW, $srcH);

        // Área izquierda: Textos Publicitarios
        $tag = $options['tag'] ?? 'Desde $9.50';
        $badge = $options['badge'] ?? '✦ OFERTA DESTACADA JIHOVA';
        $headline = Str::limit($options['headline'] ?? 'Promoción Especial', 34);
        $subline = Str::limit($options['subline'] ?? 'Disponible ahora con delivery express a tu puerta', 60);
        $storeName = $options['store_name'] ?? 'JIHOVA Centro Comercial';

        // Ribbon / Tag superior
        $this->drawText($dst, 'JIHOVA MALL · CAMPAÑA OFICIAL', 70, 75, 13, [0, 240, 255]);
        $this->drawText($dst, $badge, 70, 120, 11, [255, 51, 102]);

        // Titular
        $this->drawText($dst, $headline, 70, 195, 26, [243, 244, 246]);

        // Subtítulo
        $this->drawText($dst, $subline, 70, 265, 15, [203, 213, 225]);

        // Tag de precio / beneficio
        $this->drawText($dst, $tag, 70, 360, 28, [0, 240, 255]);

        // Botón CTA gráfico "Comprar Ahora"
        $btnBg = imagecolorallocate($dst, 255, 51, 102);
        imagefilledrectangle($dst, 70, 420, 310, 485, $btnBg);
        $this->drawText($dst, 'COMPRAR AHORA ->', 95, 458, 14, [255, 255, 255]);

        // Tienda promotora
        $this->drawText($dst, 'Comercio oficial: '.strtoupper($storeName), 70, 545, 11, [148, 163, 184]);

        return $dst;
    }

    private function renderCyberGlow(\GdImage $src, array $options): \GdImage
    {
        $w = imagesx($src);
        $h = imagesy($src);
        $dst = imagecreatetruecolor($w, $h);

        // Copiar original
        imagecopy($dst, $src, 0, 0, 0, 0, $w, $h);

        // Filtro de contraste y brillo
        imagefilter($dst, IMG_FILTER_CONTRAST, -15);
        imagefilter($dst, IMG_FILTER_BRIGHTNESS, 8);

        // Overlay de color cyberpunk violeta/cian en esquinas
        $violet = imagecolorallocatealpha($dst, 108, 40, 254, 110);
        $cyan = imagecolorallocatealpha($dst, 0, 240, 255, 110);
        imagefilledellipse($dst, 0, 0, (int) ($w * 0.7), (int) ($h * 0.7), $violet);
        imagefilledellipse($dst, $w, $h, (int) ($w * 0.7), (int) ($h * 0.7), $cyan);

        // Borde fino cian
        $border = imagecolorallocate($dst, 0, 240, 255);
        imagerectangle($dst, 8, 8, $w - 8, $h - 8, $border);

        $badgeText = $options['badge'] ?? 'CYBER GLOW AI';
        $this->drawText($dst, $badgeText, 20, 35, 11, [0, 240, 255]);

        return $dst;
    }

    private function renderCleanHd(\GdImage $src, array $options): \GdImage
    {
        $w = 800;
        $h = 800;
        $dst = imagecreatetruecolor($w, $h);

        // Fondo degradado suave gris carbón
        for ($y = 0; $y < $h; $y++) {
            $val = (int) (14 + (16 * ($y / $h)));
            $col = imagecolorallocate($dst, $val, $val + 2, $val + 8);
            imageline($dst, 0, $y, $w, $y, $col);
        }

        // Sombra de contacto
        $shadow = imagecolorallocatealpha($dst, 0, 0, 0, 80);
        imagefilledellipse($dst, (int) ($w * 0.5), (int) ($h * 0.85), 480, 80, $shadow);

        $srcW = imagesx($src);
        $srcH = imagesy($src);
        $targetW = (int) ($w * 0.82);
        $targetH = (int) ($h * 0.75);
        $ratio = min($targetW / $srcW, $targetH / $srcH);
        $finalW = (int) ($srcW * $ratio);
        $finalH = (int) ($srcH * $ratio);
        $posX = (int) (($w - $finalW) / 2);
        $posY = (int) (($h - $finalH) / 2) - 20;

        imagecopyresampled($dst, $src, $posX, $posY, 0, 0, $finalW, $finalH, $srcW, $srcH);

        // Marca sutil
        $this->drawText($dst, 'JIHOVA EDITORIAL · HD CLEAN', 30, 45, 10, [156, 163, 175]);

        return $dst;
    }

    private function drawText(\GdImage $dst, string $text, int $x, int $y, int $size, array $rgb): void
    {
        $fontPath = 'C:\\Windows\\Fonts\\arial.ttf';
        $color = imagecolorallocate($dst, $rgb[0], $rgb[1], $rgb[2]);

        if (file_exists($fontPath)) {
            imagettftext($dst, $size, 0, $x, $y, $color, $fontPath, $text);
        } else {
            // Fallback con built-in GD font
            $fontIdx = ($size > 18) ? 5 : (($size > 13) ? 4 : 3);
            imagestring($dst, $fontIdx, $x, $y - 12, $text, $color);
        }
    }

    private function resolveSourceImagePath(string $path): string
    {
        if (File::exists($path)) {
            return $path;
        }

        $cleaned = ltrim($path, '/\\');
        $publicPath = public_path($cleaned);
        if (File::exists($publicPath)) {
            return $publicPath;
        }

        $defaultCatalog = public_path('images/catalog/hero-mall.jpg');
        if (File::exists($defaultCatalog)) {
            return $defaultCatalog;
        }

        // Si no existe, creamos un archivo temporal de muestra
        $temp = tempnam(sys_get_temp_dir(), 'jihova_src_').'.jpg';
        $im = imagecreatetruecolor(400, 400);
        $col = imagecolorallocate($im, 30, 35, 55);
        imagefilledrectangle($im, 0, 0, 400, 400, $col);
        imagejpeg($im, $temp);
        imagedestroy($im);

        return $temp;
    }

    private function loadGdImage(string $path): \GdImage
    {
        $info = @getimagesize($path);
        $mime = $info['mime'] ?? 'image/jpeg';

        return match ($mime) {
            'image/png' => imagecreatefrompng($path),
            'image/webp' => imagecreatefromwebp($path),
            'image/gif' => imagecreatefromgif($path),
            default => imagecreatefromjpeg($path) ?: imagecreatetruecolor(400, 400),
        };
    }
}
