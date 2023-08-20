<?php
/*
Plugin Name: Custom Code Format Plugin
Description: ページ生成時に出力コードを自動整形するプラグイン。
Version: 1.0
Author: Mr. Tsutsumi
Author URI: https://github.com/titanvortex
*/

function custom_format_output($buffer) {
    // 管理画面であればそのまま出力
    if (is_admin()) {
        return $buffer;
    }

    // スクリプトタグ内の改行文字を削除する処理を追加
    $formatted_buffer = preg_replace_callback('/<script.*?<\/script>/s', function($matches) {
        return preg_replace("/[\r\n]+/", "", $matches[0]);
    }, $buffer);

    // スタイルタグ内の改行文字を削除する処理を追加
    $formatted_buffer = preg_replace_callback('/<style.*?<\/style>/s', function($matches) {
        return preg_replace("/[\r\n]+/", "", $matches[0]);
    }, $formatted_buffer);

    // 行頭の無駄な空白やタブを削除しつつ、可読性を維持する処理を追加
    $lines = explode("\n", $formatted_buffer);
    $formatted_lines = array_map('custom_trim_line', $lines);
    $formatted_buffer = implode("\n", $formatted_lines);

    // タグの直後と文字列の間にある改行文字を削除する処理を追加
    $formatted_buffer = preg_replace('/>\s+([^<]*)\s+</', '>$1<', $formatted_buffer);

    // 半角スペースが一つ以上連続する場合は、一つだけ残す処理を追加
    $formatted_buffer = preg_replace('/\s{2,}/', ' ', $formatted_buffer);

    // 空の行を削除する処理を追加
    $formatted_buffer = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $formatted_buffer);

    // テキストエリア内の文字列に関しては処理を除外
    $formatted_buffer = preg_replace_callback('/<textarea.*?<\/textarea>/s', function($matches) {
        return preg_replace("/[\r\n]+/", "", $matches[0]);
    }, $formatted_buffer);

    return $formatted_buffer;
}

function custom_trim_line($line) {
    $trimmed_line = preg_replace('/^[ \t]+/m', '', $line);
    return $trimmed_line;
}

function custom_ob_start() {
    ob_start("custom_format_output");
}

function custom_ob_end_flush() {
    ob_end_flush();
}

add_action('wp_loaded', 'custom_ob_start');
add_action('shutdown', 'custom_ob_end_flush');
