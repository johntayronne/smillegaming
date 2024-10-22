<?php
/**
 * Theme functions and definitions
 *
 * @package HelloElementor
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

define( 'HELLO_ELEMENTOR_VERSION', '3.1.1' );

if ( ! isset( $content_width ) ) {
    $content_width = 800; // Pixels.
}

if ( ! function_exists( 'hello_elementor_setup' ) ) {
    function hello_elementor_setup() {
        // Adicione suporte a várias funcionalidades do tema.
        add_theme_support( 'post-thumbnails' );
    }
}

add_action( 'after_setup_theme', 'hello_elementor_setup' );

// Função para autenticação OAuth2
function get_oauth2_token() {
    $client_id = 'nAxqxnnouJiWkTVw'; // Substitua pelo seu client_id
    $client_secret = 'JtXViYBkIBYsxAyKAjAVDOBDoQVUuBQS'; // Substitua pelo seu client_secret
    $url = 'https://api.g2a.com/oauth/token';

    $response = wp_remote_post($url, [
        'body' => [
            'grant_type' => 'client_credentials',
            'client_id' => $client_id,
            'client_secret' => $client_secret,
        ],
    ]);

    if ( is_wp_error( $response ) ) {
        return false; // Erro na requisição
    }

    $data = json_decode( wp_remote_retrieve_body( $response ), true );

    return isset( $data['access_token'] ) ? $data['access_token'] : false;
}

// Função para fazer requisições à API G2A
function g2a_api_request($endpoint, $method = 'GET', $body = null) {
    $base_url = 'https://api.g2a.com'; // URL da API
    $token = get_oauth2_token();

    if (!$token) {
        return new WP_Error('api_error', 'Erro na autenticação.');
    }

    $response = wp_remote_request($base_url . $endpoint, [
        'method'    => $method,
        'body'      => $body ? json_encode($body) : null,
        'headers'   => [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ],
    ]);

    if (is_wp_error($response)) {
        return new WP_Error('api_error', 'Erro ao fazer a requisição: ' . $response->get_error_message());
    }

    return json_decode(wp_remote_retrieve_body($response), true);
}

// Exemplo de uso: busca ofertas ao iniciar o site
add_action('init', function() {
    $offers = g2a_api_request('/v3/sales/offers');
    if (!is_wp_error($offers)) {
        // Exibir ofertas ou armazená-las conforme necessário
        error_log(print_r($offers, true)); // Apenas para teste, remova em produção
    } else {
        // Log de erro
        error_log('Erro ao obter ofertas: ' . $offers->get_error_message());
    }
});

// Exemplo de uso: reserva e criação de pedido no checkout
add_action('woocommerce_checkout_order_processed', function($order_id) {
    // Exemplo de dados de reserva
    $reservations = [
        [
            'product_id' => 10000068865001,
            'quantity' => 3,
            'additional_data' => new stdClass(),
        ],
        [
            'product_id' => 10000068865002,
            'quantity' => 2,
            'additional_data' => new stdClass(),
        ],
    ];

    // Fazer reserva
    $reservation_response = reserve_items($reservations);
    if (isset($reservation_response['reservation_id'])) {
        // Criar pedido
        $order_response = create_order($reservation_response['reservation_id'], 80201000000192);
        error_log('Pedido criado: ' . print_r($order_response, true));
    } else {
        error_log('Erro de reserva: ' . print_r($reservation_response, true));
    }
});

// Função para reservar itens
function reserve_items($reservations) {
    return g2a_api_request('/reservation', 'POST', $reservations);
}

// Função para criar um pedido
function create_order($reservation_id, $g2a_order_id) {
    return g2a_api_request('/order', 'POST', [
        'reservation_id' => $reservation_id,
        'g2a_order_id' => $g2a_order_id,
    ]);
}
