<?php
/*
Plugin Name: G2A Product Importer
Description: Importa produtos da API G2A e os cadastra no WordPress.
Version: 1.0
Author: Seu Nome
*/

// Função para importar produtos
function import_g2a_products() {
    $url = 'https://sandboxapi.g2a.com/v1/products?page=1&minQty=5';
    $clientId = 'qdaiciDiyMaTjxMt';
    $apiKey = '74026b3dc2c6db6a30a73e71cdb138b1e1b5eb7a97ced46689e2d28db1050875';

    $response = wp_remote_get($url, [
        'headers' => [
            'Authorization' => "{$clientId}, {$apiKey}",
        ],
    ]);

    // Log da resposta
    if (is_wp_error($response)) {
        error_log('Erro ao buscar produtos: ' . $response->get_error_message());
        return;
    }

    $products = json_decode(wp_remote_retrieve_body($response));

    // Log do conteúdo recebido
    error_log('Resposta da API: ' . print_r($products, true));

    if ($products && isset($products->documents)) {
        foreach ($products->documents as $product) {
            // Log do produto
            error_log('Produto encontrado: ' . print_r($product, true));

            // Verifica se o produto já existe
            if (!get_page_by_path($product->slug, OBJECT, 'product')) {
                // Dados do produto
                $post_data = [
                    'post_title'   => $product->name,
                    'post_content' => $product->description ?? '',
                    'post_status'  => 'publish',
                    'post_type'    => 'product',
                    'post_excerpt' => $product->slug,
                ];

                // Insere o produto no WordPress
                $post_id = wp_insert_post($post_data);

                // Verifica se a inserção foi bem-sucedida
                if (is_wp_error($post_id)) {
                    error_log('Erro ao inserir produto: ' . $post_id->get_error_message());
                } else {
                    // Define os metadados do produto
                    update_post_meta($post_id, '_regular_price', $product->price_min ?? 0);
                    update_post_meta($post_id, '_price', $product->price_min ?? 0);
                    update_post_meta($post_id, '_stock', $product->quantity ?? 0);
                    update_post_meta($post_id, '_thumbnail_id', save_product_image($product->thumbnail)); // Salva a imagem do produto
                }
            } else {
                error_log('Produto já existe: ' . $product->slug);
            }
        }
    } else {
        error_log('Nenhum produto encontrado ou resposta inválida.');
    }
}

// Função para salvar a imagem do produto
function save_product_image($image_url) {
    if (empty($image_url)) {
        return;
    }

    // Faz o upload da imagem e retorna o ID do anexo
    $upload = wp_upload_bits(basename($image_url), null, file_get_contents($image_url));

    if (isset($upload['error']) && $upload['error'] != 0) {
        error_log('Erro ao fazer upload da imagem: ' . $upload['error']);
        return;
    }

    $attachment = [
        'post_mime_type' => 'image/jpeg',
        'post_title'     => sanitize_file_name(basename($image_url)),
        'post_content'   => '',
        'post_status'    => 'inherit'
    ];

    $attach_id = wp_insert_attachment($attachment, $upload['file']);

    // Registra a imagem como um anexo
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    $attach_data = wp_generate_attachment_metadata($attach_id, $upload['file']);
    wp_update_attachment_metadata($attach_id, $attach_data);

    return $attach_id;
}

// Agende a importação de produtos
function g2a_import_schedule() {
    if (!wp_next_scheduled('g2a_import_products_event')) {
        wp_schedule_event(time(), 'hourly', 'g2a_import_products_event');
    }
}
add_action('wp', 'g2a_import_schedule');

// Hook para a importação de produtos
add_action('g2a_import_products_event', 'import_g2a_products');
