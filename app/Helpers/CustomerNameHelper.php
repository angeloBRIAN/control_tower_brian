<?php

namespace App\Helpers;

/**
 * Customer name normalization helpers
 */
class CustomerNameHelper
{
    /**
     * Common prefixes to strip for matching
     * Person titles and company types
     */
    protected static array $prefixes = [
        // Company types
        'PT.', 'PT ', 'CV.', 'CV ', 'UD.', 'UD ', 'NV.', 'NV ',
        'KOPERASI ', 'YAYASAN ', 'TOKO ', 'TB.', 'TB ',
        // Personal titles
        'MR.', 'MR ', 'MRS.', 'MRS ', 'MS.', 'MS ', 'MISS.',
        'DR.', 'DR ', 'IR.', 'IR ', 'DRS.', 'DRS ',
        'HJ.', 'HJ ', 'H.', 'H ', 'NY.', 'NY ',
        'PROF.', 'PROF ', 'SH.', 'SH ', 'SE.', 'SE ',
        // Common abbreviations
        'BPK.', 'BPK ', 'IBU.', 'IBU ',
    ];

    /**
     * Normalize customer name for matching
     * Strips common titles and prefixes
     */
    public static function normalize(string $name): string
    {
        $name = strtoupper(trim($name));
        
        // Remove dots after letters (e.g., "PT." -> "PT")
        $name = preg_replace('/(\b[A-Z]+)\./', '$1 ', $name);
        
        // Clean up multiple spaces
        $name = preg_replace('/\s+/', ' ', $name);
        
        // Strip known prefixes
        foreach (self::$prefixes as $prefix) {
            $prefix = strtoupper($prefix);
            if (str_starts_with($name, $prefix)) {
                $name = trim(substr($name, strlen($prefix)));
            }
        }
        
        // Remove trailing common suffixes
        $name = preg_replace('/\s+(SH|SE|MM|MBA|S\.KOM|S\.T|ST)$/i', '', $name);
        
        return trim($name);
    }

    /**
     * Check if two names match (with normalization)
     */
    public static function matches(string $name1, string $name2): bool
    {
        return self::normalize($name1) === self::normalize($name2);
    }

    /**
     * Get the title prefix from a name if present
     */
    public static function extractTitle(string $name): ?string
    {
        $name = strtoupper(trim($name));
        
        $titles = [
            'PT', 'CV', 'UD', 'NV', 'KOPERASI', 'YAYASAN', 'TOKO', 'TB',
            'MR', 'MRS', 'MS', 'MISS', 'DR', 'IR', 'DRS',
            'HJ', 'H', 'NY', 'PROF', 'BPK', 'IBU',
        ];
        
        foreach ($titles as $title) {
            if (str_starts_with($name, $title . '.') || str_starts_with($name, $title . ' ')) {
                return $title;
            }
        }
        
        return null;
    }
}
