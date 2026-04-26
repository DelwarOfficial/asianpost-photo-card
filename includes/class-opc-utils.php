<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class Asian_Post_Photo_Card_Utils
{
    public static function extract_image_url($xpath, $source_url)
    {
        $image_url = '';

        // Schema JSON-LD
        $schemas = $xpath->query('//script[@type="application/ld+json"]');
        if ($schemas->length) {
            foreach ($schemas as $s) {
                $json = json_decode($s->nodeValue, true);
                if (!$json) continue;
                $types = ['NewsArticle', 'Article', 'ReportageNewsArticle', 'BlogPosting'];
                $graphs = isset($json['@graph']) ? $json['@graph'] : [$json];

                foreach ($graphs as $g) {
                    if (isset($g['@type']) && in_array($g['@type'], $types)) {
                        if (isset($g['image'])) {
                            if (is_string($g['image'])) {
                                $image_url = $g['image'];
                            } elseif (isset($g['image']['url'])) {
                                $image_url = $g['image']['url'];
                            } elseif (is_array($g['image']) && isset($g['image'][0])) {
                                $image_url = $g['image'][0];
                            }
                        }
                        if ($image_url) break 2;
                    }
                }
            }
        }

        // Meta tags fallback
        if (empty($image_url)) {
            $meta_queries = [
                '//meta[@property="og:image"]/@content',
                '//meta[@name="twitter:image"]/@content',
                '//link[@rel="image_src"]/@href'
            ];
            foreach ($meta_queries as $q) {
                $nodes = $xpath->query($q);
                if ($nodes->length) {
                    $image_url = $nodes->item(0)->nodeValue;
                    break;
                }
            }
        }

        // Normalize URL
        if ($image_url && strpos($image_url, 'http') !== 0) {
            if (strpos($image_url, '//') === 0) {
                $image_url = 'https:' . $image_url;
            } else {
                $parsed = parse_url($source_url);
                if ($parsed && isset($parsed['scheme']) && isset($parsed['host'])) {
                    $root = $parsed['scheme'] . '://' . $parsed['host'];
                    $image_url = rtrim($root, '/') . '/' . ltrim($image_url, '/');
                }
            }
        }

        return esc_url_raw($image_url);
    }

    public static function extract_universal_date($xpath, $language)
    {
        $timestamp = 0;

        $schemas = $xpath->query('//script[@type="application/ld+json"]');
        if ($schemas->length) {
            foreach ($schemas as $s) {
                $json = json_decode($s->nodeValue, true);
                if (!$json) continue;
                if (isset($json['datePublished'])) {
                    $timestamp = strtotime($json['datePublished']);
                    break;
                }
                if (isset($json['@graph'])) {
                    foreach ($json['@graph'] as $g) {
                        if (isset($g['datePublished'])) {
                            $timestamp = strtotime($g['datePublished']);
                            break 2;
                        }
                    }
                }
            }
        }

        if (!$timestamp) {
            $date_queries = [
                '//meta[@property="article:published_time"]/@content',
                '//meta[@property="og:updated_time"]/@content',
                '//time/@datetime'
            ];
            foreach ($date_queries as $q) {
                $nodes = $xpath->query($q);
                if ($nodes->length) {
                    $timestamp = strtotime($nodes->item(0)->nodeValue);
                    break;
                }
            }
        }

        if (!$timestamp) $timestamp = (int)current_time('timestamp');

        if ($language === 'bangla') {
            return sanitize_text_field(self::convert_to_bangla_date(wp_date('d F Y', $timestamp, new DateTimeZone('Asia/Dhaka'))));
        } else {
            return sanitize_text_field(wp_date('j F Y', $timestamp, new DateTimeZone('Asia/Dhaka')));
        }
    }

    public static function convert_to_bangla_date($english_date)
    {
        $eng = array('1', '2', '3', '4', '5', '6', '7', '8', '9', '0', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
        $bng = array('১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '۹', '০', 'জানুয়ারি', 'ফেব্রুয়ারি', 'মার্চ', 'এপ্রিল', 'মে', 'জুন', 'জুলাই', 'আগস্ট', 'সেপ্টেম্বর', 'অক্টোবর', 'নভেম্বর', 'ডিসেম্বর');
        return str_replace($eng, $bng, $english_date);
    }

    public static function generate_qr_url($url, $response, $xpath = null)
    {
        if ($xpath) {
            $nodes = $xpath->query('//link[@rel="shortlink"]/@href');
            if ($nodes->length) {
                return esc_url_raw($nodes->item(0)->nodeValue);
            }
        }
        $parsed_url = parse_url($url);
        if (!$parsed_url || !isset($parsed_url['host'])) return esc_url_raw($url);

        $host = $parsed_url['host'];
        $scheme = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : 'https://';

        if (isset($parsed_url['query'])) {
            parse_str($parsed_url['query'], $query_params);
            if (isset($query_params['p']) && is_numeric($query_params['p'])) {
                return esc_url_raw($scheme . $host . '/?p=' . intval($query_params['p']));
            }
            if (isset($query_params['page_id']) && is_numeric($query_params['page_id'])) {
                return esc_url_raw($scheme . $host . '/?page_id=' . intval($query_params['page_id']));
            }
        }

        return esc_url_raw($url);
    }
}
